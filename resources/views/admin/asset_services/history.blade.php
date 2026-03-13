@extends('layouts.app')

@section('title', 'SIMANIS | History Perbaikan Asset')

@section('content')

<style>
    .table-modern thead th{
        background:#f8f9fa;
        font-weight:700;
        white-space:nowrap;
        border-bottom:2px solid #d0d7e2;
    }
    .table-modern tbody td{
        font-size:.95rem;
        border-top:1px solid #eef2f7;
        vertical-align:middle;
    }
</style>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">History Perbaikan Asset</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->type?->type_name }}) | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.asset-services.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>

        <a href="{{ route('admin.asset-services.create', $asset->id_asset) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Input Perbaikan
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
                        <th style="width:160px;">Tanggal Perbaikan</th>
                        <th>Keterangan Perbaikan</th>
                        <th style="width:160px;">Dibuat</th>
                        <th style="width:120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $i => $service)
                        <tr>
                            <td>{{ $history->firstItem() + $i }}</td>
                            <td class="fw-semibold">{{ optional($service->service_date)->format('d/m/Y') }}</td>
                            <td style="white-space: pre-line;">{{ $service->service_description }}</td>
                            <td>{{ optional($service->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <form method="POST"
                                      action="{{ route('admin.asset-services.destroy', [$asset->id_asset, $service->id_service]) }}"
                                      onsubmit="return confirm('Yakin ingin menghapus record perbaikan ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">
                                Belum ada history perbaikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($history, 'links'))
        <div class="d-flex flex-column align-items-center mt-4 gap-2">
            <div class="text-muted small">
                Showing {{ $history->firstItem() }}
                to {{ $history->lastItem() }}
                of {{ $history->total() }} results
            </div>

            <div class="mt-2">
                {{ $history->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
            </div>
        </div>
    @endif
</div>

@endsection