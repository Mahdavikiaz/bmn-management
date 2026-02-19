<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetsSpecifications;
use App\Models\AssetType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetController extends Controller
{
    use AuthorizesRequests;

    private function generateBmnCode(int $idType, string $nup): string
    {
        $type = AssetType::select('id_type', 'type_code')->findOrFail($idType);

        $typeCode = trim((string) $type->type_code);
        $nup = trim($nup);

        return $typeCode . $nup;
    }

    // Menampilkan asset + spec terbaru
    public function index(Request $request)
    {
        $this->authorize('viewAny', Asset::class);

        $query = Asset::query()
            ->with(['latestSpecification', 'type']);

        // Filter by asset type (id_type)
        if ($request->filled('id_type')) {
            $query->where('id_type', (int) $request->id_type);
        }

        // Search asset by bmn_code atau nama
        if ($request->filled('q')) {
            $q = trim($request->q);

            $query->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}%")
                  ->orWhere('device_name', 'like', "%{$q}%");
            });
        }

        $assets = $query
            ->orderByDesc('id_asset')
            ->paginate(10)
            ->withQueryString();

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.assets.index', compact('assets', 'types'));
    }

    // Form create asset master + spec (opsional)
    public function create()
    {
        $this->authorize('create', Asset::class);

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.assets.create', compact('types'));
    }

    // Store asset master + opsional spec jika diisi
    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);

        $validated = $request->validate([
            // Master
            'id_type' => ['required', 'exists:asset_types,id_type'],
            'nup' => ['required', 'string', 'max:255'],
            'device_name' => ['required', 'string', 'max:255'],
            'gpu' => ['nullable', 'string', 'max:255'],
            'ram_type' => ['nullable', 'in:DDR3,DDR4,DDR5'],
            'procurement_year' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],

            // Specs (opsional)
            'owner_asset' => ['nullable', 'string', 'max:255'],
            'processor' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'integer', 'min:0'],
            'storage' => ['nullable', 'integer', 'min:0'],
            'os_version' => ['nullable', 'string', 'max:255'],
            'is_hdd' => ['nullable', 'boolean'],
            'is_ssd' => ['nullable', 'boolean'],
            'is_nvme' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {

            $bmnCode = $this->generateBmnCode((int) $validated['id_type'], (string) $validated['nup']);

            // memastikan unik
            $exists = Asset::where('bmn_code', $bmnCode)->exists();
            if ($exists) {
                throw ValidationException::withMessages([
                    'nup' => ['Kode BMN hasil generate sudah ada. Coba ganti NUP atau pastikan Type Code benar.'],
                ]);
            }

            // Simpan master
            $asset = Asset::create([
                'id_type' => (int) $validated['id_type'],
                'nup' => (string) $validated['nup'],
                'bmn_code' => $bmnCode,

                'device_name' => $validated['device_name'],
                'gpu' => $validated['gpu'] ?? null,
                'ram_type' => $validated['ram_type'] ?? null,
                'procurement_year' => $validated['procurement_year'],
            ]);

            // Simpan spec (opsional) jika ada input saja
            $hasAnySpec =
                !empty($validated['owner_asset'] ?? null) ||
                !empty($validated['processor'] ?? null) ||
                ($validated['ram'] ?? null) !== null ||
                ($validated['storage'] ?? null) !== null ||
                !empty($validated['os_version'] ?? null) ||
                !empty($validated['is_hdd'] ?? null) ||
                !empty($validated['is_ssd'] ?? null) ||
                !empty($validated['is_nvme'] ?? null);

            if ($hasAnySpec) {
                AssetsSpecifications::create([
                    'id_asset' => $asset->id_asset,
                    'owner_asset' => $validated['owner_asset'] ?? '',
                    'processor' => $validated['processor'] ?? '',
                    'ram' => (int)($validated['ram'] ?? 0),
                    'storage' => (int)($validated['storage'] ?? 0),
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

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.assets.edit', compact('asset', 'types'));
    }

    // Update master
    public function update(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

        $validated = $request->validate([
            'id_type' => ['required', 'exists:asset_types,id_type'],
            'nup' => ['required', 'string', 'max:255'],

            'device_name' => ['required', 'string', 'max:255'],
            'gpu' => ['nullable', 'string', 'max:255'],
            'ram_type' => ['nullable', 'in:DDR3,DDR4,DDR5'],
            'procurement_year' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
        ]);

        DB::transaction(function () use ($asset, $validated) {

            $bmnCode = $this->generateBmnCode((int) $validated['id_type'], (string) $validated['nup']);

            // memastikan unik termasuk asset sekarang
            $exists = Asset::where('bmn_code', $bmnCode)
                ->where('id_asset', '!=', $asset->id_asset)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nup' => ['Kode BMN hasil generate sudah digunakan asset lain. Coba ganti NUP atau tipe asset.'],
                ]);
            }

            $asset->update([
                'id_type' => (int) $validated['id_type'],
                'nup' => (string) $validated['nup'],
                'bmn_code' => $bmnCode,

                'device_name' => $validated['device_name'],
                'gpu' => $validated['gpu'] ?? null,
                'ram_type' => $validated['ram_type'] ?? null,
                'procurement_year' => $validated['procurement_year'],
            ]);
        });

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset berhasil diperbarui.');
    }

    // Delete asset, spec ikut kehapus
    public function destroy(Asset $asset)
    {
        $this->authorize('delete', $asset);

        $asset->delete();

        return back()->with('success', 'Asset berhasil dihapus.');
    }
}
