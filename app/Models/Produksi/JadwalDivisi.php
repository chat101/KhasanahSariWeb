<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class JadwalDivisi extends Model
{
    //

    protected $table = 'jadwal_divisi';

    protected $fillable = ['msjobs_id', 'hari', 'jumlah'];

    public function divisi()
    {
        return $this->belongsTo(MsJobs::class, 'msjobs_id');
    }
}
