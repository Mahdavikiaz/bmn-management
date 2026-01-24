<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
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

    // normalize helper buat compare string
    private function norm(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    // bikin payload spec dari request
    private function buildSpecPayload(Request $request, Asset $asset): array
    {
        // storage type dari dropdown
        $storageType = $request->input('category_storage');

        return [
            'id_asset'   => $asset->id_asset,
            'processor'  => $this->norm($request->processor),
            'ram'        => (int) $request->ram,
            'storage'    => (int) $request->storage,
            'os_version' => $this->norm($request->os_version),
            'is_hdd'     => $storageType === 'HDD',
            'is_ssd'     => $storageType === 'SSD',
            'is_nvme'    => $storageType === 'NVME',
        ];
    }

    // cek apakah payload spec sama dengan latest spec
    private function isSameSpec(?AssetsSpecifications $latest, array $payload): bool
    {
        if (!$latest) return false;

        return (
            $this->norm($latest->processor)  === $payload['processor'] &&
            (int) $latest->ram               === (int) $payload['ram'] &&
            (int) $latest->storage           === (int) $payload['storage'] &&
            $this->norm($latest->os_version) === $payload['os_version'] &&
            (bool) $latest->is_hdd           === (bool) $payload['is_hdd'] &&
            (bool) $latest->is_ssd           === (bool) $payload['is_ssd'] &&
            (bool) $latest->is_nvme          === (bool) $payload['is_nvme']
        );
    }

    public function index()
    {
        $this->authorize('viewAny', PerformanceReport::class);

        // list asset + info apakah sudah pernah dicek
        $assets = Asset::query()
            ->with(['latestPerformanceReport'])
            ->withCount('performanceReports')
            ->latest()
            ->paginate(10);

        return view('admin.asset_checks.index', compact('assets'));
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

            // spec
            'processor' => 'required|string|max:255',
            'ram' => 'required|integer|min:0',
            'storage' => 'required|integer|min:0',
            'os_version' => 'nullable|string|max:255',

            // jawaban indikator
            'answers' => 'required|array|min:1',
        ]);

        // validasi semua pertanyaan terjawab
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

        // mapping jawaban, harus match antara question sm optionnya
        $mapAnswers = $request->input('answers', []);
        foreach ($selectedOptions as $opt) {
            $qid = $opt->id_question;
            if (!isset($mapAnswers[$qid]) || (int)$mapAnswers[$qid] !== (int)$opt->id_option) {
                return back()
                    ->withErrors(['answers' => 'Ada jawaban yang tidak cocok dengan pertanyaan.'])
                    ->withInput();
            }
        }

        // hitung rata-rata per kategori
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

        $getRecommendationText = function (string $cat, int $priorLevel): string {
            $rows = Recommendation::where('category', $cat)
                ->where('priority_level', $priorLevel)
                ->orderBy('id_recommendation')
                ->pluck('description')
                ->all();

            if (!count($rows)) return '-';
            return "• " . implode("\n• ", $rows);
        };

        $recRam     = $getRecommendationText('RAM', $priorRam);
        $recStorage = $getRecommendationText('STORAGE', $priorStorage);
        $recCpu     = $getRecommendationText('CPU', $priorCpu);

        // SPEC HANYA CREATE JIKA BERUBAH
        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $specPayload = $this->buildSpecPayload($request, $asset);

        $report = DB::transaction(function () use (
            $asset,
            $request,
            $latestSpec,
            $specPayload,
            $selectedOptions,
            $priorRam,
            $priorStorage,
            $priorCpu,
            $recRam,
            $recStorage,
            $recCpu
        ) {
            $now = now();

            // pakai spec lama kalau sama persis di form
            if ($this->isSameSpec($latestSpec, $specPayload)) {
                $spec = $latestSpec;
            } else {
                // create history spec baru
                $spec = AssetsSpecifications::create(array_merge($specPayload, [
                    'datetime' => $now,
                ]));
            }

            // estimasi harga upgrade (nullable kalau tidak ketemu / tidak perlu)
            $upgradeRamPrice = null;
            if ($priorRam >= 4) {
                $upgradeRamPrice = Sparepart::where('category', 'RAM')
                    ->where('sparepart_type', $asset->ram_type)
                    ->orderBy('price')
                    ->value('price');
            }

            $storageType = $spec->is_nvme ? 'NVME' : ($spec->is_ssd ? 'SSD' : ($spec->is_hdd ? 'HDD' : null));
            $upgradeStoragePrice = null;
            if ($priorStorage >= 4 && $storageType) {
                $upgradeStoragePrice = Sparepart::where('category', 'STORAGE')
                    ->where('sparepart_type', $storageType)
                    ->orderBy('price')
                    ->value('price');
            }

            // simpen report (historis)
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
                'upgrade_ram_price' => $upgradeRamPrice,
                'upgrade_storage_price' => $upgradeStoragePrice,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // simpen jawaban indikator
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

    // Show report tertentu, sekaligus history
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

    // Hapus report history (kayak spec history)
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

    // Halaman khusus history + latest status
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
