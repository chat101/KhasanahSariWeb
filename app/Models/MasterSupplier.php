<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterSupplier extends Model
{
    protected $table = 'mssuppliers';
    protected $fillable = [
        'supplier_id',
        'nmsupp',
        'telpsupp',
        'suppalamat',
    ];

    public function gudangMasuks()
    {
        return $this->hasMany(Gudang_Masuk::class, 'supplier_id', 'id');
    }
}
