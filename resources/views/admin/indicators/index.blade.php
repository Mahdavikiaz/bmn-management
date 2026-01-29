@extends('layouts.app')

@section('title', 'Daftar Indikator')

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
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
    }

    .filter-select{ min-width: 200px; }

    .badge-cat{
        font-weight:700;
        padding:.35rem .7rem;
        letter-spacing:.2px;
    }

    .badge-cat-ram{ background:#198754; color:#fff; }
    .badge-cat-storage{ background:#ffc107; color:#000; }
    .badge-cat-cpu{ background:#0d6efd; color:#fff; }

    .badge-label{
        background:#f1f3f5;
        border:1px solid #dee2e6;
        font-weight:700;
        padding:.3rem .6rem;
    }
</style>

<h4 class="mb-4">Daftar Indikator</h4>

{{-- ACTION BAR --}}
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

        <input type="text"
            name="q"
            class="form-control w-auto"
            style="width: 200px"
            placeholder="Cari nama indikator..."
            value="{{ request('q') }}">

        <button class="btn btn-primary">Cari</button>

        <a href="{{ route('admin.indicator-questions.index') }}" class="btn btn-danger">
            Reset
        </a>
    </form>

    {{-- TAMBAH --}}
    <a href="{{ route('admin.indicator-questions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Indikator
    </a>
</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">

                <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:120px;">Kategori</th>
                    <th>Nama Indikator</th>
                    <th>Pertanyaan</th>
                    <th style="width:180px;" class="text-center">Aksi</th>
                </tr>
                </thead>

                <tbody>
                @forelse($indicators as $indicator)
                    @php
                        $catClass = match($indicator->category) {
                            'RAM' => 'badge-cat-ram',
                            'STORAGE' => 'badge-cat-storage',
                            'CPU' => 'badge-cat-cpu',
                            default => 'text-bg-secondary',
                        };
                    @endphp

                    <tr>
                        <td>{{ $indicators->firstItem() + $loop->index }}</td>

                        <td>
                            <span class="badge rounded-pill badge-cat {{ $catClass }} fw-semibold">
                                {{ $indicator->category }}
                            </span>
                        </td>

                        <td class="fw-normal">
                            {{ $indicator->indicator_name }}
                        </td>

                        <td class="text-muted">
                            {{ Str::limit($indicator->question, 80) }}
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2">

                                {{-- DETAIL --}}
                                <button class="btn btn-info btn-icon text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal{{ $indicator->id_question }}"
                                        title="Detail Indicator">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- EDIT --}}
                                <a href="{{ route('admin.indicator-questions.edit', $indicator) }}"
                                   class="btn btn-warning btn-icon"
                                   title="Edit Indicator">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- DELETE --}}
                                <button class="btn btn-danger btn-icon js-delete"
                                        data-action="{{ route('admin.indicator-questions.destroy', $indicator) }}"
                                        data-title="Yakin ingin menghapus indicator ini?"
                                        data-message="Data indicator dan opsi akan terhapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- MODAL DETAIL --}}
                    <div class="modal fade" id="detailModal{{ $indicator->id_question }}" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">

                                {{-- HEADER --}}
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-semibold mb-3">
                                        Detail Indikator
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                {{-- BODY --}}
                                <div class="modal-body pt-2">

                                    {{-- INFO --}}
                                    <div class="d-flex flex-wrap gap-3 mb-4">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2 fw-semibold">
                                            {{ $indicator->category }}
                                        </span>

                                        <span class="fw-normal">
                                            {{ $indicator->indicator_name }}
                                        </span>
                                    </div>

                                    {{-- PERTANYAAN --}}
                                    <div class="mb-4">
                                        <div class="text-muted small mb-1">Pertanyaan</div>
                                        <div class="p-3 bg-light rounded-3">
                                            {{ $indicator->question }}
                                        </div>
                                    </div>

                                    {{-- OPSI --}}
                                    <div>
                                        <div class="text-muted small mb-2">Opsi Jawaban</div>

                                        @foreach($indicator->options as $opt)
                                            <div class="d-flex align-items-center justify-content-between
                                                        border rounded-3 px-3 py-2 mb-2">

                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="badge bg-primary">
                                                        {{ $opt->label }}
                                                    </span>

                                                    <span>
                                                        {{ $opt->option }}
                                                    </span>
                                                </div>

                                                <div class="text-warning fw-semibold small">
                                                    ⭐ {{ $opt->star_value }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>

                                {{-- FOOTER --}}
                                <div class="modal-footer border-0 pt-0">
                                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                        Tutup
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            Belum ada data indicator.
                        </td>
                    </tr>
                @endforelse
                </tbody>

            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
@if(method_exists($indicators, 'links'))
<div class="d-flex flex-column align-items-center mt-4 gap-2">

    <div class="text-muted small">
        Showing {{ $indicators->firstItem() }}
        to {{ $indicators->lastItem() }}
        of {{ $indicators->total() }} results
    </div>

    {{-- Pagination --}}
    <div class="mt-2">
        {{ $indicators->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
    </div>

</div>
@endif
</div>


@endsection
