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
            $query->where('priority_level', $request->priority_level);
        }

        $recommendations = $query
            ->orderBy('priority_level', 'asc')
            ->order_by('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        
        $categories = ['RAM', 'STORAGE', 'CPU'];
        $priorities = [1,2,3,4,5];

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
        $priorities = [1,2,3,4,5];

        return view('admin.recommendations.create', compact('categories', 'priorities'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Recommendation::class);

        $validated = $request->validate([
            'category' => 'required|in:RAM,STORAGE,CPU',
            'description' => 'required|string',
            'priority_level' => 'required|integer|min:1|max:5',
        ]);

        Recommendation::class($validated);

        return redirect()
            ->route('admin.recommendations.index')
            ->with('success', 'Recommendation berhasil ditambahkan');
    }

    public function edit(Recommendation $recommendation)
    {
        $this->authorize('update', $recommendation);

        $categories = ['RAM', 'STORAGE', 'CPU'];
        $priorities = [1,2,3,4,5];

        return view('admin.recommendations.edit', compact('recommendation', 'categories', 'priorities'));
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        $this->authorize('update', $recommendation);

        $validated = $request->validate([
            'category' => 'required|in:RAM,STORAGE,CPU',
            'description' => 'required|string',
            'priority_level' => 'required|integer|min:1|max:5',
        ]);

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
