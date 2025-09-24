<?php

namespace App\Models\Finance;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Model;

class MasterKontrakan extends Model
{
    protected $table = 'kontrakan_master';
    protected $fillable = ['area','toko_id','jenis','bank','nama_rekening','no_rekening','nilai_sewa','keterangan'];

  // Relasi Many to One
  public function toko()
  {
      return $this->belongsTo(\App\Models\MasterToko::class, 'toko_id', 'id');
  }



}
