<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\LossBahan;
use App\Models\User;
use App\Exports\Operasional\KontribusiBulanLaluExport;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Operasional\KurangSetoran;
use Maatwebsite\Excel\Facades\Excel;

class KontribusiBulanLalu extends Component
{
    public array $tokosUser = [];

    public $periodeAwal;
    public $periodeAkhir;

    public $bulanLaluAwal;
    public $bulanLaluAkhir;

    // ✅ hanya simpan key, hasil besar masuk cache (anti corrupt hydrate)
    public ?string $resultKey = null;

    // Loss modal
    public bool $showLossModal = false;
    public string $lossModalOutlet = '';
    public string $lossModalTanggal = '';
    public string $lossModalTanggalAkhir = '';
    public array $lossModalItems = [];
    public int $lossModalTotal = 0;
    public array $lossBarangListMap = [];

    public function mount(array $tokosUser = [], $periodeAwal = null, $periodeAkhir = null)
    {
        $this->tokosUser = $tokosUser;
        $this->periodeAwal  = $periodeAwal ?? now()->toDateString();
        $this->periodeAkhir = $periodeAkhir ?? now()->toDateString();
        $this->syncBulanLaluRange();
    }

    private function syncBulanLaluRange(): void
    {
        $this->bulanLaluAwal  = Carbon::parse($this->periodeAwal)->subMonthNoOverflow()->toDateString();
        $this->bulanLaluAkhir = Carbon::parse($this->periodeAkhir)->subMonthNoOverflow()->toDateString();
    }

    public function render()
    {
        $data = $this->resultKey ? Cache::get($this->resultKey) : null;

        return view('livewire.operasional.kontribusi-bulan-lalu', [
            'rowsBulanLaluView'    => $data['rowsBulanLalu'] ?? [],
            'grandTotalsView'      => $data['grandTotals'] ?? [],
            'sumNetoBulanLaluView' => $data['sumNetoBulanLalu'] ?? 0,
            'bulanLaluAwal'        => $this->bulanLaluAwal,
            'bulanLaluAkhir'       => $this->bulanLaluAkhir,
            'periodeAwal'          => $this->periodeAwal,
            'periodeAkhir'         => $this->periodeAkhir,
        ]);
    }

    public function resetBulanLalu()
    {
        $this->resultKey = null;
        $this->periodeAwal  = now()->toDateString();
        $this->periodeAkhir = now()->toDateString();
        $this->syncBulanLaluRange();
    }

