<?php

namespace App\Models\Finance;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Model;

class Setoran_Masuk extends Model
{
    protected $table = 'masuk_setoran';

    protected $fillable = [
        'tanggal_setoran',
        'tokos_id',
        'jumlah_uang',
        'keterangan',
        'status',
        'user_id',
    ];

    public function tokos()
    {
        return $this->belongsTo(MasterToko::class, 'tokos_id');
    }
}
