@extends('layouts.app')

@section('title', 'SIMANIS | Melakukan Pengecekan Asset')

@section('content')

@php
    $shouldAutoShowServiceModal =
        $hasPreviousReport &&
        old('service_modal_answered', '0') !== '1' &&
        old('service_saved_directly', '0') !== '1';

    $hasServiceSaved = old('service_saved_directly', '0') === '1';
@endphp

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

<input type="hidden" name="service_confirmation" id="service_confirmation" value="{{ old('service_confirmation', '0') }}">
<input type="hidden" name="service_modal_answered" id="service_modal_answered" value="{{ old('service_modal_answered', '0') }}">
<input type="hidden" name="service_saved_directly" id="service_saved_directly" value="{{ old('service_saved_directly', '0') }}">
<input type="hidden" name="service_id" id="service_id" value="{{ old('service_id', '') }}">

<div class="card shadow-sm">
    <div class="card-body">

        @if($hasPreviousReport)
            <div id="serviceInfoBox" class="alert alert-info {{ $hasServiceSaved ? '' : 'd-none' }}">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold mb-1">
                            Data perbaikan asset sudah berhasil tersimpan.
                        </div>
                        <div class="small text-muted">
                            Jika perlu, Anda bisa mengubah data perbaikan sebelum melanjutkan pengecekan.
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnEditServiceData">
                        <i class="bi bi-pencil-square me-1"></i> Ubah Data Perbaikan
                    </button>
                </div>
            </div>
        @endif

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

                @foreach($categories as $cat)
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

{{-- MODAL KONFIRMASI PERBAIKAN --}}
@if($hasPreviousReport)
<div class="modal fade" id="serviceBeforeCheckModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">Konfirmasi Perbaikan Asset</h5>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="fw-semibold mb-1">
                        Asset ini sudah pernah dilakukan pengecekan sebelumnya.
                    </div>
                    <div class="text-muted">
                        Apakah sudah dilakukan <strong>Perbaikan Asset</strong> setelah pengecekan sebelumnya?
                    </div>

                    @if($latestReport)
                        <div class="small text-muted mt-2">
                            Pengecekan terakhir:
                            <strong>{{ optional($latestReport->created_at)->format('d/m/Y H:i') }}</strong>
                        </div>
                    @endif
                </div>

                <div id="serviceFormFields" class="d-none">
                    <hr>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Dilakukannya Perbaikan</label>
                            <input type="date"
                                   id="service_date"
                                   class="form-control"
                                   value="{{ old('service_date', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keterangan Perbaikan</label>
                            <textarea id="service_description"
                                      rows="4"
                                      class="form-control"
                                      placeholder="Contoh: ganti charger, reinstall OS, ganti baterai, service kipas, dll.">{{ old('service_description') }}</textarea>
                        </div>
                    </div>

                    <div id="serviceValidationMessage" class="alert alert-danger mt-3 d-none mb-0"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="btnSkipService" class="btn btn-outline-secondary">
                    Belum, lanjutkan pengecekan
                </button>

                <button type="button" id="btnShowServiceForm" class="btn btn-primary">
                    Sudah, input data perbaikan
                </button>

                <button type="button" id="btnSaveServiceDirect" class="btn btn-success d-none">
                    Simpan data perbaikan
                </button>
            </div>
        </div>
    </div>
</div>
@endif

</form>

