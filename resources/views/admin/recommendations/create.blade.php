@extends('layouts.app')

@section('title', 'SIMANIS | Tambah Rekomendasi')

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
                <select id="category" name="category"
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

            {{-- Action --}}
            <div class="mb-3">
                <label class="form-label">Tindakan</label>
                <textarea name="action"
                          rows="3"
                          class="form-control @error('action') is-invalid @enderror"
                          placeholder="Contoh: Upgrade storage x2 / Ganti jadi SSD / Tambahkan RAM 8GB"
                          required>{{ old('action') }}</textarea>
                @error('action')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Explanation --}}
            <div class="mb-3">
                <label class="form-label">Penjelasan</label>
                <textarea name="explanation"
                          rows="4"
                          class="form-control @error('explanation') is-invalid @enderror"
                          placeholder="Jelaskan alasan dan dampak rekomendasi ini"
                          required>{{ old('explanation') }}</textarea>
                @error('explanation')
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
                    @for($i=1; $i<=5; $i++)
                        <option value="{{ $i }}" {{ (int)old('priority_level') === $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                @error('priority_level')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- PARAMETER ESTIMASI --}}
            <div class="card border rounded-4 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="fw-bold mb-1">Rekomendasi untuk Upgrade (Opsional)</div>
                            <div class="text-muted small">
                                Isi bagian ini <b>hanya ketika</b> rekomendasi membutuhkan tindakan untuk upgrade.
                                Jika tidak ada (misal: hapus software), biarkan kosong.
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Target Type --}}
                    <div class="mb-3">
                        <label class="form-label">Target Type</label>
                        <select id="target_type" name="target_type"
                                class="form-select @error('target_type') is-invalid @enderror">
                            <option value="">-- Tidak ada estimasi --</option>
                            <option value="SAME_AS_SPEC" {{ old('target_type') === 'SAME_AS_SPEC' ? 'selected' : '' }}>
                                Ikuti spesifikasi saat ini
                            </option>

                            {{-- RAM types --}}
                            <option class="opt-ram" value="DDR3" {{ old('target_type') === 'DDR3' ? 'selected' : '' }}>DDR3</option>
                            <option class="opt-ram" value="DDR4" {{ old('target_type') === 'DDR4' ? 'selected' : '' }}>DDR4</option>
                            <option class="opt-ram" value="DDR5" {{ old('target_type') === 'DDR5' ? 'selected' : '' }}>DDR5</option>

                            {{-- Storage types --}}
                            <option class="opt-storage" value="SSD" {{ old('target_type') === 'SSD' ? 'selected' : '' }}>SSD</option>
                            <option class="opt-storage" value="NVME" {{ old('target_type') === 'NVME' ? 'selected' : '' }}>NVME</option>
                            <option class="opt-storage" value="HDD" {{ old('target_type') === 'HDD' ? 'selected' : '' }}>HDD</option>
                        </select>

                        @error('target_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="text-muted small mt-2">
                            Contoh:
                            <span class="badge text-bg-light border">Storage HDD -> SSD</span>
                            <span class="badge text-bg-light border">RAM DDR3 (sesuaikan dengan spec)</span>
                        </div>
                    </div>

                    {{-- Size Mode --}}
                    <div class="mb-2 mt-4">
                        <label class="form-label">Target Ukuran</label>

                        @php
                            $oldMode = old('size_mode');
                        @endphp

                        <div class="d-flex flex-column gap-2">
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="size_mode" value="fixed"
                                       {{ $oldMode === 'fixed' ? 'checked' : '' }}>
                                <span>Ukuran tetap (GB)</span>
                            </label>

                            <div class="ms-4 mb-3">
                                <input type="number" min="1" step="1"
                                       id="target_size_gb"
                                       name="target_size_gb"
                                       value="{{ old('target_size_gb') }}"
                                       class="form-control @error('target_size_gb') is-invalid @enderror"
                                       placeholder="Contoh: 4 / 8 / 16 / 512 / 1024">
                                @error('target_size_gb')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="text-muted small mt-1">
                                    RAM biasanya: 4 / 8 / 16 GB (untuk tambahan). Storage bisa: 512 / 1024 GB.
                                </div>
                            </div>

                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="size_mode" value="multiplier"
                                       {{ $oldMode === 'multiplier' ? 'checked' : '' }}>
                                <span>Kelipatan dari spesifikasi saat ini</span>
                            </label>

                            <div class="ms-4 mb-3">
                                <input type="number" min="1" step="0.5"
                                       id="target_multiplier"
                                       name="target_multiplier"
                                       value="{{ old('target_multiplier') }}"
                                       class="form-control @error('target_multiplier') is-invalid @enderror"
                                       placeholder="Contoh: 2 untuk x2">
                                @error('target_multiplier')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="text-muted small mt-1">
                                    Umumnya untuk Storage: isi 2 untuk “upgrade x2”.
                                </div>
                            </div>

                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="size_mode" value="" {{ $oldMode === null ? 'checked' : '' }}>
                                <span>Tidak perlu estimasi</span>
                            </label>
                        </div>
                    </div>
                </div>
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

<script>
(function(){
    const cat = document.getElementById('category');
    const targetType = document.getElementById('target_type');

    function toggleTargetOptions(){
        const v = (cat?.value || '').toUpperCase();

        document.querySelectorAll('.opt-ram').forEach(o => o.hidden = (v !== 'RAM'));
        document.querySelectorAll('.opt-storage').forEach(o => o.hidden = (v !== 'STORAGE'));

        if (v === 'CPU') {
            document.querySelectorAll('.opt-ram,.opt-storage').forEach(o => o.hidden = true);
        }

        const selected = targetType.options[targetType.selectedIndex];
        if (selected && selected.hidden) {
            targetType.value = '';
        }
    }

    cat?.addEventListener('change', toggleTargetOptions);
    toggleTargetOptions();
})();
</script>

@endsection
