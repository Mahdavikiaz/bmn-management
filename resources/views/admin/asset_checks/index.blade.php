@extends('layouts.app')

@section('title', 'Pengecekan Asset')

@section('content')

<style>
    .table-modern thead th{
        background:#f8f9fa;
        font-weight:700;
        white-space: nowrap;
    }
    .table-modern tbody tr:hover{ background:#f6f9ff; }
    .table-modern tbody td{
        font-size: 0.95rem;
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
    .btn-icon{
        width:38px; height:38px;
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:10px;
    }
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Pengecekan Asset</h4>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="fw-semibold">No</th>
                        <th class="fw-semibold">Kode BMN</th>
                        <th class="fw-semibold">Nama Device</th>
                        <th style="width:110px;" class="fw-semibold">Kategori</th>
                        <th style="width:260px;" class="fw-semibold">Hasil Terakhir</th>
                        <th style="width:220px;" class="text-center fw-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($assets as $asset)
                        @php
                            $lastReport = $asset->latestPerformanceReport ?? null;
                            $historyCount = $asset->performance_reports_count ?? 0;
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
                                <div class="text-muted-sm">
                                    GPU: {{ $asset->gpu }} • RAM Type: {{ $asset->ram_type }}
                                </div>
                            </td>

                            <td>
                                @if($asset->device_type == 'PC')
                                    <span class="badge rounded-pill text-bg-success fw-semibold">PC</span>
                                @else
                                    <span class="badge rounded-pill text-bg-warning fw-semibold">Laptop</span>
                                @endif
                            </td>

                            <td>
                                @if($lastReport)
                                    <div class="fw-semibold">
                                        {{ optional($lastReport->created_at)->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-muted-sm">
                                        Prior: RAM {{ $lastReport->prior_ram }},
                                        Storage {{ $lastReport->prior_storage }},
                                        CPU {{ $lastReport->prior_processor }}
                                    </div>
                                    <div class="text-muted-sm">
                                        History: <strong>{{ $historyCount }}</strong> kali
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Belum pernah dicek</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-center">

                                    {{-- Lakukan pengecekan --}}
                                    <a href="{{ route('admin.asset-checks.create', $asset->id_asset) }}"
                                       class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-clipboard-check"></i>
                                        Pengecekan
                                    </a>

                                    {{-- Lihat hasil terakhir --}}
                                    @if($lastReport)
                                        <a href="{{ route('admin.asset-checks.show', [$asset->id_asset, $lastReport->id_report]) }}"
                                           class="btn btn-info btn-icon text-white"
                                           title="Lihat hasil terakhir">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        {{-- History --}}
                                        <a href="{{ route('admin.asset-checks.history', $asset->id_asset) }}"
                                           class="btn btn-secondary btn-icon"
                                           title="History pengecekan">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-icon" disabled title="Belum ada hasil">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <button class="btn btn-outline-secondary btn-icon" disabled title="Belum ada history">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
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
        <div class="card-footer bg-white">
            {{ $assets->links() }}
        </div>
    @endif
</div>

@endsection
