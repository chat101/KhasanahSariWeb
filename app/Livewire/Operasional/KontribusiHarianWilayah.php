<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJob;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Models\Operasional\LossBahan;
use App\Models\Operasional\KurangSetoran;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\TargetKontribusi;
use App\Models\User;
use App\Exports\Operasional\KontribusiHarianWilayahExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;

class KontribusiHarianWilayah extends Component
{
    public string $tanggalAwal = '';
    public string $tanggalAkhir = '';

    /** @var array<int, array{id:int,nmtoko:string,api_id:string,produksi_sendiri:int}> */
    public array $tokosUser = [];

    /** @var array<string, array<string, array<int, array<string, mixed>>>> */
    public array $rows = [];

    public array $grandTotals = [];
    public ?string $loadDuration = null;
    /** @var array<string,string> Tooltip list nama barang loss per wilayah */
    public array $lossBarangTooltip = [];
    /** @var array<string,array<string,array<int,string>>> Daftar barang per toko per wilayah */
    public array $lossBarangListMap = [];

    public bool $showLossModal = false;
    public string $lossModalWilayah = '';
    /** @var string[] */
    public array $lossModalItems = [];

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
        return view('livewire.operasional.kontribusi-harian-wilayah');
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

        $snap = $this->fetchSnapshotRowsByWilayah($tokoIds, $start, $end);
        $this->rows = $snap;
        [$this->lossBarangListMap, $this->lossBarangTooltip] = $this->buildLossBarangData($tokoIds, $start, $end);

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

