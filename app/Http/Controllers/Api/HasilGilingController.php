<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Produksi\HasilDivisi;
use Illuminate\Support\Facades\Auth;
use App\Models\Produksi\Perintah_Produksi;


class HasilGilingController extends Controller
{
    public function index(Request $request)
    {
        $perintahId = $request->integer('perintah_id');
        $date       = $request->query('date');
        $onlyNet    = filter_var($request->query('only_net_positive', 'true'), FILTER_VALIDATE_BOOLEAN);
        $withTotals = filter_var($request->query('include_totals', 'true'), FILTER_VALIDATE_BOOLEAN);
        $baselineId = $request->integer('baseline_divisi_id'); // contoh: 2

        // ambil divisi_id dari query, atau default ke user yg login
        $divisiId   = $request->integer('divisi_id') ?? optional($request->user())->divisi_id;

        if (!$perintahId && $date) {
            $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $date)
                ->orderByDesc('id')->first();
            if (!$perintah) {
                return response()->json([
                    'message' => 'Perintah produksi tidak ditemukan untuk tanggal: '.$date,
                    'data'    => [],
                ], 404);
            }
            $perintahId = $perintah->id;
        }

        if (!$perintahId) {
            return response()->json(['message' => 'Harus menyertakan perintah_id atau date (YYYY-MM-DD).'], 422);
        }

        // ⬇️ kirim juga $baselineId
        $rows = $this->queryHasil($perintahId, $onlyNet, $divisiId, $baselineId);

        $payload = [
            'perintah_id' => $perintahId,
            'tanggal'     => optional(Perintah_Produksi::find($perintahId))->tanggal_perintah,
            'data'        => $rows,
        ];

        if ($withTotals) {
            $payload['totals'] = [
                'qty_total'     => array_sum(array_column($rows, 'qty_total')),
                'target_giling' => array_sum(array_column($rows, 'target_giling')),
                'realisasi'     => array_sum(array_column($rows, 'realisasi')), // pakai display
                'selisih'       => array_sum(array_column($rows, 'selisih')),
            ];
        }

        return response()->json($payload);
    }

    public function show(int $perintah_id, Request $request)
    {
        $onlyNet    = filter_var($request->query('only_net_positive', 'true'), FILTER_VALIDATE_BOOLEAN);
        $withTotals = filter_var($request->query('include_totals', 'true'), FILTER_VALIDATE_BOOLEAN);
        $baselineId = $request->integer('baseline_divisi_id');
        $divisiId   = $request->integer('divisi_id') ?? optional($request->user())->divisi_id;

        $perintah = Perintah_Produksi::find($perintah_id);
        if (!$perintah) {
            return response()->json(['message' => 'Perintah produksi tidak ditemukan.', 'data' => []], 404);
        }

        $rows = $this->queryHasil($perintah_id, $onlyNet, $divisiId, $baselineId);

        $payload = [
            'perintah_id' => $perintah_id,
            'tanggal'     => $perintah->tanggal_perintah,
            'data'        => $rows,
        ];

        if ($withTotals) {
            $payload['totals'] = [
                'qty_total'     => array_sum(array_column($rows, 'qty_total')),
                'target_giling' => array_sum(array_column($rows, 'target_giling')),
                'realisasi'     => array_sum(array_column($rows, 'realisasi')),
                'selisih'       => array_sum(array_column($rows, 'selisih')),
            ];
        }

        return response()->json($payload);
    }

    /**
     * Basis mproducts/dpp, prefill realisasi dari hasil_divisi yg
     * sudah di-SUM per (mproducts_id, divisi_id) untuk perintah_id tsb.
     */
    private function queryHasil(
        int $perintahId,
        bool $onlyNetPositive = true,
        ?int $divisiId = null,
        ?int $baselineId = null
    ): array {
        // agregasi pengurangan & tambahan
        $subPengurangan = DB::table('produksi_pengurangan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                         SUM(qty_pengurangan) AS qty_pengurangan,
                         SUM(target_qty_pengurangan) AS total_pengurangan')
            ->where('perintah_produksi_id', $perintahId)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        $subTambahan = DB::table('produksi_tambahan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                         SUM(qty_tambahan) AS qty_tambahan,
                         SUM(target_qty_tambahan) AS total_tambahan')
            ->where('perintah_produksi_id', $perintahId)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        // agregasi realisasi per divisi (divisi yang sedang login/queried)
        $subRealisasi = DB::table('hasil_divisi')
            ->selectRaw('perintah_produksi_id, mproducts_id, divisi_id, SUM(qty_hasil) AS qty_hasil')
            ->where('perintah_produksi_id', $perintahId)
            ->when($divisiId, fn ($q) => $q->where('divisi_id', $divisiId))
            ->groupBy('perintah_produksi_id', 'mproducts_id', 'divisi_id');

        // agregasi realisasi baseline (mis. giling = 2)
        $subBaseline = DB::table('hasil_divisi')
            ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_baseline')
            ->where('perintah_produksi_id', $perintahId)
            ->when($baselineId, fn ($q) => $q->where('divisi_id', $baselineId))
            ->groupBy('perintah_produksi_id', 'mproducts_id');

        $q = DB::table('mproducts as mp')
            ->leftJoin('detail_perintah_produksi as dpp', function ($j) use ($perintahId) {
                $j->on('dpp.mproducts_id', '=', 'mp.id')
                  ->where('dpp.perintah_produksi_id', '=', $perintahId);
            })
            ->leftJoinSub($subPengurangan, 'p', fn ($j) => $j->on('p.mproducts_id', '=', 'mp.id'))
            ->leftJoinSub($subTambahan, 't', fn ($j) => $j->on('t.mproducts_id', '=', 'mp.id'))
            // gunakan alias 'hd' agar pasti unik
            ->leftJoinSub($subRealisasi, 'hd', function ($j) use ($perintahId) {
                $j->on('hd.mproducts_id', '=', 'mp.id')
                  ->where('hd.perintah_produksi_id', '=', $perintahId);
            })
            ->leftJoinSub($subBaseline, 'hb', function ($j) use ($perintahId) {
                $j->on('hb.mproducts_id', '=', 'mp.id')
                  ->where('hb.perintah_produksi_id', '=', $perintahId);
            })
            ->selectRaw("
                mp.id AS mproducts_id,
                mp.nama AS product_name,

                COALESCE(dpp.produksi_qty, 0)  AS produksi_qty,
                COALESCE(t.qty_tambahan, 0)    AS qty_tambahan,
                COALESCE(p.qty_pengurangan, 0) AS qty_pengurangan,
                (COALESCE(dpp.produksi_qty,0)
                 + COALESCE(t.qty_tambahan,0)
                 - COALESCE(p.qty_pengurangan,0)) AS qty_total,

                COALESCE(dpp.target_produksi, 0) AS target_produksi,
                COALESCE(t.total_tambahan, 0)    AS total_tambahan,
                COALESCE(p.total_pengurangan, 0) AS total_pengurangan,
                (COALESCE(dpp.target_produksi,0)
                 + COALESCE(t.total_tambahan,0)
                 - COALESCE(p.total_pengurangan,0)) AS sisa_target,

                hd.qty_hasil  AS realisasi_divisi,
                hb.qty_hasil_baseline AS realisasi_baseline
            ")
            ->orderBy('mp.urutan');

        if ($onlyNetPositive) {
            $q->whereRaw('
                (COALESCE(dpp.produksi_qty,0)
                 + COALESCE(t.qty_tambahan,0)
                 - COALESCE(p.qty_pengurangan,0)) > 0
            ');
        }

        $rows = $q->get();

        return $rows->map(function ($r) use ($divisiId, $baselineId) {
            $realisasiDiv  = is_null($r->realisasi_divisi)   ? 0 : (int) $r->realisasi_divisi;
            $realisasiBase = is_null($r->realisasi_baseline) ? 0 : (int) $r->realisasi_baseline;
            $targetSisa    = (int) $r->sisa_target;

            // tampilan & selisih:
            // - jika divisi = baseline (atau baseline tidak diset): selisih = realisasiDiv - target
            // - jika divisi ≠ baseline: selisih = realisasiBaseline - realisasiDiv
            if (!$baselineId || ($divisiId === $baselineId)) {
                $realisasiDisplay = is_null($r->realisasi_divisi) ? null : $realisasiDiv;
                $selisihDisplay   = $realisasiDiv - $targetSisa;
            } else {
                $realisasiDisplay = $realisasiBase; // tampilkan angka giling
                $selisihDisplay   = $realisasiBase - $realisasiDiv;
            }

            return [
                'mproducts_id'        => (int) $r->mproducts_id,
                'product_name'        => $r->product_name,
                'qty_total'           => (int) $r->qty_total,
                'target_giling'       => $targetSisa,
                'realisasi_divisi'    => is_null($r->realisasi_divisi)   ? null : $realisasiDiv,
                'realisasi_baseline'  => is_null($r->realisasi_baseline) ? null : $realisasiBase,
                'realisasi'           => $realisasiDisplay, // FE pakai ini untuk display
                'selisih'             => (int) $selisihDisplay,
            ];
        })->toArray();
    }


    /** POST /api/hasil-giling
     * Body:
     * {
     *   "perintah_id": 123,
     *   "divisi_id": 2,              // ← dari mobile (opsional). kalau ada, dipakai.
     *   "items": [{ "mproducts_id": 1, "qty_hasil": 10, "divisi_id": 2 }]
     * }
     */
    public function store(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::guard('sanctum')->id() ?? Auth::id();
        if (!$userId) return response()->json(['message' => 'Unauthenticated.'], 401);

        $data = $request->validate([
            'perintah_id'         => 'required|integer|exists:perintah_produksi,id',
            'items'               => 'required|array|min:1',
            'items.*.mproducts_id'=> 'required|integer|exists:mproducts,id',
            'items.*.qty_hasil'   => 'required|integer|min:0',
            'divisi_id'           => 'nullable|integer', // level root
            'items.*.divisi_id'   => 'nullable|integer', // override per item
        ]);

        // ⚠️ Prioritas: pakai yang dikirim mobile; kalau kosong fallback user->divisi_id
        $rootDivisiId = $request->integer('divisi_id') ?? optional($request->user())->divisi_id;

        DB::transaction(function () use ($data, $userId, $rootDivisiId) {
            foreach ($data['items'] as $row) {
                $divisiId = $row['divisi_id'] ?? $rootDivisiId;
                HasilDivisi::updateOrCreate(
                    [
                        'perintah_produksi_id' => $data['perintah_id'],
                        'mproducts_id'         => (int) $row['mproducts_id'],
                        'divisi_id'            => (int) $divisiId,
                    ],
                    [
                        'qty_hasil' => (int) $row['qty_hasil'],
                        'user_id'   => $userId,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Realisasi giling tersimpan.', 'saved' => count($data['items'])]);
    }
}
