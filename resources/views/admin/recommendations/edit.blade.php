@extends('layouts.app')

@section('title', 'Edit Recommendation')

@section('content')

<div class="mb-4">
    <h4>Edit Recommendation</h4>
    <p class="text-muted">Perbarui data recommendation</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.recommendations.update', $recommendation) }}">
            @csrf
            @method('PUT')

            {{-- Kategori --}}
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select name="category"
                        class="form-select @error('category') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}"
                            {{ old('category', $recommendation->category) == $category ? 'selected' : '' }}>
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
                <label class="form-label">Deskripsi Recommendation</label>
                <textarea name="description"
                          rows="4"
                          class="form-control @error('description') is-invalid @enderror"
                          required>{{ old('description', $recommendation->description) }}</textarea>

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

                    <option value="1" {{ old('priority_level', $recommendation->priority_level) == 1 ? 'selected' : '' }}>
                        1 - Sangat Rendah
                    </option>
                    <option value="2" {{ old('priority_level', $recommendation->priority_level) == 2 ? 'selected' : '' }}>
                        2 - Rendah
                    </option>
                    <option value="3" {{ old('priority_level', $recommendation->priority_level) == 3 ? 'selected' : '' }}>
                        3 - Sedang
                    </option>
                    <option value="4" {{ old('priority_level', $recommendation->priority_level) == 4 ? 'selected' : '' }}>
                        4 - Tinggi
                    </option>
                    <option value="5" {{ old('priority_level', $recommendation->priority_level) == 5 ? 'selected' : '' }}>
                        5 - Sangat Tinggi
                    </option>
                </select>

                @error('priority_level')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Action --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.recommendations.index') }}"
                   class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn btn-warning">
                    Update
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
