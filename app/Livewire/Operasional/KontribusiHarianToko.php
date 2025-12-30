<?php

namespace App\Livewire\Operasional;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\MasterToko;
use App\Models\Operasional\LossBahan;
use App\Models\Operasional\KurangSetoran;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\TargetKontribusi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\Operasional\KontribusiHarianJob;
use App\Models\Operasional\KontribusiHarianJobRow;
use Illuminate\Support\Facades\DB;


class KontribusiHarianToko extends Component
{
       public $tanggalAwal;
    public $tanggalAkhir;
    public string $periodeLabel = '';

    /** @var array<int, array<string,mixed>> */
    public array $rows = [];

    /** @var array<string,mixed> */
    public array $grandTotals = [];

    public string $namaToko = '';

    public array $tokos = [];
    public $selectedTokoId;

    // info snapshot
    public array $missingDates = [];
    public array $metaByTanggal = []; // ['2025-12-01'=>'SNAPSHOT', ...]
    public ?string $loadDuration = null;

    // Loss Bahan Modal state
    public bool $showLossModal = false;
    public string $lossModalOutlet = '';
    public string $lossModalTanggal = '';
    public string $lossModalTanggalAkhir = '';
    public array $lossModalItems = [];
    public int $lossModalTotal = 0;

    public function mount(): void
    {
        $today = now();
        $this->tanggalAwal  = $today->copy()->startOfMonth()->toDateString();
        $this->tanggalAkhir = $today->copy()->subDay()->toDateString(); // default H-1

        $user = Auth::user();
        $this->tokos = $user->tokosQuery()->orderBy('nmtoko')->get()->toArray();

        if (!empty($this->tokos)) {
            $this->selectedTokoId = $this->tokos[0]['id'];
        }

        $tokoIdUser = (int)($user->toko_id ?? 0);
        if ($tokoIdUser > 0) {
            $toko = MasterToko::find($tokoIdUser);
            $this->namaToko = strtoupper(trim((string)($toko->nmtoko ?? '')));
            if (collect($this->tokos)->contains('id', $tokoIdUser)) {
                $this->selectedTokoId = $tokoIdUser;
            }
        }
    }

    public function render()
    {
        return view('livewire.operasional.kontribusi-harian-toko');
    }

