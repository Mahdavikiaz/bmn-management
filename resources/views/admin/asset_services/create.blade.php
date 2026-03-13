@extends('layouts.app')

@section('title', 'SIMANIS | Input Perbaikan Asset')

@section('content')

<style>
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }
    .card-soft{ border: 1px solid #eef2f7; border-radius: 14px; }

    .section-title{ font-weight:700; margin-bottom:.25rem; }
    .section-subtitle{ color:#6c757d; font-size:.9rem; }

    .prio-badge{
        display:inline-flex; align-items:center; justify-content:center;
        min-width:44px; height:34px; padding:0 12px; border-radius:999px;
        font-weight:800; border:1px solid transparent;
    }
    .prio-nd{ background:#f1f3f5; border-color:#e9ecef; color:#6c757d; font-weight:700; }
    .prio-1{ background:#d1e7dd; border-color:#badbcc; color:#0f5132; }
    .prio-2{ background:#d1e7dd; border-color:#badbcc; color:#0f5132; }
    .prio-3{ background:#fff3cd; border-color:#ffecb5; color:#664d03; }
    .prio-4{ background:#ffe5d0; border-color:#ffd3b0; color:#7a3e00; }
    .prio-5{ background:#f8d7da; border-color:#f5c2c7; color:#842029; }

    .prio-desc{ margin-top:.5rem; font-size:.85rem; color:#6c757d; line-height:1.25rem; }

    .rec-box{
        background:#fff; border:1px solid #eef2f7; border-radius:14px;
        padding:14px; min-height:120px;
    }

    .rec-block-title{
        font-weight:700; font-size:.9rem; margin-bottom:6px;
        display:flex; align-items:center; gap:8px;
    }
    .rec-block-title .badge{ font-weight:700; }

    .rec-block-content{
        white-space:pre-line;
        color:#212529;
        line-height:1.45rem;
        font-size:.90rem;
    }
    .rec-block-content.text-muted{ color:#6c757d !important; }

    .form-card .form-label{
        font-weight: 600;
    }
</style>

@php
    $prioMeta = function($p){
        $map = [
            0 => ['prio-badge prio-nd', 'Belum dinilai', 'Belum ada penilaian untuk kategori ini.'],
            1 => ['prio-badge prio-1', 'Rendah', 'Tidak perlu tindakan apa-apa.'],
            2 => ['prio-badge prio-2', 'Cukup rendah', 'Pantau saja, belum perlu tindakan.'],
            3 => ['prio-badge prio-3', 'Sedang', 'Perlu dipertimbangkan untuk ditindaklanjuti.'],
            4 => ['prio-badge prio-4', 'Tinggi', 'Perlu tindakan (disarankan upgrade/penanganan).'],
            5 => ['prio-badge prio-5', 'Sangat tinggi', 'Harus segera ditindaklanjuti.'],
        ];

        $p = (int) $p;
        if (!isset($map[$p])) $p = 0;

        return [
            'badgeClass' => $map[$p][0],
            'label'      => $map[$p][1],
            'desc'       => $map[$p][2],
            'value'      => (string)$p,
        ];
    };

    $valOrDash = function(?string $t): string {
        $t = trim((string) $t);
        return ($t === '' || $t === '-') ? '-' : $t;
    };

    $pRam = (int)($lastReport->prior_ram ?? 0);
    $pSto = (int)($lastReport->prior_storage ?? 0);
    $pCpu = (int)($lastReport->prior_processor ?? 0);

    $pBat = (int)($lastReport->prior_baterai ?? 0);
    $pChg = (int)($lastReport->prior_charger ?? 0);

    $mRam = $prioMeta($pRam);
    $mSto = $prioMeta($pSto);
    $mCpu = $prioMeta($pCpu);
    $mBat = $prioMeta($pBat);
    $mChg = $prioMeta($pChg);

    $ramActionUi = $valOrDash($lastReport->recommendation_ram ?? '-');
    $stoActionUi = $valOrDash($lastReport->recommendation_storage ?? '-');
    $cpuActionUi = $valOrDash($lastReport->recommendation_processor ?? '-');

    $batActionUi = $valOrDash($lastReport->recommendation_baterai ?? '-');
    $chgActionUi = $valOrDash($lastReport->recommendation_charger ?? '-');

    $showBattery = !is_null($lastReport->prior_baterai ?? null) || trim((string)($lastReport->recommendation_baterai ?? '')) !== '';
    $showCharger = !is_null($lastReport->prior_charger ?? null) || trim((string)($lastReport->recommendation_charger ?? '')) !== '';
@endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Input Data Perbaikan</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->type?->type_name }}) | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    <a href="{{ route('admin.asset-services.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row g-3">

    {{-- FORM INPUT PERBAIKAN --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm form-card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.asset-services.store', $asset->id_asset) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tanggal Dilakukannya Perbaikan</label>
                        <input type="date"
                               name="service_date"
                               class="form-control @error('service_date') is-invalid @enderror"
                               value="{{ old('service_date', now()->format('Y-m-d')) }}"
                               required>
                        @error('service_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Keterangan Perbaikan</label>
                        <textarea name="service_description"
                                  class="form-control @error('service_description') is-invalid @enderror"
                                  rows="6"
                                  placeholder="Contoh: Ganti charger, membersihkan kipas, reinstall OS, mengganti baterai, dll."
                                  required>{{ old('service_description') }}</textarea>
                        @error('service_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.asset-services.index') }}" class="btn btn-secondary">Batal</a>
                        <button class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Simpan Perbaikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- REFERENSI HASIL PENGECEKAN --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm">
            <div class="card-body">
                <div class="section-title d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-clipboard-data"></i> Referensi Hasil Pengecekan Terakhir
                </div>

                @if($lastReport)
                    <div class="section-subtitle text-muted-sm mb-3">
                        Tanggal pengecekan:
                        <strong>{{ optional($lastReport->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>

                    {{-- PRIORITY CARDS --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 h-100">
                                <div class="text-muted-sm mb-4">Priority Level RAM</div>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="{{ $mRam['badgeClass'] }} me-2">{{ $mRam['value'] }}</span>
                                    <span class="text-muted-sm">
                                        <strong>{{ $mRam['label'] }}</strong> - {{ $mRam['desc'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 h-100">
                                <div class="text-muted-sm mb-4">Priority Level Storage</div>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="{{ $mSto['badgeClass'] }} me-2">{{ $mSto['value'] }}</span>
                                    <span class="text-muted-sm">
                                        <strong>{{ $mSto['label'] }}</strong> - {{ $mSto['desc'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 border rounded-4 h-100">
                                <div class="text-muted-sm mb-4">Priority Level CPU</div>
                                <div class="d-flex align-items-center mb-3">
                                    <span class="{{ $mCpu['badgeClass'] }} me-2">{{ $mCpu['value'] }}</span>
                                    <span class="text-muted-sm">
                                        <strong>{{ $mCpu['label'] }}</strong> - {{ $mCpu['desc'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($showBattery || $showCharger)
                        <div class="row g-3 mt-1">
                            @if($showBattery)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 h-100">
                                        <div class="text-muted-sm mb-4">Priority Level Baterai</div>
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="{{ $mBat['badgeClass'] }} me-2">{{ $mBat['value'] }}</span>
                                            <span class="text-muted-sm">
                                                <strong>{{ $mBat['label'] }}</strong> - {{ $mBat['desc'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($showCharger)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-4 h-100">
                                        <div class="text-muted-sm mb-4">Priority Level Charger</div>
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="{{ $mChg['badgeClass'] }} me-2">{{ $mChg['value'] }}</span>
                                            <span class="text-muted-sm">
                                                <strong>{{ $mChg['label'] }}</strong> - {{ $mChg['desc'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- REKOMENDASI CARDS --}}
                    <div class="row g-3 mt-4">
                        <div class="col-md-4">
                            <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-memory"></i> Rekomendasi RAM
                            </div>
                            <div class="rec-box">
                                <div class="rec-block-title mb-2">
                                    <span class="badge text-bg-primary">Tindakan</span>
                                </div>
                                <div class="rec-block-content {{ $ramActionUi === '-' ? 'text-muted' : '' }}">{{ $ramActionUi }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-hdd-stack"></i> Rekomendasi Storage
                            </div>
                            <div class="rec-box">
                                <div class="rec-block-title mb-2">
                                    <span class="badge text-bg-primary">Tindakan</span>
                                </div>
                                <div class="rec-block-content {{ $stoActionUi === '-' ? 'text-muted' : '' }}">{{ $stoActionUi }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-cpu"></i> Rekomendasi CPU
                            </div>
                            <div class="rec-box">
                                <div class="rec-block-title mb-2">
                                    <span class="badge text-bg-primary">Tindakan</span>
                                </div>
                                <div class="rec-block-content {{ $cpuActionUi === '-' ? 'text-muted' : '' }}">{{ $cpuActionUi }}</div>
                            </div>
                        </div>
                    </div>

                    @if($showBattery || $showCharger)
                        <div class="row g-3 mt-1">
                            @if($showBattery)
                                <div class="col-md-6">
                                    <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-battery-half"></i> Rekomendasi Baterai
                                    </div>
                                    <div class="rec-box">
                                        <div class="rec-block-title mb-2">
                                            <span class="badge text-bg-primary">Tindakan</span>
                                        </div>
                                        <div class="rec-block-content {{ $batActionUi === '-' ? 'text-muted' : '' }}">{{ $batActionUi }}</div>
                                    </div>
                                </div>
                            @endif

                            @if($showCharger)
                                <div class="col-md-6">
                                    <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                                        <i class="bi bi-plug"></i> Rekomendasi Charger
                                    </div>
                                    <div class="rec-box">
                                        <div class="rec-block-title mb-2">
                                            <span class="badge text-bg-primary">Tindakan</span>
                                        </div>
                                        <div class="rec-block-content {{ $chgActionUi === '-' ? 'text-muted' : '' }}">{{ $chgActionUi }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('admin.asset-checks.show', [$asset->id_asset, $lastReport->id_report]) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye me-1"></i> Lihat detail hasil pengecekan
                        </a>
                    </div>
                @else
                    <div class="text-muted fst-italic">
                        Belum ada hasil pengecekan untuk asset ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection