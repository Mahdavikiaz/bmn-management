@extends('layouts.app')

@section('title', 'Tambah Recommendation')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-0">
    <div>
        <h4>Tambah Rekomendasi</h4>
        <p class="text-muted">Form untuk menambahkan data rekomendasi baru</p>
    </div>
    <a href="{{ route('admin.recommendations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.recommendations.store') }}">
            @csrf

            {{-- Kategori --}}
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="category"
                        class="form-select @error('category') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}"
                            {{ old('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Deskripsi Recommendation --}}
            <div class="mb-3">
                <label class="form-label">Deskripsi Rekomendasi</label>
                <textarea name="description"
                          rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          placeholder="Masukkan deskripsi rekomendasi"
                          required>{{ old('description') }}</textarea>

                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Priority Level --}}
            <div class="mb-4">
                <label class="form-label">Priority Level</label>
                <select name="priority_level"
                        class="form-select @error('priority_level') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Priority --</option>

                    <option value="1" {{ old('priority_level') == 1 ? 'selected' : '' }}>
                        1 - Sangat Rendah
                    </option>
                    <option value="2" {{ old('priority_level') == 2 ? 'selected' : '' }}>
                        2 - Rendah
                    </option>
                    <option value="3" {{ old('priority_level') == 3 ? 'selected' : '' }}>
                        3 - Sedang
                    </option>
                    <option value="4" {{ old('priority_level') == 4 ? 'selected' : '' }}>
                        4 - Tinggi
                    </option>
                    <option value="5" {{ old('priority_level') == 5 ? 'selected' : '' }}>
                        5 - Sangat Tinggi
                    </option>
                </select>

                @error('priority_level')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Action --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.recommendations.index') }}"
                   class="btn btn-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>
                    Simpan
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
