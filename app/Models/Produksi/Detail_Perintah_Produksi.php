<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class Detail_Perintah_Produksi extends Model
{
    protected $table = 'detail_perintah_produksi';
    protected $fillable = ['perintah_tgl', 'perintah_produksi_id', 'mproducts_id', 'produksi_qty', 'target_produksi', 'created_at', 'updated_at'];

    public function masterProduct()
    {
        return $this->belongsTo(MasterProduct::class, 'mproducts_id', 'id'); // sesuaikan nama foreign key jika berbeda
    }
    public function perintahProduksi()
    {
        return $this->belongsTo(Perintah_Produksi::class, 'perintah_produksi_id', 'id');
    }
    public function pengurangan()
    {
        // Relasi via mproducts_id; nanti difilter perintah_produksi_id saat query
        return $this->hasMany(Produksi_Pengurangan::class, 'mproducts_id', 'mproducts_id');
    }
}
