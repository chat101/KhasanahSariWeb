<?php

namespace App\Listeners;

use App\Events\PerintahProduksiCreated;
use App\Jobs\SendExpoPush;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendPerintahProduksiPush
{
    public function handle(PerintahProduksiCreated $event): void
    {
        $pp = $event->pp;

        // Grup role yang menerima notifikasi PP
        $roles = [
            'admin',
            'adminproduksi',
            'leaderproduksi',
            'gudang'
        ];

   // Jangan pernah kirim notif PP ke teknisi
        $userIds = User::whereIn('role', $roles)
        ->where('divisi_id', '!=', 12)   // ⬅ tekankan di sini
        ->whereHas('pushTokens')
        ->pluck('id');

        if ($userIds->isEmpty()) {
            Log::info('push: no users for pp_created', ['pp_id' => $pp->id]);
            return;
        }

        $tokens = \App\Models\UserPushToken::whereIn('user_id', $userIds)
            ->pluck('expo_token')
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Log::info('push: no tokens for pp_created', ['pp_id' => $pp->id]);
            return;
        }

        // Dispatch JOB
        SendExpoPush::dispatch(
            tokens:   $tokens,
            title:    'Perintah Produksi Baru',
            body:     "PP #{$pp->id} telah dibuat.",
            data:     [
                'type'   => 'pp_created',
                'pp_id'  => $pp->id,
                'tanggal'=> $pp->tanggal_perintah
            ],
            sound:    null,             // bisa diganti "alarm.wav"
            channelId:'alerts',
            priority: 'high',
            ttl:      1800
        );

        Log::info('pp_created: push job dispatched', [
            'pp_id' => $pp->id,
            'tokens' => count($tokens)
        ]);
    }
}
