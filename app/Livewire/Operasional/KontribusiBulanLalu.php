<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Models\Operasional\LossBahan;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KontribusiBulanLalu extends Component
{
    public array $tokosUser = [];

    public $periodeAwal;
    public $periodeAkhir;

    public $bulanLaluAwal;
    public $bulanLaluAkhir;

    // ✅ hanya simpan key, hasil besar masuk cache (anti corrupt hydrate)
    public ?string $resultKey = null;

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

        // loss SUM per toko (range sekarang)
        $lossByToko = $this->fetchLossBahanByToko($tokoIds, $start, $end);

        // =========================
        // 1) Ambil latest row per (toko,tgl) untuk jenis BY BULAN LALU
        // =========================
        $rowsSnap = KontribusiHarianJobRow::query()
            ->select(['id','job_id','tanggal','jenis','payload'])
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

        $outRows = [];
        foreach ($tokoIds as $tokoId) {
            $tokoDb = $tokoLocal[$tokoId] ?? null;
            if (!$tokoDb) continue;

            $a = $agg[$tokoId] ?? null;
            if (!$a) continue;

            $hrg      = (int)$a['hrg'];
            $baseline = (int)$a['baseline'];

            $loss = (int) ($lossByToko[$tokoId] ?? 0);

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

                // total kontribusi di laporan = payload_total - loss
                'total_kontribusi' => (int)$a['total_kontribusi'] - $loss,
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
            'total_kontribusi' => $sumTotal,
        ];

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
    private function fetchLossBahanByToko(array $tokoIds, string $start, string $end): array
    {
        $tokoIds = array_values(array_unique(array_filter(array_map('intval', $tokoIds))));
        if (empty($tokoIds)) return [];

        $cacheKey = 'loss_bahan:sum:v1:' . md5(json_encode($tokoIds) . "|$start|$end");

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tokoIds, $start, $end) {
            return LossBahan::query()
                ->selectRaw('toko_id, SUM(nominal) as total')
                ->whereBetween('tanggal', [$start, $end])
                ->whereIn('toko_id', $tokoIds)
                ->groupBy('toko_id')
                ->pluck('total', 'toko_id')
                ->map(fn($v) => (int) $v)
                ->toArray();
        });
    }
}
