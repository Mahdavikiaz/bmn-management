@extends('layouts.app')

@section('title', 'Edit Asset')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-0">
        <div>
            <h4 class="mb-2">Edit Asset</h4>
            <p class="text-muted">Form untuk perbarui data asset</p>
        </div>
        <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.assets.update', $asset->id_asset) }}">
        @csrf
        @method('PUT')

        <div class="card shadow-sm">
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab"
                                data-bs-target="#pane-master" type="button" role="tab">
                            <i class="bi bi-box-seam"></i> Data Asset (Master)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab"
                                data-bs-target="#pane-spec" type="button" role="tab">
                            <i class="bi bi-cpu"></i> Spesifikasi
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4">
                    <div class="tab-pane fade show active" id="pane-master" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Kode Nup</label>
                                <input type="text" name="nup" class="form-control"
                                       value="{{ old('nup', $asset->nup) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nama Device</label>
                                <input type="text" name="device_name" class="form-control"
                                       value="{{ old('device_name', $asset->device_name) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe Device</label>
                                <select name="id_type" class="form-select" required>
                                    <option value="" disabled {{ old('id_type') ? 'selected' : '' }}>Pilih tipe asset</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->id_type }}"
                                            {{ (string)old('id_type') === (string)$t->id_type ? 'selected' : '' }}>
                                            {{ $t->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">GPU</label>
                                <input type="text" name="gpu" class="form-control"
                                       value="{{ old('gpu', $asset->gpu) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe RAM</label>
                                <input type="text" name="ram_type" class="form-control"
                                       value="{{ old('ram_type', $asset->ram_type) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tahun Pengadaan</label>
                                <input type="number" name="procurement_year" class="form-control"
                                       value="{{ old('procurement_year', $asset->procurement_year) }}"
                                       min="1900" max="{{ date('Y') }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Batal</a>
                            <button class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-spec" role="tabpanel">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            Spesifikasi bersifat <strong>history</strong> (tiap update menambah baris baru).
                            Silakan kelola lewat halaman:
                            <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}" class="fw-semibold">
                                Kelola Spesifikasi
                            </a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
