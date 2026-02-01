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

    private function getStorageTypeFromSpec(AssetsSpecifications $spec): ?string
    {
        if ($spec->is_nvme) return 'NVME';
        if ($spec->is_ssd)  return 'SSD';
        if ($spec->is_hdd)  return 'HDD';
        return null;
    }

    private function buildSpecPayload(Request $request, Asset $asset, ?string $storageTypeOverride = null): array
    {
        $storageType = $storageTypeOverride ?: $request->input('category_storage');

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

    private function findSparepartPrice(string $category, string $type, int $size): ?float
    {
        $price = Sparepart::where('category', $category)
            ->where('sparepart_type', strtoupper($type))
            ->where('size', $size)
            ->orderBy('price')
            ->value('price');

        return $price === null ? null : (float) $price;
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

    private function uniqueLines(array $lines): array
    {
        $seen = [];
        $out = [];
        foreach ($lines as $ln) {
            $ln = trim((string) $ln);
            if ($ln === '' || $ln === '-') continue;
            $key = mb_strtolower($ln);
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = $ln;
        }
        return $out;
    }

    private function getRecommendationActionRaw(string $cat, int $priorLevel): string
    {
        if ($priorLevel <= 0) return '-';

        $rows = Recommendation::where('category', $cat)
            ->where('priority_level', $priorLevel)
            ->orderBy('id_recommendation')
            ->pluck('action')
            ->map(fn($x) => trim((string) $x))
            ->filter(fn($x) => $x !== '')
            ->values()
            ->all();

        return count($rows) ? implode("\n", $rows) : '-';
    }

    private function buildStorageActionRaw(string $baseActionRaw, ?string $storageType, int $currentStorageGb): string
    {
        $actions = [];

        $base = trim((string) $baseActionRaw);
        if ($base !== '' && $base !== '-') {
            foreach (preg_split("/\r\n|\n|\r/", $base) ?: [] as $ln) {
                $ln = trim($ln);
                if ($ln !== '') $actions[] = $ln;
            }
        }

        if (strtoupper((string) $storageType) === 'HDD') {
            array_unshift($actions, 'Ganti jadi SSD');
        }

        if ($currentStorageGb >= self::STORAGE_MAX_GB) {
            array_unshift($actions, 'Kapasitas Storage sudah maksimal, silakan hapus berbagai software yang tidak digunakan');
        }

        $actions = $this->uniqueLines($actions);
        return count($actions) ? implode("\n", $actions) : '-';
    }

    private function resolveRamUpgradeMeta(Asset $asset, AssetsSpecifications $spec, int $priorRam): array
    {
        $meta = ['type' => null, 'size' => 0];
        if ($priorRam <= 0) return $meta;

        $rows = Recommendation::where('category', 'RAM')
            ->where('priority_level', $priorRam)
            ->orderBy('id_recommendation')
            ->get(['target_type', 'size_mode', 'target_size_gb', 'target_multiplier']);

        $bestSize = 0;
        $bestType = null;

        foreach ($rows as $r) {
            $mode = $r->size_mode ? trim((string)$r->size_mode) : null;
            if (!$mode) continue;

            $type = $r->target_type ? strtoupper(trim((string)$r->target_type)) : null;

            $size = 0;
            if ($mode === 'fixed') {
                $size = (int) ($r->target_size_gb ?? 0);
            } elseif ($mode === 'multiplier') {
                $mul = (float) ($r->target_multiplier ?? 0);
                if ($mul > 0) $size = (int) round(((int)$spec->ram) * $mul);
            }

            if ($size > $bestSize) {
                $bestSize = $size;
                $bestType = $type;
            }
        }

        if ($bestSize <= 0) {
            $map = [3 => 4, 4 => 8, 5 => 16];
            $bestSize = $map[$priorRam] ?? 0;
            $bestType = 'SAME_AS_SPEC';
        }

        if ($bestType === 'SAME_AS_SPEC' || !$bestType) {
            $bestType = $asset->ram_type ? strtoupper($asset->ram_type) : null;
        }

        $meta['type'] = $bestType;
        $meta['size'] = max(0, (int)$bestSize);

        return $meta;
    }

    private function resolveStorageUpgradeMeta(AssetsSpecifications $spec, int $priorStorage): array
    {
        $meta = ['type' => null, 'size' => 0];
        if ($priorStorage <= 0) return $meta;

        $currentType = $this->getStorageTypeFromSpec($spec);
        $currentSize = (int) $spec->storage;
        if (!$currentType || $currentSize <= 0) return $meta;

        $rows = Recommendation::where('category', 'STORAGE')
            ->where('priority_level', $priorStorage)
            ->orderBy('id_recommendation')
            ->get(['target_type', 'size_mode', 'target_size_gb', 'target_multiplier']);

        $bestType = null;
        $bestSize = 0;

        foreach ($rows as $r) {
            $mode = $r->size_mode ? trim((string)$r->size_mode) : null;
            if (!$mode) continue;

            $type = $r->target_type ? strtoupper(trim((string)$r->target_type)) : null;

            $size = 0;
            if ($mode === 'fixed') {
                $size = (int) ($r->target_size_gb ?? 0);
            } elseif ($mode === 'multiplier') {
                $mul = (float) ($r->target_multiplier ?? 0);
                if ($mul > 0) $size = (int) round($currentSize * $mul);
            }

            if ($size <= 0) continue;

            if ($size > $bestSize) {
                $bestSize = $size;
                $bestType = $type;
                continue;
            }

            // size sama
            if ($size === $bestSize) {
                $candidate = $type;

                // SAME_AS_SPEC > currentType > other
                $score = function($t) use ($currentType) {
                    $t = strtoupper((string)$t);
                    if ($t === 'SAME_AS_SPEC') return 3;
                    if ($t === strtoupper($currentType)) return 2;
                    if (in_array($t, ['SSD','NVME','HDD'], true)) return 1;
                    return 0;
                };

                if ($score($candidate) > $score($bestType)) {
                    $bestType = $candidate;
                }
            }
        }

        // fallback size
        if ($bestSize <= 0) {
            if (in_array($priorStorage, [4, 5], true)) {
                $bestSize = $currentSize * 2;
            } else {
                $bestSize = $currentSize;
            }
        }

        // resolve type
        if (!$bestType || $bestType === 'SAME_AS_SPEC') {
            $bestType = $currentType;
        }

        // HDD selalu ke SSD
        if (strtoupper($currentType) === 'HDD') {
            $bestType = 'SSD';
        }

        if ($bestSize > self::STORAGE_MAX_GB) $bestSize = self::STORAGE_MAX_GB;

        $meta['type'] = $bestType;
        $meta['size'] = max(0, (int)$bestSize);

        return $meta;
    }

    // INDEX
    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $q = trim((string) $request->get('q', ''));
        $typeId = $request->get('id_type');

        $assetsQuery = Asset::query()
            ->with(['latestPerformanceReport', 'type'])
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

    // CREATE
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

        return view('admin.asset_checks.create', compact('asset', 'latestSpec', 'questions', 'categories'));
    }

    // STORE
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

        // ACTION
        $ramActionRaw         = $this->getRecommendationActionRaw('RAM', $priorRam);
        $cpuActionRaw         = $this->getRecommendationActionRaw('CPU', $priorCpu);
        $storageBaseActionRaw = $this->getRecommendationActionRaw('STORAGE', $priorStorage);

        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        // ambil storage type dari latest spec kalo user tidak isi category_storage
        $storageTypeForPayload = $request->input('category_storage');
        if (!$storageTypeForPayload && $latestSpec) {
            $storageTypeForPayload = $this->getStorageTypeFromSpec($latestSpec);
        }

        $specPayload = $this->buildSpecPayload($request, $asset, $storageTypeForPayload);

        $report = DB::transaction(function () use (
            $asset,
            $latestSpec,
            $specPayload,
            $selectedOptions,
            $priorRam,
            $priorStorage,
            $priorCpu,
            $ramActionRaw,
            $cpuActionRaw,
            $storageBaseActionRaw
        ) {
            $now = now();

            // spec
            if ($this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            // STORAGE action
            $storageType = $this->getStorageTypeFromSpec($spec);
            $currentStorageGb = (int) $spec->storage;

            $storageActionRawFinal = $this->buildStorageActionRaw(
                $storageBaseActionRaw,
                $storageType,
                $currentStorageGb
            );

            // simpan action
            $recRamFinal     = $this->asBullets($ramActionRaw);
            $recCpuFinal     = $this->asBullets($cpuActionRaw);
            $recStorageFinal = $this->asBullets($storageActionRawFinal);

            // ESTIMASI HARGA
            $upgradeRamPrice = null;
            $ramMeta = $this->resolveRamUpgradeMeta($asset, $spec, $priorRam);
            if (!empty($ramMeta['type']) && !empty($ramMeta['size'])) {
                $upgradeRamPrice = $this->findSparepartPrice('RAM', $ramMeta['type'], (int)$ramMeta['size']);
            }

            $upgradeStoragePrice = null;
            $stoMeta = $this->resolveStorageUpgradeMeta($spec, $priorStorage);
            if (!empty($stoMeta['type']) && !empty($stoMeta['size'])) {
                $upgradeStoragePrice = $this->findSparepartPrice('STORAGE', $stoMeta['type'], (int)$stoMeta['size']);
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

    // SHOW
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

    // DESTROY REPORT
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

    // HISTORY
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
