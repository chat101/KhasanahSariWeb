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
        $baselineId = $request->integer('baseline_divisi_id');
        $divisiId   = $request->integer('divisi_id') ?? optional($request->user())->divisi_id;

        // 🔸 handle perintah_id via date
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

        // 🔹 1. Ambil hasil utama (produk WO saja)
        $rows = $this->queryHasil($perintahId, $onlyNet, $divisiId, $baselineId);

        // 🔹 2. Ambil produk penerima pengalihan (tanpa WO pun boleh)
        $pengalihanTargets = DB::table('produksi_pengalihan_items as pi')
            ->join('produksi_pengalihan as ph', 'ph.id', '=', 'pi.pengalihan_id')
            ->join('mproducts as mp', 'mp.id', '=', 'pi.target_mproducts_id')
            ->where('ph.perintah_produksi_id', $perintahId)
            ->selectRaw('
                mp.id as mproducts_id,
                mp.nama as product_name,
                SUM(pi.qty_pcs) as qty_pengalihan_masuk
            ')
            ->groupBy('mp.id','mp.nama')
            ->get();

        // 🔹 3. Ambil daftar produk dari WO
        $woProducts = DB::table('detail_perintah_produksi')
            ->where('perintah_produksi_id', $perintahId)
            ->pluck('mproducts_id')
            ->toArray();

        // 🔹 4. Gabungkan (WO + penerima pengalihan) jadi whitelist
        $allowedIds = collect($woProducts)
            ->merge($pengalihanTargets->pluck('mproducts_id'))
            ->unique()
            ->values()
            ->toArray();

        // 🔹 5. Filter hasil query agar hanya produk yang diizinkan muncul
        $rows = collect($rows)
        ->filter(fn($r) => in_array($r['mproducts_id'], $allowedIds))
        ->values()
        ->toArray();

        // 🔹 6. Tambahkan produk penerima pengalihan yang belum ada di WO
        $existingIds = collect($rows)->pluck('mproducts_id')->toArray();
        foreach ($pengalihanTargets as $p) {
            if (!in_array($p->mproducts_id, $existingIds)) {
                $rows[] = [
                    'mproducts_id'       => (int) $p->mproducts_id,
                    'product_name'       => $p->product_name,
                    'qty_total'          => 0,
                    'target_giling'      => 0,
                    'realisasi_divisi'   => null,
                    'realisasi_baseline' => null,
                    'realisasi'          => null,
                    'qty_pengalihan'     => (int) $p->qty_pengalihan_masuk,
                    'selisih'            => 0,
                    'sumber'             => 'PENGALIHAN',
                    'is_pengalihan'      => true,
                ];
            }
        }

        // 🔹 7. Hitung total pengalihan masuk & keluar per produk
        $pengalihanUnion = DB::table('produksi_pengalihan_items as pi')
            ->join('produksi_pengalihan as ph', 'ph.id', '=', 'pi.pengalihan_id')
            ->where('ph.perintah_produksi_id', $perintahId)
            ->selectRaw('pi.source_mproducts_id as mproducts_id, SUM(pi.qty_pcs * -1) as qty')
            ->groupBy('pi.source_mproducts_id')
            ->unionAll(
                DB::table('produksi_pengalihan_items as pi')
                    ->join('produksi_pengalihan as ph', 'ph.id', '=', 'pi.pengalihan_id')
                    ->where('ph.perintah_produksi_id', $perintahId)
                    ->selectRaw('pi.target_mproducts_id as mproducts_id, SUM(pi.qty_pcs) as qty')
                    ->groupBy('pi.target_mproducts_id')
            );

        $pengalihanSummary = DB::table(DB::raw("({$pengalihanUnion->toSql()}) as u"))
            ->mergeBindings($pengalihanUnion)
            ->selectRaw("
                u.mproducts_id,
                SUM(CASE WHEN qty > 0 THEN qty ELSE 0 END) as qty_pengalihan_masuk,
                SUM(CASE WHEN qty < 0 THEN ABS(qty) ELSE 0 END) as qty_pengalihan_keluar
            ")
            ->groupBy('u.mproducts_id')
            ->get();

        $mapMasuk  = $pengalihanSummary->pluck('qty_pengalihan_masuk', 'mproducts_id');
        $mapKeluar = $pengalihanSummary->pluck('qty_pengalihan_keluar', 'mproducts_id');

        foreach ($rows as &$r) {
            $id = (int) ($r['mproducts_id'] ?? 0);
            $r['qty_pengalihan_masuk']  = (int) ($mapMasuk[$id] ?? 0);
            $r['qty_pengalihan_keluar'] = (int) ($mapKeluar[$id] ?? 0);
        }
        unset($r);

        // 🔹 8. Urutkan dan buat payload
        $rows = collect($rows)
            ->sortBy(fn($r) => $r['sumber'] ?? 'NORMAL')
            ->values()
            ->toArray();

        $payload = [
            'perintah_id' => $perintahId,
            'tanggal'     => optional(Perintah_Produksi::find($perintahId))->tanggal_perintah,
            'data'        => $rows,
        ];

        if ($withTotals) {
            $payload['totals'] = [
                'qty_total'             => array_sum(array_column($rows, 'qty_total')),
                'target_giling'         => array_sum(array_column($rows, 'target_giling')),
                'realisasi'             => array_sum(array_map(fn($r) => (int) ($r['realisasi_divisi'] ?? 0), $rows)),
                'selisih'               => array_sum(array_column($rows, 'selisih')),
                'qty_pengalihan_masuk'  => array_sum(array_column($rows, 'qty_pengalihan_masuk')),
                'qty_pengalihan_keluar' => array_sum(array_column($rows, 'qty_pengalihan_keluar')),
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
     * 🔹 Query hasil giling utama
     */
    /**
     * Ambil data hasil produksi dari tabel-tabel dasar.
     */
    private function queryHasil(
        int $perintahId,
        bool $onlyNetPositive = true,
        ?int $divisiId = null,
        ?int $baselineId = null
    ): array {
        $effectiveDivisiId = ($divisiId === 0) ? 2 : $divisiId;

        // 🔹 Subquery untuk pengurangan dan tambahan
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

        // 🔹 Realisasi divisi & baseline
        $subRealisasi = DB::table('hasil_divisi')
            ->selectRaw('perintah_produksi_id, mproducts_id, divisi_id, SUM(qty_hasil) AS qty_hasil')
            ->where('perintah_produksi_id', $perintahId)
            ->when($effectiveDivisiId, fn ($q) => $q->where('divisi_id', $effectiveDivisiId))
            ->groupBy('perintah_produksi_id', 'mproducts_id', 'divisi_id');

        $subBaseline = DB::table('hasil_divisi')
            ->selectRaw('perintah_produksi_id, mproducts_id, SUM(qty_hasil) AS qty_hasil_baseline')
            ->where('perintah_produksi_id', $perintahId)
            ->when($baselineId, fn ($q) => $q->where('divisi_id', $baselineId))
            ->groupBy('perintah_produksi_id', 'mproducts_id');

        // 🔹 Query utama hanya produk yang punya WO
        $q = DB::table('mproducts as mp')
            ->join('detail_perintah_produksi as dpp', function ($j) use ($perintahId) {
                $j->on('dpp.mproducts_id', '=', 'mp.id')
                  ->where('dpp.perintah_produksi_id', '=', $perintahId);
            })
            ->whereIn('mp.id', function ($sub) use ($perintahId) {
                $sub->select('mproducts_id')
                    ->from('detail_perintah_produksi')
                    ->where('perintah_produksi_id', $perintahId);
            })
            ->leftJoinSub($subPengurangan, 'p', fn ($j) => $j->on('p.mproducts_id', '=', 'mp.id'))
            ->leftJoinSub($subTambahan, 't', fn ($j) => $j->on('t.mproducts_id', '=', 'mp.id'))
            ->leftJoinSub($subRealisasi, 'hd', fn ($j) => $j->on('hd.mproducts_id', '=', 'mp.id'))
            ->leftJoinSub($subBaseline, 'hb', fn ($j) => $j->on('hb.mproducts_id', '=', 'mp.id'))
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

        // 🔹 Optional: filter hanya qty positif
        if ($onlyNetPositive) {
            $q->whereRaw('
                (COALESCE(dpp.produksi_qty,0)
                 + COALESCE(t.qty_tambahan,0)
                 - COALESCE(p.qty_pengurangan,0)) > 0
            ');
        }

        $rows = $q->get();

        // 🔹 Format hasil
        return $rows->map(function ($r) use ($effectiveDivisiId, $baselineId) {
            $realisasiDiv  = (int) ($r->realisasi_divisi ?? 0);
            $realisasiBase = (int) ($r->realisasi_baseline ?? 0);
            $targetSisa    = (int) $r->sisa_target;

            if (!$baselineId || ($effectiveDivisiId === $baselineId)) {
                $realisasiDisplay = $r->realisasi_divisi !== null ? $realisasiDiv : null;
                $selisihDisplay   = $realisasiDiv - $targetSisa;
            } else {
                $realisasiDisplay = $realisasiBase;
                $selisihDisplay   = $realisasiBase - $realisasiDiv;
            }

            return [
                'mproducts_id'       => (int) $r->mproducts_id,
                'product_name'       => $r->product_name,
                'qty_total'          => (int) $r->qty_total,
                'target_giling'      => $targetSisa,
                'realisasi_divisi'   => $realisasiDiv ?: null,
                'realisasi_baseline' => $realisasiBase ?: null,
                'realisasi'          => $realisasiDisplay,
                'selisih'            => (int) $selisihDisplay,
                'is_pengalihan'      => false,
            ];
        })->toArray();
    }


    /**
     * 🔹 Query pengalihan: hasil gabungan source (-) dan target (+)
     */
    private function queryPengalihan(int $perintahId)
    {
        $source = DB::table('produksi_pengalihan_items as i')
            ->join('produksi_pengalihan as p', 'p.id', '=', 'i.pengalihan_id')
            ->join('detail_perintah_produksi as d', 'd.id', '=', 'i.detail_perintah_produksi_id')
            ->selectRaw('i.source_mproducts_id as mproducts_id, SUM(i.qty_pcs * -1) as qty')
            ->where('d.perintah_produksi_id', $perintahId)
            ->groupBy('i.source_mproducts_id');

        $target = DB::table('produksi_pengalihan_items as i')
            ->join('produksi_pengalihan as p', 'p.id', '=', 'i.pengalihan_id')
            ->join('detail_perintah_produksi as d', 'd.id', '=', 'i.detail_perintah_produksi_id')
            ->selectRaw('i.target_mproducts_id as mproducts_id, SUM(i.qty_pcs) as qty')
            ->where('d.perintah_produksi_id', $perintahId)
            ->groupBy('i.target_mproducts_id');

        $union = $source->unionAll($target);

        return DB::table(DB::raw("({$union->toSql()}) as u"))
            ->mergeBindings($union)
            ->selectRaw('mproducts_id, SUM(qty) as qty')
            ->groupBy('mproducts_id')
            ->pluck('qty', 'mproducts_id');
    }

    public function store(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::guard('sanctum')->id() ?? Auth::id();
        if (!$userId) return response()->json(['message' => 'Unauthenticated.'], 401);

        $data = $request->validate([
            'perintah_id'          => 'required|integer|exists:perintah_produksi,id',
            'items'                => 'required|array|min:1',
            'items.*.mproducts_id' => 'required|integer|exists:mproducts,id',
            'items.*.qty_hasil'    => 'required|integer|min:0',
            'divisi_id'            => 'nullable|integer',
            'items.*.divisi_id'    => 'nullable|integer',
        ]);

        $rootDivisiId = $request->integer('divisi_id') ?? optional($request->user())->divisi_id;

        foreach ($data['items'] as $row) {
            $resolvedDivisi = $row['divisi_id'] ?? $rootDivisiId;
            if (is_null($resolvedDivisi) || (int) $resolvedDivisi === 0) {
                return response()->json(['message' => 'Anda Tidak Punya Hak Untuk Input Hasil'], 403);
            }
        }

        DB::transaction(function () use ($data, $userId, $rootDivisiId) {
            foreach ($data['items'] as $row) {
                $divisiId = $row['divisi_id'] ?? $rootDivisiId;

                HasilDivisi::updateOrCreate(
                    [
                        'perintah_produksi_id' => (int) $data['perintah_id'],
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

        return response()->json([
            'message' => 'Realisasi giling tersimpan.',
            'saved'   => count($data['items']),
        ]);
    }
}

