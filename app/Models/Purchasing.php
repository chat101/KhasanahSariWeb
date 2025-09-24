<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\GudangMasuk;

class Purchasing extends Model
{
    public $table = "purchasing";
    protected $fillable = [
        'id',
        'gudangmasuk_id',
        'tgl_input',
        'user_id',
        'status_bayar',
        'grandtotal',
        'created_at',
        'updated_at',
    ];

    public function details()
    {
        return $this->hasMany(Purchasing_Details::class, 'purchasing_id');
    }
    public function gudangMasuk()
    {
        return $this->belongsTo(Gudang_Masuk::class, 'gudangmasuk_id');
    }
}
