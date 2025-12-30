<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;

class KontribusiHarianJobRow extends Model
{
    protected $table = 'kontribusi_harian_job_rows';

    protected $fillable = [
        'job_id','tanggal','jenis',
        'selisih_persen','selisih_rp','kontribusi_rp',
        'disc_persen','disc_rp','retur_persen','retur_rp',
        'gas_persen','gas_rp','telur_persen','telur_rp',
        'loss_bahan','total_kontribusi','payload'
    ];

    protected $casts = ['payload' => 'array'];
    
    public function job()
    {
        return $this->belongsTo(KontribusiHarianJob::class, 'job_id');
    }
}
