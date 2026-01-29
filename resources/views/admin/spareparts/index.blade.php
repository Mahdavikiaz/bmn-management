@extends('layouts.app')

@section('title', 'Daftar Sparepart')

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
    .filter-select{ min-width: 200px; }

    .badge-cat{
        font-weight: 700;
        padding: .35rem .7rem;
        letter-spacing: .2px;
        border: 0;
    }

    .badge-cat-ram{
        background: #198754;
        color: #fff;
    }

    .badge-cat-storage{
        background: #ffc107;
        color: #000;
    }

    .badge-type{
        background: #f1f3f5;
        color: #343a40;
        border: 1px solid #e9ecef;
        font-weight: 700;
        padding: .35rem .7rem;
        letter-spacing: .2px;
    }
</style>

<div class="mb-3">
    <h4 class="mb-1">Daftar Sparepart</h4>
    <div class="text-muted small">
        Menampilkan daftar sparepart
    </div>
</div>

{{-- ACTION BAR + FILTER --}}
<div class="d-flex justify-content-between align-items-center gap-3 mb-3">

    {{-- FILTER --}}
    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">

        <select name="category" class="form-select filter-select">
            <option value="">Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>

        <select name="sparepart_type" class="form-select filter-select">
            <option value="">Tipe</option>
            @foreach($types as $type)
                <option value="{{ $type }}" {{ request('sparepart_type') == $type ? 'selected' : '' }}>
                    {{ $type }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-primary">Cari</button>

        <a href="{{ route('admin.spareparts.index') }}" class="btn btn-danger">
            Reset
        </a>
    </form>

    {{-- TAMBAH --}}
    <a href="{{ route('admin.spareparts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Sparepart
    </a>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">

                {{-- HEADER --}}
                <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:120px;">Kategori</th>
                    <th style="width:120px;">Tipe</th>
                    <th>Nama Sparepart</th>
                    <th style="width:120px;">Ukuran</th>
                    <th style="width:140px;">Harga</th>
                    <th style="width:160px;" class="text-center">Aksi</th>
                </tr>
                </thead>

                {{-- BODY --}}
                <tbody>
                @forelse($spareparts as $sparepart)
                    @php
                        $catClass = match($sparepart->category) {
                            'RAM' => 'badge-cat-ram',
                            'STORAGE' => 'badge-cat-storage',
                            default => 'text-bg-secondary',
                        };
                    @endphp

                    <tr>
                        <td>{{ $spareparts->firstItem() + $loop->index }}</td>

                        {{-- CATEGORY --}}
                        <td>
                            <span class="badge rounded-pill badge-cat {{ $catClass }} fw-semibold">
                                {{ $sparepart->category }}
                            </span>
                        </td>

                        {{-- TYPE --}}
                        <td>
                            <span class="badge rounded-pill badge-type fw-semibold">
                                {{ $sparepart->sparepart_type }}
                            </span>
                        </td>

                        {{-- NAME --}}
                        <td>
                            <div class="fw-normal">{{ $sparepart->sparepart_name }}</div>
                        </td>

                        {{-- SIZE --}}
                        <td>{{ $sparepart->size }} GB</td>

                        {{-- PRICE --}}
                        <td>
                            Rp {{ number_format($sparepart->price, 0, ',', '.') }}
                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">

                                <a href="{{ route('admin.spareparts.edit', $sparepart) }}"
                                   class="btn btn-warning btn-icon"
                                   title="Edit Sparepart">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button class="btn btn-danger btn-icon text-white js-delete"
                                        data-action="{{ route('admin.spareparts.destroy', $sparepart) }}"
                                        data-title="Anda yakin ingin menghapus data ini?"
                                        data-message="Data ini akan terhapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted p-4">
                            Belum ada data sparepart.
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
        Showing {{ $spareparts->firstItem() }}
        to {{ $spareparts->lastItem() }}
        of {{ $spareparts->total() }} results
    </div>

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $spareparts->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
    </div>

</div>
</div>

@endsection