    /**
     * ✅ Download tanpa harus klik "Tampilkan"
     */
    public function download()
    {
        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = Carbon::parse($this->tanggalAkhir)->toDateString();

        $tokoIdsRaw = collect($this->tokosUser)->pluck('id')->map(fn($v) => (int)$v)->filter()->unique()->values()->all();
        if (empty($tokoIdsRaw)) {
            session()->flash('message', 'Tidak ada toko yang tersedia.');
            return;
        }

        $tokoIds = MasterToko::query()
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', '1')
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->all();

        if (empty($tokoIds)) {
            session()->flash('message', 'Tidak ada toko aktif yang tersedia.');
            return;
        }

        $snap = $this->fetchSnapshotRowsByWilayah($tokoIds, $start, $end);

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

        return Excel::download(
            new KontribusiHarianWilayahExport($snap, $grand),
            'Kontribusi-Harian-Wilayah-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function openLossModal(string $wilayah): void
    {
        $items = $this->lossBarangListMap[$wilayah] ?? [];
        $this->lossModalWilayah = $wilayah;
        $this->lossModalItems = $items;
        $this->showLossModal = true;
    }

    public function closeLossModal(): void
    {
        $this->showLossModal = false;
        $this->lossModalWilayah = '';
        $this->lossModalItems = [];
    }

    /**
     * Fetch & aggregate by wilayah
     * Data digroup by wilayah, hanya tampilkan total per wilayah
     */
    private function fetchSnapshotRowsByWilayah(array $tokoIds, string $startDate, string $endDate): array
    {
        $rows = [];

        // Fetch semua rows dalam date range, filter by toko via job relationship
        $allRows = KontribusiHarianJobRow::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('jenis', ['BY TARGET', 'BY BULAN LALU'])
            ->with(['job.toko.area.wilayah'])
            ->orderBy('tanggal', 'asc')
            ->get()
            ->filter(function ($row) use ($tokoIds) {
                return in_array($row->job?->toko_id, $tokoIds);
            });

        if ($allRows->isEmpty()) {
            return [];
        }

        // Ambil kurang setoran per toko per tanggal dalam periode
        $kurangMap = [];
        $ksRows = KurangSetoran::query()
            ->selectRaw('toko_id, tanggal, SUM(nominal) as total')
            ->whereBetween('tanggal', [$startDate, $endDate])
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

        // Group by tanggal & wilayah, then aggregate
        $grouped = [];
        foreach ($allRows as $row) {
            $tgl = $row->tanggal;
            $wilayahName = $row->job?->toko?->area?->wilayah?->nama_wilayah ?? 'Unknown';
            $jenis = strtoupper(trim((string)($row->jenis ?? '')));

            $grouped[$tgl] ??= [];
            $grouped[$tgl][$wilayahName] ??= [
                'BY BULAN LALU' => [
                    'hrg' => 0,
                    'selisih_rp' => 0,
                    'kontribusi' => 0,
                    'disc_rp' => 0,
                    'retur_rp' => 0,
                    'gas_rp' => 0,
                    'telur_rp' => 0,
                    'loss_bahan' => 0,
                    'kurang_setoran' => 0,
                    'total_kontribusi' => 0,
                    'type' => 'BY BULAN LALU',
                ],
                'BY TARGET' => [
                    'hrg' => 0,
                    'selisih_rp' => 0,
                    'kontribusi' => 0,
                    'disc_rp' => 0,
                    'retur_rp' => 0,
                    'gas_rp' => 0,
                    'telur_rp' => 0,
                    'loss_bahan' => 0,
                    'kurang_setoran' => 0,
                    'total_kontribusi' => 0,
                    'type' => 'BY TARGET',
                ],
            ];

            // Extract payload
            $payload = is_array($row->payload) ? $row->payload : [];
            
            // Determine bucket
            $bucketData = &$grouped[$tgl][$wilayahName][$jenis];

            // Get HRG dengan logic sophisticated sama seperti area component
            $hrg = $this->getHrg($payload);

            // Aggregate raw values
            $bucketData['hrg'] += $hrg;
            $bucketData['selisih_rp'] += $this->toInt($payload['selisih_rp'] ?? 0);
            $bucketData['kontribusi'] += $this->toInt($payload['kontribusi_rp'] ?? $payload['kontribusi'] ?? 0);
            $bucketData['disc_rp'] += $this->toInt($payload['disc_rp'] ?? $payload['sc_manual_rp'] ?? 0);
            $bucketData['retur_rp'] += $this->toInt($payload['retur_rp'] ?? 0);
            $bucketData['gas_rp'] += $this->toInt($payload['gas_rp'] ?? 0);
            $bucketData['telur_rp'] += $this->toInt($payload['telur_rp'] ?? 0);
            $loss = $this->toInt($payload['loss_bahan'] ?? 0);
            $bucketData['loss_bahan'] += $loss;

            // Kurang setoran dari DB per toko+tanggal
            $tid    = (int) ($row->job?->toko_id ?? 0);
            $tglKey = \Carbon\Carbon::parse($tgl)->toDateString();
            $kurang = (int) ($kurangMap[$tid][$tglKey] ?? 0);
            $bucketData['kurang_setoran'] += $kurang;

            // Total kontribusi dikurangi loss & kurang
            $total = $this->toInt($payload['total_kontribusi'] ?? $payload['total'] ?? 0);
            $bucketData['total_kontribusi'] += ($total - $loss - $kurang);
        }

        // Compute percentages & restructure
        foreach ($grouped as $tgl => $byWilayah) {
            $rows[$tgl] = [];
            foreach ($byWilayah as $wilayahName => $types) {
                $rows[$tgl][$wilayahName] = [];
                foreach ($types as $typeData) {
                    $hrg = (float)($typeData['hrg'] ?? 0);
                    
                    // Compute percentages (sama seperti area component)
                    $typeData['selisih_persen'] = $this->pctSelisih((float)($typeData['selisih_rp'] ?? 0), $hrg);
                    $typeData['disc_pct'] = $this->pct((float)($typeData['disc_rp'] ?? 0), $hrg);
                    $typeData['retur_pct'] = $this->pct((float)($typeData['retur_rp'] ?? 0), $hrg);
                    $typeData['gas_pct'] = $this->pct((float)($typeData['gas_rp'] ?? 0), $hrg);
                    $typeData['telur_pct'] = $this->pct((float)($typeData['telur_rp'] ?? 0), $hrg);
                    
                    $rows[$tgl][$wilayahName][] = $typeData;
                }
            }
        }

        ksort($rows);
        return $rows;
    }

    /**
     * Kumpulkan list toko+barang loss per wilayah (unique) untuk modal + tooltip.
     * @return array{0: array<string,array<int,array{toko:string,barang:string}>>, 1: array<string,string>}
     */
    private function buildLossBarangData(array $tokoIds, string $startDate, string $endDate): array
    {
        $listMap = [];
        $tooltipMap = [];

        $lossRows = LossBahan::query()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('toko_id', $tokoIds)
            ->with(['toko.area.wilayah', 'barang'])
            ->get();

        if ($lossRows->isEmpty()) {
            return [$listMap, $tooltipMap];
        }

        $tmp = [];
        foreach ($lossRows as $lr) {
            $wil = $lr->toko?->area?->wilayah?->nama_wilayah;
            if (!$wil) continue;

            $nmBarang = $lr->barang?->nmbarang;
            if (!$nmBarang) {
                $nmBarang = $lr->keterangan ?: ('Barang ID ' . ($lr->barang_id ?? '-'));
            }

            $tokoName = $lr->toko?->nmtoko ?: 'Toko ?';

            $listMap[$wil] ??= [];
            $listMap[$wil][$tokoName] ??= [];
            if (!isset($listMap[$wil][$tokoName][$nmBarang])) {
                $listMap[$wil][$tokoName][$nmBarang] = [
                    'barang' => $nmBarang,
                    'nominal' => 0,
                    'qty' => 0,
                ];
            }
            $listMap[$wil][$tokoName][$nmBarang]['nominal'] += (int) ($lr->nominal ?? 0);
            $listMap[$wil][$tokoName][$nmBarang]['qty'] += (int) ($lr->qty ?? 0);

            $tmp[$wil][$nmBarang] = true; // set unik
        }

        foreach ($tmp as $wilayah => $namesSet) {
            $names = array_keys($namesSet);
            sort($names, SORT_NATURAL | SORT_FLAG_CASE);

            // konversi set ke list per toko, urutkan nama toko dan barang
            if (isset($listMap[$wilayah])) {
                ksort($listMap[$wilayah], SORT_NATURAL | SORT_FLAG_CASE);
                foreach ($listMap[$wilayah] as $toko => $barangSet) {
                    $barangList = array_values($barangSet);
                    usort($barangList, function ($a, $b) {
                        return strnatcasecmp($a['barang'] ?? '', $b['barang'] ?? '');
                    });
                    $listMap[$wilayah][$toko] = $barangList;
                }
            }

            $limit = 12;
            if (count($names) > $limit) {
                $slice = array_slice($names, 0, $limit);
                $more = count($names) - $limit;
                $tooltipMap[$wilayah] = implode(', ', $slice) . " +{$more} item";
            } else {
                $tooltipMap[$wilayah] = implode(', ', $names);
            }
        }

        return [$listMap, $tooltipMap];
    }

    private function getHrg(array $p): int
    {
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
    }

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
}
