<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class RekeningTelur extends Model
{
    protected $table = 'rekening_telur';
    protected $fillable = ['area','toko_id','jenis','bank','nama_rekening','no_rekening','keterangan'];
    public function toko()
    {
        return $this->belongsTo(\App\Models\MasterToko::class, 'toko_id', 'id');
    }
}