    public function openLossModal(string $tanggal, ?int $tokoId = null, ?int $nominal = null): void
    {
        $dateStart = $tanggal;
        $dateEnd = $tanggal;

        $tid = (int)($tokoId ?: ($this->selectedTokoId ?: 0));
        if ($tid <= 0) {
            $this->lossModalItems = [];
            $this->showLossModal = true;
            return;
        }

        $items = $this->fetchLossItemsForModalRange('', $dateStart, $dateEnd, $tid);

        $outletLabel = $this->namaToko;
        if (!empty($items)) {
            $first = $items[0] ?? [];
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

    public function load(): void
    {
        $t0 = microtime(true);

        $this->rows = [];
        $this->grandTotals = [];
        $this->missingDates = [];
        $this->metaByTanggal = [];
        $this->loadDuration = null;

        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        // enforce H-1
        $today = now()->toDateString();
        $maxDate = now()->subDay()->toDateString();
        if ($this->tanggalAkhir >= $today) {
            $this->tanggalAkhir = $maxDate;
        }

        $user = Auth::user();
        $tokoId = (int)($this->selectedTokoId ?: ($user->toko_id ?? 0));
        if ($tokoId <= 0) {
            session()->flash('message', 'User ini belum punya toko_id dan belum memilih toko.');
            return;
        }

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = Carbon::parse($this->tanggalAkhir)->toDateString();
        $this->periodeLabel = Carbon::parse($start)->format('d/m/Y') . ' s.d ' . Carbon::parse($end)->format('d/m/Y');

        $toko = MasterToko::find($tokoId);
        if ($toko) $this->namaToko = strtoupper(trim((string)($toko->nmtoko ?? '')));

        $dates = $this->dateRangeList($start, $end);

        // ✅ ambil snapshot (latest per tanggal+jenis)
        $snap = $this->fetchSnapshotRowsPartial($tokoId, $start, $end);

        // group snapshot per tanggal
        $snapByTanggal = [];
        foreach ($snap as $r) {
            $tgl = (string)($r['tanggal'] ?? '');
            if ($tgl !== '') $snapByTanggal[$tgl][] = $r;
        }

        $out = [];
        foreach ($dates as $tgl) {
            $jenisAda = collect($snapByTanggal[$tgl] ?? [])->pluck('jenis')->unique()->values()->all();
            $lengkap  = in_array('BY TARGET', $jenisAda, true) && in_array('BY BULAN LALU', $jenisAda, true);

            if ($lengkap) {
                foreach ($snapByTanggal[$tgl] as $row) $out[] = $row;
                $this->metaByTanggal[$tgl] = 'SNAPSHOT';
            } else {
                $this->missingDates[] = $tgl;
                $this->metaByTanggal[$tgl] = 'MISSING';
            }
        }

        $this->rows = collect($out)
            ->sortBy(fn($r) => ($r['tanggal'] ?? '').'|'.($r['jenis'] ?? ''))
            ->values()
            ->toArray();

        $this->recalcGrandTotals();

        $this->loadDuration = number_format(microtime(true) - $t0, 2);

        $snapCount = count($dates) - count($this->missingDates);
        $missCount = count($this->missingDates);

        session()->flash('message', "SNAP={$snapCount} hari, MISSING={$missCount} hari. {$this->loadDuration}s");
    }

    /**
     * ✅ SNAPSHOT ONLY
     * - Ambil jobRow dalam range
     * - Dedup latest per (tanggal|jenis)
     * - Normalisasi key agar blade konsisten
     */
 private function fetchSnapshotRowsPartial(int $tokoId, string $start, string $end): array
{
    $rows = KontribusiHarianJobRow::query()
        ->whereBetween('tanggal', [$start, $end])
        ->whereIn('jenis', ['BY TARGET', 'BY BULAN LALU'])
        ->whereHas('job', fn($q) => $q->where('toko_id', $tokoId)->where('status', 'ok'))
        ->orderBy('tanggal')
        ->orderBy('jenis')
        ->orderByDesc('id')
        ->get();

    $kurangSetoranMap = KurangSetoran::query()
        ->where('toko_id', $tokoId)
        ->whereBetween('tanggal', [$start, $end])
        ->selectRaw('DATE(tanggal) as tgl, SUM(nominal) as total')
        ->groupBy('tgl')
        ->pluck('total', 'tgl')
        ->map(fn($v) => (int) $v)
        ->toArray();

    $picked = [];
    foreach ($rows as $r) {
        $tgl   = (string) ($r->tanggal ?? '');
        $jenis = strtoupper(trim((string) ($r->jenis ?? '')));
        if ($tgl === '' || $jenis === '') continue;

        $key = $tgl . '|' . $jenis;
        if (isset($picked[$key])) continue;

        $p = is_array($r->payload) ? $r->payload : (array) $r->payload;

        // ✅ pastikan ada hrg (penjualan)
        $hrg = (int) (
            $p['sales_now']
            ?? $p['sales']
            ?? $p['hrg']
            ?? 0
        );

        $payloadKurang = (int) ($p['kurang_setoran'] ?? 0);
        $kurangSetoran = $payloadKurang > 0
            ? $payloadKurang
            : (int) ($kurangSetoranMap[$tgl] ?? 0);

        $totalKontribusiPayload = (int) ($p['total_kontribusi'] ?? 0);
        $totalKontribusiFinal = $payloadKurang > 0
            ? $totalKontribusiPayload
            : ($totalKontribusiPayload - $kurangSetoran);

        $picked[$key] = [
            'tanggal' => $tgl,
            'jenis'   => $jenis,
            'source'  => 'SNAPSHOT',

            // ✅ kunci buat TOTAL%
            'hrg' => $hrg,

            'selisih_persen' => $p['selisih_persen'] ?? null,
            'selisih_rp'     => (int) ($p['selisih_rp'] ?? 0),
            'kontribusi_rp'  => (int) ($p['kontribusi_rp'] ?? 0),

            'disc_persen' => $p['disc_persen'] ?? null,
            'disc_rp'     => (int) ($p['disc_rp'] ?? 0),

            'retur_persen' => $p['retur_persen'] ?? null,
            'retur_rp'     => (int) ($p['retur_rp'] ?? 0),

            'gas_persen' => $p['gas_persen'] ?? null,
            'gas_rp'     => (int) ($p['gas_rp'] ?? 0),

            'telur_persen' => $p['telur_persen'] ?? null,
            'telur_rp'     => (int) ($p['telur_rp'] ?? 0),

            'loss_bahan'       => (int) ($p['loss_bahan'] ?? 0),
            'kurang_setoran'   => $kurangSetoran,
            'total_kontribusi' => $totalKontribusiFinal,
        ];
    }

    return array_values($picked);
}

    private function fetchLossItemsForModalRange(string $outletKey, ?string $dateAwal, ?string $dateAkhir, ?int $tokoId = null): array
    {
        if (!$dateAwal) return [];

        if ($tokoId) {
            $toko = MasterToko::query()->select('id','nmtoko')->where('id', $tokoId)->first();
        } else {
            $outletKeyUpper = mb_strtoupper(trim($outletKey));
            $toko = MasterToko::query()->select('id','nmtoko')->whereRaw('UPPER(TRIM(nmtoko)) = ?', [$outletKeyUpper])->first();
            if (!$toko) {
                $toko = MasterToko::query()->select('id','nmtoko')->whereRaw('UPPER(TRIM(nmtoko)) LIKE ?', ['%' . $outletKeyUpper . '%'])->first();
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
     * ✅ Grand total:
     * - RP dijumlah
     * - % dihitung weighted dari totalRp / totalHrg
     * - selisih% mengikuti rumus Harian Area: selisih / (hrg - selisih)
     */
    private function recalcGrandTotals(): void
    {
        $rows = collect($this->rows);

        $pct = function (int $num, int $den): ?float {
            if ($den <= 0) return null;
            return round(($num / $den) * 100, 2);
        };

        $calc = function (string $jenis) use ($rows, $pct): array {
            $r = $rows->where('jenis', $jenis);

            $sumHrg = (int) $r->sum('hrg');          // SUM sales_now
            $sumSel = (int) $r->sum('selisih_rp');   // SUM selisih

            // baseline ala Harian Area: baseline = hrg - selisih
            $baseline = $sumHrg - $sumSel;

            $sumDisc  = (int) $r->sum('disc_rp');
            $sumRetur = (int) $r->sum('retur_rp');
            $sumGas   = (int) $r->sum('gas_rp');
            $sumTelur = (int) $r->sum('telur_rp');

            return [
                // untuk debug/opsional
                'hrg' => $sumHrg,
                'baseline' => $baseline,

                'selisih_rp' => $sumSel,
                'selisih_persen' => $pct($sumSel, $baseline),

                'kontribusi_rp' => (int) $r->sum('kontribusi_rp'),

                // komponen biaya basis sales (hrg)
                'disc_rp' => $sumDisc,
                'disc_persen' => $pct($sumDisc, $sumHrg),

                'retur_rp' => $sumRetur,
                'retur_persen' => $pct($sumRetur, $sumHrg),

                'gas_rp' => $sumGas,
                'gas_persen' => $pct($sumGas, $sumHrg),

                'telur_rp' => $sumTelur,
                'telur_persen' => $pct($sumTelur, $sumHrg),

                'loss_bahan' => (int) $r->sum('loss_bahan'),
                'total_kontribusi' => (int) $r->sum('total_kontribusi'),
            ];
        };

        $this->grandTotals = [
            'by_target'     => $calc('BY TARGET'),
            'by_bulan_lalu' => $calc('BY BULAN LALU'),
        ];
    }



    private function dateRangeList(string $start, string $end): array
    {
        $out = [];
        for ($d = Carbon::parse($start); $d->lte(Carbon::parse($end)); $d->addDay()) {
            $out[] = $d->toDateString();
        }
        return $out;
    }
}
