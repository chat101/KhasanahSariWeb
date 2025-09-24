<?php

namespace App\Observers;

use App\Jobs\SendExpoPush;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PerintahProduksiObserver
{
    public function created(Perintah_Produksi $pp): void
    {
        $tanggal = optional($pp->tanggal_perintah)?->toDateString()
            ?? now('Asia/Jakarta')->toDateString();

        // ambil token dari relasi user → expoTokens
        $tokens = User::whereIn('role', ['adminproduksi','leaderproduksi'])
            ->with(['expoTokens:id,user_id,expo_token'])
            ->get()
            ->flatMap(fn ($u) => $u->expoTokens->pluck('expo_token'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!$tokens) return;

        try {
            SendExpoPush::dispatch(
                tokens:   $tokens,
                title:    'Data Baru di Work Order Utama',
                body:     "Tanggal {$tanggal}",
                data:     [
                    'type'        => 'wo_utama_created',
                    'url'         => '/work-order-utama',
                    'tanggal'     => $tanggal,
                    'perintah_id' => $pp->id,
                ],
                channelId: 'alerts',
                priority:  'high',
            ); // afterCommit sudah true di job
            SendExpoPush::dispatch(
                tokens: $tokens,
                title:  'Data Baru di Work Order Utama',
                body:   "Tanggal {$tanggal}",
                data:   [/* ... */],
                channelId: 'alerts',
                priority:  'high',
              )->afterCommit();

        } catch (\Throwable $e) {
            Log::error('Dispatch push WO Utama gagal', ['err' => $e->getMessage()]);
        }
    }
}
