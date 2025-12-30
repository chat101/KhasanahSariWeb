<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Models\Operasional\KurangSetoran;
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
    /** @var array<string,array<string,array<int,array{barang:string,nominal:int,qty:int}>>> List barang loss per outlet per tanggal */
    public array $lossBarangListMap = [];
    public bool $showLossModal = false;
    public string $lossModalOutlet = '';
    public string $lossModalTanggal = '';
    public array $lossModalItems = [];
    public int $lossModalTotal = 0;

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
        $this->lossBarangListMap = $this->buildLossBarangData($tokoIds, $start, $end);

        // GRAND TOTAL: sum Rp & hrg (weighted)
        $grand = [
            'target' => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss_bahan'=>0,'kurang_setoran'=>0,'total_kontribusi'=>0],
            'bl'     => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss_bahan'=>0,'kurang_setoran'=>0,'total_kontribusi'=>0],
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

                    $grand[$bucket]['loss_bahan']      += (int)($r['loss_bahan'] ?? 0);
                    $grand[$bucket]['kurang_setoran']  += (int)($r['kurang_setoran'] ?? 0);
                    $grand[$bucket]['total_kontribusi'] += (int)($r['total_kontribusi'] ?? 0);
                }
            }
        }

        $this->grandTotals  = $grand;
        $this->loadDuration = number_format(microtime(true) - $startTime, 2);
    }

    public function openLossModal(string $outlet, ?string $tanggal = null, ?int $nominal = null, ?string $outletLabelParam = null): void
    {
        $dateKey = $tanggal ? Carbon::parse($tanggal)->toDateString() : null;
        $items = $dateKey && isset($this->lossBarangListMap[$outlet][$dateKey])
            ? $this->lossBarangListMap[$outlet][$dateKey]
            : [];

        if (empty($items) && $dateKey) {
            $items = $this->fetchLossItemsForModal($outlet, $dateKey);
        }

        $outletLabel = $outlet;
        if (!empty($items)) {
            $first = $items[0];
            if (!empty($first['outlet_label'])) {
                $outletLabel = (string) $first['outlet_label'];
            }
        } elseif (!empty($outletLabelParam)) {
            $outletLabel = $outletLabelParam;
        }

        $this->lossModalOutlet = $outletLabel;
        $this->lossModalTanggal = $dateKey ?? '';
        $this->lossModalItems = $items;
        $this->lossModalTotal = (int) ($nominal ?? 0);
        $this->showLossModal = true;
    }

    public function closeLossModal(): void
    {
        $this->showLossModal = false;
        $this->lossModalOutlet = '';
        $this->lossModalTanggal = '';
        $this->lossModalItems = [];
        $this->lossModalTotal = 0;
    }

    /**
     * Fallback: query detail LossBahan per outlet (by name) dan tanggal saat modal dibuka.
     * @param string $outletKey uppercase/trimmed outlet name
     * @param string $dateKey Y-m-d
     * @return array<int,array{barang:string,nominal:int,qty:int,satuan?:string,outlet_label?:string}>
     */
    private function fetchLossItemsForModal(string $outletKey, string $dateKey): array
    {
        $toko = MasterToko::query()
            ->select('id','nmtoko')
            ->whereRaw('UPPER(TRIM(nmtoko)) = ?', [mb_strtoupper(trim($outletKey))])
            ->first();

        if (!$toko) return [];

        $rows = LossBahan::query()
            ->whereDate('tanggal', $dateKey)
            ->where('toko_id', (int)$toko->id)
            ->with(['barang'])
            ->get();

        if ($rows->isEmpty()) return [];

        $agg = [];
        foreach ($rows as $lr) {
            $nama = $lr->barang?->nmbarang ?: ($lr->keterangan ?: ('Barang ID ' . ($lr->barang_id ?? '-')));
            $satuan = $lr->barang?->sat1;
            $agg[$nama]['barang'] = $nama;
            $agg[$nama]['nominal'] = ($agg[$nama]['nominal'] ?? 0) + (int)($lr->nominal ?? 0);
            $agg[$nama]['qty'] = ($agg[$nama]['qty'] ?? 0) + (int)($lr->qty ?? 0);
            if (!empty($satuan)) $agg[$nama]['satuan'] = (string)$satuan;
            $agg[$nama]['outlet_label'] = $toko->nmtoko;
        }

        $list = array_values($agg);
        usort($list, function ($a, $b) {
            return strnatcasecmp($a['barang'] ?? '', $b['barang'] ?? '');
        });
        return $list;
    }
 /**
     * ✅ Download dengan SweetAlert (hindari Livewire hydration error)
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

        // Generate file langsung tanpa menyimpan ke state Livewire
        $export = new OperasionalKontribusiHarianAreaExport($snap, $grand, $start, $end);
        $filename = "detail_kontribusi_harian_area_{$start}_sd_{$end}.xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
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

        $latestKurangId = KurangSetoran::query()
            ->whereBetween('tanggal', [$start, $end])
            ->whereIn('toko_id', $tokoIds)
            ->max('id');

        $cacheKey = 'kh_area:snap_rows:v9:' . md5(
            json_encode($tokoIds) . "|$start|$end|" . (string)$latestRowId . "|" . (string)$latestLossId . "|" . (string)$latestKurangId
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

            // Ambil kurang setoran per toko per tanggal dalam periode
            $kurangMap = [];
            $ksRows = KurangSetoran::query()
                ->selectRaw('toko_id, tanggal, SUM(nominal) as total')
                ->whereBetween('tanggal', [$start, $end])
                ->whereIn('toko_id', $tokoIds)
                ->groupBy('toko_id', 'tanggal')
                ->get();

            foreach ($ksRows as $row) {
                $tid  = (int) ($row->toko_id ?? 0);
                $tglx = \Carbon\Carbon::parse($row->tanggal)->toDateString();
                if ($tid > 0 && $tglx !== '') {
                    $kurangMap[$tid][$tglx] = (int) ($row->total ?? 0);
                }
            }

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

                $loss   = (int) ($p['loss_bahan'] ?? 0);
                $tglKey = \Carbon\Carbon::parse($tgl)->toDateString();
                $kurang = (int) ($kurangMap[$tokoId][$tglKey] ?? 0);
                $totalKontrib = $this->toInt($p['total_kontribusi'] ?? ($p['total'] ?? 0)) - $loss - $kurang;

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
                    'kurang_setoran'   => $kurang,
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

    /**
     * Ambil list barang loss per outlet untuk modal (kelompok per toko, sum nominal & qty).
     * @return array<string,array<int,array{barang:string,nominal:int,qty:int}>>
     */
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
            $outletKey   = strtoupper(trim($outletLabel));
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
}
