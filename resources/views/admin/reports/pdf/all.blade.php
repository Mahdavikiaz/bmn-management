<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Semua Asset</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10px 12px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.8px;
            color: #111;
        }

        .title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .muted {
            color: #666;
            margin-top: 8px;
            font-size: 8.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #d9d9d9;
            padding: 4px 4px;
            vertical-align: top;
            white-space: normal;
            overflow-wrap: break-word;
            word-break: normal;
        }

        thead th {
            background: #f3f4f6;
            font-weight: 700;
            text-align: center;
            line-height: 1.25;
        }

        .group-head {
            background: #e5e7eb;
            width: 120px;
        }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .small { font-size: 8px; color: #555; }
        .device { line-height: 1.3; }
        .code { line-height: 1.25; }
        .date-cell { line-height: 1.25; }
        .nowrap { white-space: nowrap; }

        .indicator-cell {
            padding: 0;
        }

        .indicator-box {
            padding: 5px 6px;
            line-height: 1.3;
            min-height: 54px;
        }

        .indicator-label {
            font-size: 7.8px;
            font-weight: 700;
            color: #4b5563;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .indicator-value {
            margin-bottom: 5px;
        }

        .indicator-value.money {
            font-weight: 700;
            margin-bottom: 0;
        }

        .empty-indicator {
            padding: 18px 6px;
            text-align: center;
            color: #777;
        }

        .priority-col {
            width: 24px;
            font-size: 7.5px;
            padding: 3px 2px;
        }

        .priority-cell {
            font-size: 7.5px;
            padding: 3px 2px;
            text-align: center;
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
            $t = $parts[0] ?? '-';
        } else {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $t))));
            $t = $lines[0] ?? '-';
        }

        return $t ?: '-';
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

    $formatRecHtml = function (string $text): string {
        if ($text === '-') {
            return '-';
        }

        $safe = e($text);

        // kasih enter otomatis supaya lebih enak dibaca
        $safe = preg_replace('/\s+(dan|serta|atau)\s+/iu', '<br>$1 ', $safe, 1);

        return $safe;
    };

    $renderIndicator = function (?string $recommendation, $price) use ($summarizeRec, $fmtMoney, $safeFloat, $formatRecHtml): string {
        $rec = $summarizeRec($recommendation);
        $formattedPrice = $fmtMoney($safeFloat($price));

        if ($rec === '-' && $formattedPrice === '-') {
            return '<div class="empty-indicator">-</div>';
        }

        return '
            <div class="indicator-box">
                <div class="indicator-label"></div>
                <div class="indicator-value">' . $formatRecHtml($rec) . '</div>
                <br>
                <br>
                <div class="indicator-label">Estimasi</div>
                <div class="indicator-value money">' . e($formattedPrice) . '</div>
            </div>
        ';
    };
@endphp

<div class="title">Rekap Report Pengecekan Asset</div>

<table>
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2" style="width:78px;">Kode BMN</th>
            <th rowspan="2" style="width:90px;">Nama Device</th>
            <th rowspan="2" style="width:70px;">Kategori</th>

            <th colspan="5" class="group-head" style="width:120px;">Priority Level</th>
            <th colspan="5" class="group-head" style="width:480px;">Rekomendasi & Estimasi Upgrade</th>

            <th rowspan="2" style="width:82px;">Total Estimasi</th>
            <th rowspan="2" style="width:74px;">Tanggal Cek</th>
        </tr>
        <tr>
            <th class="priority-col">RAM</th>
            <th class="priority-col">Storage</th>
            <th class="priority-col">CPU</th>
            <th class="priority-col">Baterai</th>
            <th class="priority-col">Charger</th>

            <th style="width:95px;">RAM</th>
            <th style="width:95px;">Storage</th>
            <th style="width:95px;">CPU</th>
            <th style="width:95px;">Baterai</th>
            <th style="width:95px;">Charger</th>
        </tr>
    </thead>

    <tbody>
    @foreach($assets as $i => $asset)
        @php
            $r = $asset->latestPerformanceReport;

            $ramPrice = $safeFloat($r?->upgrade_ram_price);
            $stoPrice = $safeFloat($r?->upgrade_storage_price);

            $cpuRawPrice = $r?->upgrade_processor_price ?? $r?->upgrade_cpu_price ?? 0;
            $cpuPrice = $safeFloat($cpuRawPrice);

            $batPrice = $safeFloat($r?->upgrade_baterai_price);
            $charPrice = $safeFloat($r?->upgrade_charger_price);

            $ramSummary = $renderIndicator($r?->recommendation_ram, $ramPrice);
            $stoSummary = $renderIndicator($r?->recommendation_storage, $stoPrice);
            $cpuSummary = $renderIndicator($r?->recommendation_processor, $cpuPrice);
            $batSummary = $renderIndicator($r?->recommendation_baterai, $batPrice);
            $charSummary = $renderIndicator($r?->recommendation_charger, $charPrice);

            $totalPrice = $ramPrice + $stoPrice + $cpuPrice + $batPrice + $charPrice;
            $checkedAt = $r?->created_at;
        @endphp

        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="code">{{ $asset->bmn_code ?? '-' }}</td>
            <td class="device">{{ $asset->device_name ?? '-' }}</td>
            <td>{{ $asset->type?->type_name ?? '-' }}</td>

            <td class="priority-cell">{{ $r?->prior_ram ?? '-' }}</td>
            <td class="priority-cell">{{ $r?->prior_storage ?? '-' }}</td>
            <td class="priority-cell">{{ $r?->prior_processor ?? '-' }}</td>
            <td class="priority-cell">{{ $r?->prior_baterai ?? '-' }}</td>
            <td class="priority-cell">{{ $r?->prior_charger ?? '-' }}</td>

            <td class="indicator-cell">{!! $ramSummary !!}</td>
            <td class="indicator-cell">{!! $stoSummary !!}</td>
            <td class="indicator-cell">{!! $cpuSummary !!}</td>
            <td class="indicator-cell">{!! $batSummary !!}</td>
            <td class="indicator-cell">{!! $charSummary !!}</td>

            <td class="text-right nowrap">{{ $fmtMoney($totalPrice) }}</td>
            <td class="date-cell">
                @if($checkedAt)
                    {{ $checkedAt->format('d/m/Y') }}<br>
                    <span class="small">{{ $checkedAt->format('H:i') }}</span>
                @else
                    -
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="muted">
    Dibuat oleh Tim Prakom BPS DKI Jakarta pada : {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
