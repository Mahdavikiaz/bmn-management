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

    private function getRecommendationText(string $cat, int $priorLevel): string
    {
        if ($priorLevel <= 0) return '-';

        $rows = Recommendation::where('category', $cat)
            ->where('priority_level', $priorLevel)
            ->orderBy('id_recommendation')
            ->pluck('description')
            ->all();

        if (!count($rows)) return '-';

        return implode("\n", $rows);
    }

    private function parseRamAddSizeGb(?string $rec): int
    {
        if (!$rec) return 0;
        $t = strtolower($rec);

        if (preg_match('/(\d+)\s*\+?\s*gb\b/i', $t, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/sebesar\s*(\d+)\b/i', $t, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    private function parseStorageTargetMeta(?string $rec, ?string $currentType, int $currentStorageGb): array
    {
        $meta = [
            'target_type' => null,
            'target_size' => 0,
        ];

        if (!$rec) return $meta;
        if (!$currentType || $currentStorageGb <= 0) return $meta;

        $t = strtolower($rec);

        // default tetap pakai tipe saat ini
        $targetType = strtoupper($currentType);

        // detect "ganti"
        if (preg_match('/ganti\s*(jadi|ke)?\s*(ssd|nvme|hdd)\b/i', $t, $m)) {
            $targetType = strtoupper($m[2]);
        } else {
            if (preg_match('/(ubah|switch|convert)\s*(jadi|ke)?\s*(ssd|nvme|hdd)\b/i', $t, $m2)) {
                $targetType = strtoupper($m2[3]);
            }
        }

        // detect size
        $targetSize = 0;
        if (preg_match('/\b(\d{2,5})\s*gb\b/i', $t, $mSize)) {
            $targetSize = (int) $mSize[1];
        }

        // detect x2 / 2x / kali 2
        $hasX2 = (bool) preg_match('/\b(x2|2x|kali\s*2)\b/i', $t);
        if ($hasX2) {
            $targetSize = $currentStorageGb * 2;
        }

        // kalau tanpa angka, size pakai size sekarang
        if ($targetSize <= 0 && preg_match('/\b(ganti|ubah)\b/i', $t)) {
            $targetSize = $currentStorageGb;
        }

        // safety cap supaya ga kelewat nyari sparepart jadi ngaco
        if ($targetSize > 2048) {
            $targetSize = 2048;
        }

        $meta['target_type'] = $targetType ?: null;
        $meta['target_size'] = max(0, (int) $targetSize);

        return $meta;
    }

    // INDEX DAN FILTER
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

        // RAM
        $recRamRaw = $this->getRecommendationText('RAM', $priorRam);
        $recRam    = $this->asBullets($recRamRaw);

        // STORAGE
        $recStorageRaw = $this->getRecommendationText('STORAGE', $priorStorage);
        $recStorage    = $this->asBullets($recStorageRaw);

        // CPU
        $recCpuRaw = $this->getRecommendationText('CPU', $priorCpu);
        $recCpu    = $this->asBullets($recCpuRaw);

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
            $recRam,
            $recStorage,
            $recCpu,
            $recRamRaw,
            $recStorageRaw
        ) {
            $now = now();

            if ($this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            // HARGA RAM
            $upgradeRamPrice = null;
            $ramType = $asset->ram_type ? strtoupper($asset->ram_type) : null;
            $ramAddSize = $this->parseRamAddSizeGb($recRamRaw);

            if ($priorRam > 0 && $ramAddSize > 0 && $ramType) {
                $upgradeRamPrice = $this->findSparepartPrice('RAM', $ramType, $ramAddSize);
            }

            // HARGA STORAGE
            $upgradeStoragePrice = null;

            $storageType = $this->getStorageTypeFromSpec($spec);
            $currentStorageGb = (int) $spec->storage;

            $stoMeta = $this->parseStorageTargetMeta($recStorageRaw, $storageType, $currentStorageGb);
            $targetType = $stoMeta['target_type'] ?? null;
            $targetSize = (int) ($stoMeta['target_size'] ?? 0);

            if ($priorStorage > 0 && $targetType && $targetSize > 0) {
                $upgradeStoragePrice = $this->findSparepartPrice('STORAGE', $targetType, $targetSize);
            }

            $report = PerformanceReport::create([
                'id_user' => Auth::id(),
                'id_asset' => $asset->id_asset,
                'id_spec' => $spec->id_spec,

                'prior_ram' => $priorRam,
                'prior_storage' => $priorStorage,
                'prior_processor' => $priorCpu,

                'recommendation_ram' => $recRam,
                'recommendation_storage' => $recStorage,
                'recommendation_processor' => $recCpu,

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
