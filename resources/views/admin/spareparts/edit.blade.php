@extends('layouts.app')

@section('title', 'Edit Sparepart')

@section('content')

<div class="mb-4">
    <h4>Edit Sparepart</h4>
    <p class="text-muted">Perbarui data sparepart</p>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.spareparts.update', $sparepart) }}">
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
                            {{ old('category', $sparepart->category) == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tipe --}}
            <div class="mb-3">
                <label class="form-label">Tipe</label>
                <select name="sparepart_type"
                        class="form-select @error('sparepart_type') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Tipe --</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}"
                            {{ old('sparepart_type', $sparepart->sparepart_type) == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>

                @error('sparepart_type')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nama Sparepart --}}
            <div class="mb-3">
                <label class="form-label">Nama Sparepart</label>
                <input type="text"
                       name="sparepart_name"
                       class="form-control @error('sparepart_name') is-invalid @enderror"
                       value="{{ old('sparepart_name', $sparepart->sparepart_name) }}"
                       required>

                @error('sparepart_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Ukuran --}}
            <div class="mb-3">
                <label class="form-label">Ukuran</label>
                <input type="number"
                       name="size"
                       class="form-control @error('size') is-invalid @enderror"
                       value="{{ old('size', $sparepart->size) }}"
                       min="0"
                       required>

                @error('size')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Harga --}}
            <div class="mb-4">
                <label class="form-label">Harga</label>
                <input type="number"
                       name="price"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $sparepart->price) }}"
                       min="0"
                       required>

                @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Action --}}
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.spareparts.index') }}"
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
