@extends('layouts.app')

@section('title', 'Hasil Pengecekan')

@section('content')

<style>
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }

    .card-soft{
        border: 1px solid #eef2f7;
        border-radius: 14px;
    }

    .spec-row{
        display:flex;
        gap:14px;
        align-items:center;
        padding: 14px 0;
        border-top: 1px dashed #eef2f7;
    }
    .spec-row:first-child{ border-top: 0; padding-top: 0; }
    .spec-icon{
        width:44px; height:44px;
        border-radius:12px;
        display:flex; align-items:center; justify-content:center;
        background:#eef4ff;
        color:#0d6efd;
        flex: 0 0 auto;
    }
    .spec-label{ font-size:.9rem; color:#6c757d; }
    .spec-value{ font-weight:700; font-size:1.05rem; }

    .prio-pill{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width: 44px;
        height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        font-weight: 800;
        border: 1px solid #e9ecef;
        background: #f8f9fa;
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

    $storageType = $spec
        ? ($spec->is_nvme ? 'NVMe' : ($spec->is_ssd ? 'SSD' : ($spec->is_hdd ? 'HDD' : '-')))
        : '-';

    $isLatest = function($row) use ($report) {
        return (int)$row->id_report === (int)$report->id_report;
    };
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

{{-- STACK LAYOUT --}}
<div class="row g-3">

    {{-- SPEC TERKINI --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                    <div>
                        <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-cpu"></i> Spesifikasi Saat Ini
                        </div>
                        <div class="text-muted-sm">
                            Terakhir update:
                            <strong>{{ $spec?->datetime ? \Carbon\Carbon::parse($spec->datetime)->format('d/m/Y H:i') : '-' }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('admin.assets.specifications.index', $asset->id_asset) }}"
                       class="btn btn-outline-primary btn-sm">
                        Kelola Spesifikasi
                    </a>
                </div>

                <hr>

                @if($spec)
                    <div class="mt-3">
                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-cpu"></i></div>
                            <div>
                                <div class="spec-label">Processor</div>
                                <div class="spec-value fw-semibold">{{ $spec->processor }}</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-memory"></i></div>
                            <div>
                                <div class="spec-label">RAM</div>
                                <div class="spec-value fw-semibold">{{ $spec->ram }} GB</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-hdd-stack"></i></div>
                            <div>
                                <div class="spec-label">Storage</div>
                                <div class="spec-value fw-semibold">{{ $spec->storage }} GB</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-nvidia"></i></div>
                            <div>
                                <div class="spec-label">GPU</div>
                                <div class="spec-value fw-semibold">{{ $asset->gpu }}</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-device-hdd"></i></div>
                            <div>
                                <div class="spec-label">Tipe RAM</div>
                                <div class="spec-value fw-semibold">{{ $asset->ram_type }}</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-device-hdd"></i></div>
                            <div>
                                <div class="spec-label">Tipe Storage</div>
                                <div class="spec-value fw-semibold">{{ $storageType }}</div>
                            </div>
                        </div>

                        <div class="spec-row">
                            <div class="spec-icon"><i class="bi bi-windows"></i></div>
                            <div>
                                <div class="spec-label">OS Version</div>
                                <div class="spec-value fw-semibold">{{ $spec->os_version ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-muted fst-italic mt-3">
                        Belum ada spesifikasi tersimpan.
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
                        <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-clipboard-check"></i> Hasil Pengecekan Terbaru
                        </div>
                        <div class="text-muted-sm">
                            Dibuat:
                            <strong>{{ optional($report->created_at)->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- PRIORITIES --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-2">Priority Level RAM</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="prio-pill">{{ $report->prior_ram }}</span>
                                <span class="text-muted-sm">
                                    Estimasi Harga : {{ $report->upgrade_ram_price ? 'Rp '.number_format($report->upgrade_ram_price,0,',','.') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-2">Priority Level Storage</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="prio-pill">{{ $report->prior_storage }}</span>
                                <span class="text-muted-sm">
                                    Estimasi Harga : {{ $report->upgrade_storage_price ? 'Rp '.number_format($report->upgrade_storage_price,0,',','.') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-2">Priority CPU</div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="prio-pill">{{ $report->prior_processor }}</span>
                                <span class="text-muted-sm">Estimasi Harga : -</span>
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
                <div class="fw-semibold d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-clock-history"></i> Riwayat Pengecekan
                </div>
                <div class="text-muted-sm">
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
                        // ringkasan dari 1 point/kalimat pertama per kategori
                        $pick = function($txt) {
                            if (!$txt) return null;
                            $t = trim($txt);
                            if ($t === '-' || $t === '') return null;

                            // ambil baris pertama aja
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
                    @endphp

                    <tr>
                        <td>{{ $history->firstItem() + $i }}</td>
                        <td class="fw-semibold">
                            {{ optional($row->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td>
                            <span class="badge rounded-pill text-bg-light border">RAM : {{ $row->prior_ram }}</span>
                            <span class="badge rounded-pill text-bg-light border">STORAGE : {{ $row->prior_storage }}</span>
                            <span class="badge rounded-pill text-bg-light border">CPU : {{ $row->prior_processor }}</span>
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
                                        class="btn btn-danger d-inline-flex align-items-center gap-2 js-delete" 
                                        data-action="{{ route('admin.asset-checks.reports.destroy', [$asset->id_asset, $report->id_report]) }}"
                                        data-title="Hapus report ini?"
                                        data-message="Report pengecekan ini akan terhapus permanen."><i class="bi bi-trash"></i> Hapus
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
