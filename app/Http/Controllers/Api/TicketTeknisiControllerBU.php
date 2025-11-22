<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Teknisi\TicketTeknisi;

class TicketTeknisiController extends Controller
{
    /**
     * Tampilkan semua tiket teknisi.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = TicketTeknisi::with('user', 'divisi')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $tickets]);
    }

    /**
     * Buat tiket baru.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo_paths' => 'nullable|array',
            'photo_paths.*' => 'string',
            'category' => 'nullable|string|max:100',
        ]);

        $ticket = TicketTeknisi::create([
            'user_id' => $user->id,
            'divisi_id' => $user->divisi_id ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'photo_paths' => !empty($validated['photo_paths'])
                ? json_encode($validated['photo_paths'])
                : null,
            'category' => $validated['category'] ?? 'Lainnya',
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Tiket berhasil dibuat.',
            'data' => $ticket,
        ], 201);
    }

    /**
     * Tandai tiket sebagai sedang dikerjakan.
     *
     * @param  TicketTeknisi  $ticket
     * @return JsonResponse
     */
    public function start(Request $request, TicketTeknisi $ticket)
    {
        Log::info('Start hit', [
            'user' => $request->user()?->id,
            'auth_header' => $request->header('Authorization'),
        ]);

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($ticket->status !== 'pending') {
            return response()->json(['message' => 'Tiket sudah dimulai atau selesai.'], 400);
        }

        $ticket->update([
            'status' => 'progress',
            'handled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tiket berhasil dimulai.',
            'data' => $ticket,
        ]);
    }
    /**
     * Tandai tiket sebagai selesai dikerjakan.
     *
     * @param  Request  $request
     * @param  TicketTeknisi  $ticket
     * @return JsonResponse
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
            $desc .= "\n\n🛠️ Catatan Teknisi:\n" . $validated['catatan_teknisi'];
        }

        $ticket->update([
            'status' => 'done',
            'description' => $desc,
            'action_note' => $validated['action_note'] ?? null,
            'closed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tiket telah diselesaikan.',
            'data' => $ticket,
        ]);
    }
}
