<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\MasterProduct;

class ProduksController extends Controller
{
   /**
     * Helper: ambil dan susun data dasar untuk tanggal tertentu.
     */
    private function buildBaseData(string $tanggal): array
    {
        $locked = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)
            ->where('status', 1)
            ->exists();

        $produks = MasterProduct::select('id','nama','patokan')->get();

        // Map utama (sum produksi_qty)
        $utamaMap = Detail_Perintah_Produksi::whereHas('perintahProduksi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal_perintah', $tanggal);
            })
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_utama')
            ->groupBy('mproducts_id')
            ->pluck('total_utama','mproducts_id');

        // Map tambahan (sum qty_tambahan)
     // Map tambahan (sum qty_tambahan + kumpulkan keterangan)
$tambahanRows = Produksi_Tambahan::whereHas('perintahProduksi', function ($q) use ($tanggal) {
    $q->whereDate('tanggal_perintah', $tanggal);
})
->selectRaw('
    mproducts_id,
    SUM(qty_tambahan) as total_tambahan,
    GROUP_CONCAT(DISTINCT keterangan ORDER BY id SEPARATOR " • ") as ket
')
->groupBy('mproducts_id')
->get()
->keyBy('mproducts_id');   // ->get(id) nanti

        // Data produksi & target
        $produksiData = Detail_Perintah_Produksi::whereHas('perintahProduksi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal_perintah', $tanggal);
            })
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_produksi, SUM(target_produksi) as total_target')
            ->groupBy('mproducts_id')
            ->get()
            ->keyBy('mproducts_id');

        // Satukan
        $finalData = $produks->map(function ($produk) use ($utamaMap, $tambahanRows, $produksiData) {
            $id = $produk->id;
            $patokan = (float) ($produk->patokan ?? 0);
            $totalUtama = (float) ($utamaMap[$id] ?? 0);

            $tRow = $tambahanRows->get($id);
            $totalTambahan   = (float) ($tRow->total_tambahan ?? 0);
            $keteranganExtra = $tRow->ket ?? null;   // hasil GROUP_CONCAT

            return [
                'mproducts_id'       => $id,
                'nama'               => $produk->nama ?? '-',
                'patokan'            => $patokan,
                'total_utama'        => $totalUtama,
                'konversi_utama'     => (int) round($patokan * $totalUtama),
                'total_tambahan'     => $totalTambahan,
                'konversi_tambahan'  => (int) round($patokan * $totalTambahan),
                'produksi_qty'       => (float) ($produksiData[$id]->total_produksi ?? 0),
                'target_produksi'    => (float) ($produksiData[$id]->total_target ?? 0),
                'keterangan'         => $keteranganExtra,   // <--- field baru
            ];
        });

        $utamaVisible    = $finalData->filter(fn($r) => (float) $r['total_utama'] > 0)->values();
        $tambahanVisible = $finalData->filter(fn($r) => (float) $r['total_tambahan'] > 0)->values();

        // Tambahan max_ke
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)->first();
        $perintahId = $perintah?->id ?? null;

        $maxTambahanKe = 0;
        $detailTambahanMax = collect();
        if ($perintahId) {
            $maxTambahanKe = (int) (Produksi_Tambahan::where('perintah_produksi_id', $perintahId)->max('tambahan_ke') ?? 0);
            if ($maxTambahanKe > 0) {
                $rows = Produksi_Tambahan::where('perintah_produksi_id', $perintahId)
                    ->where('tambahan_ke', $maxTambahanKe)
                    ->selectRaw('mproducts_id, SUM(qty_tambahan) as qty')
                    ->groupBy('mproducts_id')
                    ->get();

                $produkMap = MasterProduct::select('id','nama','patokan')->get()->keyBy('id');

                $detailTambahanMax = $rows->map(function($r) use ($produkMap) {
                        $p = $produkMap->get($r->mproducts_id);
                        $patokan = (float) ($p->patokan ?? 0);
                        $qtyTong = (float) ($r->qty ?? 0);
                        return [
                            'mproducts_id' => $r->mproducts_id,
                            'nama'         => $p->nama ?? '-',
                            'patokan'      => $patokan,
                            'qty_tong'     => $qtyTong,
                            'konversi'     => (int) round($qtyTong * $patokan),
                        ];
                    })
                    ->filter(fn($x) => (float) $x['qty_tong'] !== 0.0)
                    ->sortBy('nama')
                    ->values();
            }
        }

        return [
            'locked'             => (bool) $locked,
            'utamaVisible'       => $utamaVisible,
            'tambahanVisible'    => $tambahanVisible,
            'maxTambahanKe'      => $maxTambahanKe,
            'detailTambahanMax'  => $detailTambahanMax,
        ];
    }

    /**
     * GET /api/produks/utama?tanggal=YYYY-MM-DD&per_page=50&page=1
     */
    public function utama(Request $request)
    {
        $request->validate([
            'tanggal'  => ['required','date'],
            'per_page' => ['nullable','integer','min:1','max:500'],
            'page'     => ['nullable','integer','min:1'],
        ]);

        $tanggal  = Carbon::parse($request->query('tanggal'))->toDateString();
        $perPage  = (int) ($request->query('per_page', 100));
        $page     = (int) ($request->query('page', 1));

        $base = $this->buildBaseData($tanggal);
        $data = $base['utamaVisible'];

        $total = $data->count();
        $items = $data->slice(($page-1)*$perPage, $perPage)->values();

        return response()->json([
            'date'   => $tanggal,
            'locked' => $base['locked'],
            'meta'   => [
                'rows'     => $total,
                'sum_tong' => (float) $data->sum('total_utama'),
                'sum_pcs'  => (int) round($data->sum('konversi_utama')),
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'data'   => $items,
        ]);
    }

    /**
     * GET /api/produks/tambahan?tanggal=YYYY-MM-DD&per_page=50&page=1
     */
    public function tambahan(Request $request)
    {
        $request->validate([
            'tanggal'  => ['required','date'],
            'per_page' => ['nullable','integer','min:1','max:500'],
            'page'     => ['nullable','integer','min:1'],
        ]);

        $tanggal  = Carbon::parse($request->query('tanggal'))->toDateString();
        $perPage  = (int) ($request->query('per_page', 100));
        $page     = (int) ($request->query('page', 1));

        $base = $this->buildBaseData($tanggal);
        $data = $base['tambahanVisible'];

        $total = $data->count();
        $items = $data->slice(($page-1)*$perPage, $perPage)->values();

        return response()->json([
            'date'   => $tanggal,
            'locked' => $base['locked'],
            'meta'   => [
                'rows'     => $total,
                'sum_tong' => (float) $data->sum('total_tambahan'),
                'sum_pcs'  => (int) round($data->sum('konversi_tambahan')),
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'data'   => $items,
        ]);
    }

    /**
     * GET /api/produks/tambahan-max?tanggal=YYYY-MM-DD
     */
    public function tambahanMax(Request $request)
    {
        $request->validate([
            'tanggal' => ['required','date'],
        ]);

        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();
        $base = $this->buildBaseData($tanggal);

        $detail = $base['detailTambahanMax'];
        return response()->json([
            'date'          => $tanggal,
            'locked'        => $base['locked'],
            'tambahan_ke'   => $base['maxTambahanKe'],
            'sum_tong'      => (float) $detail->sum('qty_tong'),
            'sum_pcs'       => (int) $detail->sum('konversi'),
            'data'          => $detail->values(),
        ]);
    }

    /**
     * (Opsional) GET /api/produks/summary?tanggal=YYYY-MM-DD
     */
    public function summary(Request $request)
    {
        $request->validate([
            'tanggal' => ['required','date'],
        ]);

        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();
        $base = $this->buildBaseData($tanggal);

        return response()->json([
            'date'   => $tanggal,
            'locked' => $base['locked'],
            'summary' => [
                'utama' => [
                    'rows'     => $base['utamaVisible']->count(),
                    'sum_tong' => (float) $base['utamaVisible']->sum('total_utama'),
                    'sum_pcs'  => (int) round($base['utamaVisible']->sum('konversi_utama')),
                ],
                'tambahan' => [
                    'rows'     => $base['tambahanVisible']->count(),
                    'sum_tong' => (float) $base['tambahanVisible']->sum('total_tambahan'),
                    'sum_pcs'  => (int) round($base['tambahanVisible']->sum('konversi_tambahan')),
                ],
                'tambahan_max' => [
                    'tambahan_ke' => $base['maxTambahanKe'],
                    'sum_tong'    => (float) $base['detailTambahanMax']->sum('qty_tong'),
                    'sum_pcs'     => (int) $base['detailTambahanMax']->sum('konversi'),
                ],
            ],
        ]);
    }
    public function notifyOverview(Request $request)
{
    $tanggal = $request->query('tanggal')
        ? Carbon::parse($request->query('tanggal'))->toDateString()
        : Carbon::today('Asia/Jakarta')->toDateString();

    $base = $this->buildBaseData($tanggal);

    return response()->json([
        'wo_utama' => [
            'tanggal'    => $tanggal,
            'row_count'  => $base['utamaVisible']->count(),
            'sum_tong'   => (float) $base['utamaVisible']->sum('total_utama'),
            'locked'     => (bool) $base['locked'],
        ],
        'wo_tambahan' => [
            'tanggal'          => $tanggal,
            'max_tambahan_ke'  => (int) $base['maxTambahanKe'],
        ],
    ]);
}
public function loadProduks(Request $request)
{
    $request->validate(['tanggal' => ['required','date']]);
    $tanggal = \Illuminate\Support\Carbon::parse($request->query('tanggal'))->toDateString();

    $perintah = \App\Models\Produksi\Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)->first();
    $produkMap = \App\Models\Produksi\MasterProduct::select('id','nama','patokan')->get()->keyBy('id');

    $tambahanTables = [];
    if ($perintah) {
        // group per tambahan_ke lalu susun tabelnya (ASC: 1,2,3,...)
        $grouped = \App\Models\Produksi\Produksi_Tambahan::where('perintah_produksi_id', $perintah->id)
        ->selectRaw('
            tambahan_ke,
            mproducts_id,
            SUM(qty_tambahan) as qty,
            GROUP_CONCAT(DISTINCT keterangan ORDER BY id SEPARATOR " • ") as ket
        ')
        ->groupBy('tambahan_ke', 'mproducts_id')
        ->orderBy('tambahan_ke', 'asc')
        ->get()
        ->groupBy('tambahan_ke');

        foreach ($grouped as $ke => $rows) {
            $detail = $rows->map(function ($r) use ($produkMap) {
                $p = $produkMap->get($r->mproducts_id);
                $patokan = (float) ($p->patokan ?? 0);
                $qtyTong = (float) ($r->qty ?? 0);
                return [
                    'mproducts_id' => (int) $r->mproducts_id,
                    'nama'         => $p->nama ?? '-',
                    'patokan'      => $patokan,
                    'qty_tong'     => $qtyTong,
                    'konversi'     => (int) round($qtyTong * $patokan),
                    'keterangan'   => $r->ket,   // <--- ini yang dipakai HP
                ];
            })->filter(fn($x) => (float)$x['qty_tong'] !== 0.0)->sortBy('nama')->values();

            $tambahanTables[] = [
                'tambahan_ke' => (int) $ke,
                'detail'      => $detail->toArray(),
                'sum_tong'    => (float) $detail->sum('qty_tong'),
                'sum_pcs'     => (int) $detail->sum('konversi'),
            ];
        }
    }

    return response()->json([
        'tanggal' => $tanggal,
        'summary' => [
            // inilah yang dibaca frontend
            'tambahan_tables' => $tambahanTables,
        ],
    ]);
}
public function products(Request $request)
{
    // ?keyed=1 untuk output keyed-by-id
    $keyed = filter_var($request->query('keyed'), FILTER_VALIDATE_BOOL);

    // ambil master produk
    $rows = MasterProduct::select('id','nama','patokan')
        ->orderBy('nama')
        ->get();

    if ($keyed) {
        // bentuk mirip $produkMap->keyBy('id')
        $map = $rows->mapWithKeys(function ($p) {
            return [
                (int) $p->id => [
                    'id'            => (int) $p->id,
                    'nama'          => (string) ($p->nama ?? '-'),
                    'patokan'       => (float) ($p->patokan ?? 0),
                    // alias untuk konsumsi frontend (opsional)
                    'mproducts_id'  => (int) $p->id,
                    'product_name'  => (string) ($p->nama ?? '-'),
                ]
            ];
        });

        return response()->json([
            'total' => $rows->count(),
            'data'  => $map, // keyed-by-id
        ]);
    }

    // default: list array untuk dipakai di modal produk tujuan
    $list = $rows->map(function ($p) {
        return [
            'mproducts_id' => (int) $p->id,
            'product_name' => (string) ($p->nama ?? '-'),
            'patokan'      => (float) ($p->patokan ?? 0),
        ];
    })->values();

    return response()->json([
        'total' => $list->count(),
        'data'  => $list, // list array
    ]);
}


}
