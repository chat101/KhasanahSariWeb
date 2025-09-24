<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class HasilReject extends Model
{
    protected $table = 'hasil_reject';

    protected $fillable = [
        'perintah_produksi_id',
        'mproducts_id',
        'qty_reject',
        'listreject_id',
        'keterangan',
        'divisi_id',
        'user_id',
    ];

    public $timestamps = true;
}
