@extends('layouts.app')

@section('title', 'Hasil Pengecekan Asset')

@section('content')

<style>
    .kv{
        display:flex; gap:10px; align-items:flex-start;
        padding:10px 0; border-bottom:1px dashed #e9ecef;
    }
    .kv:last-child{ border-bottom:0; }
    .kv-ic{
        width:38px; height:38px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        background:#eef2ff; color:#0d6efd; flex:0 0 auto;
        font-size:1.1rem;
    }
    .kv-label{ color:#6c757d; font-size:.85rem; }
    .kv-value{ font-weight:700; }
    .badge-prior{
        background:#f1f3f5;
        border:1px solid #dee2e6;
        padding:.35rem .7rem;
        font-weight:700;
    }
    .rec-box{
        white-space: pre-line;
        background:#f8f9fa;
        border:1px solid #e9ecef;
        padding:12px;
        border-radius:12px;
        min-height: 120px;
    }
    .table-mini td{
        font-size:.92rem;
        border-top: 1px solid #eef2f7;
        vertical-align: middle;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">Hasil Pengecekan</h4>
        <div class="text-muted">
            {{ $asset->device_name }} • Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.asset-checks.history', $asset->id_asset) }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i> History
        </a>

        <a href="{{ route('admin.asset-checks.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- SPEC TERKINI --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <div class="fw-semibold">
                    <i class="bi bi-cpu me-1"></i> Spesifikasi Terkini
                </div>
                <div class="text-muted small">
                    @if($latestSpec?->datetime)
                        Terakhir update: <strong>{{ \Carbon\Carbon::parse($latestSpec->datetime)->format('d/m/Y H:i') }}</strong>
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="card-body">
                @if(!$latestSpec)
                    <div class="text-muted fst-italic">Spesifikasi belum diinputkan.</div>
                @else
                    @php
                        $storageType = $latestSpec->is_nvme ? 'NVME' : ($latestSpec->is_ssd ? 'SSD' : ($latestSpec->is_hdd ? 'HDD' : '-'));
                    @endphp

                    <div class="kv">
                        <div class="kv-ic"><i class="bi bi-cpu"></i></div>
                        <div>
                            <div class="kv-label">Processor</div>
                            <div class="kv-value">{{ $latestSpec->processor ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="kv">
                        <div class="kv-ic"><i class="bi bi-memory"></i></div>
                        <div>
                            <div class="kv-label">RAM</div>
                            <div class="kv-value">{{ $latestSpec->ram }} GB</div>
                        </div>
                    </div>

                    <div class="kv">
                        <div class="kv-ic"><i class="bi bi-hdd-stack"></i></div>
                        <div>
                            <div class="kv-label">Storage</div>
                            <div class="kv-value">{{ $latestSpec->storage }} GB ({{ $storageType }})</div>
                        </div>
                    </div>

                    <div class="kv">
                        <div class="kv-ic"><i class="bi bi-windows"></i></div>
                        <div>
                            <div class="kv-label">OS Version</div>
                            <div class="kv-value">{{ $latestSpec->os_version ?: '-' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- REPORT + REKOMENDASI --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-semibold">
                        <i class="bi bi-clipboard-check me-1"></i> Report Pengecekan
                    </div>
                    <div class="text-muted small">
                        Dibuat: <strong>{{ optional($report->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-danger js-delete"
                            data-action="{{ route('admin.asset-checks.reports.destroy', [$asset->id_asset, $report->id_report]) }}"
                            data-title="Hapus report ini?"
                            data-message="Report pengecekan ini akan terhapus permanen.">
                        <i class="bi bi-trash me-1"></i> Hapus
                    </button>
                </div>
            </div>

            <div class="card-body">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-4">
                            <div class="text-muted small">Priority RAM</div>
                            <div class="fs-5 fw-semibold">
                                <span class="badge rounded-pill badge-prior">{{ $report->prior_ram }}</span>
                            </div>
                            <div class="text-muted small mt-2">
                                Estimasi upgrade:
                                <strong>
                                    @if(!empty($report->upgrade_ram_price))
                                        Rp {{ number_format($report->upgrade_ram_price, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-4">
                            <div class="text-muted small">Priority Storage</div>
                            <div class="fs-5 fw-semibold">
                                <span class="badge rounded-pill badge-prior">{{ $report->prior_storage }}</span>
                            </div>
                            <div class="text-muted small mt-2">
                                Estimasi upgrade:
                                <strong>
                                    @if(!empty($report->upgrade_storage_price))
                                        Rp {{ number_format($report->upgrade_storage_price, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-4">
                            <div class="text-muted small">Priority CPU</div>
                            <div class="fs-5 fw-semibold">
                                <span class="badge rounded-pill badge-prior">{{ $report->prior_processor }}</span>
                            </div>
                            <div class="text-muted small mt-2">
                                Estimasi upgrade: <strong>-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="fw-semibold mb-2"><i class="bi bi-memory me-1"></i> Rekomendasi RAM</div>
                        <div class="rec-box">{{ $report->recommendation_ram ?: '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2"><i class="bi bi-hdd-stack me-1"></i> Rekomendasi Storage</div>
                        <div class="rec-box">{{ $report->recommendation_storage ?: '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2"><i class="bi bi-cpu me-1"></i> Rekomendasi CPU</div>
                        <div class="rec-box">{{ $report->recommendation_processor ?: '-' }}</div>
                    </div>
                </div>

                {{-- MINI HISTORY (optional) --}}
                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold"><i class="bi bi-clock-history me-1"></i> History (10 terakhir)</div>
                    <a href="{{ route('admin.asset-checks.history', $asset->id_asset) }}" class="btn btn-sm btn-outline-secondary">
                        Lihat semua
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-mini mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th style="width:200px;">Tanggal</th>
                                <th style="width:240px;">Priority</th>
                                <th style="width:140px;" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $h)
                                <tr>
                                    <td>{{ optional($h->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge rounded-pill badge-prior me-1">RAM {{ $h->prior_ram }}</span>
                                        <span class="badge rounded-pill badge-prior me-1">ST {{ $h->prior_storage }}</span>
                                        <span class="badge rounded-pill badge-prior">CPU {{ $h->prior_processor }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="{{ route('admin.asset-checks.show', [$asset->id_asset, $h->id_report]) }}">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted p-3">Belum ada history.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</div>

@endsection
