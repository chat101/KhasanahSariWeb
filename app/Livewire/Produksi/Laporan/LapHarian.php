<?php

namespace App\Livewire\Produksi\Laporan;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LapHarian extends Component
{
    public string $tanggal = '';

    public function mount(?string $periode = null): void
    {
        $this->tanggal = ($periode && trim($periode) !== '')
            ? Carbon::parse($periode)->toDateString()
            : now()->toDateString();
    }

    public function today(): void
    {
        $this->tanggal = now()->toDateString();
    }

    // ============================================================
    //  QUERY PRODUKSI HARIAN (TERMASUK HASIL PENGALIHAN)
    // ============================================================
    public function getProduksiProperty(): array
    {
        try {
            $tanggal = Carbon::parse($this->tanggal ?: now()->toDateString())->toDateString();
        } catch (\Throwable $e) {
            $tanggal = now()->toDateString();
        }

        /** 🔹 Subquery Tambahan / Pengurangan / Hasil Divisi */
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

        /**
         * 🔹 Subquery pengalihan hasil
         * patokannya adalah perintah_produksi_id, bukan tanggal
         */
        $subPengalihan = DB::table('produksi_pengalihan_items as ppi')
            ->join('produksi_pengalihan as p', 'p.id', '=', 'ppi.pengalihan_id')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'p.perintah_produksi_id')
            ->selectRaw('
                p.perintah_produksi_id,
                ppi.target_mproducts_id as mproducts_id,
                SUM(ppi.qty_pcs) as qty_pengalihan
            ')
            ->groupBy('p.perintah_produksi_id', 'ppi.target_mproducts_id');

        /** 🔹 Subquery Reject */
        $subRejectByLabel = DB::table('hasil_reject as hr')
            ->leftJoin('listreject as lr', 'lr.id', '=', 'hr.listreject_id')
            ->selectRaw('
                hr.perintah_produksi_id,
                hr.mproducts_id,
                COALESCE(NULLIF(TRIM(lr.keterangan), ""), "-") AS label,
                SUM(hr.qty_reject) AS qty
            ')
            ->groupBy('hr.perintah_produksi_id', 'hr.mproducts_id', 'label');

        $subRejectPairs = DB::query()
            ->fromSub($subRejectByLabel, 'rb')
            ->selectRaw('
                rb.perintah_produksi_id,
                rb.mproducts_id,
                SUM(rb.qty) AS qty_reject,
                GROUP_CONCAT(CONCAT(rb.label, "|", rb.qty) ORDER BY rb.label SEPARATOR ",") AS reject_pairs
            ')
            ->groupBy('rb.perintah_produksi_id', 'rb.mproducts_id');

        $subRejectText = DB::table('hasil_reject as hr')
            ->leftJoin('listreject as lr', 'lr.id', '=', 'hr.listreject_id')
            ->selectRaw('
                hr.perintah_produksi_id,
                hr.mproducts_id,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(lr.keterangan), "")
                    ORDER BY lr.keterangan SEPARATOR ", ") AS listreject_keterangan,
                GROUP_CONCAT(DISTINCT NULLIF(TRIM(hr.keterangan), "")
                    ORDER BY hr.keterangan SEPARATOR ", ") AS keterangan_reject
            ')
            ->groupBy('hr.perintah_produksi_id', 'hr.mproducts_id');

        /** 🔹 Agregasi per produk */
        $agg = DB::table('perintah_produksi AS pp')
        ->join('detail_perintah_produksi AS dpp', 'dpp.perintah_produksi_id', '=', 'pp.id')
        ->leftJoinSub($subTambahan,  'pt', fn($j) => $j->on('pt.perintah_produksi_id', '=', 'pp.id')->on('pt.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subPengurangan,'pg', fn($j) => $j->on('pg.perintah_produksi_id', '=', 'pp.id')->on('pg.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subHasilDivisi2,'hd2', fn($j) => $j->on('hd2.perintah_produksi_id', '=', 'pp.id')->on('hd2.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subHasilDivisi4,'hd4', fn($j) => $j->on('hd4.perintah_produksi_id', '=', 'pp.id')->on('hd4.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subPengalihan, 'pa', fn($j) =>
            $j->on('pa.perintah_produksi_id', '=', 'pp.id')
              ->on('pa.mproducts_id', '=', 'dpp.mproducts_id')
        )
        ->leftJoinSub($subRejectPairs, 'hrp', fn($j) => $j->on('hrp.perintah_produksi_id', '=', 'pp.id')->on('hrp.mproducts_id', '=', 'dpp.mproducts_id'))
        ->leftJoinSub($subRejectText,  'hrt', fn($j) => $j->on('hrt.perintah_produksi_id', '=', 'pp.id')->on('hrt.mproducts_id', '=', 'dpp.mproducts_id'))
        ->whereDate('pp.tanggal_perintah', $tanggal)
        ->groupBy('dpp.mproducts_id')
        ->select([
            'dpp.mproducts_id AS product_id',

            DB::raw('COALESCE(SUM(dpp.produksi_qty),0) AS qty_dasar'),
            DB::raw('COALESCE(SUM(pt.qty_tambahan),0) AS qty_tambahan'),
            DB::raw('COALESCE(SUM(pg.qty_pengurangan),0) AS qty_pengurangan'),

            DB::raw('COALESCE(SUM(hd2.qty_hasil_div2),0) AS qty_hasil_div2'),
            DB::raw('COALESCE(SUM(hd4.qty_hasil_div4),0) AS qty_hasil_div4'),
            DB::raw('COALESCE(SUM(pa.qty_pengalihan),0) AS qty_pengalihan'),

            // perhitungan target total (sudah + tambahan - pengurangan)
            DB::raw('(
                COALESCE(SUM(dpp.target_produksi),0)
              + COALESCE(SUM(pt.target_qty_tambahan),0)
              - COALESCE(SUM(pg.target_qty_pengurangan),0)
            ) AS target_total'),

            // perhitungan total giling aktual (sudah + tambahan - pengurangan)
            DB::raw('(
                COALESCE(SUM(dpp.produksi_qty),0)
              + COALESCE(SUM(pt.qty_tambahan),0)
              - COALESCE(SUM(pg.qty_pengurangan),0)
            ) AS total_tong'),

            DB::raw('COALESCE(SUM(hrp.qty_reject),0) AS qty_reject'),
            DB::raw('COALESCE(MAX(hrp.reject_pairs), "") AS reject_pairs'),
            DB::raw('COALESCE(MAX(hrt.listreject_keterangan), "") AS listreject_keterangan'),
            DB::raw('COALESCE(MAX(hrt.keterangan_reject), "") AS keterangan_reject'),
        ]);


        /** 🔹 Query utama (gabung produk + mesin) */
        $rows = DB::table('mproducts AS mp')
            ->leftJoin('machine_product AS mpiv', 'mpiv.mproduct_id', '=', 'mp.id')
            ->leftJoin('machines AS m', 'm.id', '=', 'mpiv.machine_id')
            ->leftJoinSub($agg, 'ag', fn($j) => $j->on('ag.product_id', '=', 'mp.id'))
            ->orderByRaw('CASE WHEN m.id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('m.id', 'asc')
            ->orderBy('mp.nama', 'asc')
            ->get([
                'mp.id AS product_id',
                'mp.nama AS nama_produk',
                'mp.patokan AS patokan_produk',
                'm.nama AS mesin_nama',
                DB::raw('COALESCE(ag.total_tong,0) AS total_tong'),
                DB::raw('COALESCE(ag.target_total,0) AS target_total'),
                DB::raw('COALESCE(ag.qty_hasil_div2,0) AS qty_hasil_div2'),
                DB::raw('COALESCE(ag.qty_hasil_div4,0) AS qty_hasil_div4'),
                DB::raw('COALESCE(ag.qty_pengalihan,0) AS qty_pengalihan'),
                DB::raw('COALESCE(ag.qty_reject,0) AS qty_reject'),
                DB::raw('COALESCE(ag.reject_pairs,"") AS reject_pairs'),
                DB::raw('COALESCE(ag.listreject_keterangan,"") AS listreject_keterangan'),
                DB::raw('COALESCE(ag.keterangan_reject,"") AS keterangan_reject'),
            ]);

        /** 🔹 Mapping hasil ke Blade */
        return $rows->map(function ($r) {
            $hasilGiling = (int) $r->qty_hasil_div2;
            $hasilDekor  = (int) $r->qty_hasil_div4 + (int) $r->qty_pengalihan; // hasil dekor + hasil pengalihan
            $selisih     = $hasilGiling - (int) $r->target_total;

            $rejectDetail = [];
            $pairs = (string) ($r->reject_pairs ?? '');
            if ($pairs !== '') {
                foreach (explode(',', $pairs) as $pair) {
                    [$label, $qty] = array_pad(explode('|', $pair, 2), 2, 0);
                    $label = trim((string)$label);
                    $qty   = (int)$qty;
                    if ($label === '') $label = '-';
                    $rejectDetail[$label] = ($rejectDetail[$label] ?? 0) + $qty;
                }
                ksort($rejectDetail);
            }

            return [
                'id'                => (int) $r->product_id,
                'nama_produk'       => (string) $r->nama_produk,
                'patokan_produk'    => (int) $r->patokan_produk,
                'mesin_nama'        => (string)($r->mesin_nama ?? 'Tanpa Mesin'),
                'total_tong'        => (int) $r->total_tong,
                'total_target'      => (int) $r->target_total,
                'hasil_real'        => $hasilGiling,
                'hasil_dekor'       => $hasilDekor,
                'selisih_hasil'     => $selisih,
                'reject'            => (int) $r->qty_reject,
                'reject_detail'     => $rejectDetail,
                'listreject_keterangan' => (string) $r->listreject_keterangan,
                'keterangan_reject'     => (string) $r->keterangan_reject,
            ];
        })
        ->filter(fn($r) => $r['hasil_real'] > 0 || $r['hasil_dekor'] > 0)
        ->values()
        ->all();
    }

    // ============================================================
    //  TOTAL GRAND TOTAL
    // ============================================================
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
            'produksi' => $this->produksi,
            'total'    => $this->total,
        ]);
    }
}
