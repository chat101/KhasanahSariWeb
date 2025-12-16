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

        'tempo_hari',
        'max_hutang',
        'contact_person',
        'email',
        'is_aktif',
    ];

    public function gudangMasuks()
    {
        return $this->hasMany(Gudang_Masuk::class, 'supplier_id', 'id');
    }
      // optional: accessor biar gampang ambil nama supplier
      public function getSupplierNameAttribute()
      {
          return $this->gudang_masuk->supplier->nmsupp ?? null;
      }
}
