<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetsSpecifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    use AuthorizesRequests;

    // Menampilkan asset + spec terbaru
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $query = Asset::query();

        // Filter by device_type
        if ($request->filled('device_type')) {
            $query->where('device_type', $request->device_type);
        }

        // Search asset by code atau nama
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}")
                  ->orWhere('device_name', 'like', "%{$q}");
            });
        }

        $assets = $query->with('latestSpecification')->orderByDesc('id_asset')->paginate(10)->withQueryString();

        return view('admin.assets.index', compact('assets'));
    }

    // Form create asset master + spec (opsional)
    public function create()
    {
        $this->authorize('create', Asset::class);

        return view('admin.assets.create');
    }

    // Store asset master + opsional spec jika diisi
    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);

        $validated = $request->validate([
            // Master
            'bmn_code' => ['required', 'string', 'max:255', 'unique:assets,bmn_code'],
            'device_name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'in:PC,Laptop'],
            'gpu' => ['required', 'string', 'max:255'],
            'ram_type' => ['required', 'string', 'max:255'],
            'procurement_year' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],

            // Specs
            'processor' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'integer', 'min:0'],
            'storage' => ['nullable', 'integer', 'min:0'],
            'os_version' => ['nullable', 'string', 'max:255'],
            'is_hdd' => ['nullable', 'boolean'],
            'is_ssd' => ['nullable', 'boolean'],
            'is_nvme' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            // Simpan master dulu
            $asset = Asset::create([
                'bmn_code' => $validated['bmn_code'],
                'device_name' => $validated['device_name'],
                'device_type' => $validated['device_type'],
                'gpu' => $validated['gpu'],
                'ram_type' => $validated['ram_type'],
                'procurement_year' => $validated['procurement_year'],
            ]);

            // Simpan spec (opsional) jika ada input saja
            $hasAnySpec = 
                !empty($validated['processor'] ?? null) ||
                !empty($validated['ram'] ?? null) ||
                !empty($validated['storage'] ?? null) ||
                !empty($validated['os_version'] ?? null) ||
                !empty($validated['is_hdd'] ?? null) ||
                !empty($validated['is_ssd'] ?? null) ||
                !empty($validated['is_nvme'] ?? null);
            
            if ($hasAnySpec) {
                AssetsSpecifications::create([
                    'id_asset' => $asset->id_asset,
                    'processor' => $validated['processor'] ?? '',
                    'ram' => $validated['ram'] ?? 0,
                    'storage' => $validated['storage'] ?? 0,
                    'os_version' => $validated['os_version'] ?? '',
                    'is_hdd' => (bool)($validated['is_hdd'] ?? false),
                    'is_ssd' => (bool)($validated['is_ssd'] ?? false),
                    'is_nvme' => (bool)($validated['is_nvme'] ?? false),
                    'datetime' => now(),
                ]);
            }
        });

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset berhasil ditambahkan.');
    }

    // Form edit master
    public function edit(Asset $asset)
    {
        $this->authorize('update', $asset);

        return view('admin.assets.edit', compact('asset'));
    }

    // Update master
    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'bmn_code' => ['required', 'string', 'max:255', 'unique:assets,bmn_code,' . $asset->id_asset . ',id_asset'],
            'device_name' => ['required', 'string', 'max:255'],
            'device_type' => ['required', 'in:PC,Laptop'],
            'gpu' => ['required', 'string', 'max:255'],
            'ram_type' => ['required', 'string', 'max:255'],
            'procurement_year' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
        ]);

        $asset->update($validated);

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset berhasil diperbarui.');
    }

    // Delete asset -> spec ikut keapus
    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return back()->with('success', 'Asset berhasil dihapus.');
    }
}
