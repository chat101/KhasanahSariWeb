<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class ListReject extends Model
{
    public $table  = 'listreject';
    protected $fillable = [
        'keterangan',
        'created_at',
        'updated_at',
    ];

    public function hasilReject()
    {
        return $this->hasMany(HasilReject::class, 'listreject_id'); // ✅ Sesuai nama kolom sebenarnya
    }
}
