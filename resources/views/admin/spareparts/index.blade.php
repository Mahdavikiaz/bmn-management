@extends('layouts.app')

@section('title', 'Daftar Sparepart')

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

<h4 class="mb-4">Daftar Sparepart</h4>

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
        <i class="bi bi-plus-lg me-1"></i> Tambah Data
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
                    <th style="width:60px;" class="fw-semibold">No</th>
                    <th style="width:120px;" class="fw-semibold">Kategori</th>
                    <th style="width:120px;" class="fw-semibold">Tipe</th>
                    <th class="fw-semibold">Nama Sparepart</th>
                    <th style="width:120px;" class="fw-semibold">Ukuran</th>
                    <th style="width:140px;" class="fw-semibold">Harga</th>
                    <th style="width:160px;" class="text-center fw-semibold">Aksi</th>
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
</div>

{{-- PAGINATION --}}
<div class="mt-3">
    {{ $spareparts->links() }}
</div>

{{-- SUCCESS MODAL (POPUP) --}}
<div class="modal fade" id="globalSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-4 text-center">

            {{-- ICON --}}
            <div class="mx-auto mb-3">
                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px; height:72px;">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
            </div>

            {{-- TITLE --}}
            <h4 class="fw-semibold mb-2" id="successTitle">
                Berhasil
            </h4>

            {{-- MESSAGE --}}
            <p class="text-muted mb-4 px-3" id="successMessage">
                Data berhasil diproses.
            </p>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-center">
                <button type="button"
                        class="btn btn-primary px-4 d-flex align-items-center gap-2"
                        data-bs-dismiss="modal">
                    Oke
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Trigger modal jika ada session success --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('globalSuccessModal');
            if (!modalEl) return;

            const msgEl = document.getElementById('successMessage');
            if (msgEl) msgEl.textContent = @json(session('success'));

            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    </script>
@endif

@endsection
