@extends('layouts.app')

@section('title', 'Daftar Tipe Asset')

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
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
    }
</style>

<h4 class="mb-4">Daftar Tipe Asset</h4>

{{-- ACTION BAR --}}
<div class="d-flex justify-content-between align-items-center gap-3 mb-3">

    {{-- SEARCH (opsional, siap dipakai kalau nanti ditambah di controller) --}}
    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
        <select name="type_name" class="form-select filter-select">
            <option value="">Tipe Asset</option>

            @foreach($typeNames as $typeName)
                <option value="{{ $typeName }}"
                    {{ request('type_name') == $typeName ? 'selected' : '' }}>
                    {{ $typeName }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-primary">Cari</button>

        <a href="{{ route('admin.asset-types.index') }}"
           class="btn btn-danger">
            Reset
        </a>
    </form>

    {{-- TAMBAH --}}
    <a href="{{ route('admin.asset-types.create') }}"
       class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Asset
    </a>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">

            <table class="table table-modern align-middle mb-0">
                <thead>
                <tr>
                    <th style="width:70px;">No</th>
                    <th style="width:180px;">Kode Tipe</th>
                    <th>Nama Tipe Asset</th>
                    <th style="width:160px;" class="text-center">Aksi</th>
                </tr>
                </thead>

                <tbody>
                @forelse($types as $type)
                    <tr>
                        <td>
                            {{ $types->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <span class="badge bg-secondary-subtle text-dark fw-semibold">
                                {{ $type->type_code }}
                            </span>
                        </td>

                        <td class="fw-normal">
                            {{ $type->type_name }}
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2">

                                {{-- EDIT --}}
                                <a href="{{ route('admin.asset-types.edit', $type) }}"
                                   class="btn btn-warning btn-icon"
                                   title="Edit Tipe Asset">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- DELETE --}}
                                <button class="btn btn-danger btn-icon js-delete"
                                        data-action="{{ route('admin.asset-types.destroy', $type) }}"
                                        data-title="Yakin ingin menghapus tipe asset ini?"
                                        data-message="Data tipe asset akan terhapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted p-4">
                            Belum ada data tipe asset.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>

        </div>
    </div>
    {{-- PAGINATION --}}
<div class="d-flex flex-column align-items-center mt-4 gap-2">

    <div class="text-muted small">
        Showing {{ $types->firstItem() }}
        to {{ $types->lastItem() }}
        of {{ $types->total() }} results
    </div>

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $types->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
    </div>

</div>
</div>

@endsection
