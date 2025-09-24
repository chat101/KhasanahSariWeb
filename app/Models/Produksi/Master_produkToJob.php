<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class Master_produkToJob extends Model
{
    //
    public $table  = 'msproduct_jobs';
    public $timestamps = true; // Pastikan ini diatur jika tabel memiliki kolom created
    protected $fillable = [
        'msproducts_id',
        'msjobs_id',
        'created_at',
        'updated_at',

    ];
    public function product()
    {
        return $this->belongsTo(MasterProduct::class, 'msproducts_id');
    }

    public function job()
    {
        return $this->belongsTo(MsJobs::class, 'msjobs_id');
    }
    public function hasilDivisi()
    {
        return $this->hasMany(HasilDivisi::class, 'mproducts_id');
    }
}
