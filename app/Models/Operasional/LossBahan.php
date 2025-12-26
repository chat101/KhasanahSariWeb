<?php

namespace App\Models\Operasional;

use App\Models\MasterToko;
use App\Models\MasterBarang; // added import
use Illuminate\Database\Eloquent\Model;

class LossBahan extends Model
{
    protected $fillable = [
        'tanggal','toko_id','api_id','barang_id','nominal','keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'integer',
    ];

    public function toko()
    {
        return $this->belongsTo(MasterToko::class, 'toko_id'); // sesuaikan model toko kamu
    }

    // relation to master barang
    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id');
    }

}
