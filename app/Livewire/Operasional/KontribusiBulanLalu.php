<?php

namespace App\Livewire\Operasional;

use App\Models\Operasional\MasterTrendInflasi;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class KontribusiBulanLalu extends Component
{
    public array $tokosUser = [];

    public $bulanLaluAwal;
    public $bulanLaluAkhir;

    public $periodeAwal;
    public $periodeAkhir;

    public array $rowsBulanLalu = [];
    public int $sumNetoBulanLalu = 0;

    protected float $hppRatio = 0.56;
    public array $totalsByWilayah = [];
    public array $grandTotals = [];

    public function mount(array $tokosUser = [], $periodeAwal = null, $periodeAkhir = null)
    {
        $this->tokosUser = $tokosUser;

        $this->periodeAwal  = $periodeAwal ?? now()->toDateString();
        $this->periodeAkhir = $periodeAkhir ?? now()->toDateString();

        $this->syncBulanLaluRange();
    }
//    private function fetchBiaya(string $idcab, string $start, string $end): array
// {
//     $json = Http::timeout(20)
//         ->retry(2, 300)
//         ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
//             'startDate' => $start,
//             'endDate'   => $end,
//             'idcab'     => $idcab, // ✅
//         ])->json();

//     return $json['data'] ?? [];
// }
    private function prefixKet(?string $ket): string
    {
        if (!$ket) return '';
        return trim(explode('XpX', $ket, 2)[0]);
    }

    // private function sumTotBiaya(array $data, callable $filter): int
    // {
    //     return (int) collect($data)
    //         ->filter($filter)
    //         ->sum(fn($r) => (int) str_replace([',', '.'], '', (string)($r['totbiaya'] ?? 0)));
    // }

    private function hitungSelisihBiaya(int $now, int $last): int
    {
        return $now - $last;
    }

    // ini kalau kolom % selisih dibagi nilai itu sendiri
    // private function hitungSelisihBiaya(int $now, int $last): array
    // {
    //     $rp = $now - $last;
    //     $pct = $last > 0 ? round(($rp / $last) * 100, 2) : null;

    //     return [
    //         'rp'  => $rp,
    //         'pct' => is_null($pct) ? '-' : $pct . '%',
    //     ];
    // }

    private function syncBulanLaluRange(): void
    {
        $this->bulanLaluAwal  = Carbon::parse($this->periodeAwal)->subMonthNoOverflow()->toDateString();
        $this->bulanLaluAkhir = Carbon::parse($this->periodeAkhir)->subMonthNoOverflow()->toDateString();
    }
    private function hitungKontribusiDariSelisih(int $selisihRp): array
    {
        // HPP bagian dari selisih (bisa minus juga kalau selisih minus)
        $hppSelisih = (int) round($selisihRp * $this->hppRatio);

        // kontribusi = selisih - HPP
        $kontribusiRp = $selisihRp - $hppSelisih;

        return [
            'hpp_ratio'     => $this->hppRatio,
            'hpp_selisih'   => $hppSelisih,
            'kontribusi_rp' => $kontribusiRp,
        ];
    }
    private function getTrendPersenUntukPeriode(string $periodeAwal): float
    {
        $d = Carbon::parse($periodeAwal);

        return (float) (MasterTrendInflasi::query()
            ->where('tahun', (int) $d->year)
            ->where('bulan', (int) $d->month)
            ->value('trend') ?? 0);
    }
    private function fetchPenjualanAll(string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
                'startDate' => $start,
                'endDate'   => $end,
            ])->json();

        return $json['data'] ?? [];
    }
    private function toInt($v): int
    {
        return (int) str_replace([',', '.'], '', (string)($v ?? 0));
    }
    private function pctDariPenjualan(int $rp, int $penjualanNow): ?float
    {
        if ($penjualanNow <= 0) return null;
        return round(($rp / $penjualanNow) * 100, 2);
    }

    private function fetchReturAll(string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/retur', [
                'startDate' => $start,
                'endDate'   => $end,
            ])->json();

        return $json['data'] ?? [];
    }
    //     private function diffRp(int $now, int $last): int
    // {
    //     return $now - $last;
    // }

    // private function pctOfSales(int $rp, int $sales): ?float
    // {
    //     if ($sales <= 0) return null;
    //     return round(($rp / $sales) * 100, 2);
    // }
    private function fetchBiayaAll(string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
                'startDate' => $start,
                'endDate'   => $end,
            ])->json();

        return $json['data'] ?? [];
    }

    private function fetchPenjualan(string $apiId, string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
                'startDate' => $start,
                'endDate'   => $end,
                'idcabang'  => $apiId,
            ])->json();

        return $json['data'] ?? [];
    }
    // Sekarang % disc/gas/telur/retur adalah rasio terhadap penjualanNow,
    // Sekarang % disc/gas/telur/retur adalah rasio terhadap penjualanNow,
    public function loadBulanLalu()
    {
        $this->validate([
            'periodeAwal'  => 'required|date',
            'periodeAkhir' => 'required|date|after_or_equal:periodeAwal',
        ]);

        $this->syncBulanLaluRange();

        $trendNowPct = $this->getTrendPersenUntukPeriode($this->periodeAwal);

        // =========================
        // 🔥 FETCH SEKALI (OPTIMASI)
        // =========================
        $penjualanNowRaw  = collect($this->fetchPenjualanAll($this->periodeAwal, $this->periodeAkhir));
        $penjualanLastRaw = collect($this->fetchPenjualanAll($this->bulanLaluAwal, $this->bulanLaluAkhir));

        $penjualanNowById  = $penjualanNowRaw->groupBy(fn($r) => trim((string)($r['idcabang'] ?? '')));
        $penjualanLastById = $penjualanLastRaw->groupBy(fn($r) => trim((string)($r['idcabang'] ?? '')));

        // fallback by nama (karena ada key `cabang`)
        $penjualanNowByName  = $penjualanNowRaw->groupBy(fn($r) => trim((string)($r['cabang'] ?? '')));
        $penjualanLastByName = $penjualanLastRaw->groupBy(fn($r) => trim((string)($r['cabang'] ?? '')));

        // BIAYA sudah kamu ubah pakai idcab (lebih presisi ✅)
        $biayaNowAll = collect($this->fetchBiayaAll($this->periodeAwal, $this->periodeAkhir))
            ->groupBy(fn($r) => trim((string)($r['idcab'] ?? '')));

        $biayaLastAll = collect($this->fetchBiayaAll($this->bulanLaluAwal, $this->bulanLaluAkhir))
            ->groupBy(fn($r) => trim((string)($r['idcab'] ?? '')));

        // ✅ RETUR (group by idcab)
        $returNowAll = collect($this->fetchReturAll($this->periodeAwal, $this->periodeAkhir))
            ->groupBy(fn($r) => trim((string)($r['idcab'] ?? '')));

        $returLastAll = collect($this->fetchReturAll($this->bulanLaluAwal, $this->bulanLaluAkhir))
            ->groupBy(fn($r) => trim((string)($r['idcab'] ?? '')));

        // ✅ LOSS BAHAN: ambil SEKALI dari DB, lalu map by toko_id
        $lossMap = \App\Models\Operasional\LossBahan::query()
            ->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
            ->selectRaw('toko_id, SUM(nominal) as total')
            ->groupBy('toko_id')
            ->pluck('total', 'toko_id'); // [toko_id => total]

        $rows = [];
        $grand = 0;
        $tokoIds = collect($this->tokosUser)->pluck('id')->filter()->values();

        // ambil toko lokal + area + wilayah
        $tokoLocal = \App\Models\MasterToko::query()
            ->with(['area.wilayah'])
            ->whereIn('id', $tokoIds)
            ->get()
            ->keyBy('id');

        // ambil PIC AREA (role area) map: [area_id => name]
        $picAreaByAreaId = \App\Models\User::query()
            ->select('name', 'area_id')
            ->whereNotNull('area_id')
            ->whereRaw("LOWER(TRIM(role)) = 'area'")
            ->orderBy('name')
            ->get()
            ->groupBy('area_id')
            ->map(fn($g) => $g->pluck('name')->filter()->implode(', '))
            ->toArray();
        //  dd($picAreaByAreaId);
        // ambil PIC WILAYAH (role wilayah) map: [wilayah_id => name]
        $picWilByWilayahId = \App\Models\User::query()
            ->select('id', 'name', 'role', 'wilayah_id')
            ->where('role', 'wilayah')
            ->whereNotNull('wilayah_id')
            ->get()
            ->groupBy('wilayah_id')
            ->map(fn($g) => $g->first()?->name)
            ->toArray();


        foreach ($this->tokosUser as $t) {

            $tokoId = (int)($t['id'] ?? 0);
            $tokoDb = $tokoLocal[$tokoId] ?? null;

            $areaId = (int) ($tokoDb?->area_id ?? 0);
            $pic = $areaId > 0 ? ($picAreaByAreaId[$areaId] ?? '') : '';
            $picArea = $areaId > 0
                ? ($picAreaByAreaId[$areaId] ?? '')
                : '';
            $wilayahLabel = $tokoDb?->area?->wilayah?->nama_wilayah
                ?: ($tokoDb?->area?->wilayah_id ? 'WILAYAH-' . $tokoDb->area->wilayah_id : '-');
            $namaArea    = $tokoDb?->area?->nama_area;
            // $wilayahId   = $tokoDb?->area?->wilayah_id; // penting utk fallback wilayah


            // $picWilayah = $wilayahId
            //     ? ($picWilByWilayahId[$wilayahId] ?? null)
            //     : null;

            // prioritas: PIC area -> kalau kosong pakai PIC wilayah
            // $picTampil = $picArea ?: $picWilayah;            // pastikan tokosUser bawa id lokal

            $apiId   = trim((string)($t['api_id'] ?? ''));   // id cab API
            $apiName = trim((string)($t['nmcab'] ?? $t['nmtoko'] ?? ''));
            $outlet  = $t['nmtoko'] ?? '-';

            // ================= PENJUALAN =================
            $penNowRows  = $penjualanNowById[$apiId]  ?? $penjualanNowByName[$apiName]  ?? collect();
            $penLastRows = $penjualanLastById[$apiId] ?? $penjualanLastByName[$apiName] ?? collect();

            $penjualanNow  = (int) $penNowRows->sum(fn($r) => $this->toInt($r['hrg'] ?? 0));
            $penjualanLast = (int) $penLastRows->sum(fn($r) => $this->toInt($r['hrg'] ?? 0));

            $baseline   = (int) round($penjualanLast * (1 + ($trendNowPct / 100)));
            $selisihRp  = $penjualanNow - $baseline;
            $selisihPct = $baseline > 0 ? round(($selisihRp / $baseline) * 100, 2) : null;

            $k = $this->hitungKontribusiDariSelisih($selisihRp);

            // ================= BIAYA =================
            $biayaNow  = collect($biayaNowAll[$apiId]  ?? []);
            $biayaLast = collect($biayaLastAll[$apiId] ?? []);

            $discNow = (int) $biayaNow
                ->filter(fn($r) => strtoupper((string)($r['tipe'] ?? '')) === 'DISKON MANUAL')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            $discLast = (int) $biayaLast
                ->filter(fn($r) => strtoupper((string)($r['tipe'] ?? '')) === 'DISKON MANUAL')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            $gasNow = (int) $biayaNow
                ->filter(fn($r) => $this->prefixKet($r['ket'] ?? '') === 'Gas')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            $gasLast = (int) $biayaLast
                ->filter(fn($r) => $this->prefixKet($r['ket'] ?? '') === 'Gas')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            $telNow = (int) $biayaNow
                ->filter(fn($r) => $this->prefixKet($r['ket'] ?? '') === 'Telur')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            $telLast = (int) $biayaLast
                ->filter(fn($r) => $this->prefixKet($r['ket'] ?? '') === 'Telur')
                ->sum(fn($r) => $this->toInt($r['totbiaya'] ?? 0));

            // $disc = $this->hitungSelisihBiaya($discNow, $discLast);
            // $gas  = $this->hitungSelisihBiaya($gasNow,  $gasLast);
            // $tel  = $this->hitungSelisihBiaya($telNow,  $telLast);

            // // ================= RETUR =================
            // $returNow = (int) collect($returNowAll[$apiId] ?? [])
            //     ->sum(fn($r) => $this->toInt($r['tot_hrg'] ?? 0));

            // $returLast = (int) collect($returLastAll[$apiId] ?? [])
            //     ->sum(fn($r) => $this->toInt($r['tot_hrg'] ?? 0));

            // $returRp = $returNow - $returLast;

            // // % retur = selisih retur / penjualanNow
            // $returPct = $penjualanNow > 0 ? round(($returRp / $penjualanNow) * 100, 2) : null;

            // selisih Rp vs bulan lalu
            $discRp = $this->hitungSelisihBiaya($discNow, $discLast);
            $gasRp  = $this->hitungSelisihBiaya($gasNow,  $gasLast);
            $telRp  = $this->hitungSelisihBiaya($telNow,  $telLast);

            // % semua kolom = RP / penjualanNow
            $discPct = $this->pctDariPenjualan($discRp, $penjualanNow);
            $gasPct  = $this->pctDariPenjualan($gasRp,  $penjualanNow);
            $telPct  = $this->pctDariPenjualan($telRp,  $penjualanNow);

            // RETUR
            $returNow  = (int) collect($returNowAll[$apiId] ?? [])->sum(fn($r) => $this->toInt($r['tot_hrg'] ?? 0));
            $returLast = (int) collect($returLastAll[$apiId] ?? [])->sum(fn($r) => $this->toInt($r['tot_hrg'] ?? 0));

            $returRp  = $returNow - $returLast;
            $returPct = $this->pctDariPenjualan($returRp, $penjualanNow);
            // ================= LOSS BAHAN =================
            // LOSS
            $loss = (int) ($lossMap[$tokoId] ?? 0);

            $grand += $penjualanNow;

            // total kontribusi: kamu minta (+disc +gas +tel) - loss
            // (retur mau kamu masukkan? kalau iya, biasanya retur mengurangi kontribusi -> -returRp)
            $totalKontribusi = $k['kontribusi_rp'] + $discRp + $gasRp + $telRp - $loss - $returRp;

            $rows[] = [
                'wilayah_label' => $wilayahLabel,
                'area_label' => $tokoDb?->area?->nama_area ?: '-',
                'area_pic'   => $picArea, // bisa "panji, retno" atau ''
                'area_pic' => $pic,
                'outlet' => $outlet,
                'selisih_rp'     => $selisihRp,
                'selisih_persen' => is_null($selisihPct) ? '-' : $selisihPct . '%',

                'hpp_ratio'     => $k['hpp_ratio'],
                'hpp_selisih'   => $k['hpp_selisih'],
                'kontribusi_rp' => $k['kontribusi_rp'],

                // 'sc_manual_persen' => $disc['pct'],
                // 'sc_manual_rp'     => $disc['rp'],

                // 'retur_persen' => is_null($returPct) ? '-' : $returPct . '%',
                // 'retur_rp'     => $returRp,

                // 'gas_persen' => $gas['pct'],
                // 'gas_rp'     => $gas['rp'],

                // 'telur_persen' => $tel['pct'],
                // 'telur_rp'     => $tel['rp'],

                'sc_manual_persen' => is_null($discPct) ? '-' : ($discPct . '%'),
                'sc_manual_rp'     => $discRp,

                'retur_persen' => is_null($returPct) ? '-' : ($returPct . '%'),
                'retur_rp'     => $returRp,

                'gas_persen' => is_null($gasPct) ? '-' : ($gasPct . '%'),
                'gas_rp'     => $gasRp,

                'telur_persen' => is_null($telPct) ? '-' : ($telPct . '%'),
                'telur_rp'     => $telRp,
                'loss_bahan' => $loss,

                'total_kontribusi' => $totalKontribusi,
            ];
        }
        $rows = collect($rows)
            ->sort(function ($a, $b) {
                $w = strcasecmp($a['wilayah_label'] ?? '', $b['wilayah_label'] ?? '');
                if ($w !== 0) return $w;

                $area = strcasecmp($a['area_label'] ?? '', $b['area_label'] ?? '');
                if ($area !== 0) return $area;

                return strcasecmp($a['outlet'] ?? '', $b['outlet'] ?? '');
            })
            ->values()
            ->all();
        $totalsByWilayah = collect($rows)
            ->groupBy(fn($r) => $r['wilayah_label'] ?? '-')
            ->map(function ($g) {
                return [
                    'selisih_rp'        => $g->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
                    'kontribusi_rp'     => $g->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
                    'sc_manual_rp'      => $g->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
                    'retur_rp'          => $g->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
                    'gas_rp'            => $g->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
                    'telur_rp'          => $g->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
                    'loss_bahan'        => $g->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
                    'total_kontribusi'  => $g->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
                ];
            })
            ->toArray();

        $grandTotals = [
            'selisih_rp'       => collect($rows)->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
            'kontribusi_rp'    => collect($rows)->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
            'sc_manual_rp'     => collect($rows)->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
            'retur_rp'         => collect($rows)->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
            'gas_rp'           => collect($rows)->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
            'telur_rp'         => collect($rows)->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
            'loss_bahan'       => collect($rows)->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
            'total_kontribusi' => collect($rows)->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
        ];
        $this->rowsBulanLalu    = $rows;
        $this->totalsByWilayah  = $totalsByWilayah;
        $this->grandTotals      = $grandTotals;
        $this->sumNetoBulanLalu = $grand; // punya kamu
    }


    public function resetBulanLalu()
    {
        $this->rowsBulanLalu = [];
        $this->sumNetoBulanLalu = 0;

        $this->periodeAwal  = now()->toDateString();
        $this->periodeAkhir = now()->toDateString();

        $this->syncBulanLaluRange();
    }

    public function render()
    {
        return view('livewire.operasional.kontribusi-bulan-lalu');
    }
}
