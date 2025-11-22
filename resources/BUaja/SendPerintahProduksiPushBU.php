<?php

namespace App\Listeners;

use App\Events\PerintahProduksiCreated;
use App\Services\ExpoPushService;
use Illuminate\Support\Facades\Log;

class SendPerintahProduksiPush
{
    public function __construct(private ExpoPushService $expo) {}

    public function handle(PerintahProduksiCreated $event): void
    {
        /** @var \App\Models\Produksi\Perintah_Produksi $pp */
        $pp = $event->pp;

        // Ambil target user (contoh: user pembuat PP saja)
        $targetUsers = collect([$pp->user])->filter();

        // Ambil token Expo user target
        $tokens = $targetUsers
            ->flatMap(fn($u) => $u->pushTokens()->pluck('expo_token'))
            ->unique()
            ->values()
            ->all();

        if (empty($tokens)) {
            Log::info("push: no tokens for pp_created", ['pp_id' => $pp->id]);
            return;
        }

        // KIRIM PUSH (gunakan sendToMany)
        $this->expo->sendToMany(
            tokens:   $tokens,
            title:    'Perintah Produksi Baru',
            body:     "PP #{$pp->id} telah dibuat.",
            data:     [
                'type' => 'pp_created',
                'pp_id' => $pp->id
            ],
            sound:    null,               // bisa ditambah custom sound
            channelId:'alerts',
            priority: 'high',
            ttl:      1800
        );

        Log::info("push pp_created sent", ['pp_id' => $pp->id, 'tokens' => count($tokens)]);
    }
}
// 🚀 Jika kamu ingin PP dikirim ke banyak role / divisi

// Misalnya:

// admin produksi

// leader produksi

// gudang

// Tinggal ubah listener:

// $targetUsers = User::whereIn('role', [
//     'adminproduksi', 'leaderproduksi', 'gudang'
// ])->get();


// Atau:

// $tokens = $this->expo->getTokensByRoles(['adminproduksi', 'leaderproduksi']);
