<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Jobs\SendExpoPush;
use Illuminate\Http\Request;
use App\Models\UserPushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Teknisi\TicketTeknisi;
use App\Services\ExpoPushService;

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
    public function store(Request $request, ExpoPushService $expo): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',

            'photos'      => 'nullable|array',
            'photos.*'    => 'image|max:4096', // ⬅️ BUKAN string lagi
        ]);

        // ============================
        // 📸 SIMPAN FILE (PATH RELATIF)
        // ============================
        $photoPaths = [];

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                // contoh hasil: "tickets/abc123.jpg"
                $path = $file->store('tickets', 'public');

                // normalisasi backslash kalau di Windows
                $path = str_replace('\\', '/', $path);

                $photoPaths[] = $path;
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
            'photo_paths' => $photoPaths,                  // ⬅️ array, Laravel akan JSON-encode
            'category'    => $validated['category'] ?? 'Lainnya',
            'status'      => 'pending',
        ]);


        // 🔔 Cari token teknisi (divisi 12)
        $targetUserIds = User::where('divisi_id', 12)
            ->whereHas('pushTokens')
            ->pluck('id');

        $tokens = UserPushToken::whereIn('user_id', $targetUserIds)
            ->pluck('expo_token')
            ->unique()
            ->values()
            ->toArray();

        // 🔔 Kirim push langsung via service (TANPA job)
        if (!empty($tokens)) {
            try {
                $expo->sendToMany(
                    tokens:    $tokens,
                    title:     "Tiket Baru Masuk",
                    body:      $ticket->title,
                    data:      [
                        "type"      => "ticket_new",
                        "ticket_id" => $ticket->id,
                    ],
                    sound:     "biohazard.wav",
                    channelId: "ticket_alerts",
                    priority:  "high",
                    ttl:       1800,
                );
            } catch (\Throwable $e) {
                Log::error('expo push ticket_new error', [
                    'error' => $e->getMessage(),
                ]);
            }
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
    public function finish(Request $request, TicketTeknisi $ticket, ExpoPushService $expo): JsonResponse
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
        if (!empty($validated['action_note'])) {
            $desc .= "\n\n🛠️ Catatan Teknisi:\n" . $validated['action_note'];
        }

        // update tiket
        $ticket->update([
            'status'       => 'done',
            'description'  => $desc,
            'action_note'  => $validated['action_note'] ?? null,
            'closed_at'    => now(),
        ]);

        // ============================
        // 🔔 PUSH NOTIF KE PELAPOR
        // ============================
        try {
            // ambil semua token milik pelapor (bisa lebih dari 1 device)
            $tokens = UserPushToken::where('user_id', $ticket->user_id)
                ->pluck('expo_token')
                ->unique()
                ->values()
                ->toArray();

            if (!empty($tokens)) {
                $expo->sendToMany(
                    tokens:    $tokens,
                    title:     "Tiket Selesai",
                    body:      "Tiket #{$ticket->id} telah diselesaikan teknisi",
                    data:      [
                        "type"      => "ticket_done",
                        "ticket_id" => $ticket->id,
                    ],
                    sound:     "biohazard.wav",   // ← UBAH INI
                    channelId: "ticket_alerts",
                    priority:  "high",
                    ttl:       1800
                );
            }
        } catch (\Throwable $e) {
            Log::error('expo push ticket_done error', [
                'error'     => $e->getMessage(),
                'ticket_id' => $ticket->id,
            ]);
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
                    'user_close_at' => $t->user_close_at,
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
    public function userClose(Request $request, TicketTeknisi $ticket)
{
    $user = $request->user();

    // Hanya pelapor yang boleh menutup
    if ($ticket->user_id !== $user->id) {
        return response()->json([
            'message' => 'Anda tidak memiliki akses menutup tiket ini.'
        ], 403);
    }

    // Harus status = done (teknisi sudah selesai)
    if ($ticket->status !== 'done') {
        return response()->json([
            'message' => 'Tiket belum diselesaikan teknisi.'
        ], 400);
    }

    // Kalau sudah ditutup user
    if ($ticket->user_close_at !== null) {
        return response()->json([
            'message' => 'Tiket sudah ditutup oleh Anda sebelumnya.'
        ], 400);
    }

    $ticket->update([
        'user_close_at' => now(),
    ]);

    return response()->json([
        'message' => 'Tiket berhasil ditutup.',
        'data' => $ticket
    ]);
}

}
