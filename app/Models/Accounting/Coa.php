<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $table = 'coa';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'normal_balance',
        'is_kas',
        'default_role',
    ];

    protected $casts = [
        'is_kas' => 'boolean',
    ];

    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class, 'coa_id');
    }
}
