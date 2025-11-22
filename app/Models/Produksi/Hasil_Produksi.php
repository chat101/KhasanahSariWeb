<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class Hasil_Produksi extends Model
{
    //
        protected $table = 'hasil_produksi';
        protected $fillable =[
            'perintah_produksi_id',
            'mproducts_id',
            'po_sistem',
            // 'po_pengalihan',
            'po_penyesuaian',
            'gojek',
            'complain',
            'penjualan_pabrik',
            // 'retur_produksi',
            // 'retur_jadi',
            // 'total_retur',
            'ser',
            'lain_lain',
            'sblm_complain',
            'sample',
            'real',
            'total',
            'created_at',
            'updated_at',
        ];

        protected $casts = [
            'po_sistem'        => 'integer',
            'po_pengalihan'        => 'integer',
            'po_penyesuaian'   => 'integer',
            'gojek'            => 'integer',
            'complain'         => 'integer',
            'penjualan_pabrik' => 'integer',
            'retur_produksi'   => 'integer',
            'retur_jadi'       => 'integer',
            'total_retur'      => 'integer',
            'ser'              => 'integer',
            'lain_lain'        => 'integer',
            'sblm_complain'    => 'integer',
            'sample'          => 'integer',
            'real'           => 'integer',
            'total'            => 'integer',
        ];
        public function perintahProduksi()
        {
            return $this->belongsTo(Perintah_Produksi::class, 'perintah_produksi_id', 'id');
        }
}
