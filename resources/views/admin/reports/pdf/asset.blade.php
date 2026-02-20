<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Asset</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#111; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .muted { color: #666; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 7px; vertical-align: top; }
        th { background: #f3f4f6; text-align:left; width:220px; }

        .box {
            border: 1px solid #ddd;
            padding: 9px;
            margin-top: 8px;
            border-radius: 4px;
        }

        .section-title { font-weight: 700; margin-bottom: 6px; }
        .pre { white-space: pre-line; line-height: 1.4; }

        .row { margin-top: 6px; }
        .label { font-weight: 700; }
        .small { font-size: 10px; color:#444; }

        /* Preview image */
        .img-wrap { margin-top: 6px; }
        .img-preview {
            max-width: 220px;
            max-height: 220px;
            width: auto;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 3px;
            background: #fff;
        }

            .footer-signature {
        position: fixed;
        bottom: 40px;
        right: 60px;
        text-align: center;
        width: 260px;
    }

    .footer-signature .printed {
        font-size: 11px;
    }

    .signature-name {
        font-size: 11px;
        font-weight: 600;
        margin-top: 60px; /* INI YANG BUAT SPACE */
    }

        a { color: #0d6efd; text-decoration: underline; }
    </style>
</head>
<body>

@php
    use App\Models\Recommendation;

    $fmt = function($p){
        $p=(float)($p ?? 0);
        return $p>0 ? 'Rp '.number_format($p,0,',','.') : '-';
    };

    $cleanBullets = function(?string $t){
        $t = trim((string)$t);
        return $t === '' ? '-' : $t;
    };

    $getOneExplanation = function(string $category, int $priority, ?string $targetType = null): string {
        if ($priority <= 0) return '-';

        $base = Recommendation::query()
            ->where('category', $category)
            ->where('priority_level', $priority);

        if (!empty($targetType)) {
            $exp = (clone $base)->where('target_type', $targetType)->orderBy('id_recommendation')->value('explanation');
            $exp = trim((string)$exp);
            if ($exp !== '') return $exp;
        }

        $exp = (clone $base)->where('target_type', 'SAME_AS_SPEC')->orderBy('id_recommendation')->value('explanation');
        $exp = trim((string)$exp);
        if ($exp !== '') return $exp;

        $exp = (clone $base)->orderBy('id_recommendation')->value('explanation');
        $exp = trim((string)$exp);

        return $exp !== '' ? $exp : '-';
    };

    $storageTargetType = null;
    if (!empty($spec)) {
        if ($spec->is_nvme) $storageTargetType = 'NVME';
        elseif ($spec->is_ssd) $storageTargetType = 'SSD';
        elseif ($spec->is_hdd) $storageTargetType = 'HDD';
    }

    $pRam = (int)($report->prior_ram ?? 0);
    $pSto = (int)($report->prior_storage ?? 0);
    $pCpu = (int)($report->prior_processor ?? 0);

    $ramExplain = $getOneExplanation('RAM', $pRam, null);
    $stoExplain = $getOneExplanation('STORAGE', $pSto, $storageTargetType);
    $cpuExplain = $getOneExplanation('CPU', $pCpu, null);

    $issueNote = isset($spec) ? trim((string)$spec->issue_note) : '';
    $issueNote = $issueNote !== '' ? $issueNote : '-';

    $issueImageUri = isset($spec) ? trim((string)$spec->issue_image_uri) : '';
    $issueImageUri = $issueImageUri !== '' ? $issueImageUri : '';

    // kalau file ada di public/storage, bisa render file path lokal
    $issueImageLocalPath = null;
    if ($issueImageUri !== '') {
        $path = parse_url($issueImageUri, PHP_URL_PATH);
        if ($path) {
            $pos = strpos($path, '/storage/');
            if ($pos !== false) {
                $relative = substr($path, $pos + strlen('/storage/'));
                $candidate = public_path('storage/' . $relative);
                if (file_exists($candidate)) {
                    $issueImageLocalPath = $candidate;
                }
            }
        }
    }
@endphp

<div class="title">Report Pengecekan Asset</div>

<table>
    <tr><th>Kode BMN</th><td>{{ $asset->bmn_code ?? '-' }}</td></tr>
    <tr><th>Nama Device</th><td>{{ $asset->device_name ?? '-' }}</td></tr>
    <tr><th>Kategori</th><td>{{ $asset->type?->type_name ?? '-' }}</td></tr>
    <tr><th>Tahun Pengadaan</th><td>{{ $asset->procurement_year ?? '-' }}</td></tr>
    <tr><th>Owner Asset Saat Ini</th><td>{{ $spec?->owner_asset ?? '-' }}</td></tr>
    <tr><th>Waktu Report</th><td>{{ optional($report->created_at)->format('d/m/Y H:i') }}</td></tr>
</table>

<div class="box">
    <div class="section-title">Priority Level</div>
    <div>RAM: {{ $pRam ?: '-' }} | Storage: {{ $pSto ?: '-' }} | CPU: {{ $pCpu ?: '-' }}</div>
</div>

<div class="box">
    <div class="section-title">Estimasi Harga Upgrade</div>
    <div>RAM: {{ $fmt($report->upgrade_ram_price) }}</div>
    <div>Storage: {{ $fmt($report->upgrade_storage_price) }}</div>
    <br>
    <div><strong>Total :</strong> {{ $fmt(((float)$report->upgrade_ram_price) + ((float)$report->upgrade_storage_price)) }}</div>
</div>

<div class="box">
    <div class="section-title">Rekomendasi</div>

    <div class="row">
        <div class="label">RAM</div>
        <div class="label small">Tindakan</div>
        <div class="pre">{{ $cleanBullets($report->recommendation_ram) }}</div>
        <div class="label small" style="margin-top:6px;">Penjelasan</div>
        <div class="pre">{{ $ramExplain }}</div>
    </div>

    <div class="row" style="margin-top:10px;">
        <div class="label">Storage</div>
        <div class="label small">Tindakan</div>
        <div class="pre">{{ $cleanBullets($report->recommendation_storage) }}</div>
        <div class="label small" style="margin-top:6px;">Penjelasan</div>
        <div class="pre">{{ $stoExplain }}</div>
    </div>

    <div class="row" style="margin-top:10px;">
        <div class="label">CPU</div>
        <div class="label small">Tindakan</div>
        <div class="pre">{{ $cleanBullets($report->recommendation_processor) }}</div>
        <div class="label small" style="margin-top:6px;">Penjelasan</div>
        <div class="pre">{{ $cpuExplain }}</div>
    </div>
</div>

<div class="box">
    <div class="section-title">Keluhan / Catatan Tambahan</div>
    <div class="pre">{{ $issueNote }}</div>

    <div class="row" style="margin-top:10px;">
        <div class="label">Foto Keluhan</div>

        @if($issueImageLocalPath)
            <div class="img-wrap">
                <img class="img-preview" src="{{ $issueImageLocalPath }}" alt="Foto Keluhan">
            </div>

            @if($issueImageUri !== '')
                <div class="small" style="margin-top:6px;">
                    Buka foto: <a href="{{ $issueImageUri }}">{{ $issueImageUri }}</a>
                </div>
            @endif
        @elseif($issueImageUri !== '')
            <div class="small">Tersimpan: <a href="{{ $issueImageUri }}">{{ $issueImageUri }}</a></div>
        @else
            <div class="muted">-</div>
        @endif
    </div>
    </div> <!-- penutup box terakhir -->

    <div class="footer-signature">
        <div class="printed">
            Dibuat pada tanggal {{ now()->format('d/m/Y') }}
        </div>

        <div class="signature-name">
            ( Oleh Tim Prakom BPS DKI Jakarta )
        </div>
    </div>

</body>
</html>

</body>
</html>
