<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\AssetsSpecifications;
use App\Models\IndicatorAnswer;
use App\Models\IndicatorOption;
use App\Models\IndicatorQuestion;
use App\Models\PerformanceReport;
use App\Models\Recommendation;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetCheckController extends Controller
{
    use AuthorizesRequests;

    private const STORAGE_MAX_GB = 2048;

    private function norm(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    private function buildSpecPayload(Request $request, Asset $asset): array
    {
        $storageType = $request->input('category_storage');

        return [
            'id_asset'    => $asset->id_asset,
            'owner_asset' => $this->norm($request->owner_asset),
            'processor'   => $this->norm($request->processor),
            'ram'         => (int) $request->ram,
            'storage'     => (int) $request->storage,
            'os_version'  => $this->norm($request->os_version),
            'is_hdd'      => $storageType === 'HDD',
            'is_ssd'      => $storageType === 'SSD',
            'is_nvme'     => $storageType === 'NVME',
        ];
    }

    private function isSameSpec(?AssetsSpecifications $latest, array $payload): bool
    {
        if (!$latest) return false;

        return (
            $this->norm($latest->owner_asset)  === $payload['owner_asset'] &&
            $this->norm($latest->processor)    === $payload['processor'] &&
            (int) $latest->ram                 === (int) $payload['ram'] &&
            (int) $latest->storage             === (int) $payload['storage'] &&
            $this->norm($latest->os_version)   === $payload['os_version'] &&
            (bool) $latest->is_hdd             === (bool) $payload['is_hdd'] &&
            (bool) $latest->is_ssd             === (bool) $payload['is_ssd'] &&
            (bool) $latest->is_nvme            === (bool) $payload['is_nvme']
        );
    }

    private function getStorageTypeFromSpec(AssetsSpecifications $spec): ?string
    {
        if ($spec->is_nvme) return 'NVME';
        if ($spec->is_ssd)  return 'SSD';
        if ($spec->is_hdd)  return 'HDD';
        return null;
    }

    private function findSparepartPrice(string $category, string $type, int $size): ?float
    {
        $price = Sparepart::where('category', $category)
            ->where('sparepart_type', strtoupper($type))
            ->where('size', $size)
            ->orderBy('price')
            ->value('price');

        if ($price === null) return null;
        return (float) $price;
    }

    private function priceOrZero(?float $price): float
    {
        return $price === null ? 0.0 : (float) $price;
    }

    private function asBullets(string $text): string
    {
        $t = trim($text);
        if ($t === '' || $t === '-') return '-';

        $lines = preg_split("/\r\n|\n|\r/", $t) ?: [$t];
        $lines = array_values(array_filter(array_map('trim', $lines), fn($x) => $x !== ''));

        if (!count($lines)) return '-';

        return "• " . implode("\n• ", $lines);
    }

    // Ambil action dan explanation dari DB berdasarkan category & priority_level
    private function getRecommendationBundle(string $cat, int $priorLevel): array
    {
        $bundle = [
            'action_raw' => '-',
            'explanation_raw' => '-',
        ];

        if ($priorLevel <= 0) return $bundle;

        $rows = Recommendation::where('category', $cat)
            ->where('priority_level', $priorLevel)
            ->orderBy('id_recommendation')
            ->get(['action', 'explanation']);

        if ($rows->isEmpty()) {
            return $bundle;
        }

        $actions = $rows->pluck('action')
            ->map(fn($x) => trim((string) $x))
            ->filter(fn($x) => $x !== '')
            ->values()
            ->all();

        $explanations = $rows->pluck('explanation')
            ->map(fn($x) => trim((string) $x))
            ->filter(fn($x) => $x !== '')
            ->values()
            ->all();

        $bundle['action_raw'] = count($actions) ? implode("\n", $actions) : '-';
        $bundle['explanation_raw'] = count($explanations) ? implode("\n", $explanations) : '-';

        return $bundle;
    }

    // Format gabungan supaya di report bisa tampilin Action dan Explanation
    private function formatActionExplanation(string $actionRaw, string $explanationRaw): string
    {
        $actionRaw = trim($actionRaw) === '' ? '-' : trim($actionRaw);
        $explanationRaw = trim($explanationRaw) === '' ? '-' : trim($explanationRaw);

        $action = $this->asBullets($actionRaw);
        $explanation = $this->asBullets($explanationRaw);

        // kalau dua duanya kosong
        if ($action === '-' && $explanation === '-') return '-';

        $out = [];

        if ($action !== '-') {
            $out[] = "Action:\n" . $action;
        }
        if ($explanation !== '-') {
            $out[] = "Explanation:\n" . $explanation;
        }

        return implode("\n\n", $out);
    }

    // Parse ukuran RAM dari action untuk estimasi harga
    private function parseRamAddSizeGb(?string $recAction): int
    {
        if (!$recAction) return 0;
        $t = strtolower($recAction);

        if (preg_match('/(\d+)\s*\+?\s*gb\b/i', $t, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/sebesar\s*(\d+)\b/i', $t, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function buildStorageRecommendationBundle(array $base, ?string $storageType, int $currentStorageGb): array
    {
        $baseAction = trim((string) ($base['action_raw'] ?? '-'));
        $baseExplain = trim((string) ($base['explanation_raw'] ?? '-'));

        $actions = [];
        $explains = [];

        // base
        if ($baseAction !== '' && $baseAction !== '-') {
            foreach (preg_split("/\r\n|\n|\r/", $baseAction) ?: [] as $ln) {
                $ln = trim($ln);
                if ($ln !== '') $actions[] = $ln;
            }
        }
        if ($baseExplain !== '' && $baseExplain !== '-') {
            foreach (preg_split("/\r\n|\n|\r/", $baseExplain) ?: [] as $ln) {
                $ln = trim($ln);
                if ($ln !== '') $explains[] = $ln;
            }
        }

        // rule HDD
        if (strtoupper((string) $storageType) === 'HDD') {
            array_unshift($actions, 'Ganti jadi SSD');
        }

        // rule maksimal
        if ($currentStorageGb >= self::STORAGE_MAX_GB) {
            array_unshift($actions, 'Kapasitas Storage sudah maksimal, silakan hapus berbagai software yang tidak digunakan');
        }

        // action/explain
        $actions = $this->uniqueLines($actions);
        $explains = $this->uniqueLines($explains);

        return [
            'action_raw' => count($actions) ? implode("\n", $actions) : '-',
            'explanation_raw' => count($explains) ? implode("\n", $explains) : '-',
        ];
    }

    private function uniqueLines(array $lines): array
    {
        $seen = [];
        $out = [];
        foreach ($lines as $ln) {
            $ln = trim((string) $ln);
            if ($ln === '') continue;
            $key = mb_strtolower($ln);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = $ln;
        }
        return $out;
    }

    // Parse target STORAGE dari ACTION (untuk estimasi harga).
    private function parseStorageTargetMeta(?string $recAction, ?string $currentType, int $currentStorageGb): array
    {
        $meta = [
            'target_type' => null,
            'target_size' => 0,
        ];

        if (!$recAction) return $meta;
        if (!$currentType || $currentStorageGb <= 0) return $meta;

        $t = strtolower($recAction);

        $targetType = strtoupper($currentType);

        // detect ganti jadi SSD/NVME/HDD
        if (preg_match('/ganti\s*(jadi|ke)?\s*(ssd|nvme|hdd)\b/i', $t, $m)) {
            $targetType = strtoupper($m[2]);
        } elseif (preg_match('/(ubah|switch|convert)\s*(jadi|ke)?\s*(ssd|nvme|hdd)\b/i', $t, $m2)) {
            $targetType = strtoupper($m2[3]);
        }

        // detect size explicit "1024 GB"
        $targetSize = 0;
        if (preg_match('/\b(\d{2,5})\s*gb\b/i', $t, $mSize)) {
            $targetSize = (int) $mSize[1];
        }

        // detect x2 / 2x / kali 2
        $hasX2 = (bool) preg_match('/\b(x2|2x|kali\s*2)\b/i', $t);
        if ($hasX2) {
            $targetSize = $currentStorageGb * 2;
        }

        // kalau ada kata ganti/ubah tanpa angka => size pakai size sekarang
        if ($targetSize <= 0 && preg_match('/\b(ganti|ubah|switch|convert)\b/i', $t)) {
            $targetSize = $currentStorageGb;
        }

        // cap
        if ($targetSize > self::STORAGE_MAX_GB) {
            $targetSize = self::STORAGE_MAX_GB;
        }

        $meta['target_type'] = $targetType ?: null;
        $meta['target_size'] = max(0, (int) $targetSize);

        return $meta;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $q = trim((string) $request->get('q', ''));
        $typeId = $request->get('id_type');

        $assetsQuery = Asset::query()
            ->with([
                'latestPerformanceReport',
                'type',
            ])
            ->withCount('performanceReports');

        if ($q !== '') {
            $assetsQuery->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}%")
                  ->orWhere('device_name', 'like', "%{$q}%");
            });
        }

        if (!empty($typeId)) {
            $assetsQuery->where('id_type', (int) $typeId);
        }

        $assets = $assetsQuery
            ->latest('id_asset')
            ->paginate(10)
            ->withQueryString();

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.asset_checks.index', compact('assets', 'types'));
    }

    public function create(Asset $asset)
    {
        $this->authorize('create', PerformanceReport::class);

        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $questions = IndicatorQuestion::with(['options' => function ($q) {
            $q->orderBy('label');
        }])
            ->orderBy('category')
            ->orderBy('id_question')
            ->get()
            ->groupBy('category');

        $categories = ['RAM', 'STORAGE', 'CPU'];

        return view('admin.asset_checks.create', compact(
            'asset', 'latestSpec', 'questions', 'categories'
        ));
    }

    public function store(Request $request, Asset $asset)
    {
        $this->authorize('create', PerformanceReport::class);

        $request->validate([
            'category_storage' => 'nullable|in:HDD,SSD,NVME',
            'owner_asset'      => 'required|string|max:255',
            'processor'        => 'required|string|max:255',
            'ram'              => 'required|integer|min:0',
            'storage'          => 'required|integer|min:0',
            'os_version'       => 'nullable|string|max:255',
            'answers'          => 'required|array|min:1',
        ]);

        $allQuestions = IndicatorQuestion::select('id_question', 'category')->get();
        foreach ($allQuestions as $q) {
            if (!$request->filled("answers.{$q->id_question}")) {
                return back()
                    ->withErrors(['answers' => 'Semua pertanyaan wajib dijawab.'])
                    ->withInput();
            }
        }

        $selectedOptionIds = array_values($request->input('answers', []));
        $selectedOptions = IndicatorOption::with('question')
            ->whereIn('id_option', $selectedOptionIds)
            ->get();

        if ($selectedOptions->count() !== count($selectedOptionIds)) {
            return back()
                ->withErrors(['answers' => 'Ada jawaban yang tidak valid.'])
                ->withInput();
        }

        $mapAnswers = $request->input('answers', []);
        foreach ($selectedOptions as $opt) {
            $qid = $opt->id_question;
            if (!isset($mapAnswers[$qid]) || (int)$mapAnswers[$qid] !== (int)$opt->id_option) {
                return back()
                    ->withErrors(['answers' => 'Ada jawaban yang tidak cocok dengan pertanyaan.'])
                    ->withInput();
            }
        }

        $avgByCat = $selectedOptions
            ->groupBy(fn($o) => $o->question->category)
            ->map(fn($items) => round($items->avg('star_value'), 2));

        $prior = function (?float $avg): int {
            if ($avg === null) return 0;
            $p = (int) ceil((5 - $avg) + 1);
            return max(1, min(5, $p));
        };

        $priorRam     = $prior($avgByCat['RAM'] ?? null);
        $priorStorage = $prior($avgByCat['STORAGE'] ?? null);
        $priorCpu     = $prior($avgByCat['CPU'] ?? null);

        // ambil action dan explanation per kategori
        $ramBundle = $this->getRecommendationBundle('RAM', $priorRam);
        $cpuBundle = $this->getRecommendationBundle('CPU', $priorCpu);
        $storageBaseBundle = $this->getRecommendationBundle('STORAGE', $priorStorage);

        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $specPayload = $this->buildSpecPayload($request, $asset);

        $report = DB::transaction(function () use (
            $asset,
            $latestSpec,
            $specPayload,
            $selectedOptions,
            $priorRam,
            $priorStorage,
            $priorCpu,
            $ramBundle,
            $cpuBundle,
            $storageBaseBundle
        ) {
            $now = now();

            if ($this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            // STORAGE RULE
            $storageType = $this->getStorageTypeFromSpec($spec);
            $currentStorageGb = (int) $spec->storage;

            $storageFinalBundle = $this->buildStorageRecommendationBundle(
                $storageBaseBundle,
                $storageType,
                $currentStorageGb
            );

            // format untuk disimpan ke performance_reports
            $recRamFinal     = $this->formatActionExplanation($ramBundle['action_raw'], $ramBundle['explanation_raw']);
            $recCpuFinal     = $this->formatActionExplanation($cpuBundle['action_raw'], $cpuBundle['explanation_raw']);
            $recStorageFinal = $this->formatActionExplanation($storageFinalBundle['action_raw'], $storageFinalBundle['explanation_raw']);

            // ESTIMASI HARGA UPGRADE

            // RAM
            $upgradeRamPrice = null;
            $ramType = $asset->ram_type ? strtoupper($asset->ram_type) : null;
            $ramAddSize = $this->parseRamAddSizeGb($ramBundle['action_raw']);

            if ($priorRam > 0 && $ramAddSize > 0 && $ramType) {
                $upgradeRamPrice = $this->findSparepartPrice('RAM', $ramType, $ramAddSize);
            }

            // STORAGE
            $upgradeStoragePrice = null;

            $stoMeta = $this->parseStorageTargetMeta(
                $storageFinalBundle['action_raw'],
                $storageType,
                $currentStorageGb
            );

            $targetType = $stoMeta['target_type'] ?? null;
            $targetSize = (int) ($stoMeta['target_size'] ?? 0);

            if ($priorStorage > 0 && $targetType && $targetSize > 0) {
                $upgradeStoragePrice = $this->findSparepartPrice('STORAGE', $targetType, $targetSize);
            }

            // Simpan Report
            $report = PerformanceReport::create([
                'id_user' => Auth::id(),
                'id_asset' => $asset->id_asset,
                'id_spec' => $spec->id_spec,

                'prior_ram' => $priorRam,
                'prior_storage' => $priorStorage,
                'prior_processor' => $priorCpu,

                'recommendation_ram' => $recRamFinal,
                'recommendation_storage' => $recStorageFinal,
                'recommendation_processor' => $recCpuFinal,

                'upgrade_ram_price' => $this->priceOrZero($upgradeRamPrice),
                'upgrade_storage_price' => $this->priceOrZero($upgradeStoragePrice),

                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($selectedOptions as $opt) {
                IndicatorAnswer::create([
                    'id_option' => $opt->id_option,
                    'id_spec' => $spec->id_spec,
                    'star_rating' => $opt->star_value,
                    'datetime' => $now,
                ]);
            }

            return $report;
        });

        return redirect()
            ->route('admin.asset-checks.show', [$asset->id_asset, $report->id_report])
            ->with('success', 'Pengecekan asset berhasil diproses.');
    }

    public function show(Asset $asset, PerformanceReport $report)
    {
        $this->authorize('view', $report);

        if ((int)$report->id_asset !== (int)$asset->id_asset) {
            abort(404);
        }

        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $report->load(['asset', 'spec', 'user']);

        $history = PerformanceReport::where('id_asset', $asset->id_asset)
            ->latest()
            ->paginate(10);

        return view('admin.asset_checks.show', compact('asset', 'report', 'latestSpec', 'history'));
    }

    public function destroyReport(Asset $asset, PerformanceReport $report)
    {
        $this->authorize('delete', $report);

        if ((int)$report->id_asset !== (int)$asset->id_asset) {
            abort(404);
        }

        $report->delete();

        return redirect()
            ->route('admin.asset-checks.history', $asset->id_asset)
            ->with('success', 'Report pengecekan berhasil dihapus.');
    }

    public function history(Asset $asset)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $latest = PerformanceReport::where('id_asset', $asset->id_asset)->latest()->first();

        $history = PerformanceReport::where('id_asset', $asset->id_asset)
            ->latest()
            ->paginate(10);

        return view('admin.asset_checks.history', compact('asset', 'latest', 'history'));
    }
}
