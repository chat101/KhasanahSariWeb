<?php

namespace App\Models;

use App\Models\Finance\Piutang;
use App\Mode\Finance\SetoranMasuk;
use App\Models\Operasional\Area;
use App\Models\Finance\MasterKontrakan;
use App\Models\Finance\Setoran_Masuk;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\Wilayah;
use Illuminate\Database\Eloquent\Model;

class MasterToko extends Model
{
    protected $table = 'tokos';
    protected $fillable = [

        'nmtoko',
        'area_id',
        'api_id',
        'api_name',
        'alamat',
        'status',
        'produksi_sendiri',
        'created_at',
        'updated_at',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // (opsional) akses cepat wilayah via area
    public function wilayah()
    {
        return $this->hasOneThrough(
            Wilayah::class,
            Area::class,
            'id',          // FK di area yang direferensikan oleh tokos.area_id
            'id',          // PK di wilayah
            'area_id',     // FK di tokos
            'wilayah_id'   // FK di area
        );
    }


    public function uangsetor()
    {
        return $this->hasMany(Setoran_Masuk::class, 'tokos_id', 'id');
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
    public function scopeForUser($q, $user)
    {
        if ($user?->area_id) {
            return $q->where('area_id', $user->area_id);
        }

        if ($user?->wilayah_id) {
            return $q->whereHas('area', fn($a) => $a->where('wilayah_id', $user->wilayah_id));
        }

        return $q; // admin pusat -> semua toko
    }
    public function proyeksiKontribusi()
{
    return $this->hasMany(MasterProyeksiKontribusi::class, 'toko_id');
}

}
