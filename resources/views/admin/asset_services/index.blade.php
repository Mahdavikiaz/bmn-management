@extends('layouts.app')

@section('title', 'SIMANIS | Perbaikan Asset')

@section('content')

<style>
    .table-modern thead th{
        background:#f8f9fa;
        font-weight:500;
        white-space: nowrap;
    }
    .table-modern tbody tr:hover{ background:#f6f9ff; }
    .table-modern tbody td{
        font-size: 0.90rem;
        border-top: 1px solid #eef2f7;
        vertical-align: middle;
    }
    .table-modern thead th{
        font-size: 1rem;
        border-bottom: 2px solid #d0d7e2;
    }
    .table-modern td,
    .table-modern th{
        padding-top: .65rem;
        padding-bottom: .65rem;
    }

    .text-muted-sm{ color:#6c757d; font-size:.85rem; }

    .aksi-wrap{
        display:flex;
        justify-content:center;
        align-items:center;
        gap:.5rem;
        flex-wrap:nowrap;
        white-space:nowrap;
    }

    .btn-icon{
        width:38px; height:38px;
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:10px;
        padding:0;
    }

    .btn-action{
        height:38px;
        border-radius:10px;
        display:inline-flex;
        align-items:center;
        gap:.5rem;
        padding:0 .9rem;
        white-space:nowrap;
    }

    .col-aksi{ min-width: 240px; }
</style>

<div class="mb-3">
    <h4 class="mb-3">Perbaikan Asset</h4>
    <div class="text-muted small">
        Halaman untuk mencatat perbaikan pada asset
    </div>
</div>

<form method="GET" class="d-flex gap-2 align-items-center flex-wrap mb-3">

    <input type="text"
           name="q"
           class="form-control"
           style="max-width: 360px;"
           placeholder="Cari Kode BMN / Nama Device..."
           value="{{ request('q') }}">

    <select name="id_type" class="form-select" style="max-width: 280px;">
        <option value="">Semua Tipe Asset</option>
        @foreach($types as $t)
            <option value="{{ $t->id_type }}" {{ (string)request('id_type') === (string)$t->id_type ? 'selected' : '' }}>
                {{ $t->type_name }}
            </option>
        @endforeach
    </select>

    <select name="status_service" class="form-select" style="max-width: 240px;">
        <option value="">Semua Status Perbaikan</option>
        <option value="serviced" {{ request('status_service') == 'serviced' ? 'selected' : '' }}>Sudah Diperbaiki</option>
        <option value="unserviced" {{ request('status_service') == 'unserviced' ? 'selected' : '' }}>Belum Diperbaiki</option>
    </select>

    <button class="btn btn-primary">
        <i class="bi bi-search me-1"></i> Search
    </button>

    <a href="{{ route('admin.asset-services.index') }}" class="btn btn-danger">
        Reset
    </a>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Kode BMN</th>
                        <th>Nama Device</th>
                        <th style="width:160px;">Kategori</th>
                        <th style="width:260px;">Terakhir Diperbaiki</th>
                        <th style="width:60px;" class="text-center col-aksi">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($assets as $asset)
                        @php
                            $lastService = $asset->latestService ?? null;
                            $historyCount = $asset->services_count ?? 0;
                            $lastReport = $asset->latestPerformanceReport ?? null;
                        @endphp

                        <tr>
                            <td>
                                {{ method_exists($assets,'currentPage')
                                    ? (($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration)
                                    : $loop->iteration
                                }}
                            </td>

                            <td class="fw-normal">{{ $asset->bmn_code }}</td>

                            <td>
                                <div class="fw-normal">{{ $asset->device_name }}</div>
                            </td>

                            <td class="fw-normal">
                                {{ $asset->type?->type_name ?? '-' }}
                            </td>

                            <td>
                                @if($lastService)
                                    <div class="fw-semibold">
                                        {{ optional($lastService->service_date)->format('d/m/Y') }}
                                    </div>
                                    <div class="text-muted-sm">
                                        History : Sudah <strong>{{ $historyCount }}x</strong> perbaikan
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Belum pernah diperbaiki</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="aksi-wrap">

                                    {{-- input data perbaikan --}}
                                    <a href="{{ route('admin.asset-services.create', $asset->id_asset) }}"
                                       class="btn btn-primary btn-action"
                                       title="Input data perbaikan asset">
                                        <i class="bi bi-tools"></i>
                                    </a>

                                    {{-- lihat hal yang perlu diperbaiki dari hasil pengecekan terakhir --}}
                                    @if($lastReport)
                                        <a href="{{ route('admin.asset-checks.show', [$asset->id_asset, $lastReport->id_report]) }}"
                                           class="btn btn-info btn-icon text-white"
                                           title="Lihat hasil pengecekan terakhir">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- history perbaikan --}}
                                        <a href="{{ route('admin.asset-services.history', $asset->id_asset) }}"
                                           class="btn btn-secondary btn-icon"
                                           title="History perbaikan">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-icon" disabled title="Belum ada hasil pengecekan">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <a href="{{ route('admin.asset-services.history', $asset->id_asset) }}"
                                           class="btn btn-secondary btn-icon"
                                           title="History perbaikan">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted p-4">
                                Belum ada data asset.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($assets, 'links'))
    <div class="d-flex flex-column align-items-center mt-4 gap-2">
        <div class="text-muted small">
            Showing {{ $assets->firstItem() }}
            to {{ $assets->lastItem() }}
            of {{ $assets->total() }} results
        </div>

        <div class="mt-2">
            {{ $assets->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
        </div>
    </div>
    @endif
</div>

@endsection