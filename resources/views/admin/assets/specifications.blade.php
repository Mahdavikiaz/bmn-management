@extends('layouts.app')

@section('title', 'Kelola Spesifikasi')

@section('content')
    {{-- TITLE --}}
    <div class="mb-4">
        <h4 class="mb-1">Kelola Spesifikasi</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->device_type }}) • Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- RECAP / INFO --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-3 text-primary"></i>
                    <h6 class="mt-2 mb-1">Asset</h6>
                    <div class="fw-semibold">{{ $asset->device_name }}</div>
                    <div class="text-muted small">{{ $asset->device_type }} • {{ $asset->procurement_year }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-cpu fs-3 text-success"></i>
                    <h6 class="mt-2 mb-1">Spesifikasi Terbaru</h6>

                    @if(!$latestSpec)
                        <div class="text-muted fst-italic">Belum diinputkan</div>
                    @else
                        <div class="fw-semibold">{{ $latestSpec->processor }}</div>
                        <div class="text-muted small">
                            RAM {{ $latestSpec->ram }} GB • Storage {{ $latestSpec->storage }} GB
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history fs-3 text-warning"></i>
                    <h6 class="mt-2 mb-1">Total Riwayat</h6>
                    <div class="fw-semibold fs-4">{{ $specs->count() }}</div>
                    <div class="text-muted small">versi spesifikasi</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ACTION BAR (inline form) --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">

        {{-- FORM TAMBAH SPEC (INLINE) --}}
        <form method="POST"
              action="{{ route('admin.assets.specifications.store', $asset->id_asset) }}"
              class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
            @csrf

            <input type="text" name="processor"
                   class="form-control"
                   style="min-width: 240px; max-width: 320px;"
                   placeholder="Processor (opsional)"
                   value="{{ old('processor') }}">

            <input type="number" name="ram"
                   class="form-control"
                   style="width: 140px;"
                   min="0"
                   placeholder="RAM (GB)"
                   value="{{ old('ram') }}">

            <input type="number" name="storage"
                   class="form-control"
                   style="width: 160px;"
                   min="0"
                   placeholder="Storage (GB)"
                   value="{{ old('storage') }}">

            <input type="text" name="os_version"
                   class="form-control"
                   style="min-width: 220px; max-width: 280px;"
                   placeholder="OS Version (opsional)"
                   value="{{ old('os_version') }}">

            {{-- checkbox compact --}}
            <div class="d-flex align-items-center gap-2 ms-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_hdd" value="1" id="is_hdd"
                           {{ old('is_hdd') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="is_hdd">HDD</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_ssd" value="1" id="is_ssd"
                           {{ old('is_ssd') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="is_ssd">SSD</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_nvme" value="1" id="is_nvme"
                           {{ old('is_nvme') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="is_nvme">NVMe</label>
                </div>
            </div>

            <button class="btn btn-primary ms-auto">
                <i class="bi bi-plus-lg"></i> Simpan Versi
            </button>
        </form>

        {{-- KEMBALI --}}
        <a href="{{ route('admin.assets.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="text-muted small mb-3">
        * Isi minimal 1 field untuk menyimpan versi spesifikasi baru. Versi lama tidak akan berubah (history).
    </div>

    {{-- LATEST SPEC DETAIL --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            Detail Spesifikasi Terbaru
        </div>
        <div class="card-body">
            @if(!$latestSpec)
                <div class="text-muted fst-italic">Data spesifikasi belum diinputkan.</div>
            @else
                @php
                    $media = [];
                    if($latestSpec->is_hdd) $media[] = 'HDD';
                    if($latestSpec->is_ssd) $media[] = 'SSD';
                    if($latestSpec->is_nvme) $media[] = 'NVMe';
                @endphp

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Processor</div>
                        <div class="fw-semibold">{{ $latestSpec->processor }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">RAM</div>
                        <div class="fw-semibold">{{ $latestSpec->ram }} GB</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small">Storage</div>
                        <div class="fw-semibold">{{ $latestSpec->storage }} GB</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">OS Version</div>
                        <div class="fw-semibold">{{ $latestSpec->os_version }}</div>
                    </div>
                    <div class="col-md-1">
                        <div class="text-muted small">Media</div>
                        <div class="fw-semibold">{{ count($media) ? implode(', ', $media) : '-' }}</div>
                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    Terakhir diinput: <strong>{{ $latestSpec->datetime?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
            @endif
        </div>
    </div>

    {{-- HISTORY TABLE --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">
            Riwayat Spesifikasi
        </div>

        <div class="card-body p-0">
            @if($specs->isEmpty())
                <div class="p-3 text-muted fst-italic">
                    Belum ada riwayat spesifikasi.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle">
                        <thead class="table-light">
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>Datetime</th>
                            <th>Processor</th>
                            <th>RAM</th>
                            <th>Storage</th>
                            <th>OS</th>
                            <th>Media</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($specs as $spec)
                            @php
                                $media = [];
                                if($spec->is_hdd) $media[] = 'HDD';
                                if($spec->is_ssd) $media[] = 'SSD';
                                if($spec->is_nvme) $media[] = 'NVMe';
                            @endphp

                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $spec->datetime?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $spec->processor }}</td>
                                <td>{{ $spec->ram }} GB</td>
                                <td>{{ $spec->storage }} GB</td>
                                <td>{{ $spec->os_version }}</td>
                                <td>{{ count($media) ? implode(', ', $media) : '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
