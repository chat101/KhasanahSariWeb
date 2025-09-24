<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GudangMasuk extends Model
{
    public $table = "gudang_masuks";
    protected $fillable = [
        'id',
        'tgl_masuk',
        'user_id',
        'no_po',
        'no_faktur',
        'supplier_id',
        'barang_id',
        'qty_trans',
        'satuan',
        'status',
        'gramasi',
    ];
    protected $primaryKey = 'id'; // or null
    public $incrementing = false;
}
