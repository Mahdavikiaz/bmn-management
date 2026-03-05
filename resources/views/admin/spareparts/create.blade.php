@extends('layouts.app')

@section('title', 'SIMANIS | Tambah Sparepart')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-0">
    <div>
        <h4 class="mb-2">Tambah Sparepart</h4>
        <p class="text-muted">Form untuk menambahkan data sparepart baru</p>
    </div>
    <a href="{{ route('admin.spareparts.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.spareparts.store') }}">
            @csrf

            @php
                $oldCategory = old('category', '');
                $oldType = old('sparepart_type', '');
                $oldSize = old('size', '');
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
                       value="{{ old('sparepart_name') }}"
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
                       value="{{ old('price') }}"
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
                    <i class="bi bi-save me-1"></i> Simpan
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
            // baterai/charger: hide & disable & kosongkan
            sizeWrap.style.display = 'none';
            elSize.disabled = true;
            elSize.required = false;
            elSize.value = '';
        }
    }

    elCategory.addEventListener('change', function() {
        fillTypes(this.value);
        toggleSize(this.value);
    });

    // init
    fillTypes(oldCategory);
    toggleSize(oldCategory);
</script>

@endsection