<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Purchasing_Details extends Model
{
    public $table = "purchasing_details";
    protected $fillable = [
        'id',
        'purchasing_id',
        'no_urut',
        'barang_id',
        'qty',
        'gramasi',
        'satuan',
        'harga',
        'diskon',
        'ppn',
        'total',
        'created_at',
        'updated_at',
    ];

    public function purchasing()
    {
        return $this->belongsTo(Purchasing::class, 'purchasing_id');  // Pastikan nama kolomnya benar
    }
}
