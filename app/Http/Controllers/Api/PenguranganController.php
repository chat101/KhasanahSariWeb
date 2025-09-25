<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Produksi_Pengurangan; // pastikan nama model benar


class PenguranganController extends Controller
{
    /**
     * POST /api/pengurangan
     * Body JSON:
     * {
     *   "perintah_produksi_id": 123,
     *   "tanggal_produksi": "2025-09-25",
     *   "items": [
     *     {"mproducts_id": 1, "qty": 2, "target_qty": 120, "keterangan": "note"},
     *     {"mproducts_id": 2, "qty": 1, "target_qty": 60}
     *   ],
     *   "include_history": true,          // optional
     *   "history_mproducts_id": 1         // optional filter riwayat
     * }
     */
    public function store(Request $request)
    {
        // --- Validasi dasar
        $data = $request->validate([
            'perintah_produksi_id' => ['required', 'integer', 'min:1', Rule::exists('perintah_produksi', 'id')],
            'tanggal_produksi'     => ['required', 'date'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.mproducts_id' => ['required', 'integer', 'min:1', Rule::exists('mproducts', 'id')],
            'items.*.qty'          => ['required', 'numeric'],         // bisa negatif/positif sesuai bisnis rule?
            'items.*.target_qty'   => ['nullable', 'numeric'],
            'items.*.keterangan'   => ['nullable', 'string', 'max:500'],

            'include_history'      => ['sometimes', 'boolean'],
            'history_mproducts_id' => ['sometimes', 'integer', 'min:1'],
        ]);

        // Cek perintah & tanggal sinkron (opsional tetapi bagus untuk integritas)
        $perintah = Perintah_Produksi::find($data['perintah_produksi_id']);
        if (!$perintah) {
            return response()->json(['message' => 'perintah_produksi tidak ditemukan'], 422);
        }

        // Optional: pastikan tanggal_produksi sama dengan tanggal_perintah (atau biarkan longgar)
        // $tglReq = Carbon::parse($data['tanggal_produksi'])->toDateString();
        // if (Carbon::parse($perintah->tanggal_perintah)->toDateString() !== $tglReq) { ... }

        // Siapkan daftar produk unik dari payload
        $items       = $data['items'];
        $productIds  = collect($items)->pluck('mproducts_id')->map(fn($v) => (int)$v)->unique()->values()->all();

        if (empty($productIds)) {
            return response()->json(['message' => 'Draft kosong.'], 422);
        }

        // Transaksi + locking per produk untuk hitung pengurangan_ke berikutnya
        DB::beginTransaction();
        try {
            // Ambil max pengurangan_ke terakhir per produk dengan lock
            $nextKe = [];
            foreach ($productIds as $pid) {
                $maxKe = DB::table('produksi_pengurangan')
                    ->where('perintah_produksi_id', $perintah->id)
                    ->where('mproducts_id', $pid)
                    ->lockForUpdate()
                    ->max('pengurangan_ke');

                $nextKe[$pid] = (int) ($maxKe ?? 0);
            }

            $now = Carbon::now('Asia/Jakarta');

            // Susun payload insert
            $rows = [];
            foreach ($items as $r) {
                $pid = (int) $r['mproducts_id'];
                $ke  = ++$nextKe[$pid];

                // ID unik per-row (hindari sama semua). Pakai UUID atau pola timestamp+pid+ke.
                $uniqId = $now->format('YmdHis') . "-PGN-{$pid}-{$ke}-" . Str::random(4);

                $rows[] = [
                    'produksi_pengurangan_id' => $uniqId,                         // jika kolom ini ada
                    'pengurangan_ke'          => $ke,
                    'perintah_produksi_id'    => (int) $perintah->id,
                    'mproducts_id'            => $pid,
                    'qty_pengurangan'         => (float) ($r['qty'] ?? 0),
                    'target_qty_pengurangan'  => isset($r['target_qty']) ? (float)$r['target_qty'] : null,
                    'user_id'                 => Auth::id(),
                    'keterangan'              => $r['keterangan'] ?? null,
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }

            // Insert batch
            DB::table('produksi_pengurangan')->insert($rows);

            DB::commit();

            // Respons dasar
            $response = [
                'message'  => 'Pengurangan disimpan.',
                'saved'    => count($rows),
                'perintah' => [
                    'id'      => (int) $perintah->id,
                    'tanggal' => Carbon::parse($perintah->tanggal_perintah)->toDateString(),
                ],
                'next_ke'  => $nextKe, // menunjukkan ke terakhir setelah penambahan
            ];

            // Opsional: kembalikan riwayat terbaru
            if ($request->boolean('include_history')) {
                $historyQuery = Produksi_Pengurangan::with('product:id,nama') // pastikan relation product() ada di model
                    ->where('perintah_produksi_id', $perintah->id)
                    ->orderByDesc('id');

                if ($request->filled('history_mproducts_id')) {
                    $historyQuery->where('mproducts_id', (int)$request->integer('history_mproducts_id'));
                }

                $history = $historyQuery->limit(200)->get()->map(function ($row) {
                    return [
                        'id'              => (int) $row->id,
                        'produksi_pengurangan_id' => $row->produksi_pengurangan_id ?? null,
                        'pengurangan_ke'  => (int) $row->pengurangan_ke,
                        'mproducts_id'    => (int) $row->mproducts_id,
                        'nama_produk'     => optional($row->product)->nama,
                        'qty_pengurangan' => (float) $row->qty_pengurangan,
                        'target_qty'      => $row->target_qty_pengurangan !== null ? (float)$row->target_qty_pengurangan : null,
                        'keterangan'      => $row->keterangan,
                        'created_at'      => optional($row->created_at)->toDateTimeString(),
                    ];
                });

                $response['history'] = $history;
            }

            return response()->json($response, 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            // Log error untuk debugging
            logger()->error('Gagal simpan produksi_pengurangan', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal simpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/pengurangan?perintah_produksi_id=123&mproducts_id=1&per_page=50&page=1
     * Riwayat pengurangan (paging sederhana).
     */
    public function index(Request $request)
    {
        $request->validate([
            'perintah_produksi_id' => ['required', 'integer', 'min:1', Rule::exists('perintah_produksi', 'id')],
            'mproducts_id'         => ['sometimes', 'integer', 'min:1', Rule::exists('mproducts', 'id')],
            'per_page'             => ['sometimes', 'integer', 'min:1', 'max:500'],
            'page'                 => ['sometimes', 'integer', 'min:1'],
        ]);

        $perintahId = (int) $request->integer('perintah_produksi_id');
        $perPage    = (int) $request->query('per_page', 100);
        $page       = (int) $request->query('page', 1);

        $q = Produksi_Pengurangan::with('product:id,nama')
            ->where('perintah_produksi_id', $perintahId);

        if ($request->filled('mproducts_id')) {
            $q->where('mproducts_id', (int)$request->integer('mproducts_id'));
        }

        $q->orderByDesc('id');

        $total = (clone $q)->count();
        $rows  = $q->skip(($page - 1) * $perPage)->take($perPage)->get()->map(function ($row) {
            return [
                'id'              => (int) $row->id,
                'produksi_pengurangan_id' => $row->produksi_pengurangan_id ?? null,
                'pengurangan_ke'  => (int) $row->pengurangan_ke,
                'mproducts_id'    => (int) $row->mproducts_id,
                'nama_produk'     => optional($row->product)->nama,
                'qty_pengurangan' => (float) $row->qty_pengurangan,
                'target_qty'      => $row->target_qty_pengurangan !== null ? (float)$row->target_qty_pengurangan : null,
                'keterangan'      => $row->keterangan,
                'created_at'      => optional($row->created_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'meta' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
            'data' => $rows,
        ]);
    }
    /**
     * Helper khusus pengurangan: menyusun data dasar per tanggal.
     */
    private function buildBaseDataPengurangan(string $tanggal): array
    {
        // Status lock mengikuti perintah_produksi di tanggal tsb
        $locked = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)
            ->where('status', 1)
            ->exists();

        $produks = MasterProduct::select('id', 'nama', 'patokan')->get()->keyBy('id');

        // Sum pengurangan per produk pada tanggal tsb (join via whereHas ke perintah_produksi)
        $agg = Produksi_Pengurangan::whereHas('perintahProduksi', function ($q) use ($tanggal) {
            $q->whereDate('tanggal_perintah', $tanggal);
        })
            ->selectRaw('mproducts_id, SUM(qty_pengurangan) as total_pengurangan, SUM(COALESCE(target_qty_pengurangan,0)) as total_target_pengurangan')
            ->groupBy('mproducts_id')
            ->get();

        $rows = $agg->map(function ($r) use ($produks) {
            $p = $produks->get($r->mproducts_id);
            $patokan = (float) ($p->patokan ?? 0);
            $qtyTong = (float) ($r->total_pengurangan ?? 0);
            return [
                'mproducts_id'          => (int) $r->mproducts_id,
                'nama'                  => $p->nama ?? '-',
                'patokan'               => $patokan,
                'total_pengurangan'     => $qtyTong,
                'konversi_pengurangan'  => (int) round($qtyTong * $patokan),
                'total_target_peng'     => (float) ($r->total_target_pengurangan ?? 0),
            ];
        })->filter(fn($x) => (float)$x['total_pengurangan'] !== 0.0)
            ->sortBy('nama')
            ->values();

        // Ambil perintah id (pertama) di tanggal tsb untuk hitung max pengurangan_ke
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)->first();
        $perintahId = $perintah?->id ?? null;

        $maxKe = 0;
        $detailMax = collect();
        if ($perintahId) {
            $maxKe = (int) (Produksi_Pengurangan::where('perintah_produksi_id', $perintahId)->max('pengurangan_ke') ?? 0);
            if ($maxKe > 0) {
                $detailMax = Produksi_Pengurangan::where('perintah_produksi_id', $perintahId)
                    ->where('pengurangan_ke', $maxKe)
                    ->selectRaw('mproducts_id, SUM(qty_pengurangan) as qty')
                    ->groupBy('mproducts_id')
                    ->get()
                    ->map(function ($r) use ($produks) {
                        $p = $produks->get($r->mproducts_id);
                        $patokan = (float) ($p->patokan ?? 0);
                        $qtyTong = (float) ($r->qty ?? 0);
                        return [
                            'mproducts_id' => (int) $r->mproducts_id,
                            'nama'         => $p->nama ?? '-',
                            'patokan'      => $patokan,
                            'qty_tong'     => $qtyTong,
                            'konversi'     => (int) round($qtyTong * $patokan),
                        ];
                    })->filter(fn($x) => (float)$x['qty_tong'] !== 0.0)
                    ->sortBy('nama')
                    ->values();
            }
        }

        return [
            'locked'            => (bool) $locked,
            'rows'              => $rows,        // daftar per-produk dengan total pengurangan
            'maxPenguranganKe'  => $maxKe,
            'detailMax'         => $detailMax,   // detail pada pengurangan_ke terakhir
        ];
    }

    /**
     * GET /api/pengurangan/rekap?tanggal=YYYY-MM-DD&per_page=50&page=1
     * Rekap pengurangan per-produk.
     */
    public function pengurangan(Request $request)
    {
        $request->validate([
            'tanggal'  => ['required', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'page'     => ['nullable', 'integer', 'min:1'],
        ]);

        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();
        $perPage = (int) $request->query('per_page', 100);
        $page    = (int) $request->query('page', 1);

        $base = $this->buildBaseDataPengurangan($tanggal);
        $data = $base['rows'];

        $total = $data->count();
        $items = $data->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'date'   => $tanggal,
            'locked' => $base['locked'],
            'meta'   => [
                'rows'     => $total,
                'sum_tong' => (float) $data->sum('total_pengurangan'),
                'sum_pcs'  => (int) round($data->sum('konversi_pengurangan')),
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
            'data'   => $items,
        ]);
    }

    /**
     * GET /api/pengurangan/max?tanggal=YYYY-MM-DD
     * Detail batch pengurangan_ke terakhir.
     */
    public function penguranganMax(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();
        $base = $this->buildBaseDataPengurangan($tanggal);
        $detail = $base['detailMax'];

        return response()->json([
            'date'            => $tanggal,
            'locked'          => $base['locked'],
            'pengurangan_ke'  => (int) $base['maxPenguranganKe'],
            'sum_tong'        => (float) $detail->sum('qty_tong'),
            'sum_pcs'         => (int) $detail->sum('konversi'),
            'data'            => $detail->values(),
        ]);
    }

    /**
     * (Opsional) GET /api/pengurangan/summary?tanggal=YYYY-MM-DD
     */
    public function summaryPengurangan(Request $request)
    {
        $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();
        $base = $this->buildBaseDataPengurangan($tanggal);

        return response()->json([
            'date'   => $tanggal,
            'locked' => $base['locked'],
            'summary' => [
                'pengurangan' => [
                    'rows'     => $base['rows']->count(),
                    'sum_tong' => (float) $base['rows']->sum('total_pengurangan'),
                    'sum_pcs'  => (int) round($base['rows']->sum('konversi_pengurangan')),
                ],
                'pengurangan_max' => [
                    'pengurangan_ke' => (int) $base['maxPenguranganKe'],
                    'sum_tong'       => (float) $base['detailMax']->sum('qty_tong'),
                    'sum_pcs'        => (int) $base['detailMax']->sum('konversi'),
                ],
            ],
        ]);
    }

    /**
     * GET /api/pengurangan/notify-overview?tanggal=YYYY-MM-DD
     * Payload ringan untuk notifikasi.
     */
    public function notifyOverviewPengurangan(Request $request)
    {
        $tanggal = $request->query('tanggal')
            ? Carbon::parse($request->query('tanggal'))->toDateString()
            : Carbon::today('Asia/Jakarta')->toDateString();

        $base = $this->buildBaseDataPengurangan($tanggal);

        return response()->json([
            'wo_pengurangan' => [
                'tanggal'    => $tanggal,
                'row_count'  => $base['rows']->count(),
                'sum_tong'   => (float) $base['rows']->sum('total_pengurangan'),
                'locked'     => (bool) $base['locked'],
                'max_ke'     => (int) $base['maxPenguranganKe'],
            ],
        ]);
    }

    /**
     * GET /api/pengurangan/load?tanggal=YYYY-MM-DD
     * Tabel per pengurangan_ke (1..N).
     */
    public function loadPengurangan(Request $request)
    {
        $request->validate(['tanggal' => ['required', 'date']]);
        $tanggal = Carbon::parse($request->query('tanggal'))->toDateString();

        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $tanggal)->first();
        $produkMap = MasterProduct::select('id', 'nama', 'patokan')->get()->keyBy('id');

        $tables = [];
        if ($perintah) {
            $grouped = Produksi_Pengurangan::where('perintah_produksi_id', $perintah->id)
                ->selectRaw('pengurangan_ke, mproducts_id, SUM(qty_pengurangan) as qty')
                ->groupBy('pengurangan_ke', 'mproducts_id')
                ->orderBy('pengurangan_ke', 'asc')
                ->get()
                ->groupBy('pengurangan_ke');

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
                    ];
                })->filter(fn($x) => (float)$x['qty_tong'] !== 0.0)
                    ->sortBy('nama')
                    ->values();

                $tables[] = [
                    'pengurangan_ke' => (int) $ke,
                    'detail'         => $detail->toArray(),
                    'sum_tong'       => (float) $detail->sum('qty_tong'),
                    'sum_pcs'        => (int) $detail->sum('konversi'),
                ];
            }
        }

        return response()->json([
            'tanggal' => $tanggal,
            'summary' => [
                'pengurangan_tables' => $tables,
            ],
        ]);
    }
}
