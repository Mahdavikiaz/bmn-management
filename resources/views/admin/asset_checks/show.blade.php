@extends('layouts.app')

@section('title', 'Hasil Pengecekan')

@section('content')

<style>
    .text-muted-sm{ color:#6c757d; font-size:.85rem; }
    .card-soft{ border: 1px solid #eef2f7; border-radius: 14px; }

    .section-title{ font-weight:700; margin-bottom:.25rem; }
    .section-subtitle{ color:#6c757d; font-size:.9rem; }

    .spec-list .spec-item{
        display:flex; gap:10px; align-items:flex-start;
        padding:10px 0; border-bottom:1px dashed #e9ecef;
    }
    .spec-list .spec-item:last-child{ border-bottom:0; }

    .spec-icon{
        width:36px; height:36px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        background:#eef2ff; color:#0d6efd; flex:0 0 auto; font-size:1.1rem;
    }
    .spec-label{ color:#6c757d; font-size:.85rem; }
    .spec-value{ font-weight:700; }

    .badge-media{
        background:#f1f3f5; color:#343a40; border:1px solid #e9ecef; font-weight:600;
    }

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

    .rec-block-content{ white-space:pre-line; color:#212529; line-height:1.45rem; font-size:.85rem; text-align: justify; text-justify: inter-word;}
    .rec-block-content.text-muted{ color:#6c757d !important; }

    .rec-divider{ border-top:1px dashed #e9ecef; margin:12px 0; }

    .table-modern thead th{
        background:#f8f9fa; font-weight:700; white-space:nowrap; border-bottom:2px solid #d0d7e2;
    }
    .table-modern tbody td{
        font-size:.95rem; border-top:1px solid #eef2f7; vertical-align:middle;
    }
</style>

@php
    use App\Models\Recommendation;

    // ===== SPEC =====
    $spec = $latestSpec ?? null;

    $storageTypeBadges = [];
    if ($spec?->is_hdd)  $storageTypeBadges[] = 'HDD';
    if ($spec?->is_ssd)  $storageTypeBadges[] = 'SSD';
    if ($spec?->is_nvme) $storageTypeBadges[] = 'NVMe';

    // ===== PRIORITY META =====
    $prioMeta = function($p){
        $map = [
            0 => ['prio-badge prio-nd', 'Belum dinilai', 'Belum ada penilaian untuk kategori ini.'],
            1 => ['prio-badge prio-1', 'Rendah',       'Tidak perlu tindakan apa-apa.'],
            2 => ['prio-badge prio-2', 'Cukup rendah', 'Pantau saja, belum perlu tindakan.'],
            3 => ['prio-badge prio-3', 'Sedang',       'Perlu dipertimbangkan untuk ditindaklanjuti.'],
            4 => ['prio-badge prio-4', 'Tinggi',       'Perlu tindakan (disarankan upgrade/penanganan).'],
            5 => ['prio-badge prio-5', 'Sangat tinggi','Harus segera ditindaklanjuti.'],
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

    $fmtPrice = function ($price): string {
        $p = (float) ($price ?? 0);
        if ($p <= 0) return '-';
        return 'Rp ' . number_format($p, 0, ',', '.');
    };

    $valOrDash = function(?string $t): string {
        $t = trim((string) $t);
        return ($t === '' || $t === '-') ? '-' : $t;
    };

    // ambil semua explanation berdasarkan category + priority_level
    $getExplanationByPriority = function(string $category, int $priority) {
        if ($priority <= 0) return '-';

        $rows = Recommendation::where('category', $category)
            ->where('priority_level', $priority)
            ->orderBy('id_recommendation')
            ->pluck('explanation')
            ->map(fn($x) => trim((string) $x))
            ->filter(fn($x) => $x !== '')
            ->values()
            ->all();

        if (!count($rows)) return '-';

        // jadikan bullet
        return "• " . implode("\n• ", $rows);
    };

    // Ambil action
    $ramActionUi = $valOrDash($report->recommendation_ram);
    $stoActionUi = $valOrDash($report->recommendation_storage);
    $cpuActionUi = $valOrDash($report->recommendation_processor);

    // Explanation by priority
    $ramExplain = $getExplanationByPriority('RAM', (int)($report->prior_ram ?? 0));
    $stoExplain = $getExplanationByPriority('STORAGE', (int)($report->prior_storage ?? 0));
    $cpuExplain = $getExplanationByPriority('CPU', (int)($report->prior_processor ?? 0));

    // PRIORITY META
    $mRam = $prioMeta((int)($report->prior_ram ?? 0));
    $mSto = $prioMeta((int)($report->prior_storage ?? 0));
    $mCpu = $prioMeta((int)($report->prior_processor ?? 0));
@endphp

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-2">Hasil Pengecekan Asset</h4>
        <div class="text-muted">
            {{ $asset->device_name }} ({{ $asset->type?->type_name }}) | Kode BMN: <strong>{{ $asset->bmn_code }}</strong>
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
    {{-- SPEC TERKINI --}}
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
                            <div class="spec-icon"><i class="bi bi-person"></i></div>
                            <div>
                                <div class="spec-label">Pemegang Asset</div>
                                <div class="spec-value fw-semibold">{{ $spec->owner_asset ?: '-' }}</div>
                            </div>
                        </div>

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
                                <div class="spec-value fw-semibold">{{ (int)$spec->ram }} GB</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-hdd-stack"></i></div>
                            <div>
                                <div class="spec-label">Storage</div>
                                <div class="spec-value fw-semibold">{{ (int)$spec->storage }} GB</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-nvidia"></i></div>
                            <div>
                                <div class="spec-label">GPU</div>
                                <div class="spec-value fw-semibold">{{ $asset->gpu ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-device-hdd"></i></div>
                            <div>
                                <div class="spec-label">Tipe RAM</div>
                                <div class="spec-value fw-semibold">{{ $asset->ram_type ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="spec-item">
                            <div class="spec-icon"><i class="bi bi-device-ssd"></i></div>
                            <div>
                                <div class="spec-label">Tipe Storage</div>
                                <div class="spec-value">
                                    @if(count($storageTypeBadges))
                                        @foreach($storageTypeBadges as $type)
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

    {{-- HASIL PENGECEKAN --}}
    <div class="col-12">
        <div class="card card-soft shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <div class="section-title d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-clipboard-check"></i> Hasil Pengecekan Terbaru
                        </div>
                        <div class="section-subtitle text-muted-sm">
                            Dibuat: <strong>{{ optional($report->created_at)->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- PRIORITIES --}}
                <div class="row g-3">
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
                                    <strong>{{ $fmtPrice($report->upgrade_ram_price) }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>

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
                                    <strong>{{ $fmtPrice($report->upgrade_storage_price) }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 border rounded-4 h-100">
                            <div class="text-muted-sm mb-4">Priority Level CPU</div>
                            <div class="d-flex align-items-center justify-content mb-4">
                                <span class="{{ $mCpu['badgeClass'] }} me-2">{{ $mCpu['value'] }}</span>
                                <span class="text-muted-sm"><strong>{{ $mCpu['label'] }}</strong> - {{ $mCpu['desc'] }}</span>
                            </div>
                            <div class="prio-desc">
                                <span class="text-muted-sm">Estimasi Harga Upgrade : <strong>-</strong></span>
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
                        <div class="rec-box">
                            <div class="rec-block-title">
                                <span class="badge text-bg-primary">Tindakan</span>
                            </div>
                            <div class="rec-block-content {{ $ramActionUi === '-' ? 'text-muted' : '' }}">{{ $ramActionUi }}</div>

                            <div class="rec-divider"></div>

                            <div class="rec-block-title">
                                <span class="badge text-bg-light border">Penjelasan</span>
                            </div>
                            <div class="rec-block-content text-muted">{{ $ramExplain }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-hdd-stack"></i> Rekomendasi Storage
                        </div>
                        <div class="rec-box">
                            <div class="rec-block-title">
                                <span class="badge text-bg-primary">Tindakan</span>
                            </div>
                            <div class="rec-block-content {{ $stoActionUi === '-' ? 'text-muted' : '' }}">{{ $stoActionUi }}</div>

                            <div class="rec-divider"></div>

                            <div class="rec-block-title">
                                <span class="badge text-bg-light border">Penjelasan</span>
                            </div>
                            <div class="rec-block-content text-muted">{{ $stoExplain }}</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-cpu"></i> Rekomendasi CPU
                        </div>
                        <div class="rec-box">
                            <div class="rec-block-title">
                                <span class="badge text-bg-primary">Tindakan</span>
                            </div>
                            <div class="rec-block-content {{ $cpuActionUi === '-' ? 'text-muted' : '' }}">{{ $cpuActionUi }}</div>

                            <div class="rec-divider"></div>

                            <div class="rec-block-title">
                                <span class="badge text-bg-light border">Penjelasan</span>
                            </div>
                            <div class="rec-block-content text-muted">{{ $cpuExplain }}</div>
                        </div>
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
                        $pickFirstBullet = function($txt) {
                            $t = trim((string) $txt);
                            if ($t === '' || $t === '-') return null;

                            // ambil baris pertama
                            $t = preg_replace("/\r\n|\r/", "\n", $t);
                            $lines = array_values(array_filter(array_map('trim', explode("\n", $t))));
                            $first = $lines[0] ?? $t;

                            // bersihkan bullet di depan
                            $first = ltrim($first, "• \t-");
                            return trim($first) ?: null;
                        };

                        $parts = array_filter([
                            $pickFirstBullet($row->recommendation_ram) ? 'RAM: '.$pickFirstBullet($row->recommendation_ram) : null,
                            $pickFirstBullet($row->recommendation_storage) ? 'Storage: '.$pickFirstBullet($row->recommendation_storage) : null,
                            $pickFirstBullet($row->recommendation_processor) ? 'CPU: '.$pickFirstBullet($row->recommendation_processor) : null,
                        ]);

                        $summary = count($parts) ? implode(' | ', $parts) : '-';
                    @endphp

                    <tr>
                        <td>{{ $history->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge rounded-pill text-bg-light border">RAM : {{ $row->prior_ram ?? '-' }}</span>
                            <span class="badge rounded-pill text-bg-light border">STORAGE : {{ $row->prior_storage ?? '-' }}</span>
                            <span class="badge rounded-pill text-bg-light border">CPU : {{ $row->prior_processor ?? '-' }}</span>
                        </td>
                        <td class="text-muted-sm">
                            <span title="{{ $summary }}">{{ \Illuminate\Support\Str::limit($summary, 140) }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2 justify-content-center">
                                @if((int)$row->id_report !== (int)$report->id_report)
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 js-delete"
                                        data-action="{{ route('admin.asset-checks.reports.destroy', [$asset->id_asset, $row->id_report]) }}"
                                        data-title="Hapus report ini?"
                                        data-message="Report pengecekan ini akan terhapus permanen.">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @else
                                    <span class="badge rounded-pill text-bg-primary fw-semibold">
                                        <i class="bi bi-star-fill me-1"></i> Terbaru
                                    </span>
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
