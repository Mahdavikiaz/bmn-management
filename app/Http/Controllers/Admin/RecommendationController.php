<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecommendationController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Recommendation::class);

        $query = Recommendation::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority_level')) {
            $query->where('priority_level', (int) $request->priority_level);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($w) use ($q) {
                $w->where('action', 'like', "%{$q}%")
                  ->orWhere('explanation', 'like', "%{$q}%");
            });
        }

        $recommendations = $query
            ->orderBy('category', 'asc')
            ->orderBy('priority_level', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $priorities = [1, 2, 3, 4, 5];

        return view('admin.recommendations.index', compact(
            'recommendations',
            'categories',
            'priorities',
        ));
    }

    public function create()
    {
        $this->authorize('create', Recommendation::class);

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $priorities = [1, 2, 3, 4, 5];

        return view('admin.recommendations.create', compact('categories', 'priorities'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Recommendation::class);

        $validated = $request->validate([
            'category' => ['required', 'in:RAM,STORAGE,CPU'],
            'priority_level' => ['required', 'integer', 'min:1', 'max:5'],
            'action' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'target_type' => 'nullable|string|max:50',
            'size_mode' => 'nullable|in:fixed,multiplier',
            'target_size_gb' => 'nullable|integer|min:1',
            'target_multiplier' => 'nullable|numeric|min:1',
        ]);

        $mode = $request->input('size_mode');
        $size = $request->input('target_size_gb');
        $mul  = $request->input('target_multiplier');

        if ($mode === 'fixed') {
            if (!$size) return back()->withErrors(['target_size_gb' => 'Ukuran tetap wajib diisi.'])->withInput();
            if ($mul) return back()->withErrors(['target_multiplier' => 'Kosongkan multiplier jika memilih ukuran tetap.'])->withInput();
        } elseif ($mode === 'multiplier') {
            if (!$mul) return back()->withErrors(['target_multiplier' => 'Multiplier wajib diisi.'])->withInput();
            if ($size) return back()->withErrors(['target_size_gb' => 'Kosongkan ukuran jika memilih multiplier.'])->withInput();
        } else {
            if ($size || $mul) {
                return back()->withErrors(['size_mode' => 'Pilih cara ukuran (fixed/multiplier) atau kosongkan semuanya.'])->withInput();
            }
        }

        Recommendation::create($validated);

        return redirect()
            ->route('admin.recommendations.index')
            ->with('success', 'Recommendation berhasil ditambahkan.');
    }

    public function edit(Recommendation $recommendation)
    {
        $this->authorize('update', $recommendation);

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $priorities = [1, 2, 3, 4, 5];

        return view('admin.recommendations.edit', compact('recommendation', 'categories', 'priorities'));
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        $this->authorize('update', $recommendation);

        $validated = $request->validate([
            'category' => ['required', 'in:RAM,STORAGE,CPU'],
            'priority_level' => ['required', 'integer', 'min:1', 'max:5'],
            'action' => ['required', 'string'],
            'explanation' => ['required', 'string'],
            'target_type' => ['nullable', 'string', 'max:32'],
            'size_mode' => ['nullable', 'in:fixed,multiplier'],
            'target_size_gb' => ['nullable', 'integer', 'min:1'],
            'target_multiplier' => ['nullable', 'numeric', 'min:1'],
        ]);

        $sizeMode = $request->input('size_mode');

        if (!$sizeMode) {
            $validated['size_mode'] = null;
            $validated['target_type'] = null;
            $validated['target_size_gb'] = null;
            $validated['target_multiplier'] = null;
        }

        if ($sizeMode === 'fixed') {
            $request->validate([
                'target_size_gb' => ['required', 'integer', 'min:1'],
            ]);

            $validated['target_size_gb'] = (int) $request->input('target_size_gb');
            $validated['target_multiplier'] = null;

            if (empty($validated['target_type'])) {
                $validated['size_mode'] = null;
                $validated['target_size_gb'] = null;
            }
        }

        if ($sizeMode === 'multiplier') {
            $request->validate([
                'target_multiplier' => ['required', 'numeric', 'min:1'],
            ]);

            $validated['target_multiplier'] = (float) $request->input('target_multiplier');
            $validated['target_size_gb'] = null;

            if (empty($validated['target_type'])) {
                $validated['size_mode'] = null;
                $validated['target_multiplier'] = null;
            }
        }

        if (($validated['category'] ?? '') === 'CPU') {
            $validated['target_type'] = null;
            $validated['size_mode'] = null;
            $validated['target_size_gb'] = null;
            $validated['target_multiplier'] = null;
        }

        $recommendation->update($validated);

        return redirect()
            ->route('admin.recommendations.index')
            ->with('success', 'Recommendation berhasil diperbarui.');
    }


    public function destroy(Recommendation $recommendation)
    {
        $this->authorize('delete', $recommendation);

        $recommendation->delete();

        return redirect()
            ->route('admin.recommendations.index')
            ->with('success', 'Recommendation berhasil dihapus.');
    }
}
