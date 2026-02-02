@extends('layouts.app')

@section('title', 'Tambah Asset')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-0">
        <div>
            <h4 class="mb-2">Tambah Asset</h4>
            <p class="text-muted">Form untuk menambahkan data asset baru</p>
        </div>
        <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.assets.store') }}">
        @csrf

        <div class="card shadow-sm">
            <div class="card-body">

                {{-- Tabs --}}
                <ul class="nav nav-tabs" id="assetTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-master" data-bs-toggle="tab"
                                data-bs-target="#pane-master" type="button" role="tab">
                            <i class="bi bi-box-seam"></i> Data Asset (Master)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-spec" data-bs-toggle="tab"
                                data-bs-target="#pane-spec" type="button" role="tab">
                            <i class="bi bi-cpu"></i> Spesifikasi (Opsional)
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-4" id="assetTabsContent">

                    {{-- TAB: MASTER --}}
                    <div class="tab-pane fade show active" id="pane-master" role="tabpanel">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="form-label">Kode NUP</label>
                                <input type="text" name="nup" class="form-control"
                                       value="{{ old('nup') }}" required>
                                <div class="form-text">
                                    Kode BMN akan digenerate otomatis dari <strong>Kode Tipe Asset</strong> + <strong>NUP</strong>.
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe Asset</label>
                                <select name="id_type" class="form-select" required>
                                    <option value="" disabled {{ old('id_type') ? '' : 'selected' }}>Pilih tipe asset</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->id_type }}"
                                            {{ (string)old('id_type') === (string)$t->id_type ? 'selected' : '' }}>
                                            {{ $t->type_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nama Device</label>
                                <input type="text" name="device_name" class="form-control"
                                       value="{{ old('device_name') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">GPU</label>
                                <input type="text" name="gpu" class="form-control"
                                       value="{{ old('gpu') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe RAM</label>
                                <select name="ram_type" class="form-select @error('ram_type') is-invalid @enderror">
                                    <option value="">Pilih Tipe RAM</option>
                                    @foreach(['DDR3','DDR4','DDR5'] as $t)
                                        <option value="{{ $t }}" {{ old('ram_type', $asset->ram_type ?? '') === $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('ram_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tahun Pengadaan</label>
                                <input type="number" name="procurement_year" class="form-control"
                                       value="{{ old('procurement_year') }}"
                                       min="1900" max="{{ date('Y') }}" required>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-primary"
                                    onclick="document.querySelector('#tab-spec').click()">
                                Lanjut ke Spesifikasi <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- TAB: SPEC --}}
                    <div class="tab-pane fade" id="pane-spec" role="tabpanel">
                        <div class="text-muted small mb-3">
                            Spesifikasi bersifat opsional. Anda bisa mengisinya sekarang atau nanti dari menu “Kelola Spesifikasi”.
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nama Pemegang Asset</label>
                                <input type="text" name="owner_asset" class="form-control"
                                       value="{{ old('owner_asset') }}" placeholder="Masukkan nama pemegang asset saat ini.">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Processor</label>
                                <input type="text" name="processor" class="form-control"
                                       value="{{ old('processor') }}" placeholder="Contoh: Intel i5-1235U">
                            </div>

                            <div class="col-12">
                                <label class="form-label">RAM (GB)</label>
                                <input type="number" name="ram" class="form-control"
                                       value="{{ old('ram') }}" min="0" placeholder="16">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Storage (GB)</label>
                                <input type="number" name="storage" class="form-control"
                                       value="{{ old('storage') }}" min="0" placeholder="512">
                            </div>

                            <div class="col-12">
                                <label class="form-label">OS Version</label>
                                <input type="text" name="os_version" class="form-control"
                                       value="{{ old('os_version') }}" placeholder="Contoh: Windows 11 Pro">
                            </div>

                            {{-- RADIO biar cuma bisa pilih satu --}}
                            <div class="col-12">
                                <label class="form-label d-block">Jenis Storage</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="storage_kind" value="HDD"
                                               id="storage_hdd" {{ old('storage_kind') === 'HDD' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="storage_hdd">HDD</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="storage_kind" value="SSD"
                                               id="storage_ssd" {{ old('storage_kind') === 'SSD' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="storage_ssd">SSD</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="storage_kind" value="NVME"
                                               id="storage_nvme" {{ old('storage_kind') === 'NVME' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="storage_nvme">NVMe</label>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="is_hdd" id="is_hdd" value="0">
                            <input type="hidden" name="is_ssd" id="is_ssd" value="0">
                            <input type="hidden" name="is_nvme" id="is_nvme" value="0">

                        </div>

                        {{-- ACTIONS --}}
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-secondary">Batal</a>
                            <button class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <script>
        // Mapping radio storage_kind -> hidden boolean flags
        function syncStorageFlags() {
            const picked = document.querySelector('input[name="storage_kind"]:checked')?.value || '';
            document.getElementById('is_hdd').value  = (picked === 'HDD') ? '1' : '0';
            document.getElementById('is_ssd').value  = (picked === 'SSD') ? '1' : '0';
            document.getElementById('is_nvme').value = (picked === 'NVME') ? '1' : '0';
        }

        document.querySelectorAll('input[name="storage_kind"]').forEach(el => {
            el.addEventListener('change', syncStorageFlags);
        });

        // initial sync (biar old() kebaca)
        syncStorageFlags();
    </script>
@endsection
