<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Semua Asset</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .title { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
        .muted { color: #666; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align:left; }
        .small { font-size: 10px; color:#333; }
        .pre { white-space: pre-line; }
    </style>
</head>
<body>

@php
    $summarizeRec = function (?string $text): string {
        if (!$text) return '-';
        $t = trim($text);
        if ($t === '' || $t === '-') return '-';

        $t = preg_replace("/\r\n|\r/", "\n", $t);

        if (str_contains($t, '•')) {
            $parts = array_values(array_filter(array_map('trim', explode('•', $t))));
            return $parts[0] ?? '-';
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $t))));
        return $lines[0] ?? '-';
    };

    $combineSummary = function (?string $ramRec, ?string $stoRec) use ($summarizeRec): string {
        $ram = $summarizeRec($ramRec);
        $sto = $summarizeRec($stoRec);

        $parts = [];
        if ($ram !== '-') $parts[] = "RAM: {$ram}";
        if ($sto !== '-') $parts[] = "Storage: {$sto}";

        return count($parts) ? implode("\n", $parts) : '-';
    };
@endphp

<div class="title">Rekap Report Pengecekan Asset</div>
<div class="muted">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th style="width:120px;">Kode BMN</th>
            <th>Nama Device</th>
            <th style="width:120px;">Kategori</th>

            <th style="width:85px;">Priority Level RAM</th>
            <th style="width:95px;">Priority Level Storage</th>
            <th style="width:85px;">Priority Level CPU</th>

            <th style="width:240px;">Ringkasan Rekomendasi</th>
            <th style="width:120px;">Tanggal</th>
        </tr>
    </thead>
    <tbody>
    @foreach($assets as $i => $asset)
        @php
            $r = $asset->latestPerformanceReport;
            $summary = $combineSummary($r?->recommendation_ram, $r?->recommendation_storage);
        @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $asset->bmn_code ?? '-' }}</td>
            <td>{{ $asset->device_name ?? '-' }}</td>
            <td>{{ $asset->type?->type_name ?? '-' }}</td>

            <td>{{ $r?->prior_ram ?? '-' }}</td>
            <td>{{ $r?->prior_storage ?? '-' }}</td>
            <td>{{ $r?->prior_processor ?? '-' }}</td>

            <td class="small pre">{{ $summary }}</td>
            <td>{{ optional($r?->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
