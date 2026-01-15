@extends('layouts.app')

@section('title', 'Edit Asset')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Asset</h4>
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

        {{-- CARD: DATA ASSET (MASTER) --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-box-seam"></i> Data Asset (Master)
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode BMN</label>
                        <input type="text" name="bmn_code" class="form-control"
                               value="{{ old('bmn_code', $asset->bmn_code) }}" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nama Device</label>
                        <input type="text" name="device_name" class="form-control"
                               value="{{ old('device_name', $asset->device_name) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipe Device</label>
                        <select name="device_type" class="form-select" required>
                            <option value="">Pilih tipe...</option>
                            <option value="PC" {{ old('device_type', $asset->device_type)=='PC' ? 'selected' : '' }}>PC</option>
                            <option value="Laptop" {{ old('device_type', $asset->device_type)=='Laptop' ? 'selected' : '' }}>Laptop</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">GPU</label>
                        <input type="text" name="gpu" class="form-control"
                               value="{{ old('gpu', $asset->gpu) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipe RAM</label>
                        <input type="text" name="ram_type" class="form-control"
                               value="{{ old('ram_type', $asset->ram_type) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tahun Pengadaan</label>
                        <input type="number" name="procurement_year" class="form-control"
                               value="{{ old('procurement_year', $asset->procurement_year) }}"
                               min="1900" max="{{ date('Y') }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- INFO: Spesifikasi dikelola di halaman lain --}}
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            Spesifikasi tidak diedit di halaman ini karena bersifat history.
            Gunakan tombol <strong>Kelola Spesifikasi</strong> di halaman daftar asset.
        </div>

        {{-- ACTIONS --}}
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
