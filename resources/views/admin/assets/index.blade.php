@extends('layouts.app')

@section('title', 'Daftar Asset')

@section('content')
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

    {{-- ACTION BAR + FILTER (SEJAJAR) --}}
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
            <i class="bi bi-plus-lg"></i> Tambah Data
        </a>
    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Kode BMN</th>
                        <th>Nama Device</th>
                        <th>Tipe</th>
                        <th>GPU</th>
                        <th>RAM Type</th>
                        <th>Tahun</th>
                        <th>Spesifikasi</th>
                        <th style="width:180px;">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($assets as $asset)
                        @php
                            // jika kamu sudah eager-load latestSpecification di controller:
                            $latest = $asset->latestSpecification ?? null;
                        @endphp
                        <tr>
                            <td>{{ ($assets->currentPage() - 1) * $assets->perPage() + $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $asset->bmn_code }}</td>
                            <td>{{ $asset->device_name }}</td>
                            <td>
                                <span class="badge bg-{{ $asset->device_type == 'PC' ? 'success' : 'warning' }}">
                                    {{ $asset->device_type }}
                                </span>
                            </td>
                            <td>{{ $asset->gpu }}</td>
                            <td>{{ $asset->ram_type }}</td>
                            <td>{{ $asset->procurement_year }}</td>

                            {{-- SPESIFIKASI --}}
                            <td>
                                @if(!$latest)
                                    <span class="text-muted fst-italic">Data spesifikasi belum diinputkan</span>
                                @else
                                    <span class="badge bg-primary mb-1">Ada</span>
                                    <div class="small text-muted">
                                        <div><strong>CPU:</strong> {{ $latest->processor }}</div>
                                        <div><strong>RAM:</strong> {{ $latest->ram }} GB • <strong>Storage:</strong> {{ $latest->storage }} GB</div>
                                        <div><strong>OS:</strong> {{ $latest->os_version }}</div>
                                    </div>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td>
                                <div class="d-flex gap-2">
                                    {{-- Kelola Spesifikasi --}}
                                    <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                                       class="btn btn-sm btn-info text-white"
                                       title="Kelola Spesifikasi">
                                        <i class="bi bi-sliders"></i>
                                    </a>

                                    {{-- Edit Master --}}
                                    <a href="{{ route('admin.assets.edit', $asset->id_asset) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit Asset">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Delete --}}
                                    <form method="POST"
                                          action="{{ route('admin.assets.destroy', $asset->id_asset) }}"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                title="Hapus"
                                                onclick="return confirm('Yakin hapus asset ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
@endsection
