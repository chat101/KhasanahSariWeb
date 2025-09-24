<?php

namespace App\Models;

use App\Models\Purchasing;
use App\Livewire\Master\Barang;
use Illuminate\Database\Eloquent\Model;


class Gudang_Masuk extends Model
{
    public $table = "gudangmasuk";
    protected $fillable = [
        'tanggal',
        'notrans',
        'user_id',
        'no_po',
        'no_faktur',
        'status',
        'supplier_id',
    ];
    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id', 'id');
    }
    public function details()
    {
        return $this->hasMany(Gudang_Masuk_Detail::class, 'barang_masuk_id');
    }
    public function barang()
    {
        return $this->belongsTo(MasterBarang::class, 'barang_id', 'id');
    }
    public function purchasing()
    {
        return $this->hasOne(Purchasing::class, 'gudangmasuk_id');
    }
}
