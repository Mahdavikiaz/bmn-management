<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetService;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    private function buildSpecPayload(
        Request $request,
        Asset $asset,
        ?string $storageTypeOverride = null,
        ?string $issueImageUri = null
    ): array {
        $storageType = $storageTypeOverride ?: $request->input('category_storage');

        return [
            'id_asset'        => $asset->id_asset,
            'owner_asset'     => $this->normSafe($request->owner_asset),
            'processor'       => $this->normSafe($request->processor),
            'ram'             => (int) $request->ram,
            'storage'         => (int) $request->storage,
            'os_version'      => $this->normSafe($request->os_version),
            'is_hdd'          => $storageType === 'HDD',
            'is_ssd'          => $storageType === 'SSD',
            'is_nvme'         => $storageType === 'NVME',
            'issue_note'      => $this->normSafe($request->input('issue_note')),
            'issue_image_uri' => $issueImageUri,
        ];
    }

    private function normSafe(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    private function isSameSpec(?AssetsSpecifications $latest, array $payload): bool
    {
        if (!$latest) return false;

        return (
            $this->normSafe($latest->owner_asset) === $payload['owner_asset'] &&
            $this->normSafe($latest->processor)   === $payload['processor'] &&
            (int) $latest->ram                    === (int) $payload['ram'] &&
            (int) $latest->storage                === (int) $payload['storage'] &&
            $this->normSafe($latest->os_version)  === $payload['os_version'] &&
            (bool) $latest->is_hdd                === (bool) $payload['is_hdd'] &&
            (bool) $latest->is_ssd                === (bool) $payload['is_ssd'] &&
            (bool) $latest->is_nvme               === (bool) $payload['is_nvme']
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
        }

        if ($bestSize <= 0) {
            if (in_array($priorStorage, [4, 5], true)) $bestSize = $currentSize * 2;
            else $bestSize = $currentSize;
        }

        if (!$bestType || $bestType === 'SAME_AS_SPEC') $bestType = $currentType;

        if (strtoupper($currentType) === 'HDD') $bestType = 'SSD';

        if ($bestSize > self::STORAGE_MAX_GB) $bestSize = self::STORAGE_MAX_GB;

        $meta['type'] = $bestType;
        $meta['size'] = max(0, (int)$bestSize);

        return $meta;
    }

    // Menentukan kategori pertanyaan yang berlaku berdasarkan tipe asset
    private function applicableCategories(Asset $asset): array
    {
        $base = ['RAM', 'STORAGE', 'CPU'];

        $typeName = strtoupper(trim((string) ($asset->type?->type_name ?? '')));

        if (str_contains($typeName, 'LAPTOP')) {
            $base[] = 'BATERAI';
            $base[] = 'CHARGER';
        }

        return $base;
    }

    private function findCheapestSparepartPrice(string $category): ?float
    {
        $price = Sparepart::where('category', $category)
            ->orderBy('price')
            ->value('price');

        return $price === null ? null : (float) $price;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $q = trim((string) $request->get('q', ''));
        $typeId = $request->get('id_type');
        $statusCheck = $request->get('status_check');

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

        if ($statusCheck === 'checked') {
            $assetsQuery->has('performanceReports');
        } elseif ($statusCheck === 'unchecked') {
            $assetsQuery->doesntHave('performanceReports');
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

        $latestReport = PerformanceReport::where('id_asset', $asset->id_asset)
            ->latest()
            ->first();

        $hasPreviousReport = !is_null($latestReport);

        $categories = $this->applicableCategories($asset);

        $questions = IndicatorQuestion::with(['options' => function ($q) {
                $q->orderBy('label');
            }])
            ->whereIn('category', $categories)
            ->orderByRaw("FIELD(category,'RAM','STORAGE','CPU','BATERAI','CHARGER')")
            ->orderBy('id_question')
            ->get()
            ->groupBy('category');

        return view('admin.asset_checks.create', compact(
            'asset',
            'latestSpec',
            'latestReport',
            'hasPreviousReport',
            'questions',
            'categories'
        ));
    }

    public function storeServiceDirect(Request $request, Asset $asset)
    {
        $this->authorize('create', PerformanceReport::class);

        $validated = $request->validate([
            'service_id' => ['nullable', 'integer'],
            'service_date' => ['required', 'date'],
            'service_description' => ['required', 'string', 'max:5000'],
        ]);

        $service = null;

        if (!empty($validated['service_id'])) {
            $service = AssetService::where('id_service', $validated['service_id'])
                ->where('id_asset', $asset->id_asset)
                ->first();

            if (!$service) {
                return response()->json([
                    'message' => 'Data perbaikan tidak ditemukan atau tidak sesuai dengan asset ini.'
                ], 404);
            }

            $service->update([
                'service_date' => $validated['service_date'],
                'service_description' => $this->normSafe($validated['service_description']),
            ]);
        } else {
            $service = AssetService::create([
                'id_asset' => $asset->id_asset,
                'service_date' => $validated['service_date'],
                'service_description' => $this->normSafe($validated['service_description']),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data perbaikan berhasil disimpan.',
            'service' => [
                'id_service' => $service->id_service,
                'service_date' => $service->service_date,
                'service_description' => $service->service_description,
            ],
        ]);
    }

    public function store(Request $request, Asset $asset)
    {
        $this->authorize('create', PerformanceReport::class);

        $request->validate([
            'category_storage'       => 'nullable|in:HDD,SSD,NVME',
            'owner_asset'            => 'required|string|max:255',
            'processor'              => 'required|string|max:255',
            'ram'                    => 'required|integer|min:0',
            'storage'                => 'required|integer|min:0',
            'os_version'             => 'nullable|string|max:255',
            'gpu'                    => 'nullable|string|max:255',
            'ram_type'               => 'nullable|in:DDR3,DDR4,DDR5',
            'answers'                => 'required|array|min:1',
            'issue_note'             => 'nullable|string|max:5000',
            'issue_image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

            'service_confirmation'   => 'nullable|in:0,1',
            'service_modal_answered' => 'nullable|in:0,1',
            'service_saved_directly' => 'nullable|in:0,1',
            'service_id'             => 'nullable|integer',
            'service_date'           => 'nullable|date',
            'service_description'    => 'nullable|string|max:5000',
        ]);

        $categories = $this->applicableCategories($asset);

        $hasPreviousReport = PerformanceReport::where('id_asset', $asset->id_asset)->exists();
        $serviceConfirmation = $request->input('service_confirmation') === '1';
        $serviceSavedDirectly = $request->input('service_saved_directly') === '1';

        if ($hasPreviousReport && $serviceConfirmation && !$serviceSavedDirectly) {
            return back()
                ->withErrors(['service_confirmation' => 'Data perbaikan harus disimpan terlebih dahulu sebelum melanjutkan pengecekan.'])
                ->withInput();
        }

        if ($serviceSavedDirectly) {
            $savedService = AssetService::where('id_service', $request->input('service_id'))
                ->where('id_asset', $asset->id_asset)
                ->first();

            if (!$savedService) {
                return back()
                    ->withErrors(['service_id' => 'Data perbaikan tidak ditemukan. Silakan simpan ulang data perbaikan.'])
                    ->withInput();
            }
        }

        // wajib jawab semua pertanyaan yang berlaku untuk tipe asset ini
        $allQuestions = IndicatorQuestion::whereIn('category', $categories)
            ->select('id_question', 'category')
            ->get();

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

        // rata-rata star value per kategori
        $avgByCat = $selectedOptions
            ->groupBy(fn($o) => $o->question->category)
            ->map(fn($items) => round($items->avg('star_value'), 2));

        // mapping avg -> priority (1-5)
        $prior = function (?float $avg): int {
            if ($avg === null) return 0;
            $p = (int) ceil((5 - $avg) + 1);
            return max(1, min(5, $p));
        };

        $priorRam     = $prior($avgByCat['RAM'] ?? null);
        $priorStorage = $prior($avgByCat['STORAGE'] ?? null);
        $priorCpu     = $prior($avgByCat['CPU'] ?? null);

        // BATERAI/CHARGER hanya kalau kategori berlaku (Laptop)
        $priorBaterai = in_array('BATERAI', $categories, true) ? $prior($avgByCat['BATERAI'] ?? null) : null;
        $priorCharger = in_array('CHARGER', $categories, true) ? $prior($avgByCat['CHARGER'] ?? null) : null;

        // rekomendasi raw
        $ramActionRaw         = $this->getRecommendationActionRaw('RAM', $priorRam);
        $cpuActionRaw         = $this->getRecommendationActionRaw('CPU', $priorCpu);
        $storageBaseActionRaw = $this->getRecommendationActionRaw('STORAGE', $priorStorage);

        $bateraiActionRaw = ($priorBaterai !== null) ? $this->getRecommendationActionRaw('BATERAI', $priorBaterai) : '-';
        $chargerActionRaw = ($priorCharger !== null) ? $this->getRecommendationActionRaw('CHARGER', $priorCharger) : '-';

        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $storageTypeForPayload = $request->input('category_storage');
        if (!$storageTypeForPayload && $latestSpec) {
            $storageTypeForPayload = $this->getStorageTypeFromSpec($latestSpec);
        }

        // Upload foto keluhan
        $issueImageUri = null;
        if ($request->hasFile('issue_image')) {
            $file = $request->file('issue_image');

            $dir = 'asset_issues/' . $asset->id_asset . '/' . now()->format('Y-m');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs($dir, $filename, 'public');
            $issueImageUri = Storage::disk('public')->url($path);
        }

        $specPayload = $this->buildSpecPayload($request, $asset, $storageTypeForPayload, $issueImageUri);

        // Update data GPU & RAM type ke tabel assets
        $asset->update([
            'gpu' => $this->normSafe($request->gpu),
            'ram_type' => $this->normSafe($request->ram_type),
        ]);

        $report = DB::transaction(function () use (
            $asset,
            $latestSpec,
            $specPayload,
            $selectedOptions,
            $priorRam,
            $priorStorage,
            $priorCpu,
            $priorBaterai,
            $priorCharger,
            $ramActionRaw,
            $cpuActionRaw,
            $storageBaseActionRaw,
            $bateraiActionRaw,
            $chargerActionRaw
        ) {
            $now = now();

            $hasIssue = !empty($specPayload['issue_note']) || !empty($specPayload['issue_image_uri']);

            if (!$hasIssue && $this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            $storageType = $this->getStorageTypeFromSpec($spec);
            $currentStorageGb = (int) $spec->storage;

            $storageActionRawFinal = $this->buildStorageActionRaw(
                $storageBaseActionRaw,
                $storageType,
                $currentStorageGb
            );

            $recRamFinal     = $this->asBullets($ramActionRaw);
            $recCpuFinal     = $this->asBullets($cpuActionRaw);
            $recStorageFinal = $this->asBullets($storageActionRawFinal);

            $recBateraiFinal = ($priorBaterai !== null) ? $this->asBullets($bateraiActionRaw) : null;
            $recChargerFinal = ($priorCharger !== null) ? $this->asBullets($chargerActionRaw) : null;

            // RAM price
            $upgradeRamPrice = null;
            $ramMeta = $this->resolveRamUpgradeMeta($asset, $spec, $priorRam);
            if (!empty($ramMeta['type']) && !empty($ramMeta['size'])) {
                $upgradeRamPrice = $this->findSparepartPrice('RAM', $ramMeta['type'], (int)$ramMeta['size']);
            }

            // STORAGE price (hanya prior 4/5)
            $upgradeStoragePrice = null;
            if (in_array($priorStorage, [4, 5], true)) {
                $stoMeta = $this->resolveStorageUpgradeMeta($spec, $priorStorage);
                if (!empty($stoMeta['type']) && !empty($stoMeta['size'])) {
                    $upgradeStoragePrice = $this->findSparepartPrice('STORAGE', $stoMeta['type'], (int)$stoMeta['size']);
                }
            }

            $upgradeBateraiPrice = null;
            if (!is_null($priorBaterai) && in_array($priorBaterai, [4, 5], true)) {
                $upgradeBateraiPrice = $this->findCheapestSparepartPrice('BATERAI');
            }

            $upgradeChargerPrice = null;
            if (!is_null($priorCharger) && in_array($priorCharger, [4, 5], true)) {
                $upgradeChargerPrice = $this->findCheapestSparepartPrice('CHARGER');
            }

            $report = PerformanceReport::create([
                'id_user' => Auth::id(),
                'id_asset' => $asset->id_asset,
                'id_spec' => $spec->id_spec,

                'prior_ram' => $priorRam,
                'prior_storage' => $priorStorage,
                'prior_processor' => $priorCpu,

                'prior_baterai' => $priorBaterai,
                'prior_charger' => $priorCharger,

                'recommendation_ram' => $recRamFinal,
                'recommendation_storage' => $recStorageFinal,
                'recommendation_processor' => $recCpuFinal,

                'recommendation_baterai' => $recBateraiFinal,
                'recommendation_charger' => $recChargerFinal,

                'upgrade_ram_price' => $this->priceOrZero($upgradeRamPrice),

                'upgrade_storage_price' => in_array($priorStorage, [4, 5], true)
                    ? $this->priceOrZero($upgradeStoragePrice)
                    : null,

                'upgrade_baterai_price' => !is_null($priorBaterai) && in_array($priorBaterai, [4, 5], true)
                    ? $this->priceOrZero($upgradeBateraiPrice)
                    : null,

                'upgrade_charger_price' => !is_null($priorCharger) && in_array($priorCharger, [4, 5], true)
                    ? $this->priceOrZero($upgradeChargerPrice)
                    : null,

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

        if ((int)$report->id_asset !== (int)$asset->id_asset) abort(404);

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

        if ((int)$report->id_asset !== (int)$asset->id_asset) abort(404);

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