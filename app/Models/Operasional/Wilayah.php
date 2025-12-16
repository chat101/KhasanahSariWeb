<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $fillable = [
        'nama_wilayah',
        'status',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class, 'wilayah_id');
    }
}
