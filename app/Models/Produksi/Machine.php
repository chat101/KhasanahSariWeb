<?php

namespace App\Models\Produksi;

use App\Models\Produksi\MasterProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Machine extends Model
{
    use HasFactory;

    protected $table = 'machines';

    protected $fillable = [
        'kode',
        'nama',
        'kapasitas_per_jam',
        'is_active',
    ];

    // Relasi many-to-many ke produk
    public function products()
    {
        return $this->belongsToMany(MasterProduct::class, 'machine_product', 'machine_id', 'mproduct_id')
                    ->withPivot(['kapasitas_per_jam', 'waktu_setup_menit', 'is_active'])
                    ->withTimestamps();
    }
}
