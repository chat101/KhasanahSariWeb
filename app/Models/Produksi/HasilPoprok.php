<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class HasilPoprok extends Model
{
    protected $table = 'hasil_poprok';

    protected $fillable = [
        'perintah_produksi_id',
        'mproducts_id',
        'qty_hasil',
        'divisi_id',
        'user_id',
    ];

    public function perintah()
    {
        return $this->belongsTo(\App\Models\Produksi\Perintah_Produksi::class, 'perintah_produksi_id', 'id');
    }
    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class, 'mproducts_id', 'id');
    }
}
