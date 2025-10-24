<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\ProduksiPengalihan;
use App\Models\Produksi\ProduksiPengalihanItem;

class PengalihanController extends Controller
{
    // POST /api/pengalihan
    public function store(Request $r)
    {
        $v = $r->validate([
            'perintah_id'         => 'required|integer|exists:perintah_produksi,id',
            'mproducts_id'        => 'required|integer|exists:mproducts,id',
            'target_mproducts_id' => 'required|integer|different:mproducts_id|exists:mproducts,id',
            'qty'                 => 'required|integer|min:1',
            'divisi_id'           => 'nullable|integer',
            'note'                => 'nullable|string|max:500',
        ]);

        $tanggal = now('Asia/Jakarta')->toDateString();

        return DB::transaction(function () use ($v, $tanggal) {

            // 🔎 Cari baris detail_perintah_produksi_id yang sesuai
            $detailRow = \App\Models\Produksi\Detail_Perintah_Produksi::query()
                ->where('perintah_produksi_id', $v['perintah_id'])
                ->where('mproducts_id', $v['mproducts_id'])
                ->first();

            $detailId = $detailRow?->id; // bisa null kalau tidak ketemu

            // 🧾 Buat header (1 pengalihan = 1 dokumen)
            $header =ProduksiPengalihan::create([
                'perintah_produksi_id' => $v['perintah_id'],
                'tanggal'              => $tanggal,
                'divisi_id'            => $v['divisi_id'] ?? null,
                'catatan'              => $v['note'] ?? null,
            ]);

            // 🧩 Buat detail item pengalihan
            $item = ProduksiPengalihanItem::create([
                'pengalihan_id'              => $header->id,
                'detail_perintah_produksi_id'=> $detailId, // ← otomatis terisi jika ditemukan
                'source_mproducts_id'        => $v['mproducts_id'],
                'target_mproducts_id'        => $v['target_mproducts_id'],
                'qty_pcs'                    => (int) $v['qty'],
                'keterangan'                 => $v['note'] ?? null,
            ]);

            return response()->json([
                'message' => 'Pengalihan tersimpan.',
                'data' => [
                    'id'                  => $item->id,
                    'detail_perintah_id'  => $detailId,
                    'qty'                 => (int) $item->qty_pcs,
                    'note'                => $item->keterangan,
                    'target_mproducts_id' => $item->target_mproducts_id,
                ],
            ], 201);
        });
    }

  // GET /api/pengalihan?perintah_id=&mproducts_id=&divisi_id=
  public function index(Request $r)
  {
    $r->validate([
      'perintah_id'  => ['required','integer','exists:perintah_produksi,id'],
      'mproducts_id' => ['required','integer','exists:mproducts,id'],
      'divisi_id'    => ['nullable','integer'],
    ]);

    $q = ProduksiPengalihanItem::query()
      ->with(['targetProduct:id,nama','sourceProduct:id,nama','header:id,perintah_produksi_id,tanggal,divisi_id'])
      ->whereHas('header', function ($h) use ($r) {
        $h->where('perintah_produksi_id', $r->perintah_id);
        if ($r->filled('divisi_id')) $h->where('divisi_id', $r->divisi_id);
      })
      ->where('source_mproducts_id', $r->mproducts_id)
      ->orderByDesc('id');

    $rows = $q->get()->map(function ($it) {
      return [
        'id'                  => $it->id,
        'qty'                 => (int) $it->qty_pcs,  // FE lihat "qty"
        'keterangan'          => $it->keterangan,
        'target_mproducts_id' => $it->target_mproducts_id,
        'target_product_name' => $it->targetProduct->nama ?? '',
      ];
    });

    return response()->json(['data' => $rows]);
  }

  // GET /api/pengalihan/summary?perintah_id=&divisi_id=
  public function summary(Request $r)
  {
    $r->validate([
      'perintah_id' => ['required','integer','exists:perintah_produksi,id'],
      'divisi_id'   => ['nullable','integer'],
    ]);

    $items = ProduksiPengalihanItem::query()
      ->selectRaw('source_mproducts_id as mproducts_id, COUNT(*) as n, SUM(qty_pcs) as qty')
      ->whereIn('pengalihan_id', function ($q) use ($r) {
        $q->select('id')->from('produksi_pengalihan')
          ->where('perintah_produksi_id', $r->perintah_id);
        if ($r->filled('divisi_id')) $q->where('divisi_id', $r->divisi_id);
      })
      ->groupBy('source_mproducts_id')
      ->get()
      ->map(function ($row) {
        return [
          'mproducts_id' => (int) $row->mproducts_id,
          'n'            => (int) $row->n,
          'qty'          => (int) $row->qty, // FE pakai "qty"
        ];
      });

    return response()->json(['data' => $items]);
  }

  // DELETE /api/pengalihan/{itemId}
  public function destroy($id)
  {
    $item = ProduksiPengalihanItem::findOrFail($id);
    $item->delete();
    return response()->json(['ok' => true]);
  }

  // GET /api/produks/all (list produk tujuan)
  public function allProducts(Request $r)
  {
    $rows = MasterProduct::select('id','nama')->orderBy('nama')->get();
    return response()->json(['data' => $rows]);
  }
}
