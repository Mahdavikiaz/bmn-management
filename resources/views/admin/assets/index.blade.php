@extends('layouts.app')

@section('title', 'SIMANIS | Daftar Asset')

@section('content')

<style>
    .table-modern thead th{
        background:#f8f9fa;
        font-weight:500;
        white-space: nowrap;
        border-bottom:2px solid #d0d7e2;
    }
    .table-modern tbody td{
        font-size:.90rem;
        border-top:1px solid #eef2f7;
        vertical-align:middle;
    }
    .table-modern tbody tr:hover{ background:#f6f9ff; }

    .btn-icon{
        width:38px; height:38px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
    }

    .spec-kv{
        display:flex; gap:10px; align-items:flex-start;
        padding:10px 0; border-bottom:1px dashed #e9ecef;
    }
    .spec-kv:last-child{ border-bottom:0; }
    .spec-ic{
        width:38px; height:38px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        background:#eef2ff; color:#0d6efd;
        font-size:1.1rem;
    }
    .spec-label{ color:#6c757d; font-size:.85rem; }
    .spec-value{ font-weight:600; }
</style>

{{-- HEADER + FILTER + EXPORT --}}
<div class="d-flex justify-content-between align-items-end mb-3 flex-wrap gap-3">

    {{-- KIRI : TITLE + FILTER --}}
    <div class="flex-grow-1">
        <h4 class="mb-3">Daftar Asset</h4>
        <div class="text-muted small mb-3">
            Menampilkan seluruh data asset
        </div>

        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="id_type" class="form-select" style="max-width: 260px;">
                <option value="">Pilih tipe...</option>
                @foreach(($types ?? []) as $t)
                    <option value="{{ $t->id_type }}" {{ request('id_type') == $t->id_type ? 'selected' : '' }}>
                        {{ $t->type_name }}
                    </option>
                @endforeach
            </select>

            <input type="text"
                   name="q"
                   class="form-control"
                   style="max-width: 340px;"
                   placeholder="Cari kode BMN / nama device..."
                   value="{{ request('q') }}">

            <button class="btn btn-primary">
                <i class="bi bi-search me-1"></i> Cari
            </button>

            <a href="{{ route('admin.assets.index') }}" class="btn btn-danger">
                Reset
            </a>
        </form>
    </div>

    {{-- KANAN : EXPORT --}}
    <div class="d-flex gap-2 flex-shrink-0">
        <a href="{{ route('admin.assets.export.all.excel', request()->query()) }}"
           class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Export Data Asset
        </a>

        <a href="{{ route('admin.assets.create') }}"
           class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Asset
        </a>
    </div>

</div>

{{-- TABLE --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:160px;">Kode BMN</th>
                        <th>Nama Device</th>
                        <th style="width:180px;">Pemegang Asset</th>
                        <th style="width:140px;">Kategori</th>
                        <th>GPU</th>
                        <th>Tipe RAM</th>
                        <th style="width:140px;">Tahun Pengadaan</th>
                        <th style="width:200px;">Spesifikasi</th>
                        <th style="width:180px;" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($assets as $asset)
                    @php
                        $latest = $asset->latestSpecification ?? null;
                        $modalId = 'specModal-'.$asset->id_asset;
                    @endphp

                    <tr>
                        <td>
                            {{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}
                        </td>

                        <td class="fw-normal">{{ $asset->bmn_code }}</td>
                        <td>{{ $asset->device_name }}</td>
                        <td>{{ $latest->owner_asset ?? '-' }}</td>
                        <td>{{ $asset->type?->type_name ?? '-' }}</td>
                        <td>{{ $asset->gpu ?: '-' }}</td>
                        <td>{{ $asset->ram_type ?: '-' }}</td>
                        <td>{{ $asset->procurement_year }}</td>

                        {{-- SPESIFIKASI --}}
                        <td>
                            @if(!$latest)
                                <span class="text-muted fst-italic">Belum diinputkan</span>
                            @else
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#{{ $modalId }}">
                                    <i class="bi bi-eye"></i> Lihat Spesifikasi
                                </button>

                                {{-- MODAL --}}
                                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <div>
                                                    <h5 class="modal-title">Spesifikasi Asset</h5>
                                                    <div class="text-muted small mt-2">
                                                        <strong>{{ $asset->device_name }} ({{ $asset->type?->type_name ?? '-' }})</strong> |
                                                        Kode BMN: <strong>{{ $asset->bmn_code }}</strong> |
                                                        Tahun Pengadaan: <strong>{{ $asset->procurement_year }}</strong>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                @php
                                                    $media = [];
                                                    if($latest->is_hdd) $media[] = 'HDD';
                                                    if($latest->is_ssd) $media[] = 'SSD';
                                                    if($latest->is_nvme) $media[] = 'NVMe';
                                                @endphp

                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-person"></i></div>
                                                            <div>
                                                                <div class="spec-label">Pemegang Asset</div>
                                                                <div class="spec-value">{{ $latest->owner_asset ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-cpu"></i></div>
                                                            <div>
                                                                <div class="spec-label">Processor</div>
                                                                <div class="spec-value">{{ $latest->processor ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-memory"></i></div>
                                                            <div>
                                                                <div class="spec-label">RAM</div>
                                                                <div class="spec-value">{{ $latest->ram }} GB</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-hdd-stack"></i></div>
                                                            <div>
                                                                <div class="spec-label">Storage</div>
                                                                <div class="spec-value">{{ $latest->storage }} GB</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-nvidia"></i></div>
                                                            <div>
                                                                <div class="spec-label">GPU</div>
                                                                <div class="spec-value fw-semibold">{{ $asset->gpu ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-device-hdd"></i></div>
                                                            <div>
                                                                <div class="spec-label">Tipe RAM</div>
                                                                <div class="spec-value fw-semibold">{{ $asset->ram_type ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-device-ssd"></i></div>
                                                            <div>
                                                                <div class="spec-label">Tipe Storage</div>
                                                                <div class="spec-value">
                                                                    {{ count($media) ? implode(', ', $media) : '-' }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-windows"></i></div>
                                                            <div>
                                                                <div class="spec-label">OS Version</div>
                                                                <div class="spec-value">{{ $latest->os_version ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-muted small mt-3">
                                                    <i class="bi bi-clock"></i>
                                                    Terakhir diupdate: <strong>{{ $latest->datetime?->format('d/m/Y H:i') ?? '-' }}</strong>
                                                </div>
                                            </div>
                                            <div class="modal-footer d-flex justify-content-between">
                                                <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                                                   class="btn btn-primary">
                                                    <i class="bi bi-sliders me-1"></i> Kelola Spesifikasi
                                                </a>

                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                    Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                                   class="btn btn-info btn-icon text-white"
                                   title="Kelola Spesifikasi">
                                    <i class="bi bi-sliders"></i>
                                </a>

                                <a href="{{ route('admin.assets.edit', $asset->id_asset) }}"
                                   class="btn btn-warning btn-icon"
                                   title="Edit Asset">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button class="btn btn-danger btn-icon text-white js-delete"
                                        data-action="{{ route('admin.assets.destroy', $asset) }}"
                                        data-title="Hapus data?"
                                        data-message="Data ini akan dihapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted p-4">
                            Belum ada data asset.
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
            Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} results
        </div>

        <div class="mt-2">
            {{ $assets->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
        </div>
    </div>
</div>

@endsection
