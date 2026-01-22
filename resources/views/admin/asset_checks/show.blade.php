@extends('layouts.app')

@section('title', 'Hasil Pengecekan')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Hasil Pengecekan</h4>
        <div class="text-muted">
            {{ $asset->device_name }} • Kode BMN: <strong>{{ $asset->bmn_code }}</strong> •
            Waktu: <strong>{{ $report->datetime?->format('d/m/Y H:i') ?? '-' }}</strong>
        </div>
    </div>

    <a href="{{ route('admin.asset-checks.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Priority</h6>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>RAM</span><strong>{{ $report->prior_ram }}</strong>
                </div>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>Storage</span><strong>{{ $report->prior_storage }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span>CPU</span><strong>{{ $report->prior_processor }}</strong>
                </div>

                <hr>

                <h6 class="fw-semibold mb-2">Estimasi Upgrade</h6>
                <div class="small text-muted">*(muncul jika prior ≥ 4)</div>
                <div class="mt-2">
                    <div>RAM: <strong>{{ $report->upgrade_ram_price ? 'Rp '.number_format($report->upgrade_ram_price,0,',','.') : '-' }}</strong></div>
                    <div>Storage: <strong>{{ $report->upgrade_storage_price ? 'Rp '.number_format($report->upgrade_storage_price,0,',','.') : '-' }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Rekomendasi RAM</h6>
                <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;">{{ $report->recommendation_ram }}</pre>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Rekomendasi Storage</h6>
                <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;">{{ $report->recommendation_storage }}</pre>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Rekomendasi CPU</h6>
                <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;">{{ $report->recommendation_processor }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
