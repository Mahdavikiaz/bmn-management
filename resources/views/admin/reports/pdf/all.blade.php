<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Semua Asset</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12px 14px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #111;
        }

        .title { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .muted { color: #666; margin-bottom: 8px; font-size: 9px; }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 4px 5px;
            vertical-align: top;
            overflow-wrap: anywhere;
            word-wrap: break-word;
            word-break: break-word;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
        }

        .small { font-size: 9px; color:#333; }
        .pre { white-space: pre-line; }

        .summary {
            line-height: 1.25;
        }

        .device {
            line-height: 1.2;
        }
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

    $fmtMoney = function ($v): string {
        $n = (float) ($v ?? 0);
        if ($n <= 0) return '-';
        return 'Rp ' . number_format($n, 0, ',', '.');
    };

    $safeFloat = function ($v): float {
        if ($v === null) return 0.0;
        if (!is_numeric($v)) return 0.0;
        return (float) $v;
    };
@endphp

<div class="title">Rekap Report Pengecekan Asset</div>
<div class="muted">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

<table>
    <thead>
        <tr>
            <th style="width: 22px;">No</th>
            <th style="width: 90px;">Kode BMN</th>
            <th style="width: 120px;">Nama Device</th>
            <th style="width: 70px;">Kategori</th>

            <th style="width: 38px;">Priority Level<br>RAM</th>
            <th style="width: 48px;">Priority Level<br>Storage</th>
            <th style="width: 38px;">Priority Level<br>CPU</th>

            <th style="width: 230px;">Ringkasan Rekomendasi</th>

            <th style="width: 85px;">Estimasi Upgrade RAM</th>
            <th style="width: 95px;">Estimasi Upgrade Storage</th>
            <th style="width: 95px;">Total Estimasi</th>

            <th style="width: 90px;">Tanggal</th>
        </tr>
    </thead>

    <tbody>
    @foreach($assets as $i => $asset)
        @php
            $r = $asset->latestPerformanceReport;

            $summary = $combineSummary($r?->recommendation_ram, $r?->recommendation_storage);

            $ramPrice = $safeFloat($r?->upgrade_ram_price);
            $stoPrice = $safeFloat($r?->upgrade_storage_price);
            $totalPrice = $ramPrice + $stoPrice;
        @endphp

        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $asset->bmn_code ?? '-' }}</td>
            <td class="device">{{ $asset->device_name ?? '-' }}</td>
            <td>{{ $asset->type?->type_name ?? '-' }}</td>

            <td>{{ $r?->prior_ram ?? '-' }}</td>
            <td>{{ $r?->prior_storage ?? '-' }}</td>
            <td>{{ $r?->prior_processor ?? '-' }}</td>

            <td class="small pre summary">{{ $summary }}</td>

            <td class="num">{{ $fmtMoney($ramPrice) }}</td>
            <td class="num">{{ $fmtMoney($stoPrice) }}</td>
            <td class="num">{{ $fmtMoney($totalPrice) }}</td>

            <td>{{ optional($r?->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
