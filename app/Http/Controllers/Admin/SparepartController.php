<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class SparepartController extends Controller
{
    use AuthorizesRequests;

    private function categoryTypes(): array
    {
        return [
            'RAM'     => ['DDR3', 'DDR4', 'DDR5'],
            'STORAGE' => ['SSD', 'HDD', 'NVME'],
            'BATERAI' => ['BATTERY'],
            'CHARGER' => ['ADAPTER'],
        ];
    }

    private function requiresSize(?string $category): bool
    {
        return in_array(strtoupper((string)$category), ['RAM', 'STORAGE'], true);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Sparepart::class);

        $query = Sparepart::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sparepart_type')) {
            $query->where('sparepart_type', $request->sparepart_type);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('sparepart_name', 'like', "%{$q}%");
        }

        $spareparts = $query->orderByDesc('id_sparepart')
            ->paginate(10)
            ->withQueryString();

        $categories = array_keys($this->categoryTypes());
        $types = collect($this->categoryTypes())->flatten()->unique()->values()->all();

        return view('admin.spareparts.index', compact('spareparts', 'categories', 'types'));
    }

    public function create()
    {
        $this->authorize('create', Sparepart::class);

        $categories = array_keys($this->categoryTypes());
        $typesByCategory = $this->categoryTypes();

        return view('admin.spareparts.create', compact('categories', 'typesByCategory'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Sparepart::class);

        $typesByCategory = $this->categoryTypes();
        $category = strtoupper((string) $request->input('category'));
        $allowedTypes = $typesByCategory[$category] ?? [];

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys($typesByCategory))],
            'sparepart_type' => ['required', Rule::in($allowedTypes)],
            'sparepart_name' => ['required', 'string', 'max:255'],
            'size' => [
                Rule::requiredIf($this->requiresSize($category)),
                'nullable',
                'integer',
                'min:0',
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        // kalau BATERAI/CHARGER, size dipaksa null
        if (!$this->requiresSize($category)) {
            $validated['size'] = null;
        }

        Sparepart::create($validated);

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Data Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        $this->authorize('update', $sparepart);

        $categories = array_keys($this->categoryTypes());
        $typesByCategory = $this->categoryTypes();

        return view('admin.spareparts.edit', compact('sparepart', 'categories', 'typesByCategory'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $this->authorize('update', $sparepart);

        $typesByCategory = $this->categoryTypes();
        $category = strtoupper((string) $request->input('category'));
        $allowedTypes = $typesByCategory[$category] ?? [];

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys($typesByCategory))],
            'sparepart_type' => ['required', Rule::in($allowedTypes)],
            'sparepart_name' => ['required', 'string', 'max:255'],
            'size' => [
                Rule::requiredIf($this->requiresSize($category)),
                'nullable',
                'integer',
                'min:0',
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        if (!$this->requiresSize($category)) {
            $validated['size'] = null;
        }

        $sparepart->update($validated);

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Data Sparepart berhasil diperbarui.');
    }

    public function destroy(Sparepart $sparepart)
    {
        $this->authorize('delete', $sparepart);

        $sparepart->delete();

        return back()->with('success', 'Data Sparepart berhasil dihapus.');
    }
}