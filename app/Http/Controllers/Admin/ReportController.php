<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AssetsReportExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\PerformanceReport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReport::class);

        $assets = Asset::query()
            ->with([
                'type',
                'latestPerformanceReport.spec',
                'latestPerformanceReport.user',
            ])
            ->whereHas('performanceReports') // hanya yang sudah pernah dicek (punya report)
            ->latest('id_asset')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact('assets'));
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

        $fileName = 'Report_' . ($asset->bmn_code ?? ('asset_' . $asset->id_asset)) . '.pdf';

        $pdf = Pdf::loadView('admin.reports.pdf.asset', [
            'asset' => $asset,
            'report' => $report,
            'spec' => $report->spec,
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

        $report = $asset->latestPerformanceReport;

        if (!$report) {
            abort(404, 'Asset ini belum memiliki report.');
        }

        $fileName = 'Report_' . ($asset->bmn_code ?? ('asset_' . $asset->id_asset)) . '.xlsx';

        return Excel::download(new AssetsReportExport($report), $fileName);
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
}
