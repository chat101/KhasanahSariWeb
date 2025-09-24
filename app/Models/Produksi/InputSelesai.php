<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class InputSelesai extends Model
{
    protected $table = 'selesai_divisi';

    // Jika kolom primary key-nya bukan 'id', sesuaikan di sini:

    // Jika tidak pakai timestamps (created_at, updated_at)


    // Jika ingin mem-mass assign kolom tertentu:
    protected $fillable = [
        'perintah_produksi_id',
        'msjobs_id',
        'users_id',
        'waktu_selesai',
        'keterangan',
        'created_at',
        'updated_at',
      ];
      public function perintahProduksi()
      {
          return $this->belongsTo(Perintah_Produksi::class, 'perintah_produksi_id', 'id');
      }
}
