<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AssetReportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /** @var \Illuminate\Support\Collection<int,\App\Models\Asset> */
    protected Collection $assets;

    public function __construct(Collection $assets)
    {
        $this->assets = $assets;
    }

    public function collection(): Collection
    {
        return $this->assets;
    }

    public function headings(): array
    {
        return [
            'Kode BMN',
            'NUP',
            'Nama Device',
            'Kategori (Type Name)',
            'Tahun Pengadaan',
            'Owner Asset (dari Spec terakhir)',
            'Prior RAM',
            'Prior Storage',
            'Prior CPU',
            'Rekomendasi RAM',
            'Rekomendasi Storage',
            'Rekomendasi CPU',
            'Estimasi Upgrade RAM',
            'Estimasi Upgrade Storage',
            'Tanggal Report Terakhir',
        ];
    }

    public function map($asset): array
    {
        $r = $asset->latestPerformanceReport;
        $spec = $r?->spec;

        $clean = function (?string $t): string {
            if (!$t) return '-';
            $t = trim($t);
            if ($t === '') return '-';
            // hilangin bullet biar excel rapi
            $t = str_replace("• ", "- ", $t);
            return $t;
        };

        $fmtPrice = function ($p): string {
            $p = (float) $p;
            if ($p <= 0) return '-';
            return (string) $p; // excel bisa format sendiri
        };

        return [
            $asset->bmn_code ?? '-',
            $asset->nup ?? '-',
            $asset->device_name ?? '-',
            $asset->type?->type_name ?? '-',
            $asset->procurement_year ?? '-',
            $spec?->owner_asset ?? '-',

            $r?->prior_ram ?? '-',
            $r?->prior_storage ?? '-',
            $r?->prior_processor ?? '-',

            $clean($r?->recommendation_ram),
            $clean($r?->recommendation_storage),
            $clean($r?->recommendation_processor),

            $fmtPrice($r?->upgrade_ram_price),
            $fmtPrice($r?->upgrade_storage_price),

            optional($r?->created_at)->format('d/m/Y H:i') ?? '-',
        ];
    }
}
