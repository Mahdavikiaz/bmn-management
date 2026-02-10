@extends('layouts.app')

@section('title', 'SIMANIS | Dashboard')

@section('content')

<style>
    .card-soft{ border:1px solid #eef2f7; border-radius:14px; }
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }

    .stat-card{
        position: relative;
        overflow: hidden;
        border-radius:14px;
        background:#fff;
        border:1px solid #eef2f7;
    }
    .stat-card .card-body{
        padding:16px 16px;
    }

    .stat-card::before{
        content:"";
        position:absolute;
        left:0; top:0;
        width:4px; height:100%;
        background: var(--accent, #6f42c1);
        border-top-left-radius:14px;
        border-bottom-left-radius:14px;
    }

    .stat-flex{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
    }

    .stat-left{
        min-width:0;
    }
    .stat-label{
        font-size:.9rem;
        color:#6c757d;
        margin-bottom:4px;
    }
    .stat-value{
        font-size:1.4rem;
        font-weight:700;
        line-height:1.2;
        margin-bottom:4px;
    }
    .stat-desc{
        font-size:.85rem;
        color:#6c757d;
        margin:0;
    }

    .stat-right{
        flex:0 0 auto;
        display:flex;
        align-items:center;
        justify-content:center;
        width:54px;
        height:54px;
        border-radius:14px;
        background: rgba(111,66,193,.08);
        color: var(--accent, #6f42c1);
        font-size:1.35rem;
    }

    .stat-right i{
        transform: translateY(1px);
    }

    .pill{
        display:inline-flex; align-items:center; gap:8px;
        border:1px solid #eef2f7; border-radius:999px;
        padding:6px 12px; background:#fff; font-weight:600;
    }

    .prio-badge{
        display:inline-flex; align-items:center; justify-content:center;
        min-width:54px; height:30px; padding:0 12px; border-radius:999px;
        font-weight:700; border:1px solid transparent; font-size:.75rem;
        white-space:nowrap;
    }
    .prio-0{ background:#f1f3f5; border-color:#e9ecef; color:#6c757d; }
    .prio-1,.prio-2{ background:#d1e7dd; border-color:#badbcc; color:#0f5132; }
    .prio-3{ background:#fff3cd; border-color:#ffecb5; color:#664d03; }
    .prio-4{ background:#ffe5d0; border-color:#ffd3b0; color:#7a3e00; }
    .prio-5{ background:#f8d7da; border-color:#f5c2c7; color:#842029; }

    .table-modern { border-collapse: separate; border-spacing: 0; }
    .table-modern thead th{
        background:#f8f9fa; font-weight:500; white-space:nowrap;
        border-bottom:2px solid #d0d7e2;
        padding:10px 12px;
    }
    .table-modern tbody td{
        border-top:1px solid #eef2f7; vertical-align:middle;
        padding:12px;
        font-size:.85rem;
    }

    .cell-title{ font-weight:700; }
    .cell-sub{ font-size:.85rem; color:#6c757d; margin-top:2px; }

    .summary-text{
        max-width: 320px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        line-height: 1.25rem;
        font-size: .9rem;
    }

    .est-mini{
        font-size:.9rem;
        line-height:1.25rem;
        white-space:nowrap;
    }

    .filters-wrap{
        background:#fbfcff;
        border:1px solid #eef2f7;
        border-radius:14px;
        padding:12px;
    }

    @media (max-width: 992px){
        .summary-text{ max-width: 240px; }
        .stat-right{ width:48px; height:48px; }
    }
</style>

@php
    $fmtPrice = function($v){
        $p = (float) $v;
        if ($p <= 0) return '-';
        return 'Rp ' . number_format($p, 0, ',', '.');
    };

    $pickFirstLine = function(?string $t): string {
        $t = trim((string)$t);
        if ($t === '' || $t === '-') return '-';
        $t = preg_replace("/\r\n|\r/", "\n", $t);

        if (str_contains($t, '•')) {
            $parts = array_values(array_filter(array_map('trim', explode('•', $t))));
            return $parts[0] ?? '-';
        }
        $lines = array_values(array_filter(array_map('trim', explode("\n", $t))));
        return $lines[0] ?? '-';
    };

    $prioClassAvg = function($avg){
        $avg = (float)$avg;
        if ($avg <= 0) return 'prio-badge prio-0';
        if ($avg < 3)  return 'prio-badge prio-2';
        if ($avg < 4)  return 'prio-badge prio-3';
        if ($avg < 5)  return 'prio-badge prio-4';
        return 'prio-badge prio-5';
    };

    $checkedBadge = function($checkedAt){
        return $checkedAt
            ? '<span class="badge text-bg-success">Sudah dicek</span>'
            : '<span class="badge text-bg-secondary">Belum dicek</span>';
    };
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-1">Dashboard</h4>
        <div class="text-muted-sm">Ringkasan untuk monitoring asset.</div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card shadow-sm" style="--accent:#6f42c1;">
            <div class="card-body">
                <div class="stat-flex">
                    <div class="stat-left">
                        <div class="stat-label mb-2">Total Asset</div>
                        <div class="stat-value mb-2">{{ $totalAssets }}</div>
                        <p class="stat-desc">Jumlah asset yang terdaftar</p>
                    </div>
                    <div class="stat-right" title="Trend">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card shadow-sm" style="--accent:#198754;">
            <div class="card-body">
                <div class="stat-flex">
                    <div class="stat-left">
                        <div class="stat-label mb-2">Sudah Dicek</div>
                        <div class="stat-value mb-2">{{ $checkedCount }}</div>
                        <p class="stat-desc">Punya report pengecekan</p>
                    </div>
                    <div class="stat-right" title="Trend" style="background:rgba(25,135,84,.10);">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card shadow-sm" style="--accent:#6b7280;">
            <div class="card-body">
                <div class="stat-flex">
                    <div class="stat-left">
                        <div class="stat-label mb-2">Belum Dicek</div>
                        <div class="stat-value mb-2">{{ $uncheckedCount }}</div>
                        <p class="stat-desc">Perlu dilakukan pengecekan</p>
                    </div>
                    <div class="stat-right" title="Trend" style="background:rgba(107,114,128,.10);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card shadow-sm" style="--accent:#dc3545;">
            <div class="card-body">
                <div class="stat-flex">
                    <div class="stat-left">
                        <div class="stat-label mb-2">Perlu Tindak Lanjut</div>
                        <div class="stat-value mb-2">{{ $urgentCount }}</div>
                        <p class="stat-desc">Berdasarkan average priority</p>
                    </div>
                    <div class="stat-right" title="Trend" style="background:rgba(220,53,69,.10);">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Breakdown per type --}}
<div class="card card-soft shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="fw-bold mb-1">Jumlah Asset per Kategori</div>
                <div class="text-muted-sm">Distribusi jumlah asset berdasarkan tipe kategori.</div>
            </div>
        </div>
        <hr>
        <div class="d-flex flex-wrap gap-2">
            @forelse($byType as $row)
                <span class="pill">
                    <i class="bi bi-tag"></i> {{ $row->type_name }}
                    <span class="badge text-bg-light border">{{ $row->total }}</span>
                </span>
            @empty
                <span class="text-muted-sm">Belum ada data.</span>
            @endforelse
        </div>
    </div>
</div>

{{-- Table List Asset --}}
<div class="card card-soft shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <div class="fw-bold mb-1">Daftar Asset</div>
                <div class="text-muted-sm">List asset dan filter untuk pengecekan / tindak lanjut.</div>
            </div>
        </div>

        <hr>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.dashboard.index') }}" class="filters-wrap row g-2 align-items-end mb-3">
            <div class="col-lg-3">
                <label class="form-label text-muted-sm mb-1">Kategori Asset</label>
                <select name="id_type" class="form-select">
                    <option value="">Semua</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id_type }}" {{ (string)$typeId === (string)$t->id_type ? 'selected' : '' }}>
                            {{ $t->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label text-muted-sm mb-1">Status Cek</label>
                <select name="checked" class="form-select">
                    <option value="all" {{ $checked === 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="checked" {{ $checked === 'checked' ? 'selected' : '' }}>Sudah dicek</option>
                    <option value="unchecked" {{ $checked === 'unchecked' ? 'selected' : '' }}>Belum dicek</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label text-muted-sm mb-1">Min Avg Priority</label>
                <select name="min_priority" class="form-select">
                    <option value="" {{ $minAvgInt === null ? 'selected' : '' }}>Semua</option>
                    @for($i=1; $i<=5; $i++)
                        <option value="{{ $i }}" {{ (int)$minAvgInt === $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-lg-3">
                <label class="form-label text-muted-sm mb-1">Search</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control"
                       placeholder="Kode BMN / Nama device...">
            </div>

            <div class="col-lg-2 d-flex gap-2">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard.index') }}">
                    Reset
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:140px;">Kode BMN</th>
                        <th style="min-width:260px;">Nama Device</th>
                        <th style="width:130px;">Kategori</th>
                        <th style="width:120px;">Status</th>
                        <th style="width:120px;">Priority (Avg)</th>
                        <th style="width:180px;">Estimasi Upgrade</th>
                        <th style="width:150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($assets as $i => $a)
                    @php
                        $avg = (float)($a->avg_priority ?? 0);

                        $estRam = (float)($a->upgrade_ram_price ?? 0);
                        $estSto = (float)($a->upgrade_storage_price ?? 0);
                        $estTotal = $estRam + $estSto;
                    @endphp

                    <tr>
                        <td>{{ $assets->firstItem() + $i }}</td>

                        <td class="fw-semibold">{{ $a->bmn_code ?? '-' }}</td>

                        <td>
                            <div class="cell-title">{{ $a->device_name ?? '-' }}</div>
                            <div class="cell-sub">
                                @if($a->checked_at)
                                    Terakhir dicek: {{ \Carbon\Carbon::parse($a->checked_at)->format('d/m/Y H:i') }}
                                @else
                                    Belum pernah dicek
                                @endif
                            </div>
                        </td>

                        <td>{{ $a->type_name ?? '-' }}</td>

                        <td>{!! $checkedBadge($a->checked_at ?? null) !!}</td>

                        <td class="text-center">
                            <span class="{{ $prioClassAvg($avg) }}">{{ number_format($avg, 0) }}</span>
                        </td>

                        <td class="est-mini text-muted-sm">
                            <span>RAM: {{ $fmtPrice($estRam) }}</span><br>
                            <span>Storage: {{ $fmtPrice($estSto) }}</span><br>
                            Total: <b>{{ $fmtPrice($estTotal) }}</b>
                        </td>

                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                @if($a->latest_report_id)
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('admin.asset-checks.show', [$a->id_asset, $a->latest_report_id]) }}">
                                        Lihat
                                    </a>
                                @endif

                                <a class="btn btn-sm btn-primary"
                                   href="{{ route('admin.asset-checks.create', $a->id_asset) }}">
                                    Cek
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">
                            Tidak ada data asset sesuai filter.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($assets, 'links'))
            <div class="mt-3">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
