<?php

namespace App\Events;

use App\Models\Produksi\Perintah_Produksi;
use Illuminate\Foundation\Events\Dispatchable;

class PerintahProduksiCreated
{
    use Dispatchable;

    public function __construct(public Perintah_Produksi $pp) {}
}
