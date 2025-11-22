<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Teknisi\TicketTeknisi;
use App\Models\User;
use App\Models\UserPushToken;
use App\Jobs\SendExpoPush;

class TicketTeknisiController extends Controller
{
    /**
     * List tiket.
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = TicketTeknisi::with('user', 'divisi')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    /**
     * Buat tiket + dispatch push notif ke teknisi (divisi 12).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // ============================
        // 🔍 VALIDASI BARU (untuk file)
        // ============================
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',

            // ⬇️ ini menggantikan photo_paths
            'photos'      => 'nullable|array',
            'photos.*'    => 'file|image|max:4096', // 4 MB per file
        ]);

        // ============================
        // 📸 PROSES SIMPAN FILE
        // ============================
        $paths = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->store('tickets', 'public');
            }
        }

        // ============================
        // 💾 SIMPAN TIKET
        // ============================
        $ticket = TicketTeknisi::create([
            'user_id'     => $user->id,
            'divisi_id'   => $user->divisi_id ?? null,
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,

            // 👇 simpan array path (atau null)
            'photo_paths' => !empty($paths) ? json_encode($paths) : null,

            'category'    => $validated['category'] ?? 'Lainnya',
            'status'      => 'pending',
        ]);


        // ======================================
        // 🔔 KIRIM PUSH NOTIF VIA QUEUE (Tetap)
        // ======================================

        $targetUserIds = User::where('divisi_id', 12)
            ->whereHas('pushTokens')
            ->pluck('id');

        $tokens = UserPushToken::whereIn('user_id', $targetUserIds)
            ->pluck('expo_token')
            ->unique()
            ->values()
            ->toArray();

        if (!empty($tokens)) {
            SendExpoPush::dispatch(
                tokens:   $tokens,
                title:    "Tiket Baru Masuk",
                body:     $ticket->title,
                data: [
                    "type"      => "ticket_new",
                    "ticket_id" => $ticket->id,
                ],
                sound: "biohazard.wav",
                channelId: "ticket_alerts",
                priority: "high",
                ttl: 1800
            );
        }

        return response()->json([
            'message' => 'Tiket berhasil dibuat.',
            'data'    => $ticket,
        ], 201);
    }


    /**
     * Mulai pengerjaan tiket.
     */
    public function start(Request $request, TicketTeknisi $ticket)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($ticket->status !== 'pending') {
            return response()->json(['message' => 'Tiket sudah dimulai atau selesai.'], 400);
        }

        $ticket->update([
            'status'     => 'progress',
            'handled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tiket berhasil dimulai.',
            'data'    => $ticket,
        ]);
    }

    /**
     * Selesaikan tiket.
     */
    public function finish(Request $request, TicketTeknisi $ticket): JsonResponse
    {
        if ($ticket->status !== 'progress') {
            return response()->json([
                'message' => 'Tiket belum dimulai atau sudah selesai.'
            ], 400);
        }

        $validated = $request->validate([
            'action_note' => 'nullable|string|max:500',
        ]);

        $desc = $ticket->description ?? '';
        if (!empty($validated['catatan_teknisi'])) {
            $desc .= "\n\n🛠️ Catatan Teknisi:\n".$validated['catatan_teknisi'];
        }

        // update tiket
        $ticket->update([
            'status'       => 'done',
            'description'  => $desc,
            'action_note'  => $validated['action_note'] ?? null,
            'closed_at'    => now(),
        ]);

        // ============================
        // 🔔 PUSH NOTIF TIKET SELESAI
        // ============================

        // Cari semua teknisi (divisi 12)
        $targetUserIds = User::where('divisi_id', 12)
            ->whereHas('pushTokens')
            ->pluck('id');

        // Ambil token unik
        $tokens = UserPushToken::whereIn('user_id', $targetUserIds)
            ->pluck('expo_token')
            ->unique()
            ->values()
            ->toArray();

        // Kirim push
        if (!empty($tokens)) {
            SendExpoPush::dispatch(
                tokens:   $tokens,
                title:    "Tiket Selesai",
                body:     "Tiket #{$ticket->id} telah diselesaikan",
                data: [
                    "type"      => "ticket_done",
                    "ticket_id" => $ticket->id,
                ],
                sound: "default",
                channelId: "ticket_alerts",
                priority: "high",
                ttl: 1800
            );
        }

        return response()->json([
            'message' => 'Tiket telah diselesaikan.',
            'data'    => $ticket,
        ]);
    }


    /**
     * Hitung badge pending (khusus teknisi).
     */
    public function pendingCount(Request $request)
    {
        $user = $request->user();

        if ($user->divisi_id != 12) {
            return response()->json(['count' => 0]);
        }

        $count = TicketTeknisi::where('status', 'pending')->count();

        return response()->json(['count' => $count]);
    }

    /**
 * API khusus daftar tiket (dipakai daftar_tickets.js).
 * Admin melihat semua tiket. Selain admin → filter divisi_id.
 */
public function listByUser(Request $request): JsonResponse
{
    $user = $request->user();

    $query = TicketTeknisi::with('user', 'divisi')
        ->orderBy('created_at', 'desc');

    if (strtolower($user->role) !== 'admin') {
        $query->where('divisi_id', $user->divisi_id);
    }

    $tickets = $query->get();

  // ============================
// 🔥 Tambahan untuk ADMIN
// ============================
if (strtolower($user->role) === 'admin') {
    $tickets = $tickets->map(function ($t) {
        return [
            'id'               => $t->id,
            'title'            => $t->title,
            'description'      => $t->description,
            'status'           => $t->status,
            'category'         => $t->category,
            'photo_paths'      => $t->photo_paths,

            // waktu
            'created_at'       => $t->created_at,
            'updated_at'       => $t->updated_at,
            'handled_at'       => $t->handled_at,
            'closed_at'        => $t->closed_at,

            // catatan teknisi
            'action_note'      => $t->action_note,

            // relasi
            'user'             => $t->user,
            'divisi'           => $t->divisi,

            // tambahan untuk admin
            'pelapor_nama'     => $t->user->name ?? '-',
            'pelapor_divisi'   => optional($t->user->divisi)->nama_divisi ?? '-',
        ];
    });
}
    return response()->json([
        'data' => $tickets
    ]);
}


}
