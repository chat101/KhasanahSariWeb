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
}
