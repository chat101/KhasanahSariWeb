<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $table = 'complain';
    protected $fillable = [
        'tgl',
        'tokos_id',
        'complain',
        'keterangan',
        'kesalahan'];
    protected $casts = [
        'tgl' => 'date',
    ];
    public function toko()
{
    return $this->belongsTo(\App\Models\MasterToko::class, 'tokos_id');
}
}
