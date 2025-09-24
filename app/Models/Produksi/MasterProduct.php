<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class MasterProduct extends Model
{
    protected $table = 'mproducts';
    protected $fillable = [
        'produk_id',
        'nama',
        'jenis',
        'hpp_produk',
        'patokan',
        'metode',
        'dekor',
        'tong_produksi',
        'created_at',
        'updated_at'
    ];
    public function detailPerintahProduksi()
    {
        return $this->hasMany(Detail_Perintah_Produksi::class, 'mproducts_id', 'id');
    }

    public function produksiTambahan()
    {
        return $this->hasMany(Produksi_Tambahan::class, 'mproducts_id', 'id');
    }
    public function jobs()
    {
        return $this->hasMany(Master_produkToJob::class, 'msproducts_id');
    }
    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'mproducts_id');
    }
    public function hasilGiling()
    {
        return $this->hasMany(\App\Models\Produksi\HasilGiling::class, 'mproducts_id', 'id');
    }
    public function hasilCounter()
    {
        return $this->hasMany(\App\Models\Produksi\HasilGiling::class, 'mproducts_id', 'id');
    }
}
