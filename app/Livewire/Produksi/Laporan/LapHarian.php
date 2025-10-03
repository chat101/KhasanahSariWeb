<?php

namespace App\Livewire\Produksi\Laporan;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LapHarian extends Component
{
    /** Properti input dari UI (datepicker) */
    public string $tanggal = '';

    public function mount(?string $periode = null): void
    {
        // default hari ini
        $this->tanggal = ($periode && trim($periode) !== '')
            ? Carbon::parse($periode)->toDateString()
            : now()->toDateString();
    }

    public function today(): void
    {
        $this->tanggal = now()->toDateString();
    }

    /** Computed: daftar produksi (array untuk Blade) */
/** Computed: daftar produksi (array untuk Blade flat) */
/** Menampilkan kanya yang ada produksi  */

// public function getProduksiProperty(): array
// {
//     try {
//         $tanggal = \Carbon\Carbon::parse($this->tanggal ?: now()->toDateString())->toDateString();
//     } catch (\Throwable $e) {
//         $tanggal = now()->toDateString();
//     }

//     // --- subqueries agregat ---
//     $subTambahan = DB::table('produksi_tambahan')
//         ->selectRaw('perintah_produksi_id, mproducts_id,
//                      SUM(qty_tambahan) AS qty_tambahan,
//                      SUM(target_qty_tambahan) AS target_qty_tambahan')
//         ->groupBy('perintah_produksi_id', 'mproducts_id');

//     $subPengurangan = DB::table('produksi_pengurangan')
//         ->selectRaw('perintah_produksi_id, mproducts_id,
//                      SUM(qty_pengurangan) AS qty_pengurangan,
//                      SUM(target_qty_pengurangan) AS target_qty_pengurangan')
//         ->groupBy('perintah_produksi_id', 'mproducts_id');

//     $subHasilDivisi2 = DB::table('hasil_divisi')
//         ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div2')
//         ->where('divisi_id', 2)
//         ->groupBy('perintah_produksi_id', 'mproducts_id');

//     $subHasilDivisi4 = DB::table('hasil_divisi')
//         ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div4')
//         ->where('divisi_id', 4)
//         ->groupBy('perintah_produksi_id', 'mproducts_id');

//     $subReject = DB::table('hasil_reject as hr')
//         ->leftJoin('listreject as lr', 'lr.id', '=', 'hr.listreject_id')
//         ->selectRaw('
//             hr.perintah_produksi_id,
//             hr.mproducts_id,
//             SUM(hr.qty_reject) AS qty_reject,
//             GROUP_CONCAT(DISTINCT NULLIF(TRIM(lr.keterangan), "")
//                 ORDER BY lr.keterangan SEPARATOR ", ") AS listreject_keterangan,
//             GROUP_CONCAT(NULLIF(TRIM(lr.keterangan), "")
//                 ORDER BY lr.keterangan SEPARATOR ",") AS listreject_all,
//             GROUP_CONCAT(DISTINCT NULLIF(TRIM(hr.keterangan), "")
//                 ORDER BY hr.keterangan SEPARATOR ", ") AS keterangan_reject,
//             GROUP_CONCAT(NULLIF(TRIM(hr.keterangan), "")
//                 ORDER BY hr.keterangan SEPARATOR ",") AS keterangan_reject_all
//         ')
//         ->groupBy('hr.perintah_produksi_id', 'hr.mproducts_id');

//     // --- main query (tanpa struktur grup) ---
//     $rows = DB::table('perintah_produksi AS pp')
//         ->join('detail_perintah_produksi AS dpp', 'dpp.perintah_produksi_id', '=', 'pp.id')
//         ->join('mproducts AS mp', 'mp.id', '=', 'dpp.mproducts_id')

//         // hubungkan ke mesin (opsional filter aktif)
//         ->leftJoin('machine_product AS mpiv', function ($j) {
//             $j->on('mpiv.mproduct_id', '=', 'dpp.mproducts_id');
//             // HAPUS baris filter ini: $j->where('mpiv.is_active', 1);
//         })
//         ->leftJoin('machines AS m', 'm.id', '=', 'mpiv.machine_id')

//         ->leftJoinSub($subTambahan,  'pt',  fn($j) => $j->on('pt.perintah_produksi_id', '=', 'pp.id')
//         ->on('pt.mproducts_id', '=', 'dpp.mproducts_id'))
//         ->leftJoinSub($subPengurangan,'pg',  fn($j) => $j->on('pg.perintah_produksi_id', '=', 'pp.id')
//         ->on('pg.mproducts_id', '=', 'dpp.mproducts_id'))
//         ->leftJoinSub($subHasilDivisi2,'hd2', fn($j) => $j->on('hd2.perintah_produksi_id', '=', 'pp.id')
//         ->on('hd2.mproducts_id', '=', 'dpp.mproducts_id'))
//         ->leftJoinSub($subHasilDivisi4,'hd4', fn($j) => $j->on('hd4.perintah_produksi_id', '=', 'pp.id')
//         ->on('hd4.mproducts_id', '=', 'dpp.mproducts_id'))
//         ->leftJoinSub($subReject,     'hr',  fn($j) => $j->on('hr.perintah_produksi_id', '=', 'pp.id')
//         ->on('hr.mproducts_id', '=', 'dpp.mproducts_id'))

//         ->whereDate('pp.tanggal_perintah', $tanggal)

//         ->groupBy('m.id', 'm.nama','pp.id', 'pp.tanggal_perintah', 'dpp.mproducts_id', 'mp.nama', 'mp.patokan')
//         ->havingRaw('
//             (COALESCE(SUM(dpp.produksi_qty),0)
//              + COALESCE(SUM(pt.qty_tambahan),0)
//              - COALESCE(SUM(pg.qty_pengurangan),0)) > 0
//         ')
//          // urut ASC: id mesin → nama produk
//         ->orderBy('m.id', 'asc')
//         ->orderBy('mp.nama', 'asc')
//         ->get([
//             'pp.id AS perintah_id',
//             'pp.tanggal_perintah',
//             'dpp.mproducts_id AS product_id',
//             'mp.nama AS nama_produk',
//             'mp.patokan AS patokan_produk',
//             'm.id AS machine_id',
//             'm.nama AS mesin_nama',
//             DB::raw('COALESCE(SUM(dpp.produksi_qty),0) AS qty_dasar'),
//             DB::raw('COALESCE(SUM(dpp.target_produksi),0) AS target_dasar'),
//             DB::raw('COALESCE(SUM(pt.qty_tambahan),0) AS qty_tambahan'),
//             DB::raw('COALESCE(SUM(pt.target_qty_tambahan),0) AS target_tambahan'),
//             DB::raw('COALESCE(SUM(pg.qty_pengurangan),0) AS qty_pengurangan'),
//             DB::raw('COALESCE(SUM(pg.target_qty_pengurangan),0) AS target_pengurangan'),

//             DB::raw('COALESCE(ANY_VALUE(hr.listreject_keterangan), "")  AS listreject_keterangan'),
//             DB::raw('COALESCE(ANY_VALUE(hr.listreject_all), "")          AS listreject_all'),
//             DB::raw('COALESCE(ANY_VALUE(hr.keterangan_reject), "")       AS keterangan_reject'),
//             DB::raw('COALESCE(ANY_VALUE(hr.keterangan_reject_all), "")   AS keterangan_reject_all'),

//             DB::raw('COALESCE(SUM(hd2.qty_hasil_div2),0) AS qty_hasil_div2'),
//             DB::raw('COALESCE(SUM(hd4.qty_hasil_div4),0) AS qty_hasil_div4'),
//             DB::raw('COALESCE(SUM(hr.qty_reject),0)      AS qty_reject'),

//             DB::raw('(COALESCE(SUM(dpp.target_produksi),0)
//                      + COALESCE(SUM(pt.target_qty_tambahan),0)
//                      - COALESCE(SUM(pg.target_qty_pengurangan),0)) AS target_total'),

//             DB::raw('(COALESCE(SUM(dpp.produksi_qty),0)
//                      + COALESCE(SUM(pt.qty_tambahan),0)
//                      - COALESCE(SUM(pg.qty_pengurangan),0)) AS total_tong'),
//         ]);

//     // mapping ke field yg dipakai Blade
//     return $rows->map(function ($r) {
//         $hasilGiling = (int) $r->qty_hasil_div2; // divisi 2
//         $hasilDekor  = (int) $r->qty_hasil_div4; // divisi 4
//         $selisih     = $hasilGiling - (int) $r->target_total;

//         // pecah reject_detail untuk list
//         $rejectDetail = [];
//         $raw = (string) ($r->listreject_all ?? '');
//         if ($raw !== '') {
//             $labels = array_filter(array_map('trim', explode(',', $raw)), fn($x) => $x !== '');
//             if ($labels) {
//                 $rejectDetail = array_count_values($labels);
//                 ksort($rejectDetail);
//             }
//         }

//         return [
//             'id'                   => (int) $r->product_id,
//             'nama_produk'          => (string) $r->nama_produk,
//             'patokan_produk'       => (int) $r->patokan_produk,

//             'listreject_keterangan'=> (string) $r->listreject_keterangan,
//             'keterangan_reject'    => (string) $r->keterangan_reject,
//             'reject_detail'        => $rejectDetail,
//             'mesin_nama' => (string)($r->mesin_nama ?? 'Tanpa Mesin'),
//             // kolom numerik yang dipakai Blade
//             'total_tong'           => (int) $r->total_tong,     // ditaruh di kolom "TARGET GILING"
//             'total_target'         => (int) $r->target_total,   // ditaruh di kolom "HASIL GILING" (sesuai Blade)
//             'hasil_real'           => $hasilGiling,             // "HASIL REAL"
//             'hasil_dekor'          => $hasilDekor,              // "HASIL DEKOR"
//             'selisih_hasil'        => $selisih,                 // "SELISIH HASIL"
//             'reject'               => (int) $r->qty_reject,     // "REJECT / RETUR PRODUKSI"
//         ];
//     })->values()->all();
// }

/** Menampilkan semua produk */
// ⚡ Perbedaan utama:

// Query start dari mproducts → sehingga semua produk muncul.

// havingRaw(...) dihapus.

// Semua nilai produksi dibungkus COALESCE(...,0).

// Mesin tanpa mapping → otomatis mesin_nama = Tanpa Mesin.

public function getProduksiProperty(): array
{
    try {
        $tanggal = \Carbon\Carbon::parse($this->tanggal ?: now()->toDateString())->toDateString();
    } catch (\Throwable $e) {
        $tanggal = now()->toDateString();
    }

    // --- Subquery agregat ---
    $subTambahan = DB::table('produksi_tambahan')
        ->selectRaw('perintah_produksi_id, mproducts_id,
                     SUM(qty_tambahan) AS qty_tambahan,
                     SUM(target_qty_tambahan) AS target_qty_tambahan')
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    $subPengurangan = DB::table('produksi_pengurangan')
        ->selectRaw('perintah_produksi_id, mproducts_id,
                     SUM(qty_pengurangan) AS qty_pengurangan,
                     SUM(target_qty_pengurangan) AS target_qty_pengurangan')
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    $subHasilDivisi2 = DB::table('hasil_divisi')
        ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div2')
        ->where('divisi_id', 2)
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    $subHasilDivisi4 = DB::table('hasil_divisi')
        ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div4')
        ->where('divisi_id', 4)
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    $subReject = DB::table('hasil_reject as hr')
        ->leftJoin('listreject as lr', 'lr.id', '=', 'hr.listreject_id')
        ->selectRaw('
            hr.perintah_produksi_id,
            hr.mproducts_id,
            SUM(hr.qty_reject) AS qty_reject,
            GROUP_CONCAT(DISTINCT NULLIF(TRIM(lr.keterangan), "") ORDER BY lr.keterangan SEPARATOR ", ") AS listreject_keterangan,
            GROUP_CONCAT(NULLIF(TRIM(lr.keterangan), "") ORDER BY lr.keterangan SEPARATOR ",") AS listreject_all,
            GROUP_CONCAT(DISTINCT NULLIF(TRIM(hr.keterangan), "") ORDER BY hr.keterangan SEPARATOR ", ") AS keterangan_reject,
            GROUP_CONCAT(NULLIF(TRIM(hr.keterangan), "") ORDER BY hr.keterangan SEPARATOR ",") AS keterangan_reject_all
        ')
        ->groupBy('hr.perintah_produksi_id', 'hr.mproducts_id');

    // --- agregat produksi per tanggal (subquery utama) ---
    $agg = DB::table('perintah_produksi AS pp')
        ->join('detail_perintah_produksi AS dpp', 'dpp.perintah_produksi_id', '=', 'pp.id')
        ->leftJoinSub($subTambahan,  'pt',  fn($j) => $j->on('pt.perintah_produksi_id', '=', 'pp.id')->on('pt.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subPengurangan,'pg',  fn($j) => $j->on('pg.perintah_produksi_id', '=', 'pp.id')->on('pg.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subHasilDivisi2,'hd2', fn($j) => $j->on('hd2.perintah_produksi_id', '=', 'pp.id')->on('hd2.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subHasilDivisi4,'hd4', fn($j) => $j->on('hd4.perintah_produksi_id', '=', 'pp.id')->on('hd4.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subReject,     'hr',  fn($j) => $j->on('hr.perintah_produksi_id', '=', 'pp.id')->on('hr.mproducts_id', '=', 'dpp.mproducts_id'))
        ->whereDate('pp.tanggal_perintah', $tanggal)
        ->groupBy('dpp.mproducts_id')
        ->select([
            'dpp.mproducts_id AS product_id',
            DB::raw('COALESCE(SUM(dpp.produksi_qty),0) AS qty_dasar'),
            DB::raw('COALESCE(SUM(dpp.target_produksi),0) AS target_dasar'),
            DB::raw('COALESCE(SUM(pt.qty_tambahan),0) AS qty_tambahan'),
            DB::raw('COALESCE(SUM(pt.target_qty_tambahan),0) AS target_tambahan'),
            DB::raw('COALESCE(SUM(pg.qty_pengurangan),0) AS qty_pengurangan'),
            DB::raw('COALESCE(SUM(pg.target_qty_pengurangan),0) AS target_pengurangan'),
            DB::raw('COALESCE(SUM(hd2.qty_hasil_div2),0) AS qty_hasil_div2'),
            DB::raw('COALESCE(SUM(hd4.qty_hasil_div4),0) AS qty_hasil_div4'),
            DB::raw('COALESCE(SUM(hr.qty_reject),0)      AS qty_reject'),
            DB::raw('COALESCE(MAX(hr.listreject_keterangan), "")  AS listreject_keterangan'),
            DB::raw('COALESCE(MAX(hr.listreject_all), "")          AS listreject_all'),
            DB::raw('COALESCE(MAX(hr.keterangan_reject), "")       AS keterangan_reject'),
            DB::raw('COALESCE(MAX(hr.keterangan_reject_all), "")   AS keterangan_reject_all'),
            DB::raw('
                (COALESCE(SUM(dpp.target_produksi),0)
                 + COALESCE(SUM(pt.target_qty_tambahan),0)
                 - COALESCE(SUM(pg.target_qty_pengurangan),0)) AS target_total'),
            DB::raw('
                (COALESCE(SUM(dpp.produksi_qty),0)
                 + COALESCE(SUM(pt.qty_tambahan),0)
                 - COALESCE(SUM(pg.qty_pengurangan),0)) AS total_tong')
        ]);

    // --- query utama mulai dari semua produk ---
    $rows = DB::table('mproducts AS mp')
        ->leftJoin('machine_product AS mpiv', 'mpiv.mproduct_id', '=', 'mp.id')
        ->leftJoin('machines AS m', 'm.id', '=', 'mpiv.machine_id')
        ->leftJoinSub($agg, 'ag', fn($j) => $j->on('ag.product_id', '=', 'mp.id'))
          // ✅ urutan ASC: id mesin → nama produk, produk tanpa mesin di bawah
        ->orderByRaw('CASE WHEN m.id IS NULL THEN 1 ELSE 0 END')
        ->orderBy('m.id', 'asc')
        ->orderBy('mp.nama', 'asc')
        ->get([
            'mp.id AS product_id',
            'mp.nama AS nama_produk',
            'mp.patokan AS patokan_produk',
            'm.id AS machine_id',
            'm.nama AS mesin_nama',
            DB::raw('COALESCE(ag.total_tong,0) AS total_tong'),
            DB::raw('COALESCE(ag.target_total,0) AS target_total'),
            DB::raw('COALESCE(ag.qty_hasil_div2,0) AS qty_hasil_div2'),
            DB::raw('COALESCE(ag.qty_hasil_div4,0) AS qty_hasil_div4'),
            DB::raw('COALESCE(ag.qty_reject,0) AS qty_reject'),
            DB::raw('COALESCE(ag.listreject_keterangan,"") AS listreject_keterangan'),
            DB::raw('COALESCE(ag.listreject_all,"") AS listreject_all'),
            DB::raw('COALESCE(ag.keterangan_reject,"") AS keterangan_reject'),
        ]);

    return $rows->map(function ($r) {
        $hasilGiling = (int) $r->qty_hasil_div2;
        $hasilDekor  = (int) $r->qty_hasil_div4;
        $selisih     = $hasilGiling - (int) $r->target_total;

        $rejectDetail = [];
        $raw = (string) ($r->listreject_all ?? '');
        if ($raw !== '') {
            $labels = array_filter(array_map('trim', explode(',', $raw)), fn($x) => $x !== '');
            if ($labels) {
                $rejectDetail = array_count_values($labels);
                ksort($rejectDetail);
            }
        }

        return [
            'id'              => (int) $r->product_id,
            'nama_produk'     => (string) $r->nama_produk,
            'patokan_produk'  => (int) $r->patokan_produk,
            'mesin_nama'      => (string)($r->mesin_nama ?? 'Tanpa Mesin'),
            'total_tong'      => (int) $r->total_tong,
            'total_target'    => (int) $r->target_total,
            'hasil_real'      => $hasilGiling,
            'hasil_dekor'     => $hasilDekor,
            'selisih_hasil'   => $selisih,
            'reject'          => (int) $r->qty_reject,
            'listreject_keterangan' => (string) $r->listreject_keterangan,
            'keterangan_reject'     => (string) $r->keterangan_reject,
            'reject_detail'         => $rejectDetail,
        ];
    })->values()->all();
}


    /** Computed: total untuk baris "TOTAL MESIN" */
    public function getTotalProperty(): array
    {
        $sum = ['target' => 0, 'hasil' => 0, 'reject' => 0];

        foreach ($this->produksi as $r) {
            $sum['target'] += (int) ($r['total_target'] ?? 0);
            $sum['hasil']  += (int) ($r['hasil_real'] ?? 0);
            $sum['reject'] += (int) ($r['reject'] ?? 0);
        }

        return $sum;
    }

    public function render()
    {
        return view('livewire.produksi.laporan.lap-harian', [
            'produksi' => $this->produksi, // computed
            'total'    => $this->total,    // computed
        ]);
    }
}
