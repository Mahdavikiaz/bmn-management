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
                                <label class="form-label">Kode BMN</label>
                                <input type="text" name="bmn_code" class="form-control"
                                       value="{{ old('bmn_code') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nama Device</label>
                                <input type="text" name="device_name" class="form-control"
                                       value="{{ old('device_name') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe Device</label>
                                <select name="device_type" class="form-select" required>
                                    <option value="">Pilih tipe...</option>
                                    <option value="PC" {{ old('device_type')=='PC' ? 'selected' : '' }}>PC</option>
                                    <option value="Laptop" {{ old('device_type')=='Laptop' ? 'selected' : '' }}>Laptop</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">GPU</label>
                                <input type="text" name="gpu" class="form-control"
                                       value="{{ old('gpu') }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tipe RAM</label>
                                <input type="text" name="ram_type" class="form-control"
                                       value="{{ old('ram_type') }}" required>
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

                            <div class="col-12">
                                <label class="form-label d-block">Jenis Storage</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_hdd" value="1"
                                               id="is_hdd" {{ old('is_hdd') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_hdd">HDD</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_ssd" value="1"
                                               id="is_ssd" {{ old('is_ssd') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_ssd">SSD</label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_nvme" value="1"
                                               id="is_nvme" {{ old('is_nvme') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_nvme">NVMe</label>
                                    </div>
                                </div>
                            </div>
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
@endsection
