<?php

namespace App\Models\Produksi;

use App\Livewire\Produksi\SelesaikanDivisi;
use Illuminate\Database\Eloquent\Model;

class Perintah_Produksi extends Model
{
    protected $table = 'perintah_produksi';
    protected $fillable = [

        'tanggal_perintah',
        'no_perintah_produksi',
        'user_id',
        'created_at',
        'updated_at'
    ];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function detailPerintahProduksi()
    {
        return $this->hasMany(Detail_Perintah_Produksi::class, 'mproducts_id', 'id');
    }
    public function produksiTambahan()
    {
        return $this->hasMany(Produksi_Tambahan::class, 'perintah_produksi_id', 'id');
    }


    public function selesaiProduksi()
    {
        return $this->hasMany(SelesaikanDivisi::class, 'perintah_produksi_id', 'id');
    }
    public function hasilProduksi()
    {
        return $this->hasMany(Hasil_Produksi::class, 'perintah_produksi_id', 'id');
    }
    public function hasilGiling()
{
    return $this->hasMany(\App\Models\Produksi\HasilGiling::class, 'perintah_produksi_id', 'id');
}
public function hasilDivisi()
{
    return $this->hasMany(HasilDivisi::class, 'perintah_produksi_id');
}

}
