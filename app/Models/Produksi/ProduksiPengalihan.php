<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class ProduksiPengalihan extends Model
{
    protected $table = 'produksi_pengalihan';
    protected $fillable = ['perintah_produksi_id','tanggal','divisi_id','catatan'];

    public function items()
    {
      return $this->hasMany(ProduksiPengalihanItem::class, 'pengalihan_id');
    }
}
