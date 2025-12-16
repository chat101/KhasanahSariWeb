<?php

namespace App\Models\Operasional;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';

    protected $fillable = [
        'wilayah_id',
        'nama_area',
        'status',
    ];

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function tokos()
    {
        return $this->hasMany(MasterToko::class, 'area_id');
    }
}
