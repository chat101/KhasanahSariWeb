<?php

namespace App\Livewire\Produksi\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\produksi\ProduksiMingguanExport;

class ProduksiMingguan extends Component
{
    /** 📅 Rentang tanggal */
    public string $tanggalAwal;
    public string $tanggalAkhir;

    /** 🧭 UI state */
    public string $activeTab = 'browniscake';

    /** 📊 Data per tab */
    public array $brownisCakeData = [];
    public array $kukerData = [];
    public array $complainData = [];

    /** 📋 Jenis produk */
    private array $mapBrownisCake = ['Brownis', 'Bolu', 'Bolu Bulat', 'Bolu Gulung', 'Cake'];
    private array $mapKuker = ['Roker'];

    public function mount(): void
    {
        $today = now()->toDateString();
        $this->tanggalAwal = $today;
        $this->tanggalAkhir = $today;
        $this->loadData();
    }

    /** 🔄 Livewire v3 event universal */
    public function updated($property): void
    {
        if (in_array($property, ['tanggalAwal', 'tanggalAkhir'])) {
            $this->loadData();
        }
        // dd('updated() terpanggil', $property, $this->tanggalAwal, $this->tanggalAkhir);
    }

    /** 🔍 Fungsi utama untuk memuat data */
    public function loadData(): void
    {
        // Pastikan tanggal valid dan berformat sama
        $awal = Carbon::parse($this->tanggalAwal)->format('Y-m-d');
        $akhir = Carbon::parse($this->tanggalAkhir)->format('Y-m-d');

        // Jika user menukar urutan, otomatis dibalik
        if (Carbon::parse($awal)->gt(Carbon::parse($akhir))) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        /** ---------- SUBQUERY STOK ---------- */
        $stokAwalSub = DB::table('stok_rekap_harian as s')
            ->selectRaw(
                's.mproducts_id,
                 COALESCE(
                   MAX(CASE WHEN s.tanggal = ?  THEN s.stok_awal END),
                   MAX(CASE WHEN s.tanggal < ? THEN COALESCE(s.stok_akhir, s.stok_awal + s.masuk_hari - s.keluar_hari) END)
                 ) AS stok_awal_periode',
                [$awal, $awal],
            )
            ->groupBy('s.mproducts_id');

        $stokAkhirSub = DB::table('stok_rekap_harian as s')
            ->selectRaw(
                's.mproducts_id,
                 COALESCE(
                   MAX(CASE WHEN s.tanggal = ?  THEN COALESCE(s.stok_akhir, s.stok_awal + s.masuk_hari - s.keluar_hari) END),
                   MAX(CASE WHEN s.tanggal <= ? THEN COALESCE(s.stok_akhir, s.stok_awal + s.masuk_hari - s.keluar_hari) END)
                 ) AS stok_akhir_periode',
                [$akhir, $akhir],
            )
            ->groupBy('s.mproducts_id');

        /** ---------- SUBQUERY REALISASI, TARGET, TAMBAHAN, PENGURANGAN ---------- */
        $realSub = DB::table('hasil_divisi as hd')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hd.perintah_produksi_id')
            ->where('hd.divisi_id', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('hd.mproducts_id, COALESCE(SUM(hd.qty_hasil),0) as real_total')
            ->groupBy('hd.mproducts_id');

        $targetDetailSub = DB::table('detail_perintah_produksi as dpp')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'dpp.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                dpp.mproducts_id,
                COALESCE(SUM(dpp.target_produksi),0) as target_detail,
                COALESCE(SUM(dpp.produksi_qty),0) as qty_detail
            ')
            ->groupBy('dpp.mproducts_id');

        $targetTambahanSub = DB::table('produksi_tambahan as pt')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pt.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                pt.mproducts_id,
                COALESCE(SUM(pt.target_qty_tambahan),0) as target_tambahan,
                COALESCE(SUM(pt.qty_tambahan),0) as qty_tambahan
            ')
            ->groupBy('pt.mproducts_id');

        $targetPenguranganSub = DB::table('produksi_pengurangan as pg')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pg.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                pg.mproducts_id,
                COALESCE(SUM(pg.target_qty_pengurangan),0) as target_pengurangan,
                COALESCE(SUM(pg.qty_pengurangan),0) as qty_pengurangan
            ')
            ->groupBy('pg.mproducts_id');

        /** ---------- SUBQUERY PENGALIHAN & REJECT ---------- */
        $pengalihanSub = DB::table('produksi_pengalihan_items as ppi')
            ->join('produksi_pengalihan as ppn', 'ppn.id', '=', 'ppi.pengalihan_id')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'ppn.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                ppi.target_mproducts_id as mproducts_id,
                COALESCE(SUM(ppi.qty_pcs), 0) as po_pengalihan
            ')
            ->groupBy('ppi.target_mproducts_id');

        $rejectJadiSub = DB::table('hasil_reject as hr')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hr.perintah_produksi_id')
            ->where('hr.divisi_id', '<>', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('hr.mproducts_id, COALESCE(SUM(hr.qty_reject), 0) as returjadi')
            ->groupBy('hr.mproducts_id');

        $rejectProduksiSub = DB::table('hasil_reject as hr')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hr.perintah_produksi_id')
            ->where('hr.divisi_id', '=', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('hr.mproducts_id, COALESCE(SUM(hr.qty_reject), 0) as returproduksi')
            ->groupBy('hr.mproducts_id');

        /** ---------- SUBQUERY HASIL PRODUKSI ---------- */
        $hasilProduksiSub = DB::table('hasil_produksi as hp')
        ->join('perintah_produksi as pp', 'pp.id', '=', 'hp.perintah_produksi_id')
        ->whereBetween('pp.tanggal_perintah', [$awal, $akhir]) // ✅ ambil tanggal dari tabel perintah_produksi
        ->selectRaw('
            hp.mproducts_id,
            COALESCE(SUM(hp.sblm_complain), 0) as sblm_complain,
            COALESCE(SUM(hp.complain), 0) as complain,
            COALESCE(SUM(hp.penjualan_pabrik), 0) as penjualan_pabrik,
            COALESCE(SUM(hp.lain_lain), 0) as lain_lain,
            COALESCE(SUM(hp.sample), 0) as sample
        ')
        ->groupBy('hp.mproducts_id');

        /** ---------- QUERY UTAMA ---------- */
        $allRows = DB::table('mproducts as mp')
            ->leftJoinSub($realSub, 'R', 'R.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($targetDetailSub, 'TD', 'TD.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($targetTambahanSub, 'TT', 'TT.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($targetPenguranganSub, 'TPG', 'TPG.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($stokAwalSub, 'SA', 'SA.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($stokAkhirSub, 'SE', 'SE.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($pengalihanSub, 'PPN', 'PPN.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($rejectJadiSub, 'RJ', 'RJ.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($rejectProduksiSub, 'RP', 'RP.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($hasilProduksiSub, 'HP', 'HP.mproducts_id', '=', 'mp.id')
            ->selectRaw('
                mp.id, mp.nama, mp.jenis, mp.hpp_produk, mp.patokan,
                COALESCE(R.real_total,0) as real_total,
                COALESCE(PPN.po_pengalihan,0) as po_pengalihan,
                COALESCE(RJ.returjadi, 0) as returjadi,
                COALESCE(RP.returproduksi, 0) as returproduksi,
                COALESCE(HP.sblm_complain, 0) as sblm_complain,
                COALESCE(HP.complain, 0) as complain,
                COALESCE(HP.sample, 0) as sample,
                (COALESCE(TD.target_detail,0)
                 + COALESCE(TT.target_tambahan,0)
                 - COALESCE(TPG.target_pengurangan,0)) as total_target_produksi,
                (COALESCE(TD.qty_detail,0)
                 + COALESCE(TT.qty_tambahan,0)
                 - COALESCE(TPG.qty_pengurangan,0)) as total_qty_produksi,
                COALESCE(SA.stok_awal_periode,  0) as stok_awal_periode,
                COALESCE(SE.stok_akhir_periode, 0) as stok_akhir_periode
            ')
            ->get();

        /** ---------- FORMAT AKHIR ---------- */
        $formatted = $allRows->map(function ($r) {
            $target = (int) round((float) $r->total_target_produksi);
            $qty = (int) round((float) $r->total_qty_produksi);
            $real_total = (float) $r->real_total;
            $targetvsreal = $real_total - $target;
            $realvsdistribusi = $real_total - ((float) $r->sblm_complain);
            $totalretur = (float) $r->returjadi + (float) $r->returproduksi;
            $rupiah_hpp = (float) $r->hpp_produk *   $totalretur;
            $persenretur = $real_total > 0 ? ($totalretur / $real_total) * 100 : 0;

            return [
                'id' => (int) $r->id,
                'nama' => $r->nama,
                'jenis' => $r->jenis,
                'patokan' => $r->patokan,
                'hpp' =>  $rupiah_hpp,
                'total_qty' => $qty,
                'real_total' => $real_total,
                'total_target_produksi' => $target,
                'po_pengalihan' => (float) $r->po_pengalihan,
                'returjadi' => (float) $r->returjadi,
                'returproduksi' => (float) $r->returproduksi,
                'totalretur' => $totalretur,
                'target_vs_real' => $targetvsreal,
                'percent_target_vs_real' => $target > 0 ? ($targetvsreal / $target) * 100 : 0,
                'stok_awal_periode' => (float) $r->stok_awal_periode,
                'stok_akhir_periode' => (float) $r->stok_akhir_periode,
                'dist' => (float) $r->sblm_complain,
                'complain' => (float) $r->complain,
                'sample' => (float) $r->sample,
                'realvsdist' => $realvsdistribusi,
                'persenretur' => $persenretur,
            ];
        });

        /** ---------- PEMISAHAN JENIS ---------- */
        $nonCake = $formatted->filter(fn($r) => in_array($r['jenis'], $this->mapBrownisCake) && $r['jenis'] !== 'Cake');
        $cake = $formatted->filter(fn($r) => $r['jenis'] === 'Cake');

        /** ---------- SUBTOTAL & GRANDTOTAL ---------- */
        $makeSubtotal = function ($items, string $label) {
            $sum = fn($key) => (float) $items->sum($key);

            $sum_target = $sum('total_target_produksi');
            $sum_real   = $sum('real_total');

            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => $sum('patokan'),
                'total_qty' => $sum('total_qty'),
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'target_vs_real' => $sum_real - $sum_target,
                'percent_target_vs_real' => $sum_target > 0 ? (($sum_real - $sum_target) / $sum_target) * 100 : 0,

                'po_pengalihan' => $sum('po_pengalihan'),
                'returjadi' => $sum('returjadi'),
                'returproduksi' => $sum('returproduksi'),
                'totalretur' => $sum('totalretur'),

                // 🔥 PERHITUNGAN PERSENRETUR YANG BENAR
                'persenretur' => $sum_real > 0
                    ? ($sum('totalretur') / $sum_real) * 100
                    : 0,

                'dist' => $sum('dist'),
                'complain' => $sum('complain'),
                'sample' => $sum('sample'),
                'hpp' => $sum('hpp'),
                'realvsdist' => $sum('realvsdist'),

                'is_subtotal' => str_starts_with($label, 'Subtotal'),
                'is_grandtotal' => $label === 'GRAND TOTAL',
            ];
        };
        $subtotalNonCake = $makeSubtotal($nonCake, 'Subtotal NON-CAKE');
        $subtotalCake = $makeSubtotal($cake, 'Subtotal CAKE');
        $grandTotal = $makeSubtotal($nonCake->merge($cake), 'GRAND TOTAL');

        $this->brownisCakeData = $nonCake
            ->push($subtotalNonCake)
            ->merge($cake)
            ->push($subtotalCake)
            ->push($grandTotal)
            ->values()
            ->toArray();

        /** ---------- DATA KUKER ---------- */
        $kuker = $formatted
            ->whereIn('jenis', $this->mapKuker)
            ->map(fn($r) => [
                'id' => $r['id'],
                'nama' => $r['nama'],
                'jenis' => $r['jenis'],
                'patokan' => $r['patokan'],
                'total_qty' => $r['total_qty'],
                'totalretur' => $r['totalretur'],
                'total_target_produksi' => $r['total_target_produksi'],
                'real_total' => $r['real_total'],
                'targetvsrealroker' => (float) $r['total_target_produksi'] - (float) $r['real_total'],
                'stok_awal_periode' => $r['stok_awal_periode'],
                'stok_akhir_periode' => $r['stok_akhir_periode'],
                'dist' => $r['dist'],
                'complain' => $r['complain'],
                'sample' => $r['sample'],
            ])
            ->values();

        $makeTotalKuker = function ($items, string $label) {
            $sum = fn($k) => (float) $items->sum($k);
            $sum_target = $sum('total_target_produksi');
            $sum_real = $sum('real_total');
            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => $sum('patokan'),
                'total_qty' => $sum('total_qty'),
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'targetvsrealroker' => $sum_target - $sum_real,
                'totalretur' => $sum('totalretur'),
                'dist' => $sum('dist'),
                'complain' => $sum('complain'),
                'sample' => $sum('sample'),
                'stok_awal_periode' => $sum('stok_awal_periode'),
                'stok_akhir_periode' => $sum('stok_akhir_periode'),
                'is_total' => true,
            ];
        };

        $totalKuker = $makeTotalKuker($kuker, 'TOTAL KUKER');
        $this->kukerData = $kuker->push($totalKuker)->values()->toArray();
    }

    public function exportActive()
    {
        $this->loadData();
        return Excel::download(
            new ProduksiMingguanExport($this->brownisCakeData, $this->kukerData, $this->tanggalAwal, $this->tanggalAkhir),
            'laporan_produksi_' . $this->tanggalAwal . '_' . $this->tanggalAkhir . '.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.produksi.laporan.produksi-mingguan');
    }
}
