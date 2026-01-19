<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SparepartController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sparepart::class);

        $query = Sparepart::query();

        // filter category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // filter type
        if ($request->filled('sparepart_type')) {
            $query->where('sparepart_type', $request->sparepart_type);
        }

        // search nama
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('sparepart_name', 'like', "%{$q}%");
        }

        $spareparts = $query->orderByDesc('id_sparepart')
            ->paginate(10)
            ->withQueryString();
        
        $categories = ['RAM', 'STORAGE'];
        $types = ['DDR3', 'DDR4', 'DDR5', 'SSD', 'HDD', 'NVME'];

        return view('admin.spareparts.index', compact('spareparts', 'categories', 'types'));
    }

    public function create()
    {
        $this->authorize('create', Sparepart::class);

        $categories = ['RAM', 'STORAGE'];
        $types = ['DDR3', 'DDR4', 'DDR5', 'SSD', 'HDD', 'NVME'];

        return view('admin.spareparts.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Sparepart::class);

        $validated = $request->validate([
            'category' => ['required', 'in:RAM,STORAGE'],
            'sparepart_type' => ['required', 'in:DDR3,DDR4,DDR5,SSD,HDD,NVME'],
            'sparepart_name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        Sparepart::create($validated);

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Data Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        $this->authorize('update', Sparepart::class);

        $categories = ['RAM', 'STORAGE'];
        $types = ['DDR3', 'DDR4', 'DDR5', 'SSD', 'HDD', 'NVME'];

        return view('admin.spareparts.edit', compact('sparepart', 'categories', 'types'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $this->authorize('update', Sparepart::class);

        $validated = $request->validate([
            'category' => ['required', 'in:RAM,STORAGE'],
            'sparepart_type' => ['required', 'in:DDR3,DDR4,DDR5,SSD,HDD,NVME'],
            'sparepart_name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

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
