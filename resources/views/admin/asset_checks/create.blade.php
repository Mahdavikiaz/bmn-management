@extends('layouts.app')

@section('title', 'SIMANIS | Melakukan Pengecekan Asset')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Lakukan Pengecekan</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->type?->type_name }}) | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('admin.asset-checks.store', $asset->id_asset) }}"
      enctype="multipart/form-data">
@csrf

<div class="card shadow-sm">
    <div class="card-body">

        <ul class="nav nav-tabs" id="checkTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-spec" data-bs-toggle="tab"
                        data-bs-target="#pane-spec" type="button" role="tab">
                    <i class="bi bi-cpu"></i> Spesifikasi
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-q" data-bs-toggle="tab"
                        data-bs-target="#pane-q" type="button" role="tab">
                    <i class="bi bi-ui-checks"></i> Pertanyaan Indikator
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-issue" data-bs-toggle="tab"
                        data-bs-target="#pane-issue" type="button" role="tab">
                    <i class="bi bi-chat-left-text"></i> Keluhan Tambahan (Opsional)
                </button>
            </li>
        </ul>

        <div class="tab-content pt-4" id="checkTabsContent">

            {{-- TAB SPEC --}}
            <div class="tab-pane fade show active" id="pane-spec" role="tabpanel">

                <div class="text-muted small mb-3">
                    Jika sudah ada spesifikasi sebelumnya, field akan terisi otomatis. Silakan ubah jika ada update.
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Pemegang Asset</label>
                        <input type="text" name="owner_asset" class="form-control"
                               value="{{ old('owner_asset', $latestSpec->owner_asset ?? '') }}"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Processor</label>
                        <input type="text" name="processor" class="form-control"
                               value="{{ old('processor', $latestSpec->processor ?? '') }}"
                               required>
                    </div>

                    <!-- GPU -->
                    <div class="col-12">
                        <label class="form-label">GPU</label>
                        <input type="text" name="gpu" class="form-control"
                               value="{{ old('gpu', $asset->gpu ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">RAM (GB)</label>
                        <input type="number" name="ram" class="form-control" min="0"
                               value="{{ old('ram', $latestSpec->ram ?? 0) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Storage (GB)</label>
                        <input type="number" name="storage" class="form-control" min="0"
                               value="{{ old('storage', $latestSpec->storage ?? 0) }}"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Tipe RAM</label>
                        <select name="ram_type" class="form-select">
                            <option value="">Pilih tipe RAM...</option>
                            <option value="DDR3" {{ old('ram_type', $asset->ram_type ?? '') == 'DDR3' ? 'selected' : '' }}>DDR3</option>
                            <option value="DDR4" {{ old('ram_type', $asset->ram_type ?? '') == 'DDR4' ? 'selected' : '' }}>DDR4</option>
                            <option value="DDR5" {{ old('ram_type', $asset->ram_type ?? '') == 'DDR5' ? 'selected' : '' }}>DDR5</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Tipe Storage</label>
                        @php
                            $oldType = old('category_storage');
                            $type = $oldType ?: (($latestSpec->is_nvme ?? false) ? 'NVME' : (($latestSpec->is_ssd ?? false) ? 'SSD' : (($latestSpec->is_hdd ?? false) ? 'HDD' : '')));

                        @endphp
                        <select name="category_storage" class="form-select">
                            <option value="">Pilih tipe...</option>
                            <option value="HDD" {{ $type==='HDD' ? 'selected' : '' }}>HDD</option>
                            <option value="SSD" {{ $type==='SSD' ? 'selected' : '' }}>SSD</option>
                            <option value="NVME" {{ $type==='NVME' ? 'selected' : '' }}>NVMe</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">OS Version (opsional)</label>
                        <input type="text" name="os_version" class="form-control"
                               value="{{ old('os_version', $latestSpec->os_version ?? '') }}"
                               placeholder="Contoh: Windows 11 Pro">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-primary"
                            onclick="document.querySelector('#tab-q').click()">
                        Lanjut ke Pertanyaan <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            {{-- TAB QUESTIONS --}}
            <div class="tab-pane fade" id="pane-q" role="tabpanel">
                <div class="text-muted small mb-3">
                    Jawab semua pertanyaan. Setiap pilihan memiliki nilai bintang (A=5 … E=1).
                </div>

                @foreach(['RAM','STORAGE','CPU'] as $cat)
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-2">
                            {{ $cat }}
                        </h6>

                        @forelse(($questions[$cat] ?? collect()) as $q)
                            @php $opts = $q->options->keyBy('label'); @endphp

                            <div class="border rounded-3 p-3 mb-3 bg-white">
                                <div class="fw-semibold mb-1">{{ $q->indicator_name }}</div>
                                <div class="text-muted mb-2">{{ $q->question }}</div>

                                <div class="row g-2">
                                    @foreach(['A','B','C','D','E'] as $label)
                                        @php
                                            $opt = $opts[$label] ?? null;
                                            $inputName = "answers[$q->id_question]";
                                            $checked = (string)old("answers.$q->id_question") === (string)($opt->id_option ?? '');
                                        @endphp

                                        <div class="col-md-6">
                                            <label class="w-100 border rounded-3 p-2 d-flex gap-2 align-items-start" style="cursor:pointer;">
                                                <input class="form-check-input mt-1"
                                                       type="radio"
                                                       name="{{ $inputName }}"
                                                       value="{{ $opt->id_option ?? '' }}"
                                                       {{ $checked ? 'checked' : '' }}
                                                       required>
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $label }}. {{ $opt->option ?? '-' }}
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-muted fst-italic">Belum ada pertanyaan untuk kategori ini.</div>
                        @endforelse
                    </div>
                @endforeach

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="document.querySelector('#tab-spec').click()">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </button>
                    <button type="button" class="btn btn-primary"
                            onclick="document.querySelector('#tab-issue').click()">
                        Lanjut ke Keluhan Tambahan <i class="bi bi-arrow-right"></i>
                    </button>
                </div>

            </div>

            {{-- TAB ISSUE / KELUHAN --}}
            <div class="tab-pane fade" id="pane-issue" role="tabpanel">
                <div class="text-muted small mb-3">
                    Isi field di bawah ini jika ada keluhan/permasalahan lain terkait device. Foto digunakan untuk validasi kondisi fisik.
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Catatan Keluhan (opsional)</label>
                        <textarea name="issue_note" class="form-control" rows="4"
                                  placeholder="Contoh: Keyboard pada laptop tidak berfungsi optimal...">{{ old('issue_note', $latestSpec->issue_note ?? '') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Upload Foto Keluhan (opsional)</label>
                        <input type="file" name="issue_image" class="form-control" accept="image/*">
                        <div class="form-text mt-2">
                            Format: JPG/JPEG/PNG. Maksimal 5MB.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('admin.asset-checks.index') }}" class="btn btn-secondary">Batal</a>
                    <button class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Proses Pengecekan
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
</form>
@endsection