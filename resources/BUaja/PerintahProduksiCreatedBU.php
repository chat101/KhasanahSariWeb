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

        /**
         * ===========================================
         * 🎯 TARGET ROLE YANG MENDAPAT NOTIFIKASI PP
         * ===========================================
         * - admin
         * - adminproduksi
         * - leaderproduksi
         * - gudang
         * -------------------------------------------
         * Jika mau tambah role: tinggal tambah di array
         */
        $targetRoles = [
            'admin',
            'adminproduksi',
            'leaderproduksi',
            'gudang',
        ];

        // Gunakan service supaya bersih
        $this->expo->sendToRoles(
            roles: $targetRoles,
            title: 'Perintah Produksi Baru',
            body:  "PP #{$pp->id} telah dibuat.",
            data: [
                'type' => 'pp_created',
                'pp_id' => $pp->id,
                'tanggal' => $pp->tanggal_perintah,
            ],
            sound: null // bisa ganti "alarm.wav" kalau ingin
        );

        Log::info("push pp_created sent (multiple roles)", [
            'pp_id' => $pp->id,
            'roles' => $targetRoles
        ]);
    }
}
