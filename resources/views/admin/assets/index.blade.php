@extends('layouts.app')

@section('title', 'SIMANIS | Daftar Asset')

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

    .spec-kv{
        display:flex; gap:10px; align-items:flex-start;
        padding:10px 0; border-bottom:1px dashed #e9ecef;
    }
    .spec-kv:last-child{ border-bottom:0; }
    .spec-ic{
        width:38px; height:38px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        background:#eef2ff; color:#0d6efd; flex:0 0 auto;
        font-size:1.1rem;
    }
    .spec-label{ color:#6c757d; font-size:.85rem; }
    .spec-value{ font-weight:700; }
    .spec-muted{ color:#6c757d; font-size:.85rem; }
</style>

<div class="mb-3">
    <h4 class="mb-1">Daftar Asset</h4>
    <div class="text-muted small">
        Menampilkan daftar asset
    </div>
</div>

@php
    // helper count berdasarkan type_name
    $countByTypeName = function(string $name) use ($assets) {
        $items = collect(method_exists($assets, 'items') ? $assets->items() : $assets);
        return $items->filter(fn($a) => strtoupper($a->type?->type_name ?? '') === strtoupper($name))->count();
    };
@endphp

{{-- ACTION BAR + FILTER --}}
<div class="d-flex justify-content-between align-items-center gap-3 mb-3">

    {{-- FILTER FORM --}}
    <form method="GET" class="d-flex align-items-center gap-2 flex-grow-1">

        <select name="id_type" class="form-select" style="max-width: 260px;">
            <option value="">Pilih tipe...</option>
            @foreach(($types ?? []) as $t)
                <option value="{{ $t->id_type }}" {{ (string)request('id_type') === (string)$t->id_type ? 'selected' : '' }}>
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

        <button class="btn btn-primary">Cari</button>

        <a href="{{ route('admin.assets.index') }}" class="btn btn-danger">Reset</a>
    </form>

    {{-- TAMBAH DATA --}}
    <a href="{{ route('admin.assets.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Tambah Asset
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
                    <th>Kode BMN</th>
                    <th>Nama Device</th>
                    <th style="width:100px;">Kategori</th>
                    <th>GPU</th>
                    <th>Tipe RAM</th>
                    <th style="width:120px;">Tahun Pengadaan</th>
                    <th style="width:180px;">Spesifikasi</th>
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
                            {{ method_exists($assets,'currentPage')
                                ? (($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration)
                                : $loop->iteration }}
                        </td>

                        <td class="fw-normal">{{ $asset->bmn_code }}</td>

                        <td>
                            <div class="fw-normal">{{ $asset->device_name }}</div>
                        </td>

                        {{-- KATEGORI--}}
                        <td class="fw-normal">
                            {{ $asset->type?->type_name ?? '-' }}
                        </td>

                        <td>{{ $asset->gpu ?: '-'}}</td>
                        <td>{{ $asset->ram_type ?: '-'}}</td>
                        <td>{{ $asset->procurement_year }}</td>

                        {{-- SPESIFIKASI --}}
                        <td>
                            @if(!$latest)
                                <span class="text-muted fst-italic">Spesifikasi belum diinputkan</span>
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
                                                    <h5 class="modal-title mb-0">Spesifikasi Asset</h5>
                                                    <div class="text-muted small">
                                                        {{ $asset->device_name }} ({{ $asset->type?->type_name ?? '-' }}) |
                                                        Kode BMN: <strong>{{ $asset->bmn_code }}</strong> |
                                                        Tahun Pengadaan: {{ $asset->procurement_year }}
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
                                                                <div class="spec-label">Pemegang</div>
                                                                <div class="spec-value fw-semibold">{{ $latest->owner_asset ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-cpu"></i></div>
                                                            <div>
                                                                <div class="spec-label">Processor</div>
                                                                <div class="spec-value fw-semibold">{{ $latest->processor ?: '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-memory"></i></div>
                                                            <div>
                                                                <div class="spec-label">RAM</div>
                                                                <div class="spec-value fw-semibold">{{ $latest->ram }} GB</div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-hdd-stack"></i></div>
                                                            <div>
                                                                <div class="spec-label">Storage</div>
                                                                <div class="spec-value fw-semibold">{{ $latest->storage }} GB</div>
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
                                                                    @if(count($media))
                                                                        @foreach($media as $m)
                                                                            <span class="badge rounded-pill text-bg-secondary me-1 fw-semibold">{{ $m }}</span>
                                                                        @endforeach
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="spec-kv">
                                                            <div class="spec-ic"><i class="bi bi-windows"></i></div>
                                                            <div>
                                                                <div class="spec-label">OS Version</div>
                                                                <div class="spec-value fw-semibold">{{ $latest->os_version ?: '-' }}</div>
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
                                {{-- END MODAL --}}
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
                                        data-title="Anda yakin ingin menghapus data ini?"
                                        data-message="Data ini akan terhapus permanen.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">
                            Belum ada data asset.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($assets, 'links'))
        <div class="d-flex flex-column align-items-center mt-4 gap-2">

            {{-- Text info --}}
            <div class="text-muted small">
                Showing {{ $assets->firstItem() }}
                to {{ $assets->lastItem() }}
                of {{ $assets->total() }} results
            </div>

            {{-- Pagination --}}
            <div class="mt-2">
                {{ $assets->onEachSide(1)->links('vendor.pagination.bootstrap-5-no-info') }}
            </div>

        </div>
    @endif
</div>

@endsection
