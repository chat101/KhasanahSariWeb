<?php

namespace App\Models\Produksi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class HasilDivisi extends Model
{
    protected $table = 'hasil_divisi';

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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



}
