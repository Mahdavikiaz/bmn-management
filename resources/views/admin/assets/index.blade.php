@extends('layouts.app')

@section('title', 'Daftar Asset')

@section('content')

    <style>
        .table-modern thead th{
            background:#f8f9fa;
            font-weight:700;
            border-bottom:1px solid #e9ecef;
            white-space: nowrap;
        }
        .table-modern tbody tr:hover{ background:#f6f9ff; }

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

    <h4 class="mb-4">Daftar Asset</h4>

    {{-- CARD RECAP --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-laptop fs-3 text-primary"></i>
                    <h6 class="mt-2">Total Asset</h6>
                    <h4>{{ $assets->total() ?? $assets->count() }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-pc-display fs-3 text-success"></i>
                    <h6 class="mt-2">Total PC</h6>
                    <h4>{{ $assets->where('device_type','PC')->count() }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-laptop fs-3 text-warning"></i>
                    <h6 class="mt-2">Total Laptop</h6>
                    <h4>{{ $assets->where('device_type','Laptop')->count() }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTION BAR + FILTER --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">

        {{-- FILTER FORM --}}
        <form method="GET" class="d-flex align-items-center gap-2 flex-grow-1">

            <select name="device_type" class="form-select" style="max-width: 240px;">
                <option value="">Pilih tipe...</option>
                <option value="PC" {{ request('device_type') == 'PC' ? 'selected' : '' }}>PC</option>
                <option value="Laptop" {{ request('device_type') == 'Laptop' ? 'selected' : '' }}>Laptop</option>
            </select>

            <input type="text"
                   name="q"
                   class="form-control"
                   style="max-width: 340px;"
                   placeholder="Cari kode BMN / nama device..."
                   value="{{ request('q') }}">

            <button class="btn btn-primary">
                Cari
            </button>

            <a href="{{ route('admin.assets.index') }}" class="btn btn-danger">
                Reset
            </a>
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
                        <th style="width:60px;" class="fw-semibold">No</th>
                        <th class="fw-semibold">Kode BMN</th>
                        <th class="fw-semibold">Nama Device</th>
                        <th style="width:110px;" class="fw-semibold">Kategori</th>
                        <th class="fw-semibold">GPU</th>
                        <th class="fw-semibold">Tipe RAM</th>
                        <th style="width:90px;" class="fw-semibold">Tahun Pengadaan</th>
                        <th style="width:220px;" class="fw-semibold">Spesifikasi</th>
                        <th style="width:180px;" class="text-center fw-semibold">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assets as $asset)
                        @php
                            $latest = $asset->latestSpecification ?? null;
                            $modalId = 'specModal-'.$asset->id_asset;
                        @endphp

                        <tr>
                            <td>{{ method_exists($assets,'currentPage') ? (($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration) : $loop->iteration }}</td>

                            <td class="fw-normal">{{ $asset->bmn_code }}</td>

                            <td>
                                <div class="fw-normal">{{ $asset->device_name }}</div>
                            </td>

                            <td>
                                @if($asset->device_type == 'PC')
                                    <span class="badge rounded-pill text-bg-success fw-semibold">PC</span>
                                @else
                                    <span class="badge rounded-pill text-bg-warning fw-semibold">Laptop</span>
                                @endif
                            </td>

                            <td>{{ $asset->gpu }}</td>
                            <td>{{ $asset->ram_type }}</td>
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

                                    <div class="spec-muted mt-1">
                                        Terakhir update: {{ $latest->datetime?->format('d/m/Y H:i') ?? '-' }}
                                    </div>

                                    {{-- MODAL --}}
                                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <div>
                                                        <h5 class="modal-title mb-0">Spesifikasi Asset</h5>
                                                        <div class="text-muted small">
                                                            {{ $asset->device_name }} ({{ $asset->device_type }}) •
                                                            Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
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
                                                        <div class="col-md-6">
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

                                                        <div class="col-md-6">
                                                            <div class="spec-kv">
                                                                <div class="spec-ic"><i class="bi bi-windows"></i></div>
                                                                <div>
                                                                    <div class="spec-label">OS Version</div>
                                                                    <div class="spec-value fw-semibold">{{ $latest->os_version ?: '-' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="spec-kv">
                                                                <div class="spec-ic"><i class="bi bi-device-hdd"></i></div>
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

                                    {{-- Kelola Spesifikasi --}}
                                    <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                                       class="btn btn-info btn-icon text-white"
                                       title="Kelola Spesifikasi">
                                        <i class="bi bi-sliders"></i>
                                    </a>

                                    {{-- Edit Master --}}
                                    <a href="{{ route('admin.assets.edit', $asset->id_asset) }}"
                                       class="btn btn-warning btn-icon"
                                       title="Edit Asset">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Delete --}}
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
            <div class="card-footer bg-white">
                {{ $assets->links() }}
            </div>
        @endif
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
                    Data berhasil ditambahkan.
                </p>

                {{-- ACTIONS --}}
                <div class="d-flex justify-content-center gap-3">
                    <button type="button"
                            class="btn btn-primary px-4 d-flex align-items-center gap-2"
                            data-bs-dismiss="modal">
                        Oke
                    </button>
                </div>

            </div>
        </div>
    </div>

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
