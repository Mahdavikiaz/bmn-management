@extends('layouts.app')

@section('title', 'Edit Tipe Asset')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-0">
    <div>
        <h4>Edit Tipe Asset</h4>
        <p class="text-muted">Form untuk memperbarui data tipe asset</p>
    </div>

    <a href="{{ route('admin.asset-types.index') }}"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.asset-types.update', $assetType) }}">
            @csrf
            @method('PUT')

            {{-- KODE TIPE --}}
            <div class="mb-3">
                <label class="form-label">Kode Tipe Asset</label>
                <input type="text"
                       name="type_code"
                       class="form-control @error('type_code') is-invalid @enderror"
                       value="{{ old('type_code', $assetType->type_code) }}"
                       placeholder="Contoh: LAPTOP, PC, SERVER"
                       required>

                @error('type_code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- NAMA TIPE --}}
            <div class="mb-4">
                <label class="form-label">Nama Tipe Asset</label>
                <input type="text"
                       name="type_name"
                       class="form-control @error('type_name') is-invalid @enderror"
                       value="{{ old('type_name', $assetType->type_name) }}"
                       placeholder="Contoh: Laptop Kantor"
                       required>

                @error('type_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- ACTION --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.asset-types.index') }}"
                   class="btn btn-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
