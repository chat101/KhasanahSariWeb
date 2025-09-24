<?php

namespace App\Observers;

use App\Jobs\SendExpoPush;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProduksiTambahanObserver
{
    public function created(Produksi_Tambahan $t): void
    {
        $pp      = $t->perintahProduksi; // pastikan relasi ada di model
        $tanggal = optional($pp?->tanggal_perintah)?->toDateString()
            ?? now('Asia/Jakarta')->toDateString();
        $ke      = (int) ($t->tambahan_ke ?? 0);
        if ($ke <= 0) return;

        // ⚠️ Anti-spam: kirim SEKALI per tambahan_ke
        $isFirstOfKe = !\App\Models\Produksi\Produksi_Tambahan::query()
            ->where('perintah_produksi_id', $pp?->id)
            ->where('tambahan_ke', $ke)
            ->where('id', '<', $t->id)
            ->exists();
        if (!$isFirstOfKe) return;

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
                title:    'Perintah Tambahan Terbaru',
                body:     "Tambahan ke-{$ke} ({$tanggal})",
                data:     [
                    'type'        => 'wo_tambahan_created',
                    'url'         => '/work-order-tambahan',
                    'tanggal'     => $tanggal,
                    'ke'          => $ke,
                    'perintah_id' => $pp?->id,
                ],
                channelId: 'alerts',
                priority:  'high',
            );
            SendExpoPush::dispatch(
                tokens: $tokens,
                title:  'Data Baru di Work Order Utama',
                body:   "Tanggal {$tanggal}",
                data:   [/* ... */],
                channelId: 'alerts',
                priority:  'high',
              )->afterCommit();

        } catch (\Throwable $e) {
            Log::error('Dispatch push WO Tambahan gagal', ['err' => $e->getMessage()]);
        }
    }
}
