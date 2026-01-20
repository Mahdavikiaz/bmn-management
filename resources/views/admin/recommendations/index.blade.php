@extends('layouts.app')

@section('title', 'Daftar Recommendation')

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

    .filter-select{ min-width: 200px; }

    .badge-cat{
        font-weight: 700;
        padding: .35rem .7rem;
        letter-spacing: .2px;
    }

    .badge-cat-ram{ background:#198754; color:#fff; }
    .badge-cat-storage{ background:#ffc107; color:#000; }
    .badge-cat-cpu{ background:#0d6efd; color:#fff; }

    .badge-priority{
        background:#f1f3f5;
        color:#212529;
        border:1px solid #dee2e6;
        font-weight:700;
        padding:.35rem .7rem;
    }
</style>

<h4 class="mb-4">Daftar Recommendation</h4>

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

        <select name="priority_level" class="form-select filter-select">
            <option value="">Prioritas</option>

            <option value="1" {{ request('priority_level') == 1 ? 'selected' : '' }}>
                1 - Sangat Rendah
            </option>
            <option value="2" {{ request('priority_level') == 2 ? 'selected' : '' }}>
                2 - Rendah
            </option>
            <option value="3" {{ request('priority_level') == 3 ? 'selected' : '' }}>
                3 - Sedang
            </option>
            <option value="4" {{ request('priority_level') == 4 ? 'selected' : '' }}>
                4 - Tinggi
            </option>
            <option value="5" {{ request('priority_level') == 5 ? 'selected' : '' }}>
                5 - Sangat Tinggi
            </option>
        </select>

        <button class="btn btn-primary">Cari</button>

        <a href="{{ route('admin.recommendations.index') }}" class="btn btn-danger">
            Reset
        </a>
    </form>

    {{-- TAMBAH --}}
    <a href="{{ route('admin.recommendations.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Data
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
                    <th>Deskripsi Recommendation</th>
                    <th style="width:140px;">Priority</th>
                    <th style="width:160px;" class="text-center">Aksi</th>
                </tr>
                </thead>

                <tbody>
                @forelse($recommendations as $recommendation)
                    @php
                        $catClass = match($recommendation->category) {
                            'RAM' => 'badge-cat-ram',
                            'STORAGE' => 'badge-cat-storage',
                            'CPU' => 'badge-cat-cpu',
                            default => 'text-bg-secondary',
                        };
                    @endphp

                    <tr>
                        <td>{{ $recommendations->firstItem() + $loop->index }}</td>

                        <td>
                            <span class="badge rounded-pill badge-cat {{ $catClass }}">
                                {{ $recommendation->category }}
                            </span>
                        </td>

                        <td>{{ $recommendation->description }}</td>

                        <td>
                        @php
                            $priorityLabel = match($recommendation->priority_level) {
                                1 => 'Sangat Rendah',
                                2 => 'Rendah',
                                3 => 'Sedang',
                                4 => 'Tinggi',
                                5 => 'Sangat Tinggi',
                                default => '-',
                            };
                        @endphp

                        <span class="badge rounded-pill badge-priority">
                            {{ $recommendation->priority_level }} - {{ $priorityLabel }}
                        </span>
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2">

                                <a href="{{ route('admin.recommendations.edit', $recommendation) }}"
                                   class="btn btn-warning btn-icon"
                                   title="Edit Recommendation">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button class="btn btn-danger btn-icon js-delete"
                                        data-action="{{ route('admin.recommendations.destroy', $recommendation) }}"
                                        data-title="Yakin ingin menghapus data ini?"
                                        data-message="Data recommendation akan terhapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            Belum ada data recommendation.
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
    {{ $recommendations->links() }}
</div>

{{-- SUCCESS MODAL --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('globalSuccessModal');
            if (!modalEl) return;

            const msgEl = document.getElementById('successMessage');
            if (msgEl) msgEl.textContent = @json(session('success'));

            new bootstrap.Modal(modalEl).show();
        });
    </script>
@endif

@endsection
