<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineProduct extends Model
{
    protected $table = 'machine_product';

    protected $fillable = [
        'machine_id',
        'mproduct_id',
        'kapasitas_per_jam',
        'waktu_setup_menit',
        'is_active',
    ];
}
