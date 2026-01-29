<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AssetsReportExport extends StringValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomValueBinder
{
    protected array $assetIds = [];

    public function __construct($assets)
    {
        // 1 asset
        if ($assets instanceof Asset) {
            $this->assetIds = [(int) $assets->id_asset];
            return;
        }

        // collection
        if ($assets instanceof Collection) {
            $this->assetIds = $assets
                ->map(fn ($a) => (int) data_get($a, 'id_asset'))
                ->filter(fn ($id) => $id > 0)
                ->values()
                ->all();
            return;
        }

        $this->assetIds = [];
    }

    public function collection(): Collection
    {
        if (!count($this->assetIds)) {
            return collect([]);
        }

        return Asset::query()
            ->whereIn('id_asset', $this->assetIds)
            ->with([
                'type',
                'latestPerformanceReport.user',
                'latestPerformanceReport.spec',
            ])
            ->orderBy('id_asset')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode BMN',
            'Kategori (Type Name)',
            'Nama Device',
            'GPU',
            'Tipe RAM',
            'Tahun Pengadaan',

            'Tanggal Report Terakhir',
            'Dibuat Oleh',

            'Owner Asset',
            'Processor',
            'RAM (GB)',
            'Storage (GB)',
            'Tipe Storage',
            'OS Version',

            'Priority RAM',
            'Priority Storage',
            'Priority CPU',

            'Rekomendasi RAM',
            'Rekomendasi Storage',
            'Rekomendasi CPU',

            'Estimasi Upgrade RAM',
            'Estimasi Upgrade Storage',
        ];
    }

    public function map($asset): array
    {
        $r = $asset->latestPerformanceReport;
        $spec = $r?->spec;

        $storageType = '-';
        if ($spec) {
            $types = [];
            if ($spec->is_hdd)  $types[] = 'HDD';
            if ($spec->is_ssd)  $types[] = 'SSD';
            if ($spec->is_nvme) $types[] = 'NVME';
            $storageType = count($types) ? implode(', ', $types) : '-';
        }

        return [
            ($asset->bmn_code !== null && $asset->bmn_code !== '') ? (string) $asset->bmn_code : '-',

            $asset->type?->type_name ?? '-',
            $asset->device_name ?? '-',
            $asset->gpu ?? '-',
            $asset->ram_type ?? '-',
            $asset->procurement_year ?? '-',

            optional($r?->created_at)->format('d/m/Y H:i') ?? '-',
            $r?->user?->name ?? '-',

            $spec?->owner_asset ?? '-',
            $spec?->processor ?? '-',
            $spec ? (int) $spec->ram : '-',
            $spec ? (int) $spec->storage : '-',
            $storageType,
            $spec?->os_version ?? '-',

            $r?->prior_ram ?? '-',
            $r?->prior_storage ?? '-',
            $r?->prior_processor ?? '-',

            $this->cleanText($r?->recommendation_ram),
            $this->cleanText($r?->recommendation_storage),
            $this->cleanText($r?->recommendation_processor),

            $this->fmtPrice($r?->upgrade_ram_price),
            $this->fmtPrice($r?->upgrade_storage_price),
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $col = $cell->getColumn();

        // Kolom A = Kode BMN
        if (in_array($col, ['A'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
        ];
    }

    private function fmtPrice($p): string
    {
        $p = (float) $p;
        return $p > 0 ? ('Rp ' . number_format($p, 0, ',', '.')) : '-';
    }

    private function cleanText(?string $text): string
    {
        if (!$text) return '-';

        $t = trim($text);
        if ($t === '' || $t === '-') return '-';

        $t = str_replace("•", "-", $t);

        $t = preg_replace("/\r\n|\r/", "\n", $t);

        return $t ?: '-';
    }
}
