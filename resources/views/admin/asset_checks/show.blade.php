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

    .rec-block-content{ white-space:pre-line; color:#212529; line-height:1.45rem; }
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

    $storage_type = [];
    if ($spec?->is_hdd)  $storage_type[] = 'HDD';
    if ($spec?->is_ssd)  $storage_type[] = 'SSD';
    if ($spec?->is_nvme) $storage_type[] = 'NVMe';

    $isLatest = function($row) use ($report) {
        return (int)$row->id_report === (int)$report->id_report;
    };

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
        $p = (float) $price;
        if ($p <= 0) return '-';
        return 'Rp ' . number_format($p, 0, ',', '.');
    };

    $shouldShowPrice = function (?string $action): bool {
        if (!$action) return false;
        $t = strtolower(trim($action));
        if ($t === '' || $t === '-') return false;

        // RAM
        if (str_contains($t, 'tambahkan ram')) return true;

        // Storage
        if (str_contains($t, 'ganti jadi ssd')) return true;
        if (preg_match('/\b(x2|2x|kali\s*2)\b/i', $t)) return true;
        if (str_contains($t, 'upgrade storage')) return true;

        return false;
    };

    // ====== HELPER: normalize dash ======
    $valOrDash = function(?string $t): string {
        $t = trim((string) $t);
        return ($t === '' || $t === '-') ? '-' : $t;
    };

    /**
     * ✅ Support data lama yang masih gabung: ada "Action:" dan "Explanation:"
     * - Return: ['action' => '...', 'explanation' => '...']
     * - Kalau tidak ada marker, action = teks asli, explanation = null
     */
    $splitActionExplanation = function(?string $text): array {
        $raw = trim((string) $text);
        if ($raw === '' || $raw === '-') {
            return ['action' => '-', 'explanation' => null];
        }

        $raw = preg_replace("/\r\n|\r/", "\n", $raw);

        $posA = stripos($raw, 'action:');
        $posE = stripos($raw, 'explanation:');

        if ($posA === false && $posE === false) {
            return ['action' => $raw, 'explanation' => null];
        }

        $actionPart = '';
        $explainPart = '';

        if ($posE !== false) {
            $beforeE = substr($raw, 0, $posE);
            $afterE  = substr($raw, $posE);

            $beforeE = preg_replace('/^.*action:\s*/i', '', $beforeE);
            $actionPart = trim($beforeE);

            $afterE = preg_replace('/^explanation:\s*/i', '', $afterE);
            $explainPart = trim($afterE);
        } else {
            $actionPart = preg_replace('/^action:\s*/i', '', $raw);
            $actionPart = trim($actionPart);
        }

        return [
            'action' => ($actionPart === '' ? '-' : $actionPart),
            'explanation' => ($explainPart === '' ? null : $explainPart),
        ];
    };

    /**
     * Convert string ke bullet list ringan (kalau sudah ada bullet, biarkan).
     */
    $toBullets = function(?string $text): string {
        $t = trim((string)$text);
        if ($t === '' || $t === '-') return '-';

        // kalau sudah ada bullet "•" di awal, return apa adanya
        if (preg_match('/^\s*•\s+/u', $t)) return $t;

        $lines = preg_split("/\r\n|\n|\r/", $t) ?: [$t];
        $lines = array_map(fn($x) => trim((string)$x), $lines);
        $lines = array_values(array_filter($lines, fn($x) => $x !== '' && $x !== '-'));

        if (!count($lines)) return '-';

        return "• " . implode("\n• ", $lines);
    };

    /**
     * Extract baris action dari teks action (hapus bullet, dash, dsb).
     */
    $extractActionLines = function(?string $actionText): array {
        $t = trim((string) $actionText);
        if ($t === '' || $t === '-') return [];

        $lines = preg_split("/\r\n|\n|\r/", $t) ?: [];
        $lines = array_map(function($ln){
            $ln = trim((string)$ln);
            $ln = ltrim($ln, "• \t-");
            return trim($ln);
        }, $lines);

        return array_values(array_filter($lines, fn($ln) => $ln !== '' && $ln !== '-' && !preg_match('/^(action:|explanation:)$/i', $ln)));
    };

    /**
     * Ambil EXPLANATION dari DB on-the-fly
     * - Primary: match per action line dari report -> recommendations.action
     * - Fallback: gabungkan semua explanation pada category+priority_level
     * - Special rules storage kalau action-nya berasal dari rule (bukan DB)
     */
    $getExplanationFromDb = function(string $category, int $priority, ?string $actionText) use ($extractActionLines, $toBullets): string {
        if ($priority <= 0) return '-';

        // fetch rows sekali per kategori+priority
        $rows = Recommendation::where('category', $category)
            ->where('priority_level', $priority)
            ->orderBy('id_recommendation')
            ->get(['action', 'explanation']);

        $map = [];
        foreach ($rows as $r) {
            $a = trim((string)$r->action);
            $e = trim((string)$r->explanation);
            if ($a !== '' && $e !== '') {
                $map[$a] = $e;
            }
        }

        $lines = $extractActionLines($actionText);

        $exps = [];

        foreach ($lines as $ln) {
            // Special storage rule (kalau tidak ada di DB)
            if ($category === 'STORAGE') {
                if (mb_strtolower($ln) === 'ganti jadi ssd') {
                    $exps[] = 'HDD cenderung lebih lambat dibanding SSD. Upgrade ke SSD akan meningkatkan kecepatan booting, membuka aplikasi, dan respons sistem secara signifikan.';
                    continue;
                }
                if (str_starts_with(mb_strtolower($ln), mb_strtolower('Kapasitas Storage sudah maksimal'))) {
                    $exps[] = 'Kapasitas storage sudah menyentuh batas maksimal. Solusi terbaik adalah menghapus aplikasi/file yang tidak diperlukan, memindahkan data ke penyimpanan lain, atau menggunakan penyimpanan eksternal/cloud.';
                    continue;
                }
            }

            // Match exact
            if (isset($map[$ln])) {
                $exps[] = $map[$ln];
            }
        }

        // Kalau match per-action kosong -> fallback semua explanation by priority
        if (!count($exps)) {
            $fallback = $rows->pluck('explanation')
                ->map(fn($x) => trim((string)$x))
                ->filter(fn($x) => $x !== '')
                ->values()
                ->all();

            return count($fallback) ? $toBullets(implode("\n", $fallback)) : '-';
        }

        // unique
        $uniq = [];
        foreach ($exps as $e) {
            $k = mb_strtolower(trim($e));
            if (!isset($uniq[$k])) $uniq[$k] = $e;
        }

        return $toBullets(implode("\n", array_values($uniq)));
    };

    // ===== PRIORITY =====
    $mRam = $prioMeta((int)($report->prior_ram ?? 0));
    $mSto = $prioMeta((int)($report->prior_storage ?? 0));
    $mCpu = $prioMeta((int)($report->prior_processor ?? 0));

    // ===== SPLIT (kompatibel data lama) =====
    $ramSplit = $splitActionExplanation($report->recommendation_ram ?? null);
    $stoSplit = $splitActionExplanation($report->recommendation_storage ?? null);
    $cpuSplit = $splitActionExplanation($report->recommendation_processor ?? null);

    $ramAction = $valOrDash($ramSplit['action'] ?? '-');
    $stoAction = $valOrDash($stoSplit['action'] ?? '-');
    $cpuAction = $valOrDash($cpuSplit['action'] ?? '-');

    // Explanation:
    // - kalau data lama sudah punya explanation di report, pakai itu
    // - kalau tidak ada, query DB (tanpa simpan ke report)
    $ramExplain = $valOrDash($ramSplit['explanation'] ?? null);
    if ($ramExplain === '-') $ramExplain = $getExplanationFromDb('RAM', (int)($report->prior_ram ?? 0), $ramAction);

    $stoExplain = $valOrDash($stoSplit['explanation'] ?? null);
    if ($stoExplain === '-') $stoExplain = $getExplanationFromDb('STORAGE', (int)($report->prior_storage ?? 0), $stoAction);

    $cpuExplain = $valOrDash($cpuSplit['explanation'] ?? null);
    if ($cpuExplain === '-') $cpuExplain = $getExplanationFromDb('CPU', (int)($report->prior_processor ?? 0), $cpuAction);

    // Kalau action-nya masih non-bullet (data lama), bikin bullet biar rapi
    $ramActionUi = $toBullets($ramAction);
    $stoActionUi = $toBullets($stoAction);
    $cpuActionUi = $toBullets($cpuAction);
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
                                    @if ($report->upgrade_ram_price > 0)
                                        Rp {{ number_format($report->upgrade_ram_price, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
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
                                    @if ($report->upgrade_ram_price > 0)
                                        Rp {{ number_format($report->upgrade_storage_price, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
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
                                <span class="text-muted-sm">Estimasi Harga Upgrade : -</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RECOMMENDATIONS: ACTION + EXPLANATION (TERPISAH) --}}
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
                        $pick = function($txt) {
                            if (!$txt) return null;
                            $t = trim((string) $txt);
                            if ($t === '-' || $t === '') return null;

                            $t = preg_replace("/\r\n|\r/", "\n", $t);
                            $lines = preg_split("/\n/", $t) ?: [];
                            $lines = array_map('trim', $lines);

                            // skip label "Action:"/"Explanation:" kalau data lama
                            $lines = array_values(array_filter($lines, function($ln){
                                $ln2 = strtolower(trim($ln));
                                if ($ln2 === 'action:' || $ln2 === 'explanation:') return false;
                                return $ln !== '';
                            }));

                            $first = $lines[0] ?? $t;

                            $first = str_replace('•', '', $first);
                            $first = preg_replace('/\s+/', ' ', $first);
                            return trim($first);
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
                        <td class="fw-semibold">{{ optional($row->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge rounded-pill text-bg-light border">RAM : {{ $pr }}</span>
                            <span class="badge rounded-pill text-bg-light border">STORAGE : {{ $ps }}</span>
                            <span class="badge rounded-pill text-bg-light border">CPU : {{ $pc }}</span>
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
