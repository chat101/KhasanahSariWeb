<?php

namespace App\Models\Finance;


use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\PembayaranPiutang;
use App\Models\MasterToko;

class Piutang extends Model
{
    protected $table = 'piutang';
    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected $fillable = ['tanggal','trans_id','toko_id','kategori','qty','total_piutang','keterangan'];
    public $timestamps = true;
    public function toko()
    {
        return $this->belongsTo(MasterToko::class);
    }

    public function pembayaran() {
        return $this->hasMany(PembayaranPiutang::class, 'piutang_id');
    }

    public function getSisaAttribute()
    {
        return $this->total_piutang - $this->pembayaran->sum('jumlah_bayar');
    }
}
