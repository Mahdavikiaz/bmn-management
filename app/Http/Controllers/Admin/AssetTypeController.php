<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Request as FacadesRequest;

class AssetTypeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
    // ambil daftar nama tipe (unik & dinamis)
        $typeNames = AssetType::orderBy('type_name')
            ->pluck('type_name')
            ->unique();

        $query = AssetType::query();

        // filter berdasarkan type_name
        if ($request->filled('type_name')) {
            $query->where('type_name', $request->type_name);
        }

        $types = $query->orderByDesc('id_type')
            ->paginate(10)
            ->withQueryString();

        return view('admin.asset_types.index', compact('types', 'typeNames'));
    }

    public function create()
    {
        $this->authorize('create', AssetType::class);

        return view('admin.asset_types.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', AssetType::class);

        $validated = $request->validate([
            'type_code' => 'required|string|max:50|unique:asset_types,type_code',
            'type_name' => 'required|string|max:100|unique:asset_types,type_name',
        ]);

        AssetType::create($validated);

        return redirect()
            ->route('admin.asset-types.index')
            ->with('success', 'Tipe Asset berhasil ditambahkan.');
    }

    public function edit(AssetType $assetType)
    {
        $this->authorize('update', $assetType);

        return view('admin.asset_types.edit', compact('assetType'));
    }

    public function update(Request $request, AssetType $assetType)
    {
        $this->authorize('update', $assetType);

        $validated = $request->validate([
            'type_code' => 'required|string|max:50',
            'type_name' => 'required|string|max:100',
        ]);

        $assetType->update($validated);

        return redirect()
            ->route('admin.asset-types.index')
            ->with('success', 'Tipe Asset berhasil diperbarui.');
    }

    public function destroy(AssetType $assetType)
    {
        $this->authorize('delete', $assetType);

        $assetType->delete();

        return redirect()
            ->route('admin.asset-types.index')
            ->with('success', 'Tipe Asset berhasil dihapus.');
    }
}
