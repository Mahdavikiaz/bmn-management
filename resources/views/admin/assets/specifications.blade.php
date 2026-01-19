@extends('layouts.app')

@section('title', 'Kelola Spesifikasi')

@section('content')
    <style>
        /* --- helper --- */
        .table-modern thead th {
            background: #f8f9fa;
            font-weight: 700;
            border-bottom: 1px solid #e9ecef;
        }

        .table-modern tbody tr:hover {
            background: #f6f9ff;
        }

        .section-title {
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .section-subtitle {
            color: #6c757d;
            font-size: .9rem;
        }

        .spec-list .spec-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px dashed #e9ecef;
        }

        .spec-list .spec-item:last-child {
            border-bottom: 0;
        }

        .spec-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: #0d6efd;
            flex: 0 0 auto;
            font-size: 1.1rem;
        }

        .spec-label {
            color: #6c757d;
            font-size: .85rem;
        }

        .spec-value {
            font-weight: 700;
        }

        .badge-soft {
            background: #eef2ff;
            color: #0d6efd;
            border: 1px solid #dfe7ff;
            font-weight: 600;
        }

        .badge-media {
            background: #f1f3f5;
            color: #343a40;
            border: 1px solid #e9ecef;
            font-weight: 600;
        }

        .version-pill {
            background: #e7f5ff;
            color: #1864ab;
            border: 1px solid #a5d8ff;
            font-weight: 700;
        }
    </style>

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-1">Kelola Spesifikasi</h4>
            <div class="text-muted">
                {{ $asset->device_name }} ({{ $asset->device_type }}) •
                Kode BMN: <strong>{{ $asset->bmn_code }}</strong> •
                Tahun: <strong>{{ $asset->procurement_year }}</strong>
            </div>
        </div>

        <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- SECTION: SPESIFIKASI SAAT INI --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="section-title">
                        <i class="bi bi-cpu me-1"></i> Spesifikasi Saat Ini
                    </div>
                    <div class="section-subtitle">
                        Menampilkan spesifikasi terbaru yang tersimpan (versi lama tersimpan sebagai history).
                    </div>
                </div>

                <div>
                    <span class="badge rounded-pill {{ $specs->count() ? 'badge-soft' : 'text-bg-secondary' }}">
                        <i class="bi bi-clock-history"></i>
                        Total Riwayat: {{ $specs->count() }}
                    </span>
                </div>
            </div>

            <hr class="my-3">

            @if (!$latestSpec)
                <div class="text-muted fst-italic">
                    Data spesifikasi belum diinputkan.
                </div>
            @else
                @php
                    $storage_type = [];
                    if ($latestSpec->is_hdd) $storage_type[] = 'HDD';
                    if ($latestSpec->is_ssd) $storage_type[] = 'SSD';
                    if ($latestSpec->is_nvme) $storage_type[] = 'NVMe';
                @endphp

                <div class="spec-list">
                    <div class="spec-item">
                        <div class="spec-icon"><i class="bi bi-cpu"></i></div>
                        <div>
                            <div class="spec-label">Processor</div>
                            <div class="spec-value">{{ $latestSpec->processor ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-icon"><i class="bi bi-memory"></i></div>
                        <div>
                            <div class="spec-label">RAM</div>
                            <div class="spec-value">{{ $latestSpec->ram }} GB</div>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-icon"><i class="bi bi-hdd-stack"></i></div>
                        <div>
                            <div class="spec-label">Storage</div>
                            <div class="spec-value">{{ $latestSpec->storage }} GB</div>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-icon"><i class="bi bi-windows"></i></div>
                        <div>
                            <div class="spec-label">OS Version</div>
                            <div class="spec-value">{{ $latestSpec->os_version ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="spec-item">
                        <div class="spec-icon"><i class="bi bi-device-hdd"></i></div>
                        <div>
                            <div class="spec-label">Tipe Storage</div>
                            <div class="spec-value">
                                @if (count($storage_type))
                                    @foreach ($storage_type as $type)
                                        <span class="badge rounded-pill badge-media me-1">{{ $type }}</span>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-muted small">
                        <i class="bi bi-calendar-event me-1"></i>
                        Terakhir diupdate:
                        <strong>{{ $latestSpec->datetime?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- SECTION: FORM UPDATE VERSI SPESIFIKASI --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="section-title">
                <i class="bi bi-plus-circle me-1"></i> Form Update Spesifikasi
            </div>
            <div class="section-subtitle mb-3">
                Isi minimal 1 field untuk membuat versi baru. Versi lama tidak akan berubah (history).
            </div>

            <form method="POST" action="{{ route('admin.assets.specifications.store', $asset->id_asset) }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Processor</label>
                        <input
                            type="text"
                            name="processor"
                            class="form-control"
                            value="{{ old('processor', $latestSpec->processor ?? '') }}"
                            placeholder="Contoh: Intel i5-1235U"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">RAM (GB)</label>
                        <input
                            type="number"
                            name="ram"
                            class="form-control"
                            value="{{ old('ram', $latestSpec->ram ?? '') }}"
                            min="0"
                            placeholder="16"
                        >
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Storage (GB)</label>
                        <input
                            type="number"
                            name="storage"
                            class="form-control"
                            value="{{ old('storage', $latestSpec->storage ?? '') }}"
                            min="0"
                            placeholder="512"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">OS Version</label>
                        <input
                            type="text"
                            name="os_version"
                            class="form-control"
                            value="{{ old('os_version', $latestSpec->os_version ?? '') }}"
                            placeholder="Contoh: Windows 11 Pro"
                        >
                    </div>

                    <div class="col-md-6">
                        <label class="form-label d-block">Jenis Storage</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_hdd"
                                    value="1"
                                    id="is_hdd"
                                    {{ old('is_hdd', $latestSpec->is_hdd ?? false) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_hdd">HDD</label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_ssd"
                                    value="1"
                                    id="is_ssd"
                                    {{ old('is_ssd', $latestSpec->is_ssd ?? false) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_ssd">SSD</label>
                            </div>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="is_nvme"
                                    value="1"
                                    id="is_nvme"
                                    {{ old('is_nvme', $latestSpec->is_nvme ?? false) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_nvme">NVMe</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Versi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SECTION: TABEL HISTORY --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="section-title mb-1">
                <i class="bi bi-clock-history me-1"></i> Riwayat Spesifikasi
            </div>
            <div class="section-subtitle mb-3">
                Daftar versi spesifikasi dari yang terbaru sampai terlama.
            </div>

            @if ($specs->isEmpty())
                <div class="text-muted fst-italic">Belum ada riwayat spesifikasi.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:60px;">No</th>
                                <th style="width:180px;">Datetime</th>
                                <th>Processor</th>
                                <th style="width:120px;">RAM</th>
                                <th style="width:140px;">Storage</th>
                                <th>OS</th>
                                <th style="width:170px;">Tipe Storage</th>
                                <th style="width:120px;">Versi</th>
                                <th style="width:110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($specs as $spec)
                                @php
                                    $storage_type = [];
                                    if ($spec->is_hdd) $storage_type[] = 'HDD';
                                    if ($spec->is_ssd) $storage_type[] = 'SSD';
                                    if ($spec->is_nvme) $storage_type[] = 'NVMe';
                                    $isLatest = $latestSpec && $spec->id_spec === $latestSpec->id_spec;
                                @endphp

                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $spec->datetime?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $spec->processor ?: '-' }}</td>
                                    <td>{{ $spec->ram }} GB</td>
                                    <td>{{ $spec->storage }} GB</td>
                                    <td>{{ $spec->os_version ?: '-' }}</td>
                                    <td>
                                        @if (count($storage_type))
                                            @foreach ($storage_type as $type)
                                                <span class="badge rounded-pill badge-media me-1">{{ $type }}</span>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($isLatest)
                                            <span class="badge rounded-pill version-pill">
                                                <i class="bi bi-star-fill"></i> Terbaru
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    {{-- HAPUS VERSI SPEC (tidak boleh hapus versi terbaru) --}}
                                    <td class="text-center">
                                        @if(!$isLatest)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.assets.specifications.destroy', [$asset->id_asset, $spec->id_spec]) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Yakin hapus versi spesifikasi ini?')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus versi">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
