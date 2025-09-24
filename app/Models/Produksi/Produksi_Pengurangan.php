<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class Produksi_Pengurangan extends Model
{
    protected $table = 'produksi_pengurangan';   // <= nama tabel yang benar
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'produksi_pengurangan_id',
        'pengurangan_ke',
        'perintah_produksi_id',
        'mproducts_id',
        'qty_pengurangan',
        'target_qty_pengurangan',
        'user_id',
        'keterangan',
        'created_at',
        'updated_at'
    ];
    public function detail()
    {
        return $this->belongsTo(Detail_Perintah_Produksi::class, 'detail_perintah_produksi_id');
    }
    public function perintahProduksi()
    {
        return $this->belongsTo(Perintah_Produksi::class, 'perintah_produksi_id', 'id');
    }
    public function product() { return $this->belongsTo(MasterProduct::class,'mproducts_id'); }
}
