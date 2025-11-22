<?php

namespace App\Livewire\Produksi\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\MasterProduct;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\produksi\BrownisCakeExport;
use App\Exports\produksi\KukerExport;
use App\Exports\produksi\ProduksiMingguanExport;

class ProduksiMingguan extends Component
{
    // Date range
    public string $tanggalAwal;
    public string $tanggalAkhir;
    // UI state
    public string $activeTab = 'browniscake'; // <-- TAMBAHKAN INI

    // Data per tab
    public array $brownisCakeData = [];
    public array $kukerData = [];
    public array $complainData = []; // siapkan kalau nanti ada tabel complain

    // List jenis yang dipakai memetakan tab
    private array $mapBrownisCake = ['Brownis', 'Bolu', 'Bolu Bulat', 'Bolu Gulung', 'Cake'];
    private array $mapKuker = ['Roker']; // sesuaikan dgn field "jenis" Anda

    public function mount(): void
    {
        $today = now()->toDateString();
        $this->tanggalAwal = $today;
        $this->tanggalAkhir = $today;

        $this->loadData();
    }

    // Dipanggil saat tanggal berubah
    public function updatedTanggalAwal(): void
    {
        $this->loadData();
    }
    public function updatedTanggalAkhir(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        if ($this->tanggalAwal > $this->tanggalAkhir) {
            [$this->tanggalAwal, $this->tanggalAkhir] = [$this->tanggalAkhir, $this->tanggalAwal];
        }

        $awal = $this->tanggalAwal;
        $akhir = $this->tanggalAkhir;

        /** --- Subquery stok awal periode ($awal) --- */
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

        /** --- Subquery stok akhir periode ($akhir) --- */
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

        /** --- Subquery realisasi hasil_divisi (divisi_id = 2) --- */
        $realSub = DB::table('hasil_divisi as hd')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hd.perintah_produksi_id')
            ->where('hd.divisi_id', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                '
                hd.mproducts_id,
                COALESCE(SUM(hd.qty_hasil),0) as real_total
            ',
            )
            ->groupBy('hd.mproducts_id');

        /** --- Subquery target detail --- */
        $targetDetailSub = DB::table('detail_perintah_produksi as dpp')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'dpp.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                '
                dpp.mproducts_id,
                COALESCE(SUM(dpp.target_produksi),0) as target_detail,
                COALESCE(SUM(dpp.produksi_qty),0) as qty_detail
            ',
            )
            ->groupBy('dpp.mproducts_id');

        /** --- Subquery target tambahan --- */
        $targetTambahanSub = DB::table('produksi_tambahan as pt')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pt.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                '
                pt.mproducts_id,
                COALESCE(SUM(pt.target_qty_tambahan),0) as target_tambahan,
                COALESCE(SUM(pt.qty_tambahan),0) as qty_tambahan
            ',
            )
            ->groupBy('pt.mproducts_id');

        /** --- Subquery target pengurangan --- */
        $targetPenguranganSub = DB::table('produksi_pengurangan as pg')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pg.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                '
                pg.mproducts_id,
                COALESCE(SUM(pg.target_qty_pengurangan),0) as target_pengurangan,
                COALESCE(SUM(pg.qty_pengurangan),0) as qty_pengurangan
            ',
            )
            ->groupBy('pg.mproducts_id');
        /** --- Subquery pengalihan (qty_pcs) --- */
        $pengalihanSub = DB::table('produksi_pengalihan_items as ppi')
            ->join('produksi_pengalihan as ppn', 'ppn.id', '=', 'ppi.pengalihan_id')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'ppn.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                '
                    ppi.target_mproducts_id as mproducts_id,
                    COALESCE(SUM(ppi.qty_pcs), 0) as po_pengalihan
                ',
            )
            ->groupBy('ppi.target_mproducts_id');
                /** --- Subquery hasil_reject (qty_reject) --- */
        /** --- Subquery hasil_reject (divisi_id ≠ 2) → returjadi --- */
        $rejectJadiSub = DB::table('hasil_reject as hr')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hr.perintah_produksi_id')
            ->where('hr.divisi_id', '<>', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                hr.mproducts_id,
                COALESCE(SUM(hr.qty_reject), 0) as returjadi
            ')
            ->groupBy('hr.mproducts_id');

        /** --- Subquery hasil_reject (divisi_id = 2) → returproduksi --- */
        $rejectProduksiSub = DB::table('hasil_reject as hr')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hr.perintah_produksi_id')
            ->where('hr.divisi_id', '=', 2)
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw('
                hr.mproducts_id,
                COALESCE(SUM(hr.qty_reject), 0) as returproduksi
            ')
            ->groupBy('hr.mproducts_id');

                /** --- Subquery hasil_produksi (ambil kolom sblm_complain) --- */
                $hasilProduksiSub = DB::table('hasil_produksi as hp')
                ->selectRaw('
                    hp.mproducts_id,
                    COALESCE(SUM(hp.sblm_complain), 0) as sblm_complain,
                    COALESCE(SUM(hp.complain), 0) as complain,
                    COALESCE(SUM(hp.penjualan_pabrik), 0) as penjualan_pabrik,
                    COALESCE(SUM(hp.lain_lain), 0) as lain_lain,
                         COALESCE(SUM(hp.sample), 0) as sample
                ')
                ->groupBy('hp.mproducts_id');

        /** --- Query utama --- */
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
            ->leftJoinSub($hasilProduksiSub, 'HP', 'HP.mproducts_id', '=', 'mp.id') // 🔹 Tambahkan di sini
            ->selectRaw(
                '
                mp.id,
                mp.nama,
                mp.jenis,
                mp.hpp_produk,
                mp.patokan,

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
            ',
            )
            ->get();

        /** --- Format hasil akhir --- */
        $formatted = $allRows->map(function ($r) {
            $target = (int) round((float) $r->total_target_produksi);
            $qty = (int) round((float) $r->total_qty_produksi);
            $real_total = (float) $r->real_total;
            $targetvsreal = $real_total - $target;
            $realvsdistribusi = $real_total - ((float) $r->sblm_complain);

            return [
                'id' => (int) $r->id,
                'nama' => $r->nama,
                'jenis' => $r->jenis,
                'patokan' => $r->patokan,
                'hpp' => $r->hpp_produk,
                'total_qty' => $qty,
                'real_total' => $real_total,
                'total_target_produksi' => $target,
                'po_pengalihan' => (float) $r->po_pengalihan,
                'returjadi' => (float) $r->returjadi,
                'returproduksi' => (float) $r->returproduksi,
                'totalretur' => (float) $r->returjadi + (float) $r->returproduksi,
                'target_vs_real' => $targetvsreal,
                'percent_target_vs_real' => $target > 0 ? ($targetvsreal / $target) * 100 : 0,
                'stok_awal_periode' => (float) $r->stok_awal_periode,
                'stok_akhir_periode' => (float) $r->stok_akhir_periode,
                'dist' => (float) $r->sblm_complain, // 🔹 Tambahkan agar bisa diakses di Blade
                'complain' => (float) $r->complain, // ✅ kolom baru
                'sample' => (float) $r->sample,
                'realvsdist' => $realvsdistribusi,
            ];
        });

        /** --- Pisahkan jenis produk (Brownis/Bolu/Cake) --- */
        $nonCake = $formatted->filter(fn($r) => in_array($r['jenis'], $this->mapBrownisCake) && $r['jenis'] !== 'Cake');
        $cake = $formatted->filter(fn($r) => $r['jenis'] === 'Cake');

        /** --- Fungsi subtotal Brownis & Cake --- */
        $makeSubtotalBC = function ($items, string $label) {
            $sum = fn($k) => (float) $items->sum($k);
            $sumpatokan = $sum('patokan');
            $sum_target = $sum('total_target_produksi');
            $sum_real = $sum('real_total');
            $sum_po_pengalihan = $sum('po_pengalihan');
            $sum_returjadi = $sum('returjadi');
            $sum_returproduksi = $sum('returproduksi');
            $totalretur = $sum('totalretur');
            $target_vs_real = $sum_real - $sum_target;
            $percent_target_vs_real = $sum_target > 0 ? ($target_vs_real / $sum_target) * 100 : 0;
            $sum_dist = $sum('dist');
            $sum_complain = $sum('complain');
            $sumrealvsdist = $sum('realvsdist');
            $sumhpp = $sum('hpp');


            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => $sumpatokan,
                'total_qty' => $sum('total_qty'),
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'po_pengalihan' => $sum_po_pengalihan,
                'returjadi' => $sum_returjadi,
                'returproduksi' => $sum_returproduksi,
                'totalretur' => $totalretur,
                'target_vs_real' => $target_vs_real,
                'percent_target_vs_real' => $percent_target_vs_real,
                'dist' => $sum_dist,          // ✅ subtotal dist
                'complain' => $sum_complain,  // ✅ subtotal complain
                'sample' => $sum('sample'),
                'hpp' => $sumhpp,
                'realvsdist' => $sumrealvsdist,
                'is_subtotal' => str_starts_with($label, 'Subtotal'),
                'is_grandtotal' => $label === 'GRAND TOTAL',
            ];
        };

        /** --- Hitung subtotal & grand total --- */
        $nonCakeArr = $nonCake->map(fn($r) => (array) $r);
        $cakeArr = $cake->map(fn($r) => (array) $r);
        $subtotalNonCake = $makeSubtotalBC($nonCakeArr, 'Subtotal NON-CAKE');
        $subtotalCake = $makeSubtotalBC($cakeArr, 'Subtotal CAKE');
        $grandTotal = $makeSubtotalBC($nonCakeArr->merge($cakeArr), 'GRAND TOTAL');

        /** --- Susun urutan tampilannya --- */
        $this->brownisCakeData = $nonCakeArr->push($subtotalNonCake)->merge($cakeArr)->push($subtotalCake)->push($grandTotal)->values()->toArray();

        /** --- Data KUKER --- */
        $kuker = $formatted
        ->whereIn('jenis', $this->mapKuker)
        ->map(
            fn($r) => [
                'id' => $r['id'],
                'nama' => $r['nama'],
                'jenis' => $r['jenis'],
                'patokan' => $r['patokan'],
                'total_qty' => $r['total_qty'],
                'totalretur' => $r['totalretur'],
                'total_target_produksi' => $r['total_target_produksi'],
                'real_total' => $r['real_total'],
                'targetvsrealroker' => (float) $r['total_target_produksi'] - (float) $r['real_total'], // ✅ Tambahan baru
                'stok_awal_periode' => $r['stok_awal_periode'],
                'stok_akhir_periode' => $r['stok_akhir_periode'],
                'dist' => $r['dist'],
                'complain' => $r['complain'], // ✅ kolom baru
                'sample' => $r['sample'],
            ],
        )
        ->values();

        $makeTotalKuker = function ($items, string $label) {
            $sum = fn($k) => (float) $items->sum($k);
            $sumpatokan = $sum('patokan');
            $sum_target = $sum('total_target_produksi');
            $sum_real = $sum('real_total');
            $sum_targetvsrealroker = $sum('targetvsrealroker'); // ✅ Tambahan
            $sum_po_pengalihan = $sum('po_pengalihan');
            $sum_returjadi = $sum('returjadi');
            $sum_returproduksi = $sum('returproduksi');
            $sum_totalretur = $sum('totalretur');
            $sum_dist = $sum('dist');
            $sumcomplain = $sum('complain');
            $target_vs_real = $sum_real - $sum_target;
            $percent_target_vs_real = $sum_target > 0 ? ($target_vs_real / $sum_target) * 100 : 0;

            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => '',
                'total_qty' => $sum('total_qty'),
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'targetvsrealroker' => $sum_targetvsrealroker, // ✅ Tambahkan di hasil total
                'po_pengalihan' => $sum_po_pengalihan,
                'returjadi' => $sum_returjadi,
                'returproduksi' => $sum_returproduksi,
                'totalretur' => $sum_totalretur,
                'target_vs_real' => $target_vs_real,
                'percent_target_vs_real' => $percent_target_vs_real,
                'stok_awal_periode' => $sum('stok_awal_periode'),
                'stok_akhir_periode' => $sum('stok_akhir_periode'),
                'is_total' => true,
                'dist' => $sum_dist,
                'complain' => $sumcomplain,
                'sample' => $sum('sample'),
                'patokan' => $sumpatokan,
            ];
        };




        $totalKuker = $makeTotalKuker($kuker, 'TOTAL KUKER');
        $this->kukerData = $kuker->push($totalKuker)->values()->toArray();
    }

    public function render()
    {
        return view('livewire.produksi.laporan.produksi-mingguan');
    }
    public function exportActive()
    {
        // pastikan data sesuai rentang tanggal terkini
        $this->loadData();

        return Excel::download(new ProduksiMingguanExport($this->brownisCakeData ?? [], $this->kukerData ?? [], $this->tanggalAwal, $this->tanggalAkhir), 'laporan_produksi_' . ($this->tanggalAwal ?: 'all') . '_' . ($this->tanggalAkhir ?: 'all') . '.xlsx');
    }
}
