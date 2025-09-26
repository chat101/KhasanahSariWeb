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

        $subHasilDivisi = DB::table('hasil_divisi')
            ->selectRaw('perintah_produksi_id, mproducts_id,
                         SUM(qty_hasil) AS qty_hasil_divisi')
            ->groupBy('perintah_produksi_id', 'mproducts_id');

        $subReject = DB::table('hasil_reject')
            ->selectRaw('perintah_produksi_id, mproducts_id,
                         SUM(qty_reject) AS qty_reject')
            ->groupBy('perintah_produksi_id', 'mproducts_id');

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
            ->leftJoinSub($subHasilDivisi, 'hd', fn($j) =>
                $j->on('hd.perintah_produksi_id', '=', 'pp.id')
                  ->on('hd.mproducts_id', '=', 'dpp.mproducts_id'))
            ->leftJoinSub($subReject, 'hr', fn($j) =>
                $j->on('hr.perintah_produksi_id', '=', 'pp.id')
                  ->on('hr.mproducts_id', '=', 'dpp.mproducts_id'))
            ->whereDate('pp.tanggal_perintah', $tanggal)
            ->groupBy('pp.id', 'pp.tanggal_perintah', 'dpp.mproducts_id', 'mp.nama')
            ->orderBy('mp.nama')
            ->get([
                'pp.id AS perintah_id',
                'pp.tanggal_perintah',
                'dpp.mproducts_id AS product_id',
                'mp.nama AS nama_produk',
                DB::raw('COALESCE(SUM(dpp.produksi_qty),0) AS qty_dasar'),
                DB::raw('COALESCE(SUM(dpp.target_produksi),0) AS target_dasar'),
                DB::raw('COALESCE(SUM(pt.qty_tambahan),0) AS qty_tambahan'),
                DB::raw('COALESCE(SUM(pt.target_qty_tambahan),0) AS target_tambahan'),
                DB::raw('COALESCE(SUM(pg.qty_pengurangan),0) AS qty_pengurangan'),
                DB::raw('COALESCE(SUM(pg.target_qty_pengurangan),0) AS target_pengurangan'),
                DB::raw('COALESCE(SUM(hd.qty_hasil_divisi),0) AS qty_hasil_divisi'),
                DB::raw('COALESCE(SUM(hr.qty_reject),0) AS qty_reject'),
                DB::raw('(COALESCE(SUM(dpp.target_produksi),0)
                         + COALESCE(SUM(pt.target_qty_tambahan),0)
                         - COALESCE(SUM(pg.target_qty_pengurangan),0)) AS target_total'),
            ]);

        if ($rows->isEmpty()) {
            return [];
        }

        // Mapping hasil query ke struktur tabel
        return $rows->map(function ($r) {
            $hasilReal  = (int) $r->qty_hasil_divisi; // sesuaikan jika definisi lain
            $selisih    = $hasilReal - (int) $r->target_total;

            return [
                'id'            => (int) $r->product_id,
                'nama_produk'   => (string) $r->nama_produk,
                'target_giling' => (int) $r->target_total,   // kolom "TARGET GILING" di header
                'total_target'  => (int) $r->target_total,
                'hasil_real'    => $hasilReal,
                'selisih_hasil' => $selisih,
                'hasil_dekor'   => 0,                        // isi jika ada sumber dekor
                'reject'        => (int) $r->qty_reject,
                'keterangan'    => '',
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
