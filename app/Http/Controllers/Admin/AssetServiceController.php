<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetService;
use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssetServiceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', AssetService::class);

        $q = trim((string) $request->get('q', ''));
        $typeId = $request->get('id_type');
        $statusService = $request->get('status_service');

        $assetsQuery = Asset::query()
            ->with(['latestService', 'latestPerformanceReport', 'type'])
            ->withCount(['services', 'performanceReports']);
        
        if ($q !== '') {
            $assetsQuery->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}%");
            });
        }

        if (!empty($typeId)) {
            $assetsQuery->where('id_type', (int) $typeId);
        }

        if ($statusService === 'serviced') {
            $assetsQuery->has('services');
        } elseif ($statusService === 'unserviced') {
            $assetsQuery->doesntHave('services');
        }

        $assets = $assetsQuery
            ->latest('id_asset')
            ->paginate(10)
            ->withQueryString();
        
        $types = AssetType::orderBy('type_name')->get();

        return view('admin.asset_services.index', compact('assets', 'types'));
    }

    public function create(Asset $asset)
    {
        $this->authorize('create', AssetService::class);

        $asset->load(['type', 'latestPerformanceReport']);

        $lastReport = $asset->latestPerformanceReport;

        return view('admin.asset_services.create', compact('asset', 'lastReport'));
    }

    public function store(Request $request, Asset $asset)
    {
        $this->authorize('create', AssetService::class);

        $validated = $request->validate([
            'service_date' => ['required', 'date'],
            'service_description' => ['required', 'string', 'max:5000'],
        ]);

        AssetService::create([
            'id_asset' => $asset->id_asset,
            'service_date' => $validated['service_date'],
            'service_description' => $validated['service_description'],
        ]);

        return redirect()
            ->route('admin.asset-services.history', $asset->id_asset)
            ->with('success', 'Data service berhasil disimpan.');
    }

    public function history(Asset $asset)
    {
        $this->authorize('viewAny', AssetService::class);

        $asset->load(['type', 'latestPerformanceReport']);

        $latest = AssetService::where('id_asset', $asset->id_asset)
            ->orderByDesc('service_date')
            ->orderByDesc('id_service')
            ->first();
        
        $history = AssetService::where('id_asset', $asset->id_asset)
            ->orderByDesc('service_date')
            ->orderByDesc('id_service')
            ->paginate(10);
        
        $lastReport = $asset->latestPerformanceReport;

        return view('admin.asset_services.history', compact('asset', 'latest', 'history', 'lastReport'));
    }

    public function destroy(Asset $asset, AssetService $service)
    {
        $this->authorize('delete', $service);

        if ((int)$service->id_asset !== (int)$asset->id_asset) {
            abort(404);
        }

        $service->delete();

        return redirect()
            ->route('admin.asset-services.history', $asset->id_asset)
            ->with('success', 'Record service berhasil dihapus.');
    }
}
