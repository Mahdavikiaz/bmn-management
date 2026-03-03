<?php

namespace App\Exports;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AssetsAllExport extends StringValueBinder implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomValueBinder,
    WithChunkReading
{
    public function __construct(
        protected ?int $idType = null,
        protected ?string $q = null
    ) {}

    public function query(): Builder
    {
        $query = Asset::query()
            ->with(['type', 'latestSpecification'])
            ->orderBy('id_asset');

        // export ikut filter dari halaman (id_type dan q)
        if (!is_null($this->idType)) {
            $query->where('id_type', $this->idType);
        }

        if (!empty($this->q)) {
            $q = trim($this->q);
            $query->where(function ($w) use ($q) {
                $w->where('bmn_code', 'like', "%{$q}%")
                  ->orWhere('device_name', 'like', "%{$q}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Kode BMN',
            'Kategori Asset',
            'Nama Device',
            'GPU',
            'Tipe RAM',
            'Tahun Pengadaan',

            'Owner Asset',
            'Processor',
            'RAM (GB)',
            'Storage (GB)',
            'Tipe Storage',
            'OS Version',
        ];
    }

    public function map($asset): array
    {
        $spec = $asset->latestSpecification;

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

            $spec?->owner_asset ?? '-',
            $spec?->processor ?? '-',
            $spec ? (int) $spec->ram : '-',
            $spec ? (int) $spec->storage : '-',
            $storageType,
            $spec?->os_version ?? '-',
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        $col = $cell->getColumn();

        // Kolom A = Kode BMN (jadi string)
        if ($col === 'A') {
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

    public function chunkSize(): int
    {
        return 1000;
    }
}