@if($hasPreviousReport)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('serviceBeforeCheckModal');
    if (!modalEl) return;

    const serviceModal = new bootstrap.Modal(modalEl, {
        backdrop: 'static',
        keyboard: false
    });

    const serviceConfirmationInput = document.getElementById('service_confirmation');
    const serviceModalAnsweredInput = document.getElementById('service_modal_answered');
    const serviceSavedDirectlyInput = document.getElementById('service_saved_directly');
    const serviceIdInput = document.getElementById('service_id');

    const serviceFormFields = document.getElementById('serviceFormFields');
    const serviceInfoBox = document.getElementById('serviceInfoBox');

    const btnSkipService = document.getElementById('btnSkipService');
    const btnShowServiceForm = document.getElementById('btnShowServiceForm');
    const btnSaveServiceDirect = document.getElementById('btnSaveServiceDirect');
    const btnEditServiceData = document.getElementById('btnEditServiceData');

    const serviceDate = document.getElementById('service_date');
    const serviceDescription = document.getElementById('service_description');
    const serviceValidationMessage = document.getElementById('serviceValidationMessage');

    const shouldAutoShow = @json($shouldAutoShowServiceModal);

    function showValidation(message) {
        serviceValidationMessage.textContent = message;
        serviceValidationMessage.classList.remove('d-none');
    }

    function hideValidation() {
        serviceValidationMessage.textContent = '';
        serviceValidationMessage.classList.add('d-none');
    }

    function refreshModalButtons() {
        const alreadySaved = serviceSavedDirectlyInput.value === '1';

        if (alreadySaved) {
            btnSkipService.textContent = 'Tutup';
            btnShowServiceForm.classList.add('d-none');
            btnSaveServiceDirect.classList.remove('d-none');
        } else {
            btnSkipService.textContent = 'Belum, lanjutkan pengecekan';
        }
    }

    function showServiceForm() {
        serviceFormFields.classList.remove('d-none');
        btnShowServiceForm.classList.add('d-none');
        btnSaveServiceDirect.classList.remove('d-none');
        hideValidation();
    }

    function updateInfoBox() {
        if (serviceSavedDirectlyInput.value === '1') {
            serviceInfoBox?.classList.remove('d-none');
        } else {
            serviceInfoBox?.classList.add('d-none');
        }
    }

    if (shouldAutoShow) {
        serviceModal.show();
    }

    if (serviceSavedDirectlyInput.value === '1') {
        updateInfoBox();
        refreshModalButtons();
    }

    btnSkipService?.addEventListener('click', function () {
        const alreadySaved = serviceSavedDirectlyInput.value === '1';

        if (alreadySaved) {
            serviceModal.hide();
            return;
        }

        serviceConfirmationInput.value = '0';
        serviceModalAnsweredInput.value = '1';
        serviceModal.hide();
    });

    btnShowServiceForm?.addEventListener('click', function () {
        showServiceForm();
    });

    btnEditServiceData?.addEventListener('click', function () {
        showServiceForm();
        refreshModalButtons();
        serviceModal.show();
    });

    btnSaveServiceDirect?.addEventListener('click', async function () {
        hideValidation();

        const dateValue = serviceDate.value.trim();
        const descValue = serviceDescription.value.trim();

        if (!dateValue || !descValue) {
            showValidation('Tanggal perbaikan dan keterangan perbaikan wajib diisi.');
            return;
        }

        btnSaveServiceDirect.disabled = true;
        btnSaveServiceDirect.innerHTML = 'Menyimpan...';

        try {
            const response = await fetch('{{ route('admin.asset-checks.service-direct.store', $asset->id_asset) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: new URLSearchParams({
                    service_id: serviceIdInput.value || '',
                    service_date: dateValue,
                    service_description: descValue
                })
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    showValidation(Array.isArray(firstError) ? firstError[0] : 'Validasi gagal.');
                } else {
                    showValidation(data.message || 'Gagal menyimpan data perbaikan.');
                }
                return;
            }

            serviceConfirmationInput.value = '1';
            serviceModalAnsweredInput.value = '1';
            serviceSavedDirectlyInput.value = '1';
            serviceIdInput.value = data.service.id_service;

            updateInfoBox();
            refreshModalButtons();
            serviceModal.hide();
        } catch (error) {
            showValidation('Terjadi kesalahan saat menyimpan data perbaikan.');
        } finally {
            btnSaveServiceDirect.disabled = false;
            btnSaveServiceDirect.innerHTML = 'Simpan data perbaikan';
        }
    });
});
</script>
@endif

@endsection