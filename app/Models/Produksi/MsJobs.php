<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class MsJobs extends Model
{
    protected  $table = "msjobs";
    protected $fillable = ['group_job','nama_job','jml_orang','target','unit','jam_mulai','deskripsi','use_target_as_output'];
    protected $casts = ['use_target_as_output' => 'boolean'];

    public function produkToJob()
    {
        return $this->hasMany(Master_produkToJob::class, 'msjobs_id');
    }
    public function jadwalDivisi()
    {
        return $this->hasMany(JadwalDivisi::class, 'msjobs_id'); // ✅ Sesuai nama kolom sebenarnya
    }

    public function masterProducts()
{
    return $this->belongsToMany(MasterProduct::class, 'msproduct_jobs', 'msjobs_id', 'msproducts_id');
}
public function produkToJobSetting()
{
    return $this->belongsToMany(MasterProduct::class, 'msproduct_jobs', 'msjobs_id', 'msproducts_id')
                ->withPivot(['created_at','updated_at'])
                ->withTimestamps();
}
}
