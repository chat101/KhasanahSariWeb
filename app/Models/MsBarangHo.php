<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsBarangHo extends Model
{
    protected $primaryKey = 'barang_id'; // or null
    public $incrementing = false;
    protected $table = 'msbarangsho';
    protected $fillable = ['barang_id', 'alamat', 'sat_barang', 'gramasi', 'user_id','created_at','updated_at'];
    // MsBarangHo.php
    public function masterBarang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id'); // sesuaikan nama foreign key jika berbeda
    }
}
