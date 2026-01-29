@extends('layouts.app')

@section('title', 'Report Asset')

@section('content')

<style>
    .table-modern thead th{ background:#f8f9fa; font-weight:500; white-space: nowrap; border-bottom:2px solid #d0d7e2; }
    .table-modern tbody td{ font-size:.90rem; border-top:1px solid #eef2f7; vertical-align:middle; }
</style>

<div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-3">

    {{-- KIRI : TITLE + FILTER --}}
    <div class="flex-grow-1">
        <h4 class="mb-1">Report Asset</h4>
        <div class="text-muted small mb-2">
            Menampilkan asset yang sudah memiliki report (hasil pengecekan terakhir).
        </div>

        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text"
                   name="q"
                   class="form-control"
                   style="max-width: 220px;"
                   placeholder="Cari Kode BMN / Nama Device..."
                   value="{{ request('q') }}">

            <select name="id_type" class="form-select" style="max-width: 180px;">
                <option value="">Semua Kategori</option>
                @foreach($types as $t)
                    <option value="{{ $t->id_type }}" {{ request('id_type') == $t->id_type ? 'selected' : '' }}>
                        {{ $t->type_name }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Search
            </button>

            <a href="{{ route('admin.reports.index') }}" class="btn btn-danger">
                Reset
            </a>
        </form>
    </div>

    {{-- KANAN : EXPORT --}}
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('admin.reports.export.all.excel') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.reports.export.all.pdf') }}" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </div>

</div>


<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width: 160px">Kode BMN</th>
                        <th style="width: 200px">Nama Device</th>
                        <th style="width:160px;">Kategori</th>
                        <th style="width:220px;">Report Terakhir</th>
                        <th style="width:220px;">Priority</th>
                        <th style="width:200px;">Estimasi Upgrade</th>
                        <th style="width:210px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($assets as $asset)
                    @php
                        $r = $asset->latestPerformanceReport;
                        $fmtPrice = function($p){
                            $p = (float) $p;
                            return $p > 0 ? 'Rp ' . number_format($p,0,',','.') : '-';
                        };
                    @endphp

                    <tr>
                        <td>{{ ($assets->currentPage()-1)*$assets->perPage() + $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $asset->bmn_code ?? '-' }}</td>
                        <td>{{ $asset->device_name ?? '-' }}</td>
                        <td>{{ $asset->type?->type_name ?? '-' }}</td>

                        <td>
                            <div class="fw-semibold">{{ optional($r?->created_at)->format('d/m/Y H:i') ?? '-' }}</div>
                        </td>

                        <td>
                            <span class="badge text-bg-light border">RAM: {{ $r?->prior_ram ?? '-' }}</span>
                            <span class="badge text-bg-light border">STORAGE: {{ $r?->prior_storage ?? '-' }}</span>
                            <span class="badge text-bg-light border">CPU: {{ $r?->prior_processor ?? '-' }}</span>
                        </td>

                        <td>
                            <div class="text-muted small">RAM: <strong>{{ $fmtPrice($r?->upgrade_ram_price) }}</strong></div>
                            <div class="text-muted small">Storage: <strong>{{ $fmtPrice($r?->upgrade_storage_price) }}</strong></div>
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('admin.reports.export.asset.excel', $asset->id_asset) }}"
                                   class="btn btn-sm btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Excel
                                </a>

                                <a href="{{ route('admin.reports.export.asset.pdf', $asset->id_asset) }}"
                                   class="btn btn-sm btn-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted p-4">
                            Belum ada asset yang memiliki report.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($assets, 'links'))
        <div class="card-footer bg-white">
            {{ $assets->links() }}
        </div>
    @endif
</div>

@endsection
