@extends('layouts.app')

@section('title', 'Edit Indicator')

@section('content')

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="mb-1">Edit Indicator</h4>
        <small class="text-muted">
            Form untuk memperbarui indikator dan pertanyaan penilaian
        </small>
    </div>

    <a href="{{ route('admin.indicator-questions.index') }}"
       class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">

        <form method="POST"
              action="{{ route('admin.indicator-questions.update', $indicator) }}">
            @csrf
            @method('PUT')

            {{-- SECTION: INFO --}}
            <div class="mb-4">
                <div class="section-title">Informasi Indicator</div>

                <div class="row g-3">
                    {{-- KATEGORI --}}
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category"
                                class="form-select @error('category') is-invalid @enderror"
                                required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}"
                                    {{ old('category', $indicator->category) == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>

                        @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NAMA --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Indicator</label>
                        <input type="text"
                               name="indicator_name"
                               class="form-control @error('indicator_name') is-invalid @enderror"
                               value="{{ old('indicator_name', $indicator->indicator_name) }}"
                               placeholder="Contoh: Kinerja RAM"
                               required>

                        @error('indicator_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- PERTANYAAN --}}
            <div class="mb-5">
                <label class="form-label">Pertanyaan Penilaian</label>
                <textarea name="question"
                          rows="3"
                          class="form-control @error('question') is-invalid @enderror"
                          required>{{ old('question', $indicator->question) }}</textarea>

                @error('question')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- SECTION: OPSI --}}
            <div class="mb-4">
                <div class="section-title">Opsi Jawaban</div>

                @php
                    $labelNames = [
                        'A' => 'Sangat Baik',
                        'B' => 'Baik',
                        'C' => 'Cukup',
                        'D' => 'Kurang',
                        'E' => 'Sangat Kurang',
                    ];
                @endphp

                @foreach($labels as $label)
                    <div class="option-card mb-3">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="option-label">{{ $label }}</div>
                            <div class="fw-semibold">
                                {{ $labelNames[$label] ?? '' }}
                            </div>
                        </div>

                        <input type="text"
                               name="options[{{ $label }}]"
                               class="form-control @error("options.$label") is-invalid @enderror"
                               value="{{ old(
                                   "options.$label",
                                   $optionsByLabel[$label]->option ?? ''
                               ) }}"
                               required>

                        @error("options.$label")
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            </div>

            {{-- ACTION --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.indicator-questions.index') }}"
                   class="btn btn-secondary px-4">
                    Batal
                </a>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>

@endsection
