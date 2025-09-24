<?php

namespace App\Models;

use App\Models\Finance\Piutang;
use App\Livewire\Finance\SetoranMasuk;
use App\Models\Finance\MasterKontrakan;
use Illuminate\Database\Eloquent\Model;

class MasterToko extends Model
{
    protected $table = 'tokos';
    protected $fillable = [
        'tokos_id',
        'tanggal',
        'alamat',
        'created_at',
        'updated_at',
    ];
    public function uangsetor()
    {
        return $this->hasMany(SetoranMasuk::class, 'tokos_id', 'id');
    }
    public function piutang()
    {
        return $this->hasMany(Piutang::class);
    }
    public function kontrakans()
    {
        return $this->hasMany(\App\Models\Finance\MasterKontrakan::class, 'toko_id');
    }
    public function gass()
    {
        return $this->hasMany(\App\Models\Finance\RekeningGas::class, 'toko_id');
    }
    public function eggs()
    {
        return $this->hasMany(\App\Models\Finance\RekeningTelur::class, 'toko_id');
    }
}
