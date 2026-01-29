<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Asset</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align:left; }
        .box { border:1px solid #ddd; padding:10px; margin-top:10px; }
        .pre { white-space: pre-line; }
    </style>
</head>
<body>

<div class="title">Report Pengecekan Asset</div>
<div class="muted">
    Dicetak: {{ now()->format('d/m/Y H:i') }}
</div>

<table>
    <tr>
        <th style="width:220px;">Kode BMN</th>
        <td>{{ $asset->bmn_code ?? '-' }}</td>
    </tr>
    <tr>
        <th>Nama Device</th>
        <td>{{ $asset->device_name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Kategori</th>
        <td>{{ $asset->type?->type_name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Tahun Pengadaan</th>
        <td>{{ $asset->procurement_year ?? '-' }}</td>
    </tr>
    <tr>
        <th>Owner Asset Saat Ini</th>
        <td>{{ $spec?->owner_asset ?? '-' }}</td>
    </tr>
    <tr>
        <th>Waktu Report</th>
        <td>{{ optional($report->created_at)->format('d/m/Y H:i') }}</td>
    </tr>
</table>

<div class="box">
    <div><strong>Priority Level</strong></div>
    <div>RAM: {{ $report->prior_ram ?? '-' }} | Storage: {{ $report->prior_storage ?? '-' }} | CPU: {{ $report->prior_processor ?? '-' }}</div>
</div>

@php
    $fmt = function($p){
        $p=(float)$p;
        return $p>0 ? 'Rp '.number_format($p,0,',','.') : '-';
    };
@endphp

<div class="box">
    <div><strong>Estimasi Harga Upgrade</strong></div>
    <div>RAM: {{ $fmt($report->upgrade_ram_price) }}</div>
    <div>Storage: {{ $fmt($report->upgrade_storage_price) }}</div>
    <br>
    <div><strong>Total : </strong>{{ $fmt($report->upgrade_ram_price + $report->upgrade_storage_price) }}</div>
</div>

<div class="box">
    <div><strong>Rekomendasi</strong></div>
    <div style="margin-top:6px;"><strong>RAM</strong></div>
    <div class="pre">{{ $report->recommendation_ram ?? '-' }}</div>

    <div style="margin-top:10px;"><strong>Storage</strong></div>
    <div class="pre">{{ $report->recommendation_storage ?? '-' }}</div>

    <div style="margin-top:10px;"><strong>CPU</strong></div>
    <div class="pre">{{ $report->recommendation_processor ?? '-' }}</div>
</div>

</body>
</html>
