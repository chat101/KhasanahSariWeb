<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class ProduksiPengalihanItem extends Model
{
    protected $table = 'produksi_pengalihan_items';
    protected $fillable = [
      'pengalihan_id','detail_perintah_produksi_id',
      'source_mproducts_id','target_mproducts_id',
      'qty_pcs','keterangan'
    ];

    public function header()        { return $this->belongsTo(ProduksiPengalihan::class, 'pengalihan_id'); }
    public function sourceProduct() { return $this->belongsTo(\App\Models\Produksi\MasterProduct::class, 'source_mproducts_id'); }
    public function targetProduct() { return $this->belongsTo(\App\Models\Produksi\MasterProduct::class, 'target_mproducts_id'); }
}
