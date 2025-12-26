<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Models\Operasional\LossBahan;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\TargetKontribusi;
use App\Models\User;
use App\App\Exports\Operasional\KontribusiHarianAreaExport;
use App\Exports\Operasional\KontribusiHarianAreaExport as OperasionalKontribusiHarianAreaExport;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class KontribusiHarianArea extends Component
{
    public string $tanggalAwal = '';
    public string $tanggalAkhir = '';

    /** @var array<int, array{id:int,nmtoko:string,api_id:string,produksi_sendiri:int}> */
    public array $tokosUser = [];

    /** @var array<string, array<string, array<int, array<string, mixed>>>> */
    public array $rows = [];

    public array $grandTotals = [];
    public ?string $loadDuration = null;

    public function mount(array $tokosUser = []): void
    {
        $this->tokosUser = $this->sanitizeTokosUser($tokosUser);

        $today = now();
        $this->tanggalAwal  = $today->copy()->startOfMonth()->toDateString();
        $this->tanggalAkhir = $today->copy()->toDateString();
    }

    public function hydrate(): void
    {
        $this->tokosUser    = $this->sanitizeTokosUser($this->tokosUser);
        $this->rows         = is_array($this->rows) ? $this->rows : [];
        $this->grandTotals  = is_array($this->grandTotals) ? $this->grandTotals : [];
        $this->loadDuration = $this->loadDuration === null ? null : (string) $this->loadDuration;
    }

    private function sanitizeTokosUser($tokosUser): array
    {
        return collect($tokosUser)
            ->map(function ($t) {
                $t = (array) $t;
                return [
                    'id'               => (int) ($t['id'] ?? 0),
                    'nmtoko'           => (string) ($t['nmtoko'] ?? ''),
                    'api_id'           => (string) ($t['api_id'] ?? ''),
                    'produksi_sendiri' => (int) ($t['produksi_sendiri'] ?? 0),
                ];
            })
            ->filter(fn ($t) => $t['id'] > 0)
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.operasional.kontribusi-harian-area');
    }

    public function load(): void
    {
        $startTime = microtime(true);

        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = Carbon::parse($this->tanggalAkhir)->toDateString();

        $tokoIdsRaw = collect($this->tokosUser)->pluck('id')->map(fn($v) => (int)$v)->filter()->unique()->values()->all();
        if (empty($tokoIdsRaw)) {
            $this->rows = [];
            $this->grandTotals = [];
            $this->loadDuration = number_format(microtime(true) - $startTime, 2);
            return;
        }

        $tokoIds = MasterToko::query()
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', '1')
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->all();

        if (empty($tokoIds)) {
            $this->rows = [];
            $this->grandTotals = [];
            $this->loadDuration = number_format(microtime(true) - $startTime, 2);
            return;
        }

        $snap = $this->fetchSnapshotRows($tokoIds, $start, $end);
        $this->rows = $snap;

        // GRAND TOTAL: sum Rp & hrg (weighted)
        $grand = [
            'target' => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
            'bl'     => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
        ];

        foreach ($snap as $byTanggal) {
            foreach ($byTanggal as $list) {
                foreach ($list as $r) {
                    $jenis  = strtoupper(trim((string)($r['type'] ?? '')));
                    $bucket = $jenis === 'BY TARGET' ? 'target' : 'bl';

                    $grand[$bucket]['hrg']        += (int)($r['hrg'] ?? 0);
                    $grand[$bucket]['selisih_rp'] += (int)($r['selisih_rp'] ?? 0);
                    $grand[$bucket]['kontribusi'] += (int)($r['kontribusi'] ?? 0);

                    $grand[$bucket]['disc']  += (int)($r['disc_rp'] ?? 0);
                    $grand[$bucket]['retur'] += (int)($r['retur_rp'] ?? 0);
                    $grand[$bucket]['gas']   += (int)($r['gas_rp'] ?? 0);
                    $grand[$bucket]['telur'] += (int)($r['telur_rp'] ?? 0);

                    $grand[$bucket]['loss']  += (int)($r['loss_bahan'] ?? 0);
                    $grand[$bucket]['total'] += (int)($r['total_kontribusi'] ?? 0);
                }
            }
        }

        $this->grandTotals  = $grand;
        $this->loadDuration = number_format(microtime(true) - $startTime, 2);
    }
 /**
     * ✅ Download tanpa harus klik "Tampilkan"
     */
    public function download()
    {
        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        $start = \Carbon\Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = \Carbon\Carbon::parse($this->tanggalAkhir)->toDateString();

        // pakai snapshot yang sama dengan load()
        $tokoIds = collect($this->tokosUser)->pluck('id')->map(fn($v)=>(int)$v)->filter()->unique()->values()->all();
        $tokoIds = \App\Models\MasterToko::whereIn('id',$tokoIds)->where('status','1')->pluck('id')->map(fn($v)=>(int)$v)->all();

        $snap = $this->fetchSnapshotRows($tokoIds, $start, $end);

        // hitung grand total sama seperti load()
        $grand = [
            'target' => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
            'bl'     => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
        ];

        foreach ($snap as $outlet => $byTanggal) {
            foreach ($byTanggal as $tgl => $list) {
                foreach ($list as $r) {
                    $jenis  = strtoupper(trim((string) ($r['type'] ?? '')));
                    $bucket = $jenis === 'BY TARGET' ? 'target' : 'bl';

                    $grand[$bucket]['hrg']        += (int)($r['hrg'] ?? 0);
                    $grand[$bucket]['selisih_rp'] += (int)($r['selisih_rp'] ?? 0);
                    $grand[$bucket]['kontribusi'] += (int)($r['kontribusi'] ?? 0);
                    $grand[$bucket]['disc']       += (int)($r['disc_rp'] ?? 0);
                    $grand[$bucket]['retur']      += (int)($r['retur_rp'] ?? 0);
                    $grand[$bucket]['gas']        += (int)($r['gas_rp'] ?? 0);
                    $grand[$bucket]['telur']      += (int)($r['telur_rp'] ?? 0);
                    $grand[$bucket]['loss']       += (int)($r['loss_bahan'] ?? 0);
                    $grand[$bucket]['total']      += (int)($r['total_kontribusi'] ?? 0);
                }
            }
        }

        return Excel::download(
            new OperasionalKontribusiHarianAreaExport($snap, $grand, $start, $end),
            "detail_kontribusi_harian_area_{$start}_sd_{$end}.xlsx"
        );
    }
    private function resolveParamsOrEmpty(): array
    {
        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = Carbon::parse($this->tanggalAkhir)->toDateString();

        $tokoIdsRaw = collect($this->tokosUser)
            ->pluck('id')->map(fn($v) => (int)$v)
            ->filter()->unique()->values()->all();

        if (empty($tokoIdsRaw)) return [$start, $end, []];

        $tokoIds = MasterToko::query()
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', '1')
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->all();

        return [$start, $end, $tokoIds];
    }
    private function computeGrandTotalsFromSnap(array $snap): array
    {
        $grand = [
            'target' => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
            'bl'     => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
        ];

        foreach ($snap as $outlet => $byTanggal) {
            foreach ($byTanggal as $tgl => $list) {
                foreach ($list as $r) {
                    $jenis  = strtoupper(trim((string)($r['type'] ?? '')));
                    $bucket = $jenis === 'BY TARGET' ? 'target' : 'bl';

                    $grand[$bucket]['hrg']        += (int)($r['hrg'] ?? 0);
                    $grand[$bucket]['selisih_rp'] += (int)($r['selisih_rp'] ?? 0);
                    $grand[$bucket]['kontribusi'] += (int)($r['kontribusi'] ?? 0);

                    $grand[$bucket]['disc']  += (int)($r['disc_rp'] ?? 0);
                    $grand[$bucket]['retur'] += (int)($r['retur_rp'] ?? 0);
                    $grand[$bucket]['gas']   += (int)($r['gas_rp'] ?? 0);
                    $grand[$bucket]['telur'] += (int)($r['telur_rp'] ?? 0);

                    $grand[$bucket]['loss']  += (int)($r['loss_bahan'] ?? 0);
                    $grand[$bucket]['total'] += (int)($r['total_kontribusi'] ?? 0);
                }
            }
        }

        return $grand;
    }

    // =========================
    // Helpers parsing angka
    // =========================

    private function toInt($v): int
    {
        if ($v === null) return 0;
        if (is_int($v)) return $v;
        if (is_float($v)) return (int) round($v);

        if (is_string($v)) {
            $s = trim($v);
            if ($s === '' || $s === '-') return 0;

            // buang pemisah ribuan, biarkan minus
            $s = str_replace(['.', ' '], '', $s);
            $s = str_replace(',', '.', $s); // kalau ada desimal koma
            return (int) round((float) $s);
        }

        return (int) $v;
    }

    private function toFloatPct($v): ?float
    {
        if ($v === null) return null;
        if (is_float($v) || is_int($v)) return (float) $v;

        if (is_string($v)) {
            $s = trim(str_replace('%', '', $v));
            if ($s === '' || $s === '-') return null;
            $s = str_replace(',', '.', $s);
            return is_numeric($s) ? (float) $s : null;
        }

        return null;
    }

    private function pct(float $num, float $den): float
    {
        if ($den == 0.0) return 0.0;
        return round(($num / $den) * 100, 2);
    }

    private function pctSelisih(float $selisihRp, float $hrgNow): float
    {
        // baseline = hrgNow - selisihRp
        $baseline = $hrgNow - $selisihRp;
        if ($baseline == 0.0) return 0.0;
        return round(($selisihRp / $baseline) * 100, 2);
    }

    // =========================
    // Data fetch
    // =========================

    private function fetchLossByTokoTanggal(array $tokoIds, string $start, string $end): array
    {
        $tokoIds = array_values(array_unique(array_filter(array_map('intval', $tokoIds))));
        if (empty($tokoIds)) return [];

        return LossBahan::query()
            ->selectRaw('DATE(tanggal) as tgl, toko_id, SUM(nominal) as total')
            ->whereBetween('tanggal', [$start, $end])
            ->whereIn('toko_id', $tokoIds)
            ->groupBy('tgl', 'toko_id')
            ->get()
            ->groupBy('tgl')
            ->map(fn($g) => $g->pluck('total', 'toko_id')->map(fn($v) => (int)$v)->toArray())
            ->toArray();
    }

    private function fetchSnapshotRows(array $tokoIds, string $start, string $end): array
    {
        $tokoIds = array_values(array_unique(array_filter(array_map('intval', $tokoIds))));
        if (empty($tokoIds)) return [];

        $latestRowId = KontribusiHarianJobRow::query()
            ->whereBetween('tanggal', [$start, $end])
            ->whereIn('jenis', ['BY TARGET', 'BY BULAN LALU'])
            ->whereHas('job', fn($q) => $q->whereIn('toko_id', $tokoIds)->where('status', 'ok'))
            ->max('id');

        $latestLossId = LossBahan::query()
            ->whereBetween('tanggal', [$start, $end])
            ->whereIn('toko_id', $tokoIds)
            ->max('id');

        $cacheKey = 'kh_area:snap_rows:v8:' . md5(
            json_encode($tokoIds) . "|$start|$end|" . (string)$latestRowId . "|" . (string)$latestLossId
        );

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tokoIds, $start, $end) {

            $rows = KontribusiHarianJobRow::query()
                ->whereBetween('tanggal', [$start, $end])
                ->whereIn('jenis', ['BY TARGET', 'BY BULAN LALU'])
                ->whereHas('job', fn($q) => $q->whereIn('toko_id', $tokoIds)->where('status', 'ok'))
                ->with(['job:id,toko_id'])
                ->orderBy('tanggal')
                ->orderByDesc('id')
                ->get();

            // pick latest per (toko,tgl,jenis)
            $picked = [];
            foreach ($rows as $r) {
                $tokoId = (int) ($r->job?->toko_id ?? 0);
                $tgl    = (string) ($r->tanggal ?? '');
                $jenis  = strtoupper(trim((string) ($r->jenis ?? '')));

                if ($tokoId <= 0 || $tgl === '' || $jenis === '') continue;

                $k = $tokoId . '|' . $tgl . '|' . $jenis;
                if (isset($picked[$k])) continue;

                $p = $r->payload;
                if (is_string($p)) $p = json_decode($p, true) ?: [];
                elseif (is_object($p)) $p = (array) $p;
                elseif (!is_array($p)) $p = [];

                if ($jenis === 'BY BULAN LALU' && isset($p['by_bulan_lalu']) && is_array($p['by_bulan_lalu'])) $p = $p['by_bulan_lalu'];
                if ($jenis === 'BY TARGET'    && isset($p['by_target'])     && is_array($p['by_target']))     $p = $p['by_target'];

                $picked[$k] = ['toko_id'=>$tokoId,'tanggal'=>$tgl,'jenis'=>$jenis,'payload'=>$p];
            }

            $tokoLocal = MasterToko::query()
                ->select('id', 'nmtoko')
                ->whereIn('id', $tokoIds)
                ->get()
                ->keyBy('id');

            $lossMap = $this->fetchLossByTokoTanggal($tokoIds, $start, $end);

            $getHrg = function (array $p): int {
                $candidates = ['sales_rp','hrg','penjualan_rp','neto_rp','neto','sales','penjualan','omzet_rp'];
                foreach ($candidates as $k) {
                    if (array_key_exists($k, $p)) {
                        $v = $this->toInt($p[$k]);
                        if ($v !== 0) return $v;
                    }
                }

                // fallback reconstruct dari selisih_rp & selisih_persen (baseline style)
                $selRp  = $this->toInt($p['selisih_rp'] ?? 0);
                $selPct = $this->toFloatPct($p['selisih_persen'] ?? ($p['selisih_pct'] ?? null));

                if ($selRp !== 0 && $selPct !== null && $selPct != 0.0) {
                    $hrg = (int) round(((float)$selRp * (100.0 + $selPct)) / $selPct);
                    return $hrg < 0 ? abs($hrg) : $hrg;
                }

                return 0;
            };

            $out = [];

            foreach ($picked as $item) {
                $tokoId = (int) $item['toko_id'];
                $tgl    = (string) $item['tanggal'];
                $jenis  = (string) $item['jenis'];
                $p      = (array) ($item['payload'] ?? []);

                $outlet = $tokoLocal[$tokoId]->nmtoko ?? (string)($p['outlet'] ?? '-');

                $hrgNow   = $getHrg($p);
                $selisih  = $this->toInt($p['selisih_rp'] ?? 0);

                $discRp   = $this->toInt($p['disc_rp'] ?? ($p['sc_manual_rp'] ?? 0));
                $returRp  = $this->toInt($p['retur_rp'] ?? 0);
                $gasRp    = $this->toInt($p['gas_rp'] ?? 0);
                $telurRp  = $this->toInt($p['telur_rp'] ?? 0);

                $loss = (int) ($lossMap[$tgl][$tokoId] ?? 0);
                $totalKontrib = $this->toInt($p['total_kontribusi'] ?? ($p['total'] ?? 0)) - $loss;

                $out[$outlet] ??= [];
                $out[$outlet][$tgl] ??= [];

                $out[$outlet][$tgl][] = [
                    'outlet' => $outlet,
                    'hrg'    => $hrgNow,
                    'type'   => $jenis,

                    'selisih_rp'     => $selisih,
                    'selisih_persen' => $this->pctSelisih((float)$selisih, (float)$hrgNow),
                    'kontribusi'     => $this->toInt($p['kontribusi_rp'] ?? ($p['kontribusi'] ?? 0)),

                    'disc_rp'   => $discRp,
                    'disc_pct'  => $this->pct((float)$discRp, (float)$hrgNow),

                    'retur_rp'  => $returRp,
                    'retur_pct' => $this->pct((float)$returRp, (float)$hrgNow),

                    'gas_rp'    => $gasRp,
                    'gas_pct'   => $this->pct((float)$gasRp, (float)$hrgNow),

                    'telur_rp'  => $telurRp,
                    'telur_pct' => $this->pct((float)$telurRp, (float)$hrgNow),

                    'loss_bahan'       => $loss,
                    'total_kontribusi' => $totalKontrib,
                ];
            }

            ksort($out);

            foreach ($out as &$byTanggal) {
                ksort($byTanggal);
                foreach ($byTanggal as &$list) {
                    usort($list, function ($a, $b) {
                        $order = ['BY TARGET' => 0, 'BY BULAN LALU' => 1];
                        return ($order[$a['type']] ?? 9) <=> ($order[$b['type']] ?? 9);
                    });
                }
            }

            return $out;
        });
    }
}
