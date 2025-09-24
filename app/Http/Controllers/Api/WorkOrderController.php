<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\MasterProduct;
use Illuminate\Http\Request;

class PerintahProduksiController extends Controller
{
    public function loadProduks(Request $request)
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        // === ambil data utama ===
        $utamaMap = Detail_Perintah_Produksi::whereHas('perintahProduksi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal_perintah', $tanggal);
            })
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_utama')
            ->groupBy('mproducts_id')
            ->pluck('total_utama', 'mproducts_id');

        // === tambahan ===
        $tambahanMap = Produksi_Tambahan::whereHas('perintahProduksi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal_perintah', $tanggal);
            })
            ->selectRaw('mproducts_id, SUM(qty_tambahan) as total_tambahan')
            ->groupBy('mproducts_id')
            ->pluck('total_tambahan', 'mproducts_id');

        // === qty & target ===
        $produksiData = Detail_Perintah_Produksi::whereHas('perintahProduksi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal_perintah', $tanggal);
            })
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_produksi, SUM(target_produksi) as total_target')
            ->groupBy('mproducts_id')
            ->get()
            ->keyBy('mproducts_id');

        $produks = MasterProduct::all();

        $finalData = $produks->map(function ($produk) use ($utamaMap, $tambahanMap, $produksiData) {
            $id = $produk->id;
            $totalutama = $utamaMap[$id] ?? 0;
            $totaltambahan = $tambahanMap[$id] ?? 0;

            return [
                'mproducts_id'      => $id,
                'nama'              => $produk->nama,
                'patokan'           => $produk->patokan ?? 0,
                'total_utama'       => (float) $totalutama,
                'konversiutama'     => (int) round(($produk->patokan ?? 0) * (float) $totalutama),
                'total_tambahan'    => (float) $totaltambahan,
                'konversitambahan'  => (int) round((float) $totaltambahan * ($produk->patokan ?? 0)),
                'produksi_qty'      => (float) ($produksiData[$id]->total_produksi ?? 0),
                'target_produksi'   => (float) ($produksiData[$id]->total_target ?? 0),
            ];
        });

        // Ambil tambahan terakhir (max tambahan_ke) + build tabel per tambahan_ke
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)->first();
        $detailTambahanMax = [];
        $sumTongTambahanMax = 0;
        $sumPcsTambahanMax = 0;
        $maxTambahanKe = 0;

        // === build tambahan_tables (per gilingan) ===
        $tambahanTables = [];
        if ($perintah) {
            $maxTambahanKe = (int) (Produksi_Tambahan::where('perintah_produksi_id', $perintah->id)->max('tambahan_ke') ?? 0);

            $produkMap = MasterProduct::select('id', 'nama', 'patokan')->get()->keyBy('id');

            // Kumpulkan semua tambahan per tambahan_ke
            $grouped = Produksi_Tambahan::where('perintah_produksi_id', $perintah->id)
                ->selectRaw('tambahan_ke, mproducts_id, SUM(qty_tambahan) as qty')
                ->groupBy('tambahan_ke', 'mproducts_id')
                ->orderBy('tambahan_ke', 'asc') // pastikan ke-1 di atas
                ->get()
                ->groupBy('tambahan_ke');

            foreach ($grouped as $ke => $rows) {
                $detail = $rows->map(function ($r) use ($produkMap) {
                    $p = $produkMap->get($r->mproducts_id);
                    $patokan = (float) ($p->patokan ?? 0);
                    $qtyTong = (float) ($r->qty ?? 0);
                    return [
                        'nama'     => $p->nama ?? '-',
                        'patokan'  => $patokan,
                        'qty_tong' => $qtyTong,
                        'konversi' => (int) round($qtyTong * $patokan),
                    ];
                })
                ->filter(fn($x) => (float) $x['qty_tong'] !== 0.0)
                ->sortBy('nama')
                ->values();

                $tambahanTables[] = [
                    'tambahan_ke' => (int) $ke,
                    'detail'      => $detail->toArray(),
                    'sum_tong'    => (float) $detail->sum('qty_tong'),
                    'sum_pcs'     => (int) $detail->sum('konversi'),
                ];
            }

            // Data untuk tambahan terakhir (backward compatibility)
            if ($maxTambahanKe > 0 && isset($grouped[$maxTambahanKe])) {
                $detailCol = collect($grouped[$maxTambahanKe])->map(function ($r) use ($produkMap) {
                    $p = $produkMap->get($r->mproducts_id);
                    $patokan = (float) ($p->patokan ?? 0);
                    $qtyTong = (float) ($r->qty ?? 0);
                    return [
                        'nama'     => $p->nama ?? '-',
                        'patokan'  => $patokan,
                        'qty_tong' => $qtyTong,
                        'konversi' => (int) round($qtyTong * $patokan),
                    ];
                })
                ->filter(fn($x) => (float) $x['qty_tong'] !== 0.0)
                ->sortBy('nama')
                ->values();

                $sumTongTambahanMax = (float) $detailCol->sum('qty_tong');
                $sumPcsTambahanMax  = (int) $detailCol->sum('konversi');
                $detailTambahanMax  = $detailCol->toArray();
            }
        }

        return response()->json([
            'tanggal' => $tanggal,
            'data'    => $finalData, // daftar produk + total harian
            'summary' => [
                'utama' => [
                    'row_count' => $finalData->where('total_utama', '>', 0)->count(),
                    'sum_tong'  => (float) $finalData->sum('total_utama'),
                    'sum_pcs'   => (int) $finalData->sum('konversiutama'),
                ],
                'tambahan' => [
                    'row_count' => $finalData->where('total_tambahan', '>', 0)->count(),
                    'sum_tong'  => (float) $finalData->sum('total_tambahan'),
                    'sum_pcs'   => (int) $finalData->sum('konversitambahan'),
                ],
                // tetap kirim "tambahan_max" untuk kompatibilitas lama
                'tambahan_max' => [
                    'tambahan_ke' => (int) $maxTambahanKe,
                    'detail'      => $detailTambahanMax,
                    'sum_tong'    => $sumTongTambahanMax,
                    'sum_pcs'     => $sumPcsTambahanMax,
                ],
                // NEW: daftar tabel per tambahan_ke (ke-1 di paling atas)
                'tambahan_tables' => $tambahanTables,
            ],
        ]);
    }
}
