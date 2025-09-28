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
    public function getProduksiProperty(): array
{
    // fallback aman jika $this->tanggal kosong/tidak valid
    try {
        $tanggal = Carbon::parse($this->tanggal ?: now()->toDateString())->toDateString();
    } catch (\Throwable $e) {
        $tanggal = now()->toDateString();
    }

    // --- subqueries agregat ---
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

    // ⬇️ hasil divisi giling (2)
    $subHasilDivisi2 = DB::table('hasil_divisi')
        ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div2')
        ->where('divisi_id', 2)
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    // ⬇️ hasil divisi dekor (4)
    $subHasilDivisi4 = DB::table('hasil_divisi')
        ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_div4')
        ->where('divisi_id', 4)
        ->groupBy('perintah_produksi_id', 'mproducts_id');

    // ⬇️ REJECT: simpan daftar unik untuk display + daftar mentah utk hitung frekuensi
    $subReject = DB::table('hasil_reject as hr')
        ->leftJoin('listreject as lr', 'lr.id', '=', 'hr.listreject_id')
        ->selectRaw('
            hr.perintah_produksi_id,
            hr.mproducts_id,
            SUM(hr.qty_reject) AS qty_reject,

            -- daftar unik (rapi) untuk tampilan cepat
            GROUP_CONCAT(DISTINCT NULLIF(TRIM(lr.keterangan), "")
                ORDER BY lr.keterangan SEPARATOR ", ") AS listreject_keterangan,

            -- daftar MENTAH tanpa DISTINCT untuk hitung jumlah per label (penting!)
            GROUP_CONCAT(NULLIF(TRIM(lr.keterangan), "")
                ORDER BY lr.keterangan SEPARATOR ",") AS listreject_all,

            -- catatan bebas pada hasil_reject (unik & mentah)
            GROUP_CONCAT(DISTINCT NULLIF(TRIM(hr.keterangan), "")
                ORDER BY hr.keterangan SEPARATOR ", ") AS keterangan_reject,
            GROUP_CONCAT(NULLIF(TRIM(hr.keterangan), "")
                ORDER BY hr.keterangan SEPARATOR ",") AS keterangan_reject_all
        ')
        ->groupBy('hr.perintah_produksi_id', 'hr.mproducts_id');

    // --- main query ---
    $rows = DB::table('perintah_produksi AS pp')
        ->join('detail_perintah_produksi AS dpp', 'dpp.perintah_produksi_id', '=', 'pp.id')
        ->join('mproducts AS mp', 'mp.id', '=', 'dpp.mproducts_id')
        ->leftJoinSub($subTambahan, 'pt', fn($j) =>
            $j->on('pt.perintah_produksi_id', '=', 'pp.id')
              ->on('pt.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subPengurangan, 'pg', fn($j) =>
            $j->on('pg.perintah_produksi_id', '=', 'pp.id')
              ->on('pg.mproducts_id', '=', 'dpp.mproducts_id'))
        // ⬇️ join per-divisi
        ->leftJoinSub($subHasilDivisi2, 'hd2', fn($j) =>
            $j->on('hd2.perintah_produksi_id', '=', 'pp.id')
              ->on('hd2.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subHasilDivisi4, 'hd4', fn($j) =>
            $j->on('hd4.perintah_produksi_id', '=', 'pp.id')
              ->on('hd4.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subReject, 'hr', fn($j) =>
            $j->on('hr.perintah_produksi_id', '=', 'pp.id')
              ->on('hr.mproducts_id', '=', 'dpp.mproducts_id'))
        ->whereDate('pp.tanggal_perintah', $tanggal)
        ->groupBy('pp.id', 'pp.tanggal_perintah', 'dpp.mproducts_id', 'mp.nama')
        ->havingRaw('
            (COALESCE(SUM(dpp.produksi_qty),0)
             + COALESCE(SUM(pt.qty_tambahan),0)
             - COALESCE(SUM(pg.qty_pengurangan),0)) > 0
        ')
        ->orderBy('mp.nama')
        ->get([
            'pp.id AS perintah_id',
            'pp.tanggal_perintah',
            'dpp.mproducts_id AS product_id',
            'mp.nama AS nama_produk',
            'mp.patokan AS patokan_produk',

            DB::raw('COALESCE(SUM(dpp.produksi_qty),0) AS qty_dasar'),
            DB::raw('COALESCE(SUM(dpp.target_produksi),0) AS target_dasar'),
            DB::raw('COALESCE(SUM(pt.qty_tambahan),0) AS qty_tambahan'),
            DB::raw('COALESCE(SUM(pt.target_qty_tambahan),0) AS target_tambahan'),
            DB::raw('COALESCE(SUM(pg.qty_pengurangan),0) AS qty_pengurangan'),
            DB::raw('COALESCE(SUM(pg.target_qty_pengurangan),0) AS target_pengurangan'),

            // -- ambil kedua jenis daftar + catatan
            DB::raw('COALESCE(ANY_VALUE(hr.listreject_keterangan), "") AS listreject_keterangan'),
            DB::raw('COALESCE(ANY_VALUE(hr.listreject_all), "")       AS listreject_all'),
            DB::raw('COALESCE(ANY_VALUE(hr.keterangan_reject), "")    AS keterangan_reject'),
            DB::raw('COALESCE(ANY_VALUE(hr.keterangan_reject_all), "") AS keterangan_reject_all'),

            // ⬇️ nilai per-divisi; SUM untuk aman jika duplikasi join
            DB::raw('COALESCE(SUM(hd2.qty_hasil_div2),0) AS qty_hasil_div2'),
            DB::raw('COALESCE(SUM(hd4.qty_hasil_div4),0) AS qty_hasil_div4'),
            DB::raw('COALESCE(SUM(hr.qty_reject),0) AS qty_reject'),
            DB::raw('(COALESCE(SUM(dpp.target_produksi),0)
                     + COALESCE(SUM(pt.target_qty_tambahan),0)
                     - COALESCE(SUM(pg.target_qty_pengurangan),0)) AS target_total'),
            DB::raw('(COALESCE(SUM(dpp.produksi_qty),0)
                     + COALESCE(SUM(pt.qty_tambahan),0)
                     - COALESCE(SUM(pg.qty_pengurangan),0)) AS total_tong'),
        ]);

    // mapping
    return $rows->map(function ($r) {
        $hasilGiling = (int) $r->qty_hasil_div2; // divisi 2
        $hasilDekor  = (int) $r->qty_hasil_div4; // divisi 4
        $selisih     = $hasilGiling - (int) $r->target_total; // target giling dibanding giling

        // === Hitung frekuensi dinamis per label dari listreject_all (tanpa DISTINCT) ===
        $rejectDetail = [];
        $raw = (string) ($r->listreject_all ?? '');
        if ($raw !== '') {
            // pecah, trim, buang kosong
            $labels = array_filter(array_map('trim', explode(',', $raw)), fn($x) => $x !== '');
            if (!empty($labels)) {
                $rejectDetail = array_count_values($labels); // ['dimakan'=>2, 'miring'=>2, ...]
                ksort($rejectDetail); // optional: urut abjad
            }
        }

        return [
            'id'                   => (int) $r->product_id,
            'nama_produk'          => (string) $r->nama_produk,
            'patokan_produk'       => (int) $r->patokan_produk,

            // tampilan cepat (unik, rapih)
            'listreject_keterangan'=> (string) $r->listreject_keterangan,
            // catatan bebas di hasil_reject (unik, rapih)
            'keterangan_reject'    => (string) $r->keterangan_reject,

            // detail dinamis per-label untuk ditampilkan sebagai list
            'reject_detail'        => $rejectDetail,

            'total_tong'           => (int) $r->total_tong,
            'target_giling'        => (int) $r->target_total,
            'total_target'         => (int) $r->target_total,
            'hasil_real'           => $hasilGiling,     // dari divisi 2
            'hasil_dekor'          => $hasilDekor,      // dari divisi 4
            'selisih_hasil'        => $selisih,
            'reject'               => (int) $r->qty_reject,
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
