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

        // Ambil versi terbaru (bisa null)
        $latestSpec = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();

        $validated = $request->validate([
            'processor' => ['nullable', 'string', 'max:255'],
            'ram' => ['nullable', 'integer', 'min:0'],
            'storage' => ['nullable', 'integer', 'min:0'],
            'os_version' => ['nullable', 'string', 'max:255'],
            'is_hdd' => ['nullable', 'boolean'],
            'is_ssd' => ['nullable', 'boolean'],
            'is_nvme' => ['nullable', 'boolean'],
        ]);

        // Checkbox kalau ga dicentang field ga terkirim
        $inputIsHdd  = $request->boolean('is_hdd');
        $inputIsSsd  = $request->boolean('is_ssd');
        $inputIsNvme = $request->boolean('is_nvme');

        // Kalau field kosong, ambil dari latestSpec (kalau ada aja)
        $newProcessor = filled($validated['processor'] ?? null)
            ? $validated['processor']
            : ($latestSpec->processor ?? null);

        // ram/storage bisa 0
        $newRam = array_key_exists('ram', $validated) && $validated['ram'] !== null
            ? (int)$validated['ram']
            : ($latestSpec->ram ?? null);

        $newStorage = array_key_exists('storage', $validated) && $validated['storage'] !== null
            ? (int)$validated['storage']
            : ($latestSpec->storage ?? null);

        $newOs = filled($validated['os_version'] ?? null)
            ? $validated['os_version']
            : ($latestSpec->os_version ?? null);

        // Checkbox karena form akan prefill (checked sesuai latest), ambil dari input aja
        $newIsHdd  = $inputIsHdd;
        $newIsSsd  = $inputIsSsd;
        $newIsNvme = $inputIsNvme;

        // Kalau belum ada latest, minimal harus isi 1 field (biar ga semua null)
        if (!$latestSpec) {
            $hasAny =
                !is_null($newProcessor) ||
                !is_null($newRam) ||
                !is_null($newStorage) ||
                !is_null($newOs) ||
                $newIsHdd || $newIsSsd || $newIsNvme;

            if (!$hasAny) {
                return back()
                    ->withErrors(['spec' => 'Isi minimal 1 field spesifikasi sebelum menyimpan.'])
                    ->withInput();
            }
        } else {
            // kalau udah ada latest, cek apakah ada perubahan dari latest
            $isSame =
                ($newProcessor ?? '') === ($latestSpec->processor ?? '') &&
                (int)($newRam ?? 0) === (int)($latestSpec->ram ?? 0) &&
                (int)($newStorage ?? 0) === (int)($latestSpec->storage ?? 0) &&
                ($newOs ?? '') === ($latestSpec->os_version ?? '') &&
                (bool)$newIsHdd === (bool)$latestSpec->is_hdd &&
                (bool)$newIsSsd === (bool)$latestSpec->is_ssd &&
                (bool)$newIsNvme === (bool)$latestSpec->is_nvme;

            if ($isSame) {
                return back()
                    ->withErrors(['spec' => 'Tidak ada perubahan. Silakan ubah minimal 1 field sebelum menyimpan versi baru.'])
                    ->withInput();
            }
        }

        AssetsSpecifications::create([
            'id_asset' => $asset->id_asset,
            'processor' => $newProcessor ?? '',
            'ram' => $newRam ?? 0,
            'storage' => $newStorage ?? 0,
            'os_version' => $newOs ?? '',
            'is_hdd' => (bool)$newIsHdd,
            'is_ssd' => (bool)$newIsSsd,
            'is_nvme' => (bool)$newIsNvme,
            'datetime' => now(),
        ]);

        return redirect()
            ->route('admin.assets.specifications.index', $asset->id_asset)
            ->with('success', 'Spesifikasi berhasil ditambahkan (versi baru).');
    }

    // Hapus versi-versi spec lama
    public function destroy(Asset $asset, AssetsSpecifications $spec)
    {
        $this->authorize('update', $asset);

        // Validasi spec dengan assetnya
        if ($spec->id_asset != $asset->id_asset) {
            abort(404);
        }

        // Supaya versi terbaru ga sengaja keapus
        $latest = AssetsSpecifications::where('id_asset', $asset->id_asset)
            ->orderByDesc('datetime')
            ->first();
        
        if ($latest && $spec->id_spec === $latest->id_spec) {
            return back()->withErrors(['spec' => 'Versi terbaru tidak bisa dihapus. Buat versi baru terlebih dahulu, lalu hapus versi lama.']);
        }

        $spec->delete();

        return redirect()
            ->route('admin.assets.specifications.index', $asset->id_asset)
            ->with('success', 'Versi spesifikasi berhasil dihapus.');
    }
}
