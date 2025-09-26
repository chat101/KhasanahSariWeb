<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Produksi\HasilReject;
use Illuminate\Support\Facades\Auth;

class HasilRejectController extends Controller
{
   /**
     * GET /api/rejects/lists
     * Ambil master list reject (id, keterangan)
     */
    public function lists(Request $r)
    {
        $rows = DB::table('listreject')
            ->orderBy('keterangan')
            ->get(['id','keterangan']);

        return response()->json(['data' => $rows]);
    }

    /**
     * GET /api/rejects?perintah_id=..&mproducts_id=..[&divisi_id=..]
     * Ambil daftar reject utk 1 produk pada 1 perintah (opsional per divisi).
     * Sekarang ikut mengembalikan listreject_id + nama list.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'perintah_id'  => ['required','integer'],
            'mproducts_id' => ['required','integer'],
            'divisi_id'    => ['nullable','integer'],
        ]);

        // map: 0 -> 4, null = tanpa filter
        $requestedDivisi   = array_key_exists('divisi_id', $data) ? (int)$data['divisi_id'] : null;
        $effectiveDivisiId = is_null($requestedDivisi) ? null : (($requestedDivisi === 0) ? 4 : $requestedDivisi);

        $q = HasilReject::query()
            ->where('perintah_produksi_id', (int)$data['perintah_id'])
            ->where('mproducts_id', (int)$data['mproducts_id']);

        if (!is_null($effectiveDivisiId)) {
            $q->where('divisi_id', $effectiveDivisiId); // ← jika request 0, ini jadi 4
        }

        $rows = $q->leftJoin('listreject as lr', 'lr.id', '=', 'hasil_reject.listreject_id')
            ->orderByDesc('hasil_reject.id')
            ->get([
                'hasil_reject.id',
                'hasil_reject.qty_reject',
                'hasil_reject.keterangan',
                'hasil_reject.user_id',
                'hasil_reject.listreject_id',
                DB::raw('COALESCE(lr.keterangan, "") AS listreject_name'),
                'hasil_reject.created_at',
            ]);

        return response()->json([
            'perintah_id'      => (int)$data['perintah_id'],
            'mproducts_id'     => (int)$data['mproducts_id'],
            'divisi_id'        => $effectiveDivisiId,               // 4 jika request 0
            'total_qty_reject' => (int)$rows->sum('qty_reject'),
            'data'             => $rows,
        ]);
    }


    /**
     * POST /api/rejects
     * (A) single  -> qty_reject, keterangan, (opsional) listreject_id
     * (B) multi   -> items: [{ qty, note, listreject_id? }, ...]
     */
    public function store(Request $request)
    {
        $userId = $request->user()?->id ?? Auth::guard('sanctum')->id() ?? Auth::id();
        if (!$userId) return response()->json(['message' => 'Unauthenticated.'], 401);

        // validasi dasar (wajib)
        $base = $request->validate([
            'perintah_id'  => ['required','integer'],
            'mproducts_id' => ['required','integer'],
            'divisi_id'    => ['nullable','integer'],
        ]);

        // resolve rootDivisiId (0 berarti "tidak punya hak input")
        $rootDivisiId = (int)($base['divisi_id'] ?? optional($request->user())->divisi_id ?? 0);

        // ⛔ blokir input jika 0
        if ($rootDivisiId === 0) {
            return response()->json(['message' => 'Anda Tidak Punya Hak Untuk Input Hasil'], 403);
        }

        // Mode multi-items
        if ($request->filled('items')) {
            $request->validate([
                'items'                 => ['array','min:1'],
                'items.*.qty'           => ['required','integer','min:1'],
                'items.*.note'          => ['nullable','string','max:255'],
                'items.*.listreject_id' => ['nullable','integer','exists:listreject,id'],
            ]);

            $items = $request->input('items', []);
            DB::transaction(function () use ($items, $base, $rootDivisiId, $userId) {
                foreach ($items as $it) {
                    HasilReject::create([
                        'perintah_produksi_id' => (int)$base['perintah_id'],
                        'mproducts_id'         => (int)$base['mproducts_id'],
                        'qty_reject'           => (int)$it['qty'],
                        'keterangan'           => (string)($it['note'] ?? ''),
                        'listreject_id'        => isset($it['listreject_id']) ? (int)$it['listreject_id'] : null,
                        'divisi_id'            => $rootDivisiId,
                        'user_id'              => $userId,
                    ]);
                }
            });

            return response()->json([
                'message' => 'Reject tersimpan.',
                'saved'   => count($items),
            ]);
        }

        // Mode single
        $single = $request->validate([
            'qty_reject'   => ['required','integer','min:1'],
            'keterangan'   => ['nullable','string','max:255'],
            'listreject_id'=> ['nullable','integer','exists:listreject,id'],
        ]);

        $row = HasilReject::create([
            'perintah_produksi_id' => (int)$base['perintah_id'],
            'mproducts_id'         => (int)$base['mproducts_id'],
            'qty_reject'           => (int)$single['qty_reject'],
            'keterangan'           => (string)($single['keterangan'] ?? ''),
            'listreject_id'        => isset($single['listreject_id']) ? (int)$single['listreject_id'] : null,
            'divisi_id'            => $rootDivisiId,
            'user_id'              => $userId,
        ]);

        return response()->json([
            'message' => 'Reject tersimpan.',
            'id'      => $row->id,
        ], 201);
    }


    /**
     * DELETE /api/rejects/{id}
     */
    public function destroy(int $id, Request $request)
    {
        $row = HasilReject::find($id);
        if (!$row) return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        $row->delete();
        return response()->json(['message' => 'Reject dihapus.']);
    }

    public function summary(Request $r) {
        $data = $r->validate([
            'perintah_id' => ['required','integer'],
            'divisi_id'   => ['nullable','integer'],
        ]);

        // map: 0 -> 4, null = tanpa filter
        $requestedDivisi   = array_key_exists('divisi_id', $data) ? (int)$data['divisi_id'] : null;
        $effectiveDivisiId = is_null($requestedDivisi) ? null : (($requestedDivisi === 0) ? 4 : $requestedDivisi);

        $rows = HasilReject::select(
                    'mproducts_id',
                    DB::raw('SUM(qty_reject) as qty'),
                    DB::raw('COUNT(*) as n')
                )
            ->where('perintah_produksi_id', (int)$data['perintah_id'])
            ->when($effectiveDivisiId, fn($q,$d)=>$q->where('divisi_id',$d))
            ->groupBy('mproducts_id')
            ->get();

        return response()->json([
            'perintah_id' => (int)$data['perintah_id'],
            'divisi_id'   => $effectiveDivisiId, // 4 jika request 0
            'data'        => $rows->keyBy('mproducts_id'),
        ]);
    }

}
