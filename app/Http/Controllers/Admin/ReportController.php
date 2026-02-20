<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AssetsReportExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\PerformanceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $q = trim((string) $request->get('q', ''));
        $typeId = $request->get('id_type');

        $assetsQuery = Asset::query()
            ->with([
                'latestPerformanceReport',
                'type',
            ])
            ->withCount('performanceReports');

        if ($q !== '') {
            $assetsQuery->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}%")
                  ->orWhere('device_name', 'like', "%{$q}%");
            });
        }

        if (!empty($typeId)) {
            $assetsQuery->where('id_type', (int) $typeId);
        }

        $assets = $assetsQuery
            ->latest('id_asset')
            ->paginate(10)
            ->withQueryString();

        $types = AssetType::orderBy('type_name')->get();

        return view('admin.reports.index', compact('assets', 'types'));
    }

    public function exportAssetPdf(Asset $asset)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $asset->load([
            'type',
            'latestPerformanceReport.spec',
            'latestPerformanceReport.user',
        ]);

        $report = $asset->latestPerformanceReport;
        if (!$report) {
            abort(404, 'Asset ini belum memiliki report.');
        }

        $fileName = $this->safeFileName('Report_' . ($asset->bmn_code ?? ('asset_' . $asset->id_asset)) . '.pdf');

        $pdf = Pdf::loadView('admin.reports.pdf.asset', [
            'asset'  => $asset,
            'report' => $report,
            'spec'   => $report->spec,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    public function exportAssetExcel(Asset $asset)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $asset->load([
            'type',
            'latestPerformanceReport.spec',
            'latestPerformanceReport.user',
        ]);

        if (!$asset->latestPerformanceReport) {
            abort(404, 'Asset ini belum memiliki report.');
        }

        $fileName = $this->safeFileName('Report_' . ($asset->bmn_code ?? ('asset_' . $asset->id_asset)) . '.xlsx');

        return Excel::download(new AssetsReportExport($asset), $fileName);
    }

    public function exportAllPdf(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $assets = Asset::query()
            ->with([
                'type',
                'latestPerformanceReport.spec',
                'latestPerformanceReport.user',
            ])
            ->whereHas('performanceReports')
            ->latest('id_asset')
            ->get();

        $pdf = Pdf::loadView('admin.reports.pdf.all', [
            'assets' => $assets,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Reports_All_Assets.pdf');
    }

    public function exportAllExcel(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $assets = Asset::query()
            ->with([
                'type',
                'latestPerformanceReport.spec',
                'latestPerformanceReport.user',
            ])
            ->whereHas('performanceReports')
            ->latest('id_asset')
            ->get();

        return Excel::download(new AssetsReportExport($assets), 'Reports_All_Assets.xlsx');
    }

    private function safeFileName(string $name): string
    {
        return preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '-', $name) ?? $name;
    }
}
