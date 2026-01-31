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
        // Cari harga sparepart yang cocok
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

    // Ambil ACTION dari tabel recommendations (berdasarkan category + priority_level)
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

    // STORAGE TEXT GENERATOR
    private function buildStorageActionRaw(string $baseActionRaw, ?string $storageType, int $currentStorageGb): string
    {
        $actions = [];

        // base actions dari DB
        $base = trim((string) $baseActionRaw);
        if ($base !== '' && $base !== '-') {
            foreach (preg_split("/\r\n|\n|\r/", $base) ?: [] as $ln) {
                $ln = trim($ln);
                if ($ln !== '') $actions[] = $ln;
            }
        }

        // HDD -> ganti SSD
        if (strtoupper((string) $storageType) === 'HDD') {
            array_unshift($actions, 'Ganti jadi SSD untuk performa lebih baik');
        }

        // kapasitas maksimal
        if ($currentStorageGb >= self::STORAGE_MAX_GB) {
            array_unshift($actions, 'Kapasitas Storage sudah maksimal, silakan hapus software/file tidak terpakai');
        }

        $actions = $this->uniqueLines($actions);

        return count($actions) ? implode("\n", $actions) : '-';
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

        // Validasi Input
        $request->validate([
            'category_storage' => 'nullable|in:HDD,SSD,NVME',
            'owner_asset'      => 'required|string|max:255',
            'processor'        => 'required|string|max:255',
            'ram'              => 'required|integer|min:0',
            'storage'          => 'required|integer|min:0',
            'os_version'       => 'nullable|string|max:255',
            'answers'          => 'required|array|min:1',
        ]);

        // Validasi kelengkapan jawaban
        $allQuestions = IndicatorQuestion::select('id_question', 'category')->get();
        foreach ($allQuestions as $q) {
            if (!$request->filled("answers.{$q->id_question}")) {
                return back()->withErrors(['answers' => 'Semua pertanyaan wajib dijawab.'])->withInput();
            }
        }

        $selectedOptionIds = array_values($request->input('answers', []));
        $selectedOptions = IndicatorOption::with('question')
            ->whereIn('id_option', $selectedOptionIds)
            ->get();

        if ($selectedOptions->count() !== count($selectedOptionIds)) {
            return back()->withErrors(['answers' => 'Ada jawaban yang tidak valid.'])->withInput();
        }

        // Hitung Rata-rata & Priority Level
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

        // Ambil Teks Rekomendasi yg Action dari DB untuk display
        $ramActionRaw         = $this->getRecommendationActionRaw('RAM', $priorRam);
        $cpuActionRaw         = $this->getRecommendationActionRaw('CPU', $priorCpu);
        $storageBaseActionRaw = $this->getRecommendationActionRaw('STORAGE', $priorStorage);

        // Persiapan data Spec
        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();
        $specPayload = $this->buildSpecPayload($request, $asset);

        // Mulai Transaksi Simpen
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

            // Simpen/Cek Spec Baru
            if ($this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            // Format Teks Rekomendasi
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

            // ESTIMASI HARGA
            $upgradeRamPrice = null;
            $ramType = $asset->ram_type ? strtoupper($asset->ram_type) : null;
            
            // cari harga hanya jika tipe RAM diketahui & priority level memenuhi syarat
            if ($ramType && $priorRam >= 3) {
                $ramSizeNeeded = 0;
                
                if ($priorRam === 3) {
                    $ramSizeNeeded = 4;
                } elseif ($priorRam === 4) {
                    $ramSizeNeeded = 8;
                } elseif ($priorRam === 5) {
                    $ramSizeNeeded = 16;
                }

                if ($ramSizeNeeded > 0) {
                    $upgradeRamPrice = $this->findSparepartPrice('RAM', $ramType, $ramSizeNeeded);
                }
            }

            // HARGA STORAGE
            $upgradeStoragePrice = null;
            $currentType = $storageType ? strtoupper($storageType) : null;
            
            if ($currentType) {
                // Tentukan Target Tipe
                $targetType = ($currentType === 'HDD') ? 'SSD' : $currentType;

                // Tentukan Target Size
                $targetSize = $currentStorageGb;
                if ($priorStorage >= 4) {
                    $targetSize = $currentStorageGb * 2;
                }

                // Max Storage
                if ($targetSize > self::STORAGE_MAX_GB) {
                    $targetSize = self::STORAGE_MAX_GB;
                }

                // Cek apakah butuh upgrade
                $isTypeChange = ($currentType === 'HDD' && $targetType === 'SSD');
                $isSizeChange = ($targetSize > $currentStorageGb);

                if (($isTypeChange || $isSizeChange) && $targetSize > 0) {
                    // Cari harga berdasarkan target type & target size
                    $upgradeStoragePrice = $this->findSparepartPrice('STORAGE', $targetType, $targetSize);
                }
            }

            // Create Performance Report
            $report = PerformanceReport::create([
                'id_user' => Auth::id(),
                'id_asset' => $asset->id_asset,
                'id_spec' => $spec->id_spec,

                'prior_ram' => $priorRam,
                'prior_storage' => $priorStorage,
                'prior_processor' => $priorCpu,

                // Teks rekomendasi
                'recommendation_ram' => $recRamFinal,
                'recommendation_storage' => $recStorageFinal,
                'recommendation_processor' => $recCpuFinal,

                // Harga hasil kalkulasi
                'upgrade_ram_price' => $this->priceOrZero($upgradeRamPrice),
                'upgrade_storage_price' => $this->priceOrZero($upgradeStoragePrice),

                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Simpen Detail Jawaban
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