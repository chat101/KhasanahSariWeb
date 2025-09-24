<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gudang_Masuk_Detail extends Model
{
    public $table = "gudang_masuk_detail";
    protected $fillable = [
        'id',
        'barang_masuk_id',
        'barang_id',
        'nmbarang',
        'qty',
        'satuan',
        'gramasi',
        'no_urut',
    ];

    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id', 'id');
    }
}
