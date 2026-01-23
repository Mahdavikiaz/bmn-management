@extends('layouts.app')

@section('title', 'Hasil Pengecekan')

@section('content')

<style>
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }

    .card-soft{
        border: 1px solid #eef2f7;
        border-radius: 14px;
    }

    /* ===== SPEC (samakan dengan halaman Kelola Spesifikasi) ===== */
    .section-title{
        font-weight:700;
        margin-bottom:.25rem;
    }
    .section-subtitle{
        color:#6c757d;
        font-size:.9rem;
    }

    .spec-list .spec-item{
        display:flex;
        gap:10px;
        align-items:flex-start;
        padding:10px 0;
        border-bottom:1px dashed #e9ecef;
    }
    .spec-list .spec-item:last-child{ border-bottom:0; }

    .spec-icon{
        width:36px;
        height:36px;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
        background:#eef2ff;
        color:#0d6efd;
        flex:0 0 auto;
        font-size:1.1rem;
    }

    .spec-label{
        color:#6c757d;
        font-size:.85rem;
    }

    .spec-value{
        font-weight:700;
    }

    .badge-soft{
        background:#eef2ff;
        color:#0d6efd;
        border:1px solid #dfe7ff;
        font-weight:600;
    }

    .badge-media{
        background:#f1f3f5;
        color:#343a40;
        border:1px solid #e9ecef;
        font-weight:600;
    }

    /* ==== Priority badge soft ==== */
    .prio-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width: 44px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .prio-nd{
        background:#f1f3f5;
        border-color:#e9ecef;
        color:#6c757d;
        font-weight:700;
    }
    .prio-1{ background:#d1e7dd; border-color:#badbcc; color:#0f5132; } /* green */
    .prio-2{ background:#d1e7dd; border-color:#badbcc; color:#0f5132; } /* green */
    .prio-3{ background:#fff3cd; border-color:#ffecb5; color:#664d03; } /* yellow */
    .prio-4{ background:#ffe5d0; border-color:#ffd3b0; color:#7a3e00; } /* orange soft */
    .prio-5{ background:#f8d7da; border-color:#f5c2c7; color:#842029; } /* red */

    .prio-desc{
        margin-top: .5rem;
        font-size: .85rem;
        color:#6c757d;
        line-height: 1.25rem;
    }

    .rec-box{
        background:#fff;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 14px;
        min-height: 120px;
        white-space: pre-line;
    }

    .table-modern thead th{
        background:#f8f9fa;
        font-weight:700;
        white-space: nowrap;
        border-bottom: 2px solid #d0d7e2;
    }
    .table-modern tbody td{
        font-size: 0.95rem;
        border-top: 1px solid #eef2f7;
        vertical-align: middle;
    }
    .btn-icon{
        width:38px; height:38px;
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:10px;
        padding:0;
    }
</style>

@php
    // spec terkini
    $spec = $latestSpec ?? null;

    // tipe storage
    $storage_type = [];
    if ($spec?->is_hdd)  $storage_type[] = 'HDD';
    if ($spec?->is_ssd)  $storage_type[] = 'SSD';
    if ($spec?->is_nvme) $storage_type[] = 'NVMe';

    $isLatest = function($row) use ($report) {
        return (int)$row->id_report === (int)$report->id_report;
    };

    // PRIORITY
    $prioMeta = function($p){
        if ($p === null) {
            return [
                'badgeClass' => 'prio-badge prio-nd',
                'label'      => 'Belum dinilai',
                'desc'       => 'Belum ada penilaian untuk kategori ini (pertanyaan indikator belum tersedia).',
                'value'      => '-',
            ];
        }

        $map = [
            1 => ['prio-badge prio-1', 'Rendah',       'Tidak perlu tindakan apa-apa.'],
            2 => ['prio-badge prio-2', 'Cukup rendah', 'Pantau saja, belum perlu tindakan.'],
            3 => ['prio-badge prio-3', 'Sedang',       'Perlu dipertimbangkan untuk ditindaklanjuti.'],
            4 => ['prio-badge prio-4', 'Tinggi',       'Perlu tindakan (disarankan upgrade/penanganan).'],
            5 => ['prio-badge prio-5', 'Sangat tinggi','Harus segera ditindaklanjuti.'],
        ];

        return [
            'badgeClass' => $map[$p][0],
            'label'      => $map[$p][1],
            'desc'       => $map[$p][2],
            'value'      => (string)$p,
        ];
    };

    $mRam = $prioMeta($report->prior_ram);
    $mSto = $prioMeta($report->prior_storage);
    $mCpu = $prioMeta($report->prior_processor);
@endphp

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Hasil Pengecekan Asset</h4>
        <div class="text-muted-sm">
            {{ $asset->device_name }} | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.asset-checks.index') }}"
           class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- ===== SPEC TERKINI (UPDATED: sama seperti halaman Kelola Spesifikasi) ===== --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="section-title">
                            <i class="bi bi-cpu me-1"></i> Spesifikasi Saat Ini
                        </div>
                        <div class="section-subtitle">
                            Menampilkan spesifikasi terbaru yang tersimpan (versi lama tersimpan sebagai history).
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                           class="btn btn-outline-primary btn-sm">
                            Kelola Spesifikasi
                        </a>
                    </div>
                </div>

                <hr class="my-3">

                @if(!$spec)
                    <div class="text-muted fst-italic">
                        Data spesifikasi belum diinputkan.
                    </div>
                @else
                    <div class="spec-list">
                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-cpu"></i></div>
                            <div>
                                <div class="spec-label">Processor</div>
                                <div class="spec-value fw-semibold">{{ $spec->processor ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-memory"></i></div>
                            <div>
                                <div class="spec-label">RAM</div>
                                <div class="spec-value fw-semibold">{{ $spec->ram }} GB</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-hdd-stack"></i></div>
                            <div>
                                <div class="spec-label">Storage</div>
                                <div class="spec-value fw-semibold">{{ $spec->storage }} GB</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-nvidia"></i></div>
                            <div>
                                <div class="spec-label">GPU</div>
                                <div class="spec-value fw-semibold">{{ $asset->gpu }}</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-device-hdd"></i></div>
                            <div>
                                <div class="spec-label">Tipe RAM</div>
                                <div class="spec-value fw-semibold">{{ $asset->ram_type }}</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-device-ssd"></i></div>
                            <div>
                                <div class="spec-label">Tipe Storage</div>
                                <div class="spec-value">
                                    @if(count($storage_type))
                                        @foreach($storage_type as $type)
                                            <span class="badge rounded-pill badge-media me-1 fw-semibold">{{ $type }}</span>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-windows"></i></div>
                            <div>
                                <div class="spec-label">OS Version</div>
                                <div class="spec-value fw-semibold">{{ $spec->os_version ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-2 text-muted small">
                            <i class="bi bi-calendar-event me-1"></i>
                            Terakhir diupdate:
                            <strong>{{ $spec->datetime?->format('d/m/Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- HASIL PENGECEKAN SAAT INI --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="section-title d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-clipboard-check"></i> Hasil Pengecekan Terbaru
                        </div>
                        <div class="section-subtitle text-muted-sm">
                            Dibuat:
                            <strong>{{ optional($report->created_at)->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- PRIORITIES --}}
                <div class="row g-3">
                    {{-- RAM --}}
                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-4">Priority Level RAM</div>
                            <div class="d-flex align-items-center justify-content mb-4">
                                <span class="{{ $mRam['badgeClass'] }} me-2">{{ $mRam['value'] }}</span>
                                <span class="text-muted-sm"><strong>{{ $mRam['label'] }}</strong> - {{ $mRam['desc'] }}</span>
                            </div>
                            <div class="prio-desc">
                                <span class="text-muted-sm">
                                    Estimasi Harga Upgrade :
                                    {{ ($report->prior_ram !== null && $report->upgrade_ram_price)
                                        ? 'Rp '.number_format($report->upgrade_ram_price,0,',','.')
                                        : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- STORAGE --}}
                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-4">Priority Level Storage</div>
                            <div class="d-flex align-items-center justify-content mb-4">
                                <span class="{{ $mSto['badgeClass'] }} me-2">{{ $mSto['value'] }}</span>
                                <span class="text-muted-sm"><strong>{{ $mSto['label'] }}</strong> - {{ $mSto['desc'] }}</span>
                            </div>
                            <div class="prio-desc">
                                <span class="text-muted-sm">
                                    Estimasi Harga Upgrade :
                                    {{ ($report->prior_storage !== null && $report->upgrade_storage_price)
                                        ? 'Rp '.number_format($report->upgrade_storage_price,0,',','.')
                                        : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- CPU --}}
                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-4">Priority Level CPU</div>
                            <div class="d-flex align-items-center justify-content mb-4">
                                <span class="{{ $mCpu['badgeClass'] }} me-2">{{ $mCpu['value'] }}</span>
                                <span class="text-muted-sm"><strong>{{ $mCpu['label'] }}</strong> - {{ $mCpu['desc'] }}</span>
                            </div>
                            <div class="prio-desc">
                                <span class="text-muted-sm">Estimasi Harga Upgrade : -</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RECOMMENDATIONS --}}
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-memory"></i> Rekomendasi RAM
                        </div>
                        <div class="rec-box">{{ $report->recommendation_ram ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-hdd-stack"></i> Rekomendasi Storage
                        </div>
                        <div class="rec-box">{{ $report->recommendation_storage ?? '-' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-cpu"></i> Rekomendasi CPU
                        </div>
                        <div class="rec-box">{{ $report->recommendation_processor ?? '-' }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- RIWAYAT --}}
<div class="card card-soft shadow-sm mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
                <div class="section-title d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-clock-history"></i> Riwayat Pengecekan
                </div>
                <div class="section-subtitle text-muted-sm">
                    Menampilkan report terbaru sampai terlama (versi lama bisa dihapus).
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:180px;">Tanggal</th>
                        <th style="width:220px;">Priority Level</th>
                        <th>Ringkasan</th>
                        <th style="width:170px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($history as $i => $row)
                    @php
                        $pick = function($txt) {
                            if (!$txt) return null;
                            $t = trim($txt);
                            if ($t === '-' || $t === '') return null;

                            $t = preg_split("/\r\n|\n|\r/", $t)[0] ?? $t;
                            $t = str_replace('•', '', $t);
                            $t = preg_replace('/\s+/', ' ', $t);
                            return trim($t);
                        };

                        $parts = array_filter([
                            $pick($row->recommendation_ram) ? 'RAM: '.$pick($row->recommendation_ram) : null,
                            $pick($row->recommendation_storage) ? 'Storage: '.$pick($row->recommendation_storage) : null,
                            $pick($row->recommendation_processor) ? 'CPU: '.$pick($row->recommendation_processor) : null,
                        ]);

                        $summary = count($parts) ? implode(' | ', $parts) : '-';

                        $pr = $row->prior_ram === null ? 'Belum dinilai' : $row->prior_ram;
                        $ps = $row->prior_storage === null ? 'Belum dinilai' : $row->prior_storage;
                        $pc = $row->prior_processor === null ? 'Belum dinilai' : $row->prior_processor;
                    @endphp

                    <tr>
                        <td>{{ $history->firstItem() + $i }}</td>
                        <td class="fw-semibold">
                            {{ optional($row->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <span class="badge rounded-pill text-bg-light border">RAM : {{ $pr }}</span>
                            <span class="badge rounded-pill text-bg-light border">STORAGE : {{ $ps }}</span>
                            <span class="badge rounded-pill text-bg-light border">CPU : {{ $pc }}</span>
                        </td>

                        <td class="text-muted-sm">
                            <span title="{{ $summary }}">
                                {{ \Illuminate\Support\Str::limit($summary, 140) }}
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2 justify-content-center">
                                @if(!$isLatest($row))
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 js-delete"
                                        data-action="{{ route('admin.asset-checks.reports.destroy', [$asset->id_asset, $row->id_report]) }}"
                                        data-title="Hapus report ini?"
                                        data-message="Report pengecekan ini akan terhapus permanen.">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <div class="mt-0">
                                        <span class="badge rounded-pill text-bg-primary fw-semibold">
                                            <i class="bi bi-star-fill me-1"></i> Terbaru
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            Belum ada riwayat pengecekan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($history, 'links'))
            <div class="mt-3">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
