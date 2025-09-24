<?php

namespace App\Models\Finance;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Model;

class PembayaranPiutang extends Model
{
    protected $table = 'pembayaran_piutang';
    protected $fillable = ['piutang_id','tgl_bayar','jumlah_bayar','metode','catatan'];
    protected $casts = [
        'tgl_bayar' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public $timestamps = true;
    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_id');
    }


    public function getSisaAttribute()
    {
        return $this->total_piutang - $this->pembayaran->sum('jumlah_bayar');
    }
}
