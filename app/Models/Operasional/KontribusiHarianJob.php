<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;

class KontribusiHarianJob extends Model
{
  protected $table = 'kontribusi_harian_jobs';
    protected $fillable = [
        'tanggal_awal','tanggal_akhir','toko_id','nama_toko','grand_totals','status','error'
    ];
    protected $casts = ['grand_totals' => 'array'];

  public function toko()
  {
      return $this->belongsTo(\App\Models\MasterToko::class, 'toko_id');
  }

  public function rows()
{
    return $this->hasMany(
        KontribusiHarianJobRow::class,
        'job_id',   // foreign key di child table
        'id'        // local key di parent table
    );
}
}
