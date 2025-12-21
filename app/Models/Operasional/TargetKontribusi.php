<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;

class TargetKontribusi extends Model
{
    protected $table = 'target_kontribusis';
    protected $fillable = ['kode','nama','tipe','nilai','aktif',
    'pakai_rule_produksi',
    'nilai_produksi_sendiri',
    'nilai_non_produksi_sendiri',];
}
