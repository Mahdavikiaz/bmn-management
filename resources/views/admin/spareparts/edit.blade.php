@extends('layouts.app')

@section('title', 'SIMANIS | Edit Sparepart')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-0">
    <div>
        <h4>Edit Sparepart</h4>
        <p class="text-muted">Form untuk perbarui data sparepart</p>
    </div>
    <a href="{{ route('admin.spareparts.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.spareparts.update', $sparepart) }}">
            @csrf
            @method('PUT')

            @php
                $oldCategory = old('category', $sparepart->category);
                $oldType = old('sparepart_type', $sparepart->sparepart_type);
                $oldSize = old('size', $sparepart->size);
            @endphp

            {{-- Kategori --}}
            <div class="mb-3">
                <label class="form-label">Kategori</label>
                <select id="category" name="category"
                        class="form-select @error('category') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ $oldCategory == $category ? 'selected' : '' }}>
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
                <select id="sparepart_type" name="sparepart_type"
                        class="form-select @error('sparepart_type') is-invalid @enderror"
                        required>
                    <option value="">-- Pilih Tipe --</option>
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
            <div class="mb-3" id="sizeWrap">
                <label class="form-label" id="sizeLabel">Ukuran (GB)</label>
                <input type="number"
                       id="size"
                       name="size"
                       class="form-control @error('size') is-invalid @enderror"
                       value="{{ $oldSize }}"
                       min="0">

                <div class="form-text text-muted" id="sizeHelp">
                    Wajib untuk RAM / STORAGE. Tidak diperlukan untuk BATERAI / CHARGER.
                </div>

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
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.spareparts.index') }}" class="btn btn-secondary">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    const TYPES_BY_CATEGORY = @json($typesByCategory);
    const oldType = @json($oldType);
    const oldCategory = @json($oldCategory);

    const elCategory = document.getElementById('category');
    const elType = document.getElementById('sparepart_type');

    const sizeWrap = document.getElementById('sizeWrap');
    const elSize = document.getElementById('size');

    function fillTypes(category) {
        elType.innerHTML = '<option value="">-- Pilih Tipe --</option>';

        const types = TYPES_BY_CATEGORY[category] || [];
        types.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t;
            if (t === oldType) opt.selected = true;
            elType.appendChild(opt);
        });
    }

    function toggleSize(category) {
        const cat = (category || '').toUpperCase();
        const needSize = (cat === 'RAM' || cat === 'STORAGE');

        if (needSize) {
            sizeWrap.style.display = '';
            elSize.disabled = false;
            elSize.required = true;
        } else {
            sizeWrap.style.display = 'none';
            elSize.disabled = true;
            elSize.required = false;
            elSize.value = '';
        }
    }

    elCategory.addEventListener('change', function() {
        // saat ganti kategori di edit, reset oldType supaya user pilih ulang yang sesuai
        fillTypes(this.value);
        toggleSize(this.value);
    });

    // init
    fillTypes(oldCategory);
    toggleSize(oldCategory);
</script>

@endsection