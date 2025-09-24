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
                // stok_awal tepat di $awal, kalau tidak ada → last ending < $awal
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
                // stok_akhir tepat di $akhir, kalau tidak ada → last ending ≤ $akhir
                's.mproducts_id,
         COALESCE(
           MAX(CASE WHEN s.tanggal = ?  THEN COALESCE(s.stok_akhir, s.stok_awal + s.masuk_hari - s.keluar_hari) END),
           MAX(CASE WHEN s.tanggal <= ? THEN COALESCE(s.stok_akhir, s.stok_awal + s.masuk_hari - s.keluar_hari) END)
         ) AS stok_akhir_periode',
                [$akhir, $akhir],
            )
            ->groupBy('s.mproducts_id');
        // --- Subquery realisasi dari hasil_produksi ---
        $realSub = DB::table('hasil_produksi as hp')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'hp.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                'hp.mproducts_id,
                     COALESCE(SUM(hp.sblm_complain),0) as sblm_complain,
                     COALESCE(SUM(hp.po_pengalihan),0) as po_pengalihan,
                     COALESCE(SUM(hp.complain),0) as complain,
                     COALESCE(SUM(hp.real),0)            AS real_total,
                     COALESCE(SUM(hp.retur_produksi),0)            AS retur_produksi,
                     COALESCE(SUM(hp.retur_jadi),0)            AS retur_jadi,
                        COALESCE(SUM(hp.total_retur),0)            AS total_retur,
                        COALESCE(SUM(hp.sample),0)            AS sample',
            )
            ->groupBy('hp.mproducts_id');

        // --- Subquery target detail ---
        $targetDetailSub = DB::table('detail_perintah_produksi as dpp')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'dpp.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                'dpp.mproducts_id,
                     COALESCE(SUM(dpp.target_produksi),0) as target_detail,
                         COALESCE(SUM(dpp.produksi_qty),0) as qty_detail',
            )
            ->groupBy('dpp.mproducts_id');

        // --- Subquery target tambahan ---
        $targetTambahanSub = DB::table('produksi_tambahan as pt')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pt.perintah_produksi_id')
            ->whereBetween('pp.tanggal_perintah', [$awal, $akhir])
            ->selectRaw(
                'pt.mproducts_id,
                     COALESCE(SUM(pt.target_qty_tambahan),0) as target_tambahan,
                       COALESCE(SUM(pt.qty_tambahan),0) as qty_tambahan',
            )
            ->groupBy('pt.mproducts_id');

        // --- Query utama ambil semua produk ---
        $allRows = DB::table('mproducts as mp')
            ->leftJoinSub($realSub, 'R', 'R.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($targetDetailSub, 'TD', 'TD.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($targetTambahanSub, 'TT', 'TT.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($stokAwalSub, 'SA', 'SA.mproducts_id', '=', 'mp.id')
            ->leftJoinSub($stokAkhirSub, 'SE', 'SE.mproducts_id', '=', 'mp.id')
            ->selectRaw(
                '
            mp.id,
            mp.nama,
            mp.jenis,
            mp.hpp_produk,
            mp.patokan,

            COALESCE(R.sblm_complain,0)   as sblm_complain,
            COALESCE(R.po_pengalihan,0)   as po_pengalihan,
            COALESCE(R.complain,0)        as complain,
            COALESCE(R.real_total,0)      as real_total,
            COALESCE(R.retur_produksi,0)  as retur_produksi,
            COALESCE(R.retur_jadi,0)      as retur_jadi,
            COALESCE(R.total_retur,0)     as total_retur,
            COALESCE(R.sample,0)          as sample,

            COALESCE(TD.target_detail,0) + COALESCE(TT.target_tambahan,0) as total_target_produksi,
            COALESCE(TD.qty_detail,0)    + COALESCE(TT.qty_tambahan,0)    as total_qty_produksi,

            COALESCE(SA.stok_awal_periode,  0) as stok_awal_periode,
            COALESCE(SE.stok_akhir_periode, 0) as stok_akhir_periode
        ',
            )
            ->get();

        // --- Format hasil agar konsisten ---
        $formatted = $allRows->map(function ($r) {
            $dist = (float) $r->sblm_complain;
            $realsistem = $dist + (float) $r->po_pengalihan;
            $target = (int) round((float) $r->total_target_produksi);
            $qty = (int) round((float) $r->total_qty_produksi);
            $complain = (float) $r->complain;
            $real_total = (float) $r->real_total;
            $pengalihan = (float) $r->po_pengalihan;
            $returjadi = (float) $r->retur_jadi;
            $returproduksi = (float) $r->retur_produksi;
            $totalretur = (float) $r->total_retur;
            $sample = (float) $r->sample;
            $persenretur = $real_total > 0 ? ($totalretur / $real_total) * 100 : 0;
            $realvsdist = $dist + $complain + $totalretur - ($real_total + $pengalihan);
            $targetvsrealroker = $real_total + $totalretur + $sample - $target;
            $realvssistemroker = (float) $r->stok_akhir_periode - $complain;

            return [
                'id' => (int) $r->id,
                'nama' => $r->nama,
                'jenis' => $r->jenis,
                'patokan' => $r->patokan,
                'hpp' => $r->hpp_produk * $totalretur,
                'dist' => $dist,
                'total_qty' => $qty,
                'real_total' => $real_total,
                'returjadi' => $returjadi,
                'returproduksi' => $returproduksi,
                'complain' => $complain,
                'sample' => $sample,
                'totalretur' => $totalretur,
                'realvsdist' => $realvsdist,
                'po_pengalihan' => $pengalihan,
                'total_target_produksi' => $target,
                'target_vs_real' => $real_total + $pengalihan - $target,
                'percent_target_vs_real' => $target > 0 ? (($realsistem - $target) / $target) * 100 : 0,
                'persenretur' => $persenretur,
                'targetvsrealroker' => $targetvsrealroker,
                'stok_awal_periode' => (float) $r->stok_awal_periode,
                'stok_akhir_periode' => (float) $r->stok_akhir_periode,
                'realvssistemroker' => $realvssistemroker,
            ];
        });
        // --- Pisahkan data CAKE dan non-CAKE dari brownis & bolu
        $nonCake = $formatted->filter(fn($r) => in_array($r['jenis'], $this->mapBrownisCake) && $r['jenis'] !== 'Cake');
        $cake = $formatted->filter(fn($r) => $r['jenis'] === 'Cake');

        // Konversi semua item jadi array agar Blade bisa pakai $row['xxx']
        $nonCakeArr = $nonCake->map(fn($r) => (array) $r);
        $cakeArr = $cake->map(fn($r) => (array) $r);

        // --- Subtotal NON-CAKE
        // $subtotalNonCake = [
        //     'nama' => 'Subtotal NON-CAKE',
        //     'jenis' => '',
        //     'patokan' => '',
        //     'total_qty' => $nonCakeArr->sum('total_qty'),
        //     'real_total' => $nonCakeArr->sum('real_total'),
        //     'totalretur' => $nonCakeArr->sum('totalretur'),
        //     'complain' => $nonCakeArr->sum('complain'),
        //     'dist' => $nonCakeArr->sum('dist'),
        //     'hpp' => $nonCakeArr->sum('hpp'),
        //     'is_subtotal' => true,
        // ];

        // // --- Subtotal CAKE
        // $subtotalCake = [
        //     'nama' => 'Subtotal CAKE',
        //     'jenis' => '',
        //     'patokan' => '',
        //     'total_qty' => $cakeArr->sum('total_qty'),
        //     'real_total' => $cakeArr->sum('real_total'),
        //     'totalretur' => $cakeArr->sum('totalretur'),
        //     'complain' => $cakeArr->sum('complain'),
        //     'dist' => $cakeArr->sum('dist'),
        //     'hpp' => $cakeArr->sum('hpp'),
        //     'is_subtotal' => true,
        // ];

        // // --- Grand Total
        // $grandTotal = [
        //     'nama' => 'GRAND TOTAL',
        //     'jenis' => '',
        //     'patokan' => '',
        //     'total_qty' => $formatted->sum('total_qty'),
        //     'real_total' => $formatted->sum('real_total'),
        //     'totalretur' => $formatted->sum('totalretur'),
        //     'complain' => $formatted->sum('complain'),
        //     'dist' => $formatted->sum('dist'),
        //     'hpp' => $formatted->sum('hpp'),
        //     'is_grandtotal' => true,
        // ];

        // // --- Gabungkan semua
        // $this->brownisCakeData = $nonCakeArr->push($subtotalNonCake)->merge($cakeArr)->push($subtotalCake)->push($grandTotal)->values()->toArray();
        // --- helper subtotal Brownis & Cake (pakai angka subtotal untuk kolom turunan)
        $makeSubtotalBC = function ($items, string $label) {
            $sum = fn($k) => (float) $items->sum($k);

            $sum_qty = $sum('total_qty');
            $sum_target = $sum('total_target_produksi');
            $sum_real = $sum('real_total');
            $sum_pengalihan = $sum('po_pengalihan');
            $sum_dist = $sum('dist');
            $sum_complain = $sum('complain');
            $sum_ret_prod = $sum('returproduksi');
            $sum_ret_jadi = $sum('returjadi');
            $sum_totalretur = $sum('totalretur');
            $sum_hpp = $sum('hpp');

            // turunan dihitung dari subtotal
            $target_vs_real = $sum_real + $sum_pengalihan - $sum_target;
            $percent_target_vs_real = $sum_target > 0 ? (($sum_dist + $sum_pengalihan - $sum_target) / $sum_target) * 100 : 0;
            $realvsdist = $sum_dist + $sum_complain + $sum_totalretur - ($sum_real + $sum_pengalihan);
            $persenretur = $sum_real > 0 ? ($sum_totalretur / $sum_real) * 100 : 0;

            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => '',
                'total_qty' => $sum_qty,
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'po_pengalihan' => $sum_pengalihan,
                'target_vs_real' => $target_vs_real,
                'percent_target_vs_real' => $percent_target_vs_real,
                'dist' => $sum_dist,
                'complain' => $sum_complain,
                'realvsdist' => $realvsdist,
                'returproduksi' => $sum_ret_prod,
                'returjadi' => $sum_ret_jadi,
                'totalretur' => $sum_totalretur,
                'hpp' => $sum_hpp,
                'persenretur' => $persenretur,
                'is_subtotal' => str_starts_with($label, 'Subtotal'),
                'is_grandtotal' => $label === 'GRAND TOTAL',
            ];
        };

        // ---- KONVERSI KE ARRAY
        $nonCakeArr = $nonCake->map(fn($r) => (array) $r);
        $cakeArr = $cake->map(fn($r) => (array) $r);

        // ---- SUBTOTAL & GRAND TOTAL (Brownis & Cake)
        $subtotalNonCake = $makeSubtotalBC($nonCakeArr, 'Subtotal NON-CAKE');
        $subtotalCake = $makeSubtotalBC($cakeArr, 'Subtotal CAKE');
        $grandTotal = $makeSubtotalBC($nonCakeArr->merge($cakeArr), 'GRAND TOTAL');

        // ---- SUSUN URUTAN UNTUK TABEL Brownis & Cake
        $this->brownisCakeData = $nonCakeArr->push($subtotalNonCake)->merge($cakeArr)->push($subtotalCake)->push($grandTotal)->values()->toArray();

        $kuker = $formatted
            ->whereIn('jenis', $this->mapKuker)
            ->map(function ($r) {
                return [
                    'id' => $r['id'] ?? null,
                    'nama' => $r['nama'] ?? '',
                    'jenis' => $r['jenis'] ?? '',
                    'patokan' => $r['patokan'] ?? '',
                    'total_qty' => $r['total_qty'] ?? 0,
                    'total_target_produksi' => $r['total_target_produksi'] ?? 0,
                    'real_total' => $r['real_total'] ?? 0,
                    'totalretur' => $r['totalretur'] ?? 0,
                    'complain' => $r['complain'] ?? 0,
                    'sample' => $r['sample'] ?? 0,
                    'targetvsrealroker' => $r['targetvsrealroker'] ?? 0,
                    'stok_awal_periode' => $r['stok_awal_periode'] ?? 0,
                    'dist' => $r['dist'] ?? 0,
                    'stok_akhir_periode' => $r['stok_akhir_periode'] ?? 0,
                    'realvssistemroker' => $r['realvssistemroker'] ?? 0,
                ];
            })
            ->values();
        // --- helper TOTAL KUKER (turunan dihitung dari subtotal)
        $makeTotalKuker = function ($items, string $label) {
            $sum = fn($k) => (float) $items->sum($k);

            $sum_qty = $sum('total_qty');
            $sum_target = $sum('total_target_produksi');
            $sum_real = $sum('real_total');
            $sum_totalretur = $sum('totalretur');
            $sum_sample = $sum('sample');
            $sum_stok_awal = $sum('stok_awal_periode');
            $sum_stok_akhir = $sum('stok_akhir_periode');
            $sum_dist = $sum('dist');
            $sum_complain = $sum('complain');

            // turunan dari subtotal:
            // definisi yang kamu pakai di per-row:
            // targetvsrealroker = real_total + totalretur + sample - target
            $targetvsrealroker = $sum_real + $sum_totalretur + $sum_sample - $sum_target;

            // realvssistemroker = stok_akhir_periode - complain  (pakai subtotalnya)
            $realvssistemroker = $sum_stok_akhir - $sum_complain;

            return [
                'nama' => $label,
                'jenis' => '',
                'patokan' => '',
                'total_qty' => $sum_qty,
                'total_target_produksi' => $sum_target,
                'real_total' => $sum_real,
                'totalretur' => $sum_totalretur,
                'complain' => $sum_complain,
                'sample' => $sum_sample,
                'targetvsrealroker' => $targetvsrealroker,
                'stok_awal_periode' => $sum_stok_awal,
                'dist' => $sum_dist,
                'stok_akhir_periode' => $sum_stok_akhir,
                'realvssistemroker' => $realvssistemroker,
                'is_total' => true,
            ];
        };

        $totalKuker = $makeTotalKuker($kuker, 'TOTAL KUKER');

        $this->kukerData = $kuker->push($totalKuker)->values()->toArray();
        // $totalKuker = [
        //     'nama' => 'TOTAL KUKER',
        //     'jenis' => '',
        //     'patokan' => '',
        //     'total_qty' => $kuker->sum('total_qty'),
        //     'total_target_produksi' => $kuker->sum('total_target_produksi'),
        //     'real_total' => $kuker->sum('real_total'),
        //     'totalretur' => $kuker->sum('totalretur'),
        //     'complain' => $kuker->sum('complain'),
        //     'sample' => $kuker->sum('sample'),
        //     'targetvsrealroker' => $kuker->sum('targetvsrealroker'),
        //     'stok_awal_periode' => $kuker->sum('stok_awal_periode'),
        //     'dist' => $kuker->sum('dist'),
        //     'stok_akhir_periode' => $kuker->sum('stok_akhir_periode'),
        //     'realvssistemroker' => $kuker->sum('realvssistemroker'),
        //     'is_total' => true,
        // ];

        // $this->kukerData = $kuker->push($totalKuker)->values()->toArray();
        // $this->complainData = [];
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
// // 1) Ambil total sblm_complain & po_pengalihan per produk di rentang tanggal
// $aggMap = DB::table('hasil_produksi as hp')
//     ->join('perintah_produksi as pp', 'pp.id', '=', 'hp.perintah_produksi_id')
//     ->whereBetween('pp.tanggal_perintah', [$this->tanggalAwal, $this->tanggalAkhir])
//     ->select('hp.mproducts_id', DB::raw('COALESCE(SUM(hp.sblm_complain),0) as total_sblm'), DB::raw('COALESCE(SUM(hp.po_pengalihan),0) as total_pengalihan'))
//     ->groupBy('hp.mproducts_id')
//     ->get()
//     ->keyBy('mproducts_id'); // hasilnya object [mproducts_id => {total_sblm, total_pengalihan}]

// // 2) Ambil produk + detail sesuai rentang tanggal (seperti sebelumnya)
// $produk = MasterProduct::whereHas('detailPerintahProduksi.perintahProduksi', function ($q) {
//     $q->whereBetween('tanggal_perintah', [$this->tanggalAwal, $this->tanggalAkhir]);
// })
//     ->with([
//         'detailPerintahProduksi' => fn($q) => $q->whereHas('perintahProduksi', fn($qq) => $qq->whereBetween('tanggal_perintah', [$this->tanggalAwal, $this->tanggalAkhir])),
//         'produksiTambahan' => fn($q) => $q->whereHas('perintahProduksi', fn($qq) => $qq->whereBetween('tanggal_perintah', [$this->tanggalAwal, $this->tanggalAkhir])),
//     ])
//     ->get();

// // 3) Bentuk rows + merge data tambahan
// $rows = $produk->map(function ($p) use ($aggMap) {

//     // --- REAL dari hasil_produksi (aggMap) ---
//     $agg         = $aggMap->get((int) $p->id);
//     $sblm        = $agg ? (float) $agg->total_sblm       : 0.0;
//     $pengalihan  = $agg ? (float) $agg->total_pengalihan : 0.0;
//     $real        = $sblm + $pengalihan;

//     // --- TARGET dari detail + tambahan (koleksi Eloquent) ---
//     $target_raw = (float) $p->detailPerintahProduksi->sum('target_produksi')
//                + (float) $p->produksiTambahan->sum('target_qty_tambahan');

//     // Jika target memang bilangan bulat, amankan precision:
//     $target = (int) round($target_raw); // <- gunakan SATU variabel ini di bawah

//     // --- TURUNAN: gunakan variabel $target yang sama ---
//     $target_vs_real      = $real - $target;
//     $percent_vs_target   = $target > 0 ? ($real / $target) * 100 : 0;
//     $percent_target_diff = $target > 0 ? ($target_vs_real / $target) * 100 : 0;

//     return [
//         'id'                    => $p->id,
//         'nama'                  => $p->nama,
//         'jenis'                 => $p->jenis,
//         'total_produksi_qty'    => (float) $p->detailPerintahProduksi->sum('produksi_qty')
//                                  + (float) $p->produksiTambahan->sum('qty_tambahan'),

//         // tampilkan target yang SAMA dengan yang dipakai hitung
//         'total_target_produksi' => $target,

//         'sblm_complain'         => $sblm,
//         'po_pengalihan'         => $pengalihan,
//         'target_vs_real'        => $target_vs_real,
//         'percent_vs_target'     => $percent_vs_target,      // real ÷ target × 100
//         'percent_target_vs_real'=> $percent_target_diff,     // (real-target) ÷ target × 100
//     ];
// })->values();

// $this->brownisCakeData = $rows->whereIn('jenis', $this->mapBrownisCake)->values()->toArray();
// $this->kukerData = $rows->whereIn('jenis', $this->mapKuker)->values()->toArray();
// $this->complainData = [];
// dd(  $this->brownisCakeData);
