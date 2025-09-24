<?php

namespace App\Listeners;

use App\Events\PerintahProduksiCreated;
use App\Services\ExpoPushService;

class SendPerintahProduksiPush
{
    public function __construct(private ExpoPushService $expo) {}

    public function handle(PerintahProduksiCreated $event): void
    {
        // Ambil model dari event, bukan variabel terpisah
        $pp = $event->pp;

        // Ambil target user (contoh: semua baker / atau user_id dari pp)
        $targetUsers = collect([$pp->user])->filter();

        $tokens = $targetUsers
            ->flatMap(fn($u) => $u->pushTokens()->pluck('expo_token'))
            ->unique()
            ->values();

        if ($tokens->isNotEmpty()) {
            $this->expo->send(
                $tokens,
                'Perintah Produksi Baru',
                "PP #{$pp->id} telah dibuat.",
                ['type' => 'pp_created', 'pp_id' => $pp->id]
            );
        }
    }
}