    // =========================
    // ACTION
    // =========================
    public function loadBulanLalu()
    {
        $this->validate([
            'periodeAwal'  => 'required|date',
            'periodeAkhir' => 'required|date|after_or_equal:periodeAwal',
        ]);

        $this->syncBulanLaluRange();

        $start = Carbon::parse($this->periodeAwal)->toDateString();
        $end   = Carbon::parse($this->periodeAkhir)->toDateString();

        // id toko dari parent
        $tokoIdsRaw = collect($this->tokosUser)
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tokoIdsRaw)) {
            $this->resultKey = null;
            return;
        }

        // toko aktif saja
        $tokoLocal = MasterToko::query()
            ->with(['area.wilayah'])
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', '1')
            ->get()
            ->keyBy('id');

        $tokoIds = $tokoLocal->keys()->map(fn($v) => (int)$v)->values()->all();
        if (empty($tokoIds)) {
            $this->resultKey = null;
            return;
        }

        // PIC AREA (optional)
        $picAreaByAreaId = User::query()
            ->select('name', 'area_id')
            ->whereNotNull('area_id')
            ->whereRaw("LOWER(TRIM(role)) = 'area'")
            ->orderBy('name')
            ->get()
            ->groupBy('area_id')
            ->map(fn($g) => $g->pluck('name')->filter()->implode(', '))
            ->toArray();



        // =========================
        // 1) Ambil latest row per (toko,tgl) untuk jenis BY BULAN LALU
        // =========================
        $rowsSnap = KontribusiHarianJobRow::query()
            ->select(['id','job_id','tanggal','jenis','payload','loss_bahan'])
            ->whereBetween('tanggal', [$start, $end])
            ->where('jenis', 'BY BULAN LALU')
            ->whereHas('job', fn($q) => $q->whereIn('toko_id', $tokoIds)->where('status', 'ok'))
            ->with(['job:id,toko_id'])
            ->orderBy('tanggal')
            ->orderByDesc('id') // penting: pick terbaru per toko+tgl
            ->get();

        $picked = []; // [tokoId|tgl => payload array]
        foreach ($rowsSnap as $r) {
            $tokoId = (int) ($r->job?->toko_id ?? 0);
            $tgl    = (string) ($r->tanggal ?? '');
            if ($tokoId <= 0 || $tgl === '') continue;

            $k = $tokoId.'|'.$tgl;
            if (isset($picked[$k])) continue;

            $p = $r->payload;

            // payload bisa json string / object / array
            if (is_string($p)) $p = json_decode($p, true) ?: [];
            elseif (is_object($p)) $p = (array)$p;
            elseif (!is_array($p)) $p = [];

            // kalau payload nested
            if (isset($p['by_bulan_lalu']) && is_array($p['by_bulan_lalu'])) $p = $p['by_bulan_lalu'];

            $picked[$k] = $p;
        }

        // =========================
        // 2) Agregasi per toko (SUM) + simpan basis (hrg & baseline)
        // =========================
        $lossByToko = []; // [tokoId => sum of loss_bahan]
        foreach ($rowsSnap as $r) {
            $tokoId = (int) ($r->job?->toko_id ?? 0);
            if ($tokoId <= 0) continue;
            $lossByToko[$tokoId] = ($lossByToko[$tokoId] ?? 0) + (int)($r->loss_bahan ?? 0);
        }

        $agg = []; // [tokoId => sums]
        foreach ($picked as $k => $p) {
            [$tokoIdStr] = explode('|', $k, 2);
            $tokoId = (int)$tokoIdStr;

            $hrg = $this->getHrgFromPayload($p); // ✅ FIX utama (jangan cuma hrg/sales_rp)
            $selisihRp = $this->toInt($p['selisih_rp'] ?? 0);

            // baseline = hrgNow - selisihRp
            $baseline = $hrg - $selisihRp;

            $agg[$tokoId] ??= [
                'hrg' => 0,
                'baseline' => 0,

                'selisih_rp' => 0,
                'kontribusi_rp' => 0,

                'disc_rp' => 0,
                'retur_rp' => 0,
                'gas_rp' => 0,
                'telur_rp' => 0,

                'total_kontribusi' => 0,
            ];

            $agg[$tokoId]['hrg']      += $hrg;
            $agg[$tokoId]['baseline'] += max(0, (int)$baseline);

            $agg[$tokoId]['selisih_rp']    += $selisihRp;
            $agg[$tokoId]['kontribusi_rp'] += $this->toInt($p['kontribusi_rp'] ?? ($p['kontribusi'] ?? 0));

            $agg[$tokoId]['disc_rp']  += $this->toInt($p['disc_rp']  ?? ($p['sc_manual_rp'] ?? 0));
            $agg[$tokoId]['retur_rp'] += $this->toInt($p['retur_rp'] ?? 0);
            $agg[$tokoId]['gas_rp']   += $this->toInt($p['gas_rp']   ?? 0);
            $agg[$tokoId]['telur_rp'] += $this->toInt($p['telur_rp'] ?? 0);

            $agg[$tokoId]['total_kontribusi'] += $this->toInt($p['total_kontribusi'] ?? ($p['total'] ?? 0));
        }

        // =========================
        // 3) Build rows view (persen = weighted pakai base)
        //    - jika base 0 => persen NULL (biar Blade tampil "-")
        // =========================
        $pct = function(int $rp, int $base): ?float {
            if ($base <= 0) return null;
            return round(($rp / $base) * 100, 2);
        };

        // Aggregate kurang setoran per toko in period
        $kurangSetoranByToko = KurangSetoran::query()
            ->whereIn('toko_id', $tokoIds)
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('toko_id, SUM(nominal) as total')
            ->groupBy('toko_id')
            ->pluck('total', 'toko_id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        $outRows = [];
        foreach ($tokoIds as $tokoId) {
            $tokoDb = $tokoLocal[$tokoId] ?? null;
            if (!$tokoDb) continue;

            $a = $agg[$tokoId] ?? null;
            if (!$a) continue;

            $hrg      = (int)$a['hrg'];
            $baseline = (int)$a['baseline'];

            $loss = (int) ($lossByToko[$tokoId] ?? 0);
            $kurang = (int) ($kurangSetoranByToko[$tokoId] ?? 0);

            $areaId = (int)($tokoDb->area_id ?? 0);
            $areaLabel = $tokoDb->area?->nama_area ?: '-';
            $areaPic = $areaId > 0 ? ($picAreaByAreaId[$areaId] ?? '') : '';
            $wilayahLabel = $tokoDb->area?->wilayah?->nama_wilayah ?: '-';

            $selisihRp = (int)$a['selisih_rp'];

            $outRows[] = [
                'wilayah_label' => $wilayahLabel,
                'area_label'    => $areaLabel,
                'area_pic'      => $areaPic,
                'outlet'        => (string)($tokoDb->nmtoko ?? '-'),
                'toko_id'       => $tokoId,

                // base (opsional untuk debug / grand total)
                'hrg'           => $hrg,
                'baseline'      => $baseline,

                // selisih% ikut rumus Harian (selisih / baseline)
                'selisih_persen' => $baseline > 0 ? round(($selisihRp / $baseline) * 100, 2) : null,
                'selisih_rp'     => $selisihRp,
                'kontribusi_rp'  => (int)$a['kontribusi_rp'],

                // % lain ikut Harian: rp / hrgNow
                'sc_manual_persen' => $pct((int)$a['disc_rp'], $hrg),
                'sc_manual_rp'     => (int)$a['disc_rp'],

                'retur_persen' => $pct((int)$a['retur_rp'], $hrg),
                'retur_rp'     => (int)$a['retur_rp'],

                'gas_persen' => $pct((int)$a['gas_rp'], $hrg),
                'gas_rp'     => (int)$a['gas_rp'],

                'telur_persen' => $pct((int)$a['telur_rp'], $hrg),
                'telur_rp'     => (int)$a['telur_rp'],

                'loss_bahan' => $loss,
                'kurang_setoran' => $kurang,

                // total kontribusi di laporan = payload_total - loss - kurang_setoran
                'total_kontribusi' => (int)$a['total_kontribusi'] - $loss - $kurang,
            ];
        }

        $outRows = collect($outRows)
            ->sortBy([['wilayah_label','asc'],['area_label','asc'],['outlet','asc']])
            ->values()
            ->all();

        // =========================
        // 4) GRAND TOTAL (WEIGHTED)
        // =========================
        $sumHrg      = (int) collect($outRows)->sum('hrg');
        $sumBaseline = (int) collect($outRows)->sum('baseline');

        $sumSelisih  = (int) collect($outRows)->sum('selisih_rp');
        $sumKontrib  = (int) collect($outRows)->sum('kontribusi_rp');

        $sumDisc     = (int) collect($outRows)->sum('sc_manual_rp');
        $sumRetur    = (int) collect($outRows)->sum('retur_rp');
        $sumGas      = (int) collect($outRows)->sum('gas_rp');
        $sumTelur    = (int) collect($outRows)->sum('telur_rp');

        $sumLoss     = (int) collect($outRows)->sum('loss_bahan');
        $sumKurang   = (int) collect($outRows)->sum('kurang_setoran');
        $sumTotal    = (int) collect($outRows)->sum('total_kontribusi');

        $grandTotals = [
            'selisih_persen' => $sumBaseline > 0 ? round(($sumSelisih / $sumBaseline) * 100, 2) : null,
            'selisih_rp'     => $sumSelisih,
            'kontribusi_rp'  => $sumKontrib,

            'sc_manual_persen' => $pct($sumDisc, $sumHrg),
            'sc_manual_rp'     => $sumDisc,

            'retur_persen' => $pct($sumRetur, $sumHrg),
            'retur_rp'     => $sumRetur,

            'gas_persen' => $pct($sumGas, $sumHrg),
            'gas_rp'     => $sumGas,

            'telur_persen' => $pct($sumTelur, $sumHrg),
            'telur_rp'     => $sumTelur,

            'loss_bahan' => $sumLoss,
            'kurang_setoran' => $sumKurang,
            'total_kontribusi' => $sumTotal,
        ];

        // Build loss barang data untuk modal
        $this->lossBarangListMap = $this->buildLossBarangData($tokoIds, $this->bulanLaluAwal, $this->bulanLaluAkhir);

        // simpan cache hasil besar
        $key = $this->makeResultKey($start, $end, $tokoIds);

        Cache::put($key, [
            'rowsBulanLalu'     => $outRows,
            'grandTotals'       => $grandTotals,
            'sumNetoBulanLalu'  => 0,
        ], now()->addMinutes(10));

        $this->resultKey = $key;
    }

    // =========================
    // Helpers: cache key
    // =========================
    private function makeResultKey(string $start, string $end, array $tokoIds): string
    {
        sort($tokoIds);
        $userId = (int) Auth::id();

        return 'kontribusi_bulan_lalu:result:v2:' . md5($userId . '|' . $start . '|' . $end . '|' . implode(',', $tokoIds));
    }

    // =========================
    // Helpers: parsing angka
    // =========================
    private function toInt($v): int
    {
        if ($v === null) return 0;

        if (is_string($v)) {
            $v = trim($v);
            if ($v === '' || $v === '-') return 0;

            $v = str_replace(['Rp', 'rp', ' '], '', $v);

            // "1.234.567" => "1234567"
            // "1.234,56"  => "1234.56"
            if (str_contains($v, ',') && str_contains($v, '.')) {
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            } else {
                $v = str_replace('.', '', $v);
                $v = str_replace(',', '.', $v);
            }
        }

        return (int) round((float) $v);
    }

    private function toFloat($v): ?float
    {
        if ($v === null) return null;

        if (is_string($v)) {
            $v = trim(str_replace('%', '', $v));
            if ($v === '' || $v === '-') return null;
            $v = str_replace(['.', ','], ['', '.'], $v);
        }

        return is_numeric($v) ? (float) $v : null;
    }

    private function getHrgFromPayload(array $p): int
    {
        // prioritas basis penjualan "sekarang"
        $keys = [
            'hrg',
            'sales_rp',
            'penjualan_rp',
            'omzet_rp',
            'neto_rp',
            'neto',
            'sales',
            'penjualan',
            'total_sales_rp',
            'total_penjualan_rp',
        ];

        foreach ($keys as $k) {
            if (array_key_exists($k, $p)) {
                $n = $this->toInt($p[$k]);
                if ($n !== 0) return $n;
            }
        }

        // fallback: estimasi dari selisih_rp & selisih_persen (kalau ada)
        $sel = $this->toInt($p['selisih_rp'] ?? 0);
        $pct = $this->toFloat($p['selisih_persen'] ?? ($p['selisih_pct'] ?? null));

        if ($sel !== 0 && $pct !== null && abs($pct) > 0.00001) {
            // 100*sel = pct*(hrg - sel)  =>  hrg = (100+pct)*sel/pct
            $hrg = ((100.0 + $pct) * (float)$sel) / $pct;
            return (int) round($hrg);
        }

        return 0;
    }

    // =========================
    // Helpers: loss bahan
    // =========================
    // Removed fetchLossBahanByToko - loss_bahan now aggregated from KontribusiHarianJobRow snapshot above

    public function openLossModal(string $outlet, ?string $tanggalAwal = null, ?string $tanggalAkhir = null, ?int $nominal = null, ?int $tokoId = null): void
    {
        // Search in current periode (user's selected range)
        $dateStart = $tanggalAwal ?? $this->periodeAwal;
        $dateEnd = $tanggalAkhir ?? $this->periodeAkhir;
        
        // Always fetch from database for fresh data
        $items = $this->fetchLossItemsForModalRange($outlet, $dateStart, $dateEnd, $tokoId);

        $outletLabel = $outlet;
        if (!empty($items)) {
            $first = $items[0];
            if (!empty($first['outlet_label'])) {
                $outletLabel = (string) $first['outlet_label'];
            }
        }

        $this->lossModalOutlet = $outletLabel;
        $this->lossModalTanggal = $dateStart ?? '';
        $this->lossModalTanggalAkhir = $dateEnd ?? '';
        $this->lossModalItems = $items;
        $this->lossModalTotal = (int) ($nominal ?? 0);
        $this->showLossModal = true;
    }

    public function closeLossModal(): void
    {
        $this->showLossModal = false;
        $this->lossModalOutlet = '';
        $this->lossModalTanggal = '';
        $this->lossModalTanggalAkhir = '';
        $this->lossModalItems = [];
        $this->lossModalTotal = 0;
    }

    private function fetchLossItemsForModalRange(string $outletKey, ?string $dateAwal, ?string $dateAkhir, ?int $tokoId = null): array
    {
        if (!$dateAwal) return [];

        // Prioritaskan pakai toko_id jika tersedia
        if ($tokoId) {
            $toko = MasterToko::query()
                ->select('id','nmtoko')
                ->where('id', $tokoId)
                ->first();
        } else {
            $outletKeyUpper = mb_strtoupper(trim($outletKey));
            
            // First, try exact match on outlet name
            $toko = MasterToko::query()
                ->select('id','nmtoko')
                ->whereRaw('UPPER(TRIM(nmtoko)) = ?', [$outletKeyUpper])
                ->first();

            // Fallback: LIKE search
            if (!$toko) {
                $toko = MasterToko::query()
                    ->select('id','nmtoko')
                    ->whereRaw('UPPER(TRIM(nmtoko)) LIKE ?', ['%' . $outletKeyUpper . '%'])
                    ->first();
            }

            // Fallback: search in all user's tokos
            if (!$toko && !empty($this->tokosUser)) {
                $tokoIds = collect($this->tokosUser)->pluck('id')->map(fn($v) => (int)$v)->filter()->unique()->values()->all();
                $toko = MasterToko::query()
                    ->select('id','nmtoko')
                    ->whereIn('id', $tokoIds)
                    ->whereRaw('UPPER(TRIM(nmtoko)) LIKE ?', ['%' . $outletKeyUpper . '%'])
                    ->first();
            }
        }

        if (!$toko) return [];

        $rows = LossBahan::query()
            ->where('toko_id', (int)$toko->id)
            ->whereDate('tanggal', '>=', $dateAwal)
            ->whereDate('tanggal', '<=', $dateAkhir ?? $dateAwal)
            ->with(['barang'])
            ->get();

        if ($rows->isEmpty()) return [];

        // Group by tanggal, then by nama barang (aggregate qty & nominal)
        $tmp = [];
        foreach ($rows as $lr) {
            $tgl = Carbon::parse($lr->tanggal)->toDateString();
            $nmBarang = $lr->barang?->nmbarang ?: ($lr->keterangan ?: ('Barang ID ' . ($lr->barang_id ?? '-')));
            $satuan = $lr->barang?->sat1;
            
            $tmp[$tgl][$nmBarang]['barang'] = $nmBarang;
            $tmp[$tgl][$nmBarang]['nominal'] = ($tmp[$tgl][$nmBarang]['nominal'] ?? 0) + (int)($lr->nominal ?? 0);
            $tmp[$tgl][$nmBarang]['qty'] = ($tmp[$tgl][$nmBarang]['qty'] ?? 0) + (int)($lr->qty ?? 0);
            if (!empty($satuan)) {
                $tmp[$tgl][$nmBarang]['satuan'] = (string) $satuan;
            }
            $tmp[$tgl][$nmBarang]['outlet_label'] = $toko->nmtoko;
        }

        // Convert to flat list sorted by tanggal then barang name
        $result = [];
        $dates = array_keys($tmp);
        sort($dates);
        
        foreach ($dates as $tgl) {
            $barangSet = $tmp[$tgl];
            $list = array_values($barangSet);
            usort($list, function ($a, $b) {
                return strnatcasecmp($a['barang'] ?? '', $b['barang'] ?? '');
            });
            foreach ($list as $item) {
                $item['tanggal'] = $tgl;
                $result[] = $item;
            }
        }

        return $result;
    }

    private function buildLossBarangData(array $tokoIds, string $startDate, string $endDate): array
    {
        $result = [];

        $lossRows = LossBahan::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('toko_id', $tokoIds)
            ->with(['toko', 'barang'])
            ->get();

        if ($lossRows->isEmpty()) {
            return $result;
        }

        $tmp = [];
        foreach ($lossRows as $lr) {
            $outletLabel = $lr->toko?->nmtoko ?? 'Outlet ?';
            $outletKey   = mb_strtoupper(trim($outletLabel));
            $tgl = Carbon::parse($lr->tanggal)->toDateString();
            $nmBarang = $lr->barang?->nmbarang;
            $satuan = $lr->barang?->sat1;
            if (!$nmBarang) {
                $nmBarang = $lr->keterangan ?: ('Barang ID ' . ($lr->barang_id ?? '-'));
            }

            $tmp[$outletKey][$tgl][$nmBarang]['barang'] = $nmBarang;
            $tmp[$outletKey][$tgl][$nmBarang]['nominal'] = ($tmp[$outletKey][$tgl][$nmBarang]['nominal'] ?? 0) + (int)($lr->nominal ?? 0);
            $tmp[$outletKey][$tgl][$nmBarang]['qty'] = ($tmp[$outletKey][$tgl][$nmBarang]['qty'] ?? 0) + (int)($lr->qty ?? 0);
            $tmp[$outletKey][$tgl][$nmBarang]['outlet_label'] = $outletLabel;
            if (!empty($satuan)) {
                $tmp[$outletKey][$tgl][$nmBarang]['satuan'] = (string) $satuan;
            }
        }

        foreach ($tmp as $outletKey => $byDate) {
            ksort($byDate);

            foreach ($byDate as $tgl => $barangSet) {
                $list = array_values($barangSet);
                usort($list, function ($a, $b) {
                    return strnatcasecmp($a['barang'] ?? '', $b['barang'] ?? '');
                });
                $result[$outletKey][$tgl] = $list;
            }
        }

        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);
        return $result;
    }

    private function aggregateLossRange(array $byDateMap): array
    {
        $agg = [];
        
        foreach ($byDateMap as $tgl => $barangList) {
            foreach ($barangList as $item) {
                $nama = $item['barang'] ?? '-';
                $agg[$nama]['barang'] = $nama;
                $agg[$nama]['nominal'] = ($agg[$nama]['nominal'] ?? 0) + (int)($item['nominal'] ?? 0);
                $agg[$nama]['qty'] = ($agg[$nama]['qty'] ?? 0) + (int)($item['qty'] ?? 0);
                if (!empty($item['satuan'])) {
                    $agg[$nama]['satuan'] = (string)$item['satuan'];
                }
                $agg[$nama]['outlet_label'] = $item['outlet_label'] ?? '-';
            }
        }

        $list = array_values($agg);
        usort($list, function ($a, $b) {
            return strnatcasecmp($a['barang'] ?? '', $b['barang'] ?? '');
        });
        return $list;
    }

    // =========================
    // Export
    // =========================
    public function loadAndDownload()
    {
        // Validate date range
        $this->validate([
            'periodeAwal'  => 'required|date',
            'periodeAkhir' => 'required|date|after_or_equal:periodeAwal',
        ]);

        $this->syncBulanLaluRange();

        $start = Carbon::parse($this->periodeAwal)->toDateString();
        $end   = Carbon::parse($this->periodeAkhir)->toDateString();

        // id toko dari parent
        $tokoIdsRaw = collect($this->tokosUser)
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tokoIdsRaw)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Data Tidak Ditemukan',
                'text' => 'Tidak ada toko yang tersedia.',
            ]);
            return;
        }

        // toko aktif saja
        $tokoLocal = MasterToko::query()
            ->with(['area.wilayah'])
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', '1')
            ->get()
            ->keyBy('id');

        $tokoIds = $tokoLocal->keys()->map(fn($v) => (int)$v)->values()->all();
        if (empty($tokoIds)) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Data Tidak Ditemukan',
                'text' => 'Tidak ada toko aktif dalam periode ini.',
            ]);
            return;
        }

        // PIC AREA (optional)
        $picAreaByAreaId = User::query()
            ->select('name', 'area_id')
            ->whereNotNull('area_id')
            ->whereRaw("LOWER(TRIM(role)) = 'area'")
            ->orderBy('name')
            ->get()
            ->groupBy('area_id')
            ->map(fn($g) => $g->pluck('name')->filter()->implode(', '))
            ->toArray();

        // =========================
        // 1) Ambil latest row per (toko,tgl) untuk jenis BY BULAN LALU
        // =========================
        $rowsSnap = KontribusiHarianJobRow::query()
            ->select(['id','job_id','tanggal','jenis','payload','loss_bahan'])
            ->whereBetween('tanggal', [$start, $end])
            ->where('jenis', 'BY BULAN LALU')
            ->whereHas('job', fn($q) => $q->whereIn('toko_id', $tokoIds)->where('status', 'ok'))
            ->with(['job:id,toko_id'])
            ->orderBy('tanggal')
            ->orderByDesc('id')
            ->get();

        $picked = [];
        foreach ($rowsSnap as $r) {
            $tokoId = (int) ($r->job?->toko_id ?? 0);
            $tgl    = (string) ($r->tanggal ?? '');
            if ($tokoId <= 0 || $tgl === '') continue;

            $k = $tokoId.'|'.$tgl;
            if (isset($picked[$k])) continue;

            $p = $r->payload;
            if (is_string($p)) $p = json_decode($p, true) ?: [];
            elseif (is_object($p)) $p = (array)$p;
            elseif (!is_array($p)) $p = [];
            if (isset($p['by_bulan_lalu']) && is_array($p['by_bulan_lalu'])) $p = $p['by_bulan_lalu'];

            $picked[$k] = $p;
        }

        $lossByToko = [];
        foreach ($rowsSnap as $r) {
            $tokoId = (int) ($r->job?->toko_id ?? 0);
            if ($tokoId <= 0) continue;
            $lossByToko[$tokoId] = ($lossByToko[$tokoId] ?? 0) + (int)($r->loss_bahan ?? 0);
        }

        $agg = [];
        foreach ($picked as $k => $p) {
            [$tokoIdStr] = explode('|', $k, 2);
            $tokoId = (int)$tokoIdStr;

            $hrg = $this->getHrgFromPayload($p);
            $selisihRp = $this->toInt($p['selisih_rp'] ?? 0);
            $baseline = $hrg - $selisihRp;

            $agg[$tokoId] ??= [
                'hrg' => 0,
                'baseline' => 0,
                'selisih_rp' => 0,
                'kontribusi_rp' => 0,
                'disc_rp' => 0,
                'retur_rp' => 0,
                'gas_rp' => 0,
                'telur_rp' => 0,
                'total_kontribusi' => 0,
            ];

            $agg[$tokoId]['hrg']      += $hrg;
            $agg[$tokoId]['baseline'] += max(0, (int)$baseline);
            $agg[$tokoId]['selisih_rp']    += $selisihRp;
            $agg[$tokoId]['kontribusi_rp'] += $this->toInt($p['kontribusi_rp'] ?? ($p['kontribusi'] ?? 0));
            $agg[$tokoId]['disc_rp']  += $this->toInt($p['disc_rp']  ?? ($p['sc_manual_rp'] ?? 0));
            $agg[$tokoId]['retur_rp'] += $this->toInt($p['retur_rp'] ?? 0);
            $agg[$tokoId]['gas_rp']   += $this->toInt($p['gas_rp']   ?? 0);
            $agg[$tokoId]['telur_rp'] += $this->toInt($p['telur_rp'] ?? 0);
            $agg[$tokoId]['total_kontribusi'] += $this->toInt($p['total_kontribusi'] ?? ($p['total'] ?? 0));
        }

        $pct = function(int $rp, int $base): ?float {
            if ($base <= 0) return null;
            return round(($rp / $base) * 100, 2);
        };

        $kurangSetoranByToko = KurangSetoran::query()
            ->whereIn('toko_id', $tokoIds)
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('toko_id, SUM(nominal) as total')
            ->groupBy('toko_id')
            ->pluck('total', 'toko_id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        $outRows = [];
        foreach ($tokoIds as $tokoId) {
            $tokoDb = $tokoLocal[$tokoId] ?? null;
            if (!$tokoDb) continue;

            $a = $agg[$tokoId] ?? null;
            if (!$a) continue;

            $hrg      = (int)$a['hrg'];
            $baseline = (int)$a['baseline'];
            $loss = (int) ($lossByToko[$tokoId] ?? 0);
            $kurang = (int) ($kurangSetoranByToko[$tokoId] ?? 0);

            $areaId = (int)($tokoDb->area_id ?? 0);
            $areaLabel = $tokoDb->area?->nama_area ?: '-';
            $areaPic = $areaId > 0 ? ($picAreaByAreaId[$areaId] ?? '') : '';
            $wilayahLabel = $tokoDb->area?->wilayah?->nama_wilayah ?: '-';

            $selisihRp = (int)$a['selisih_rp'];

            $outRows[] = [
                'wilayah_label' => $wilayahLabel,
                'area_label'    => $areaLabel,
                'area_pic'      => $areaPic,
                'outlet'        => (string)($tokoDb->nmtoko ?? '-'),
                'toko_id'       => $tokoId,
                'hrg'           => $hrg,
                'baseline'      => $baseline,
                'selisih_persen' => $baseline > 0 ? round(($selisihRp / $baseline) * 100, 2) : null,
                'selisih_rp'     => $selisihRp,
                'kontribusi_rp'  => (int)$a['kontribusi_rp'],
                'sc_manual_persen' => $pct((int)$a['disc_rp'], $hrg),
                'sc_manual_rp'     => (int)$a['disc_rp'],
                'retur_persen' => $pct((int)$a['retur_rp'], $hrg),
                'retur_rp'     => (int)$a['retur_rp'],
                'gas_persen' => $pct((int)$a['gas_rp'], $hrg),
                'gas_rp'     => (int)$a['gas_rp'],
                'telur_persen' => $pct((int)$a['telur_rp'], $hrg),
                'telur_rp'     => (int)$a['telur_rp'],
                'loss_bahan' => $loss,
                'kurang_setoran' => $kurang,
                'total_kontribusi' => (int)$a['total_kontribusi'] - $loss - $kurang,
            ];
        }

        $outRows = collect($outRows)
            ->sortBy([['wilayah_label','asc'],['area_label','asc'],['outlet','asc']])
            ->values()
            ->all();

        // =========================
        // 4) GRAND TOTAL
        // =========================
        $sumHrg      = (int) collect($outRows)->sum('hrg');
        $sumBaseline = (int) collect($outRows)->sum('baseline');
        $sumSelisih  = (int) collect($outRows)->sum('selisih_rp');
        $sumKontrib  = (int) collect($outRows)->sum('kontribusi_rp');
        $sumDisc     = (int) collect($outRows)->sum('sc_manual_rp');
        $sumRetur    = (int) collect($outRows)->sum('retur_rp');
        $sumGas      = (int) collect($outRows)->sum('gas_rp');
        $sumTelur    = (int) collect($outRows)->sum('telur_rp');
        $sumLoss     = (int) collect($outRows)->sum('loss_bahan');
        $sumKurang   = (int) collect($outRows)->sum('kurang_setoran');
        $sumTotal    = (int) collect($outRows)->sum('total_kontribusi');

        $grandTotals = [
            'selisih_persen' => $sumBaseline > 0 ? round(($sumSelisih / $sumBaseline) * 100, 2) : null,
            'selisih_rp'     => $sumSelisih,
            'kontribusi_rp'  => $sumKontrib,
            'sc_manual_persen' => $pct($sumDisc, $sumHrg),
            'sc_manual_rp'     => $sumDisc,
            'retur_persen' => $pct($sumRetur, $sumHrg),
            'retur_rp'     => $sumRetur,
            'gas_persen' => $pct($sumGas, $sumHrg),
            'gas_rp'     => $sumGas,
            'telur_persen' => $pct($sumTelur, $sumHrg),
            'telur_rp'     => $sumTelur,
            'loss_bahan' => $sumLoss,
            'kurang_setoran' => $sumKurang,
            'total_kontribusi' => $sumTotal,
        ];

        // Create export instance dan langsung download
        $export = new KontribusiBulanLaluExport(
            rows: $outRows,
            grandTotals: $grandTotals,
            periodStart: $this->periodeAwal,
            periodEnd: $this->periodeAkhir,
            bulanLaluStart: $this->bulanLaluAwal,
            bulanLaluEnd: $this->bulanLaluAkhir
        );

        $filename = 'Kontribusi_Bulan_Lalu_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download($export, $filename);
    }

    public function downloadExcel()
    {
        // Validate that we have a result
        if (!$this->resultKey) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Data Tidak Ditemukan',
                'text' => 'Silakan tampilkan data terlebih dahulu sebelum melakukan export.',
            ]);
            return;
        }

        // Get cached data
        $data = Cache::get($this->resultKey);
        if (!$data) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Cache Expired',
                'text' => 'Data sudah kedaluwarsa. Silakan tampilkan ulang data.',
            ]);
            return;
        }

        $rows = $data['rowsBulanLalu'] ?? [];
        $grandTotals = $data['grandTotals'] ?? [];

        // Create export instance
        $export = new KontribusiBulanLaluExport(
            rows: $rows,
            grandTotals: $grandTotals,
            periodStart: $this->periodeAwal,
            periodEnd: $this->periodeAkhir,
            bulanLaluStart: $this->bulanLaluAwal,
            bulanLaluEnd: $this->bulanLaluAkhir
        );

        // Generate filename with date
        $filename = 'Kontribusi_Bulan_Lalu_' . date('Y-m-d_His') . '.xlsx';

        // Download
        return Excel::download($export, $filename);
    }
}
