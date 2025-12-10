<?php

namespace App\Models\Purchasing;

use App\Models\Purchasing;
use Illuminate\Database\Eloquent\Model;

class PurchasingPayment extends Model
{
    protected $table = 'purchasing_payments';

    protected $fillable = [
        'purchasing_id',
        'tanggal_bayar',
        'jumlah_bayar',
        'metode_bayar',
        'keterangan',
    ];

    public function purchasing()
    {
        return $this->belongsTo(Purchasing::class, 'purchasing_id');
    }
}
