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

    // Mapping label star
    private array $starMap = [
        'A' => 5,
        'B' => 4,
        'C' => 3,
        'D' => 2,
        'E' => 1,
    ];

    public function index()
    {
        // Policy: hanya admin (viewAny)
        $this->authorize('viewAny', PerformanceReport::class);

        $assets = Asset::query()->latest()->paginate(10);

        return view('admin.asset_checks.index', compact('assets'));
    }

    public function create(Asset $asset)
    {
        // Policy: create report (hanya admin)
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
        $labels = array_keys($this->starMap);

        return view('admin.asset_checks.create', compact(
            'asset', 'latestSpec', 'questions', 'categories', 'labels'
        ));
    }

    public function store(Request $request, Asset $asset)
    {
        // Policy: create report (hanya admin)
        $this->authorize('create', PerformanceReport::class);

        $request->validate([
            // spec
            'processor' => 'required|string|max:255',
            'ram' => 'required|integer|min:0',
            'storage' => 'required|integer|min:0',
            'os_version' => 'nullable|string|max:255',

            // storage type dari form
            'category_storage' => 'nullable|in:HDD,SSD,NVME',

            // jawaban indikator
            'answers' => 'required|array|min:1',
        ]);

        // 1) pastikan semua pertanyaan wajib dijawab
        $allQuestions = IndicatorQuestion::select('id_question', 'category')->get();
        foreach ($allQuestions as $q) {
            if (!$request->filled("answers.{$q->id_question}")) {
                return back()
                    ->withErrors(['answers' => 'Semua pertanyaan wajib dijawab.'])
                    ->withInput();
            }
        }

        // 2) Ambil option yang dipilih, termasuk relasi question
        $selectedOptionIds = array_values($request->input('answers', []));
        $selectedOptions = IndicatorOption::with('question')
            ->whereIn('id_option', $selectedOptionIds)
            ->get();

        // validasi jumlah option harus sama
        if ($selectedOptions->count() !== count($selectedOptionIds)) {
            return back()
                ->withErrors(['answers' => 'Ada jawaban yang tidak valid.'])
                ->withInput();
        }

        // 3) Pastikan option yang dipilih bener-bener milik question yang sama
        // map: question_id => selected_option_id
        $mapAnswers = $request->input('answers', []);

        foreach ($selectedOptions as $opt) {
            $qid = (int) $opt->id_question;

            if (!isset($mapAnswers[$qid])) {
                return back()
                    ->withErrors(['answers' => 'Ada jawaban yang tidak cocok dengan pertanyaan.'])
                    ->withInput();
            }

            if ((int) $mapAnswers[$qid] !== (int) $opt->id_option) {
                return back()
                    ->withErrors(['answers' => 'Ada jawaban yang tidak cocok dengan pertanyaan.'])
                    ->withInput();
            }
        }

        $now = now();

        $report = DB::transaction(function () use ($request, $asset, $selectedOptions, $now) {

            // 4) Simpan spesifikasi sebagai record baru (biar ada histori)
            $spec = AssetsSpecifications::create([
                'id_asset' => $asset->id_asset,
                'processor' => $request->processor,
                'ram' => $request->ram,
                'storage' => $request->storage,
                'os_version' => $request->os_version,
                'is_hdd' => $request->category_storage === 'HDD',
                'is_ssd' => $request->category_storage === 'SSD',
                'is_nvme' => $request->category_storage === 'NVME',
                'datetime' => $now,
            ]);

            // 5) Simpan jawaban ke indicator_answers
            foreach ($selectedOptions as $opt) {
                IndicatorAnswer::create([
                    'id_option' => $opt->id_option,
                    'id_spec' => $spec->id_spec,
                    'star_rating' => $opt->star_value,
                    'datetime' => $now,
                ]);
            }

            // 6) Hitung rata-rata per kategori
            $avgByCat = $selectedOptions
                ->groupBy(fn ($o) => $o->question->category)
                ->map(fn ($items) => round($items->avg('star_value'), 2));

            // 7) Konversi avg -> prior (rumus: (5 - avg) + 1)
            $prior = function (?float $avg): int {
                if ($avg === null) return 5;
                $p = (int) ceil((5 - $avg) + 1);
                return max(1, min(5, $p));
            };

            $priorRam     = $prior($avgByCat['RAM'] ?? null);
            $priorStorage = $prior($avgByCat['STORAGE'] ?? null);
            $priorCpu     = $prior($avgByCat['CPU'] ?? null);

            // 8) Ambil rekomendasi text dari tabel recommendations
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

            // 9) Ambil estimasi harga upgrade (contoh aturan: prior >= 4)
            $upgradeRamPrice = null;
            if ($priorRam >= 4) {
                $upgradeRamPrice = Sparepart::where('category', 'RAM')
                    ->where('sparepart_type', $asset->ram_type) // dari tabel assets
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

            // 10) Simpan performance_report
            return PerformanceReport::create([
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
            ]);
        });

        return redirect()
            ->route('admin.asset-checks.show', [
                'asset' => $asset->id_asset,
                'report' => $report->id_report,
            ])
            ->with('success', 'Pengecekan asset berhasil diproses.');
    }

    public function show(Asset $asset, PerformanceReport $report)
    {
        // Policy: view report (admin / atau rules lain kalau kamu ubah)
        $this->authorize('view', $report);

        // safety: pastiin report milik asset yg sama
        if ((int) $report->id_asset !== (int) $asset->id_asset) {
            abort(404);
        }

        $report->load(['asset', 'spec']);

        return view('admin.asset_checks.show', compact('asset', 'report'));
    }
}
