@extends('layouts.app')

@section('title', 'Pengecekan Asset')

@section('content')
<style>
    .table-modern thead th{ background:#f8f9fa; font-weight:700; white-space:nowrap; }
    .table-modern tbody tr:hover{ background:#f6f9ff; }
    .table-modern tbody td{ font-size:.95rem; border-top:1px solid #eef2f7; }
    .table-modern thead th{ font-size:1rem; border-bottom:2px solid #d0d7e2; }
    .btn-icon{ width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border-radius:10px; }
</style>

<h4 class="mb-4">Pengecekan Asset</h4>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Kode BMN</th>
                        <th>Nama Device</th>
                        <th style="width:120px;">Tipe</th>
                        <th style="width:160px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td>{{ method_exists($assets,'currentPage') ? (($assets->currentPage()-1)*$assets->perPage()+$loop->iteration) : $loop->iteration }}</td>
                        <td>{{ $asset->bmn_code }}</td>
                        <td class="fw-semibold">{{ $asset->device_name }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $asset->device_type=='PC' ? 'text-bg-success' : 'text-bg-warning' }} fw-semibold">
                                {{ $asset->device_type }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.asset-checks.create', $asset->id_asset) }}"
                               class="btn btn-primary">
                                <i class="bi bi-clipboard-check me-1"></i> Lakukan Pengecekan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">Belum ada data asset.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($assets,'links'))
        <div class="card-footer bg-white">
            {{ $assets->links() }}
        </div>
    @endif
</div>
@endsection
