<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\PerformanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DashboardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        // Filters
        $q         = trim((string) $request->get('q', ''));
        $typeId    = $request->get('id_type');
        $checked   = $request->get('checked', 'all');
        $minAvg    = $request->get('min_priority');

        $minAvgInt = is_numeric($minAvg) ? (int) $minAvg : null;
        if ($minAvgInt !== null) {
            $minAvgInt = max(1, min(5, $minAvgInt));
        }

        // Subquery latest report per asset
        $latestReportSub = PerformanceReport::query()
            ->selectRaw('MAX(id_report) as id_report, id_asset')
            ->groupBy('id_asset');

        // Query list assets dan latest report
        $assetsQuery = Asset::query()
            ->leftJoinSub($latestReportSub, 'lr', function ($join) {
                $join->on('assets.id_asset', '=', 'lr.id_asset');
            })
            ->leftJoin('performance_reports as pr', 'pr.id_report', '=', 'lr.id_report')
            ->leftJoin('asset_types as at', 'assets.id_type', '=', 'at.id_type')
            ->select([
                'assets.*',
                DB::raw('at.type_name as type_name'),
                DB::raw('pr.id_report as latest_report_id'),
                DB::raw('pr.created_at as checked_at'),

                DB::raw('pr.prior_ram as prior_ram'),
                DB::raw('pr.prior_storage as prior_storage'),
                DB::raw('pr.prior_processor as prior_processor'),

                DB::raw('pr.recommendation_ram as recommendation_ram'),
                DB::raw('pr.recommendation_storage as recommendation_storage'),
                DB::raw('pr.recommendation_processor as recommendation_processor'),

                DB::raw('pr.upgrade_ram_price as upgrade_ram_price'),
                DB::raw('pr.upgrade_storage_price as upgrade_storage_price'),

                // Average priority (0 -> 5). Kalo belum ada report -> avg = 0
                DB::raw('ROUND( (COALESCE(pr.prior_ram,0) + COALESCE(pr.prior_storage,0) + COALESCE(pr.prior_processor,0)) / 3, 2) as avg_priority'),
            ]);

        // Apply filters
        if ($q !== '') {
            $assetsQuery->where(function ($w) use ($q) {
                $w->where('assets.bmn_code', 'like', "%{$q}%")
                  ->orWhere('assets.device_name', 'like', "%{$q}%");
            });
        }

        if (!empty($typeId)) {
            $assetsQuery->where('assets.id_type', (int) $typeId);
        }

        if ($checked === 'checked') {
            $assetsQuery->whereNotNull('lr.id_report');
        } elseif ($checked === 'unchecked') {
            $assetsQuery->whereNull('lr.id_report');
        }

        if ($minAvgInt !== null) {
            $assetsQuery->whereRaw('ROUND( (COALESCE(pr.prior_ram,0) + COALESCE(pr.prior_storage,0) + COALESCE(pr.prior_processor,0)) / 3, 2) >= ?', [$minAvgInt]);
        }

        // Ordering belum dicek, avg priority tinggi, terakhir dicek
        $assets = $assetsQuery
            ->orderByRaw('CASE WHEN lr.id_report IS NULL THEN 0 ELSE 1 END ASC')
            ->orderByDesc('avg_priority')
            ->orderByDesc('checked_at')
            ->orderByDesc('assets.id_asset')
            ->paginate(12)
            ->withQueryString();

        // Summary cards
        $totalAssets = Asset::count();

        $checkedCount = Asset::query()
            ->joinSub($latestReportSub, 'lr', function ($join) {
                $join->on('assets.id_asset', '=', 'lr.id_asset');
            })
            ->count();

        $uncheckedCount = max(0, $totalAssets - $checkedCount);

        // Urgent pakai avg_priority (cuma yang udah dicek)
        $urgentCount = Asset::query()
            ->leftJoinSub($latestReportSub, 'lr', function ($join) {
                $join->on('assets.id_asset', '=', 'lr.id_asset');
            })
            ->leftJoin('performance_reports as pr', 'pr.id_report', '=', 'lr.id_report')
            ->whereNotNull('lr.id_report')
            ->whereRaw('ROUND( (COALESCE(pr.prior_ram,0) + COALESCE(pr.prior_storage,0) + COALESCE(pr.prior_processor,0)) / 3, 2) >= 4')
            ->count();

        $byType = Asset::query()
            ->leftJoin('asset_types as at', 'assets.id_type', '=', 'at.id_type')
            ->selectRaw('COALESCE(at.type_name, "Unknown") as type_name, COUNT(*) as total')
            ->groupBy('type_name')
            ->orderByDesc('total')
            ->get();

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.dashboard.index', compact(
            'assets',
            'types',
            'byType',
            'totalAssets',
            'checkedCount',
            'uncheckedCount',
            'urgentCount',
            'q',
            'typeId',
            'checked',
            'minAvgInt'
        ));
    }
}
