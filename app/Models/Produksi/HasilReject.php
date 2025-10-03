<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produksi\HasilRejectPhoto;

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

    public function listreject()
    {
        return $this->belongsTo(ListReject::class, 'listreject_id', 'id');
    }
    // app/Models/HasilReject.php
    public function photos()
    {
        return $this->hasMany(HasilRejectPhoto::class);
    }
}
