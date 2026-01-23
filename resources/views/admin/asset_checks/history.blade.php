@extends('layouts.app')

@section('title', 'History Pengecekan Asset')

@section('content')

<style>
    .table-modern thead th{
        background:#f8f9fa;
        font-weight:700;
        white-space: nowrap;
    }
    .table-modern tbody tr:hover{ background:#f6f9ff; }
    .table-modern tbody td{
        font-size: 0.95rem;
        border-top: 1px solid #eef2f7;
        vertical-align: middle;
    }
    .table-modern thead th{
        font-size: 1rem;
        border-bottom: 2px solid #d0d7e2;
    }
    .table-modern td,
    .table-modern th{
        padding-top: .65rem;
        padding-bottom: .65rem;
    }
    .btn-icon{
        width:38px; height:38px;
        display:inline-flex; align-items:center; justify-content:center;
        border-radius:10px;
    }
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }
    .pill{
        background:#f1f3f5;
        border:1px solid #dee2e6;
        padding:.25rem .6rem;
        font-weight:700;
        border-radius:999px;
        font-size:.85rem;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-2">History Pengecekan</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->device_type }}) | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
        </div>

        <div class="mt-1">
            @if($latest)
                <span class="text-muted-sm">
                    Pengecekan Terakhir : <strong>{{ optional($latest->created_at)->format('d/m/Y H:i') }}</strong>
                </span>
            @else
                <span class="text-muted-sm fst-italic">Belum ada hasil pengecekan.</span>
            @endif
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.asset-checks.create', $asset->id_asset) }}" class="btn btn-primary">
            <i class="bi bi-clipboard-check me-1"></i> Pengecekan Baru
        </a>

        <a href="{{ route('admin.asset-checks.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;" class="fw-semibold">No</th>
                        <th style="width:200px;" class="fw-semibold">Tanggal</th>
                        <th style="width:260px;" class="fw-semibold">Priority</th>
                        <th class="fw-semibold">Ringkasan</th>
                        <th style="width:170px;" class="text-center fw-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($history as $report)
                        @php
                            // Hanya report terbaru yang boleh dilihat detail
                            $isLatest = $latest && ((int)$latest->id_report === (int)$report->id_report);

                            // Ringkasan dari rekomendasi baris pertama per kategori
                            $pick = function($txt) {
                                if (!$txt) return null;
                                $t = trim($txt);
                                if ($t === '-' || $t === '') return null;

                                $t = preg_split("/\r\n|\n|\r/", $t)[0] ?? $t;
                                $t = str_replace('•', '', $t);
                                $t = preg_replace('/\s+/', ' ', $t);

                                return trim($t) ?: null;
                            };

                            $ramLine = $pick($report->recommendation_ram);
                            $stLine  = $pick($report->recommendation_storage);
                            $cpuLine = $pick($report->recommendation_processor);

                            $parts = array_filter([
                                $ramLine ? "RAM: {$ramLine}" : null,
                                $stLine  ? "Storage: {$stLine}" : null,
                                $cpuLine ? "CPU: {$cpuLine}" : null,
                            ]);

                            $summary = count($parts) ? implode(' | ', $parts) : '-';
                        @endphp

                        <tr>
                            <td>
                                {{ method_exists($history,'currentPage')
                                    ? (($history->currentPage() - 1) * $history->perPage() + $loop->iteration)
                                    : $loop->iteration
                                }}
                            </td>

                            <td>
                                <div class="fw-semibold">{{ optional($report->created_at)->format('d/m/Y H:i') }}</div>
                            </td>

                            <td>
                                <span class="pill me-1">RAM: {{ $report->prior_ram }}</span>
                                <span class="pill me-1">Storage: {{ $report->prior_storage }}</span>
                                <span class="pill">CPU: {{ $report->prior_processor }}</span>
                            </td>

                            <td class="text-muted-sm">
                                <span title="{{ $summary }}">
                                    {{ $summary !== '-' ? \Illuminate\Support\Str::limit($summary, 140) : '-' }}
                                </span>
                                @if($isLatest)
                                    <span class="ms-2 badge rounded-pill text-bg-primary">
                                        <i class="bi bi-star-fill me-1"></i> Terbaru
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-inline-flex gap-2">
                                    @if($isLatest)
                                        <a href="{{ route('admin.asset-checks.show', [$asset->id_asset, $report->id_report]) }}"
                                           class="btn btn-info btn-icon text-white"
                                           title="Lihat detail (terbaru)">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @else
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger js-delete"
                                            data-action="{{ route('admin.asset-checks.reports.destroy', [$asset->id_asset, $report->id_report]) }}"
                                            data-title="Hapus report ini?"
                                            data-message="Report pengecekan ini akan terhapus permanen.">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">
                                Belum ada history pengecekan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($history, 'links'))
        <div class="card-footer bg-white">
            {{ $history->links() }}
        </div>
    @endif
</div>

@endsection
