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
        .pre { white-space: pre-line; }
    </style>
</head>
<body>

<div class="title">Rekap Report Pengecekan Asset</div>
<div class="muted">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>

<table>
    <thead>
        <tr>
            <th style="width:30px;">No</th>
            <th style="width:120px;">Kode BMN</th>
            <th>Nama</th>
            <th style="width:120px;">Kategori</th>
            <th style="width:60px;">RAM</th>
            <th style="width:60px;">STO</th>
            <th style="width:60px;">CPU</th>
            <th style="width:120px;">Tanggal</th>
        </tr>
    </thead>
    <tbody>
    @foreach($assets as $i => $asset)
        @php $r = $asset->latestPerformanceReport; @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $asset->bmn_code ?? '-' }}</td>
            <td>{{ $asset->device_name ?? '-' }}</td>
            <td>{{ $asset->type?->type_name ?? '-' }}</td>
            <td>{{ $r?->prior_ram ?? '-' }}</td>
            <td>{{ $r?->prior_storage ?? '-' }}</td>
            <td>{{ $r?->prior_processor ?? '-' }}</td>
            <td>{{ optional($r?->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
