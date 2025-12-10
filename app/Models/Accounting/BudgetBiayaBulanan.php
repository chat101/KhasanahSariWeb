<?php

namespace App\Models\Accounting;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Model;

class BudgetBiayaBulanan extends Model
{
    protected $fillable = [
        'toko_id',
        'idakun_api',
        'tahun',
        'bulan',
        'budget',
        'jenis',
        'senin','selasa','rabu','kamis','jumat','sabtu','minggu',
        'nama_akun',
        'tipe_api',
    ];

    public function toko()
    {
        return $this->belongsTo(MasterToko::class, 'toko_id');
    }
}
