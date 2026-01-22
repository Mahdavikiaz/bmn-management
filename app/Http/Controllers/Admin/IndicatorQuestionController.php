<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndicatorOption;
use App\Models\IndicatorQuestion;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class IndicatorQuestionController extends Controller
{
    use AuthorizesRequests;

    private array $starMap = [
        'A' => 5,
        'B' => 4,
        'C' => 3,
        'D' => 2,
        'E' => 1,
    ];

    public function index(Request $request)
    {
        $this->authorize('viewAny', IndicatorQuestion::class);

        $categories = ['RAM', 'STORAGE', 'CPU'];

        $query = IndicatorQuestion::query()->with('options');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('indicator_name', 'like', "%{$q}%")
                    ->orWhere('question', 'like', "%{$q}%");
            });
        }

        $indicators = $query->latest()->paginate(10)->withQueryString();

        return view('admin.indicators.index', compact('indicators', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', IndicatorQuestion::class);

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $labels = array_keys($this->starMap);

        return view('admin.indicators.create', compact('categories', 'labels'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', IndicatorQuestion::class);

        $request->validate([
            'category' => 'required|in:RAM,STORAGE,CPU',
            'indicator_name' => 'required|string|max:255',
            'question' => 'required|string',
            'options' => 'required|array',
            'options.A' => 'required|string',
            'options.B' => 'required|string',
            'options.C' => 'required|string',
            'options.D' => 'required|string',
            'options.E' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $question = IndicatorQuestion::create([
                'category' => $request->category,
                'indicator_name' => $request->indicator_name,
                'question' => $request->question,
            ]);

            foreach ($this->starMap as $label => $star) {
                IndicatorOption::create([
                    'id_question' => $question->id_question,
                    'label' => $label,
                    'option' => $request->input("options.$label"),
                    'star_value' => $star,
                ]);
            }
        });

        return redirect()
            ->route('admin.indicator-questions.index')
            ->with('success', 'Indicator berhasil ditambahkan.');
    }

    public function edit(IndicatorQuestion $indicator_question)
    {
        $this->authorize('update', $indicator_question);

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $labels = array_keys($this->starMap);

        $indicator_question->load('options');
        $optionsByLabel = $indicator_question->options->keyBy('label');

        $indicator = $indicator_question;

        return view('admin.indicators.edit', compact('indicator', 'categories', 'labels', 'optionsByLabel'));
    }

    public function update(Request $request, IndicatorQuestion $indicator_question)
    {
        $this->authorize('update', $indicator_question);

        $request->validate([
            'category' => 'required|in:RAM,STORAGE,CPU',
            'indicator_name' => 'required|string|max:255',
            'question' => 'required|string',
            'options' => 'required|array',
            'options.A' => 'required|string',
            'options.B' => 'required|string',
            'options.C' => 'required|string',
            'options.D' => 'required|string',
            'options.E' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $indicator_question) {
            $indicator_question->update([
                'category' => $request->category,
                'indicator_name' => $request->indicator_name,
                'question' => $request->question,
            ]);

            foreach ($this->starMap as $label => $star) {
                IndicatorOption::updateOrCreate(
                    [
                        'id_question' => $indicator_question->id_question,
                        'label' => $label,
                    ],
                    [
                        'option' => $request->input("options.$label"),
                        'star_value' => $star,
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.indicator-questions.index')
            ->with('success', 'Indicator berhasil diperbarui.');
    }

    public function destroy(IndicatorQuestion $indicator_question)
    {
        $this->authorize('delete', $indicator_question);

        $indicator_question->delete();

        return redirect()
            ->route('admin.indicator-questions.index')
            ->with('success', 'Indicator berhasil dihapus.');
    }
}
