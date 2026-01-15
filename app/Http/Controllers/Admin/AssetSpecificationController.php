<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetsSpecifications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AssetSpecificationController extends Controller
{
    use AuthorizesRequests;

    public function index(Asset $asset)
    {
        $this->authorize('update', $asset);

        // Get history specs terbaru
        $specs = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->get();
        
        // Spec terbaru bisa null
        $latestSpec = $specs->first();

        return view('admin.assets.specifications', compact('asset', 'latestSpec', 'specs'));
    }

    // Simpan spec baru buat history, row lama ga keubah
    public function store(Request $request, Asset $asset)
    {
        $this->authorize('update', $asset);

         $validated = $request->validate([
            'processor' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'integer', 'min:0'],
            'storage' => ['nullable', 'integer', 'min:0'],
            'os_version' => ['nullable', 'string', 'max:255'],
            'is_hdd' => ['nullable', 'boolean'],
            'is_ssd' => ['nullable', 'boolean'],
            'is_nvme' => ['nullable', 'boolean'],
        ]);

        // Cegah insert spec kosong total
        $hasAny =
            !empty($validated['processor'] ?? null) ||
            !is_null($validated['ram'] ?? null) ||
            !is_null($validated['storage'] ?? null) ||
            !empty($validated['os_version'] ?? null) ||
            !empty($validated['is_hdd'] ?? null) ||
            !empty($validated['is_ssd'] ?? null) ||
            !empty($validated['is_nvme'] ?? null);

        if (!$hasAny) {
            return back()
                ->withErrors(['spec' => 'Isi minimal 1 field spesifikasi sebelum menyimpan.'])
                ->withInput();
        }

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

        return redirect()
            ->route('admin.assets.specifications.index', $asset->id_asset)
            ->with('success', 'Spesifikasi berhasil ditambahkan (versi baru).');
    }
}
