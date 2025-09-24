<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBarang extends Model
{
    //  use HasFactory;

    // setting dulu di config/database
    // protected $connection = 'mysql_external';
    // public function __construct(array $attributes = [])
    // {
    //     $this->table = env('DB_EXT_DATABASE') . '.' . $this->table;
    //     parent::__construct($attributes);
    // }
    public $table = "msbarangs";



    protected $fillable = [
        'id',
        'barang_id',
        'nmbarang',
        'jenis',
        'harga',
        'sat1',
        'sat2',
        'sat3',
        'sat4',
        'sat5',
        'keterangan',
        'created_at',
        'updated_at',

    ];
    public function detailbarang()
    {
        return $this->hasOne(MsBarangHo::class, 'barang_id', 'id');
    }
    public function gudangMasuk()
    {
        return $this->hasMany(Gudang_Masuk::class, 'barang_id');
    }
    // public function detailbarang()
    // {
    //     return $this->setConnection('mysql')->hasOne(Msbarang::class);
    // }
    // public function gudangMasuk()
    // {
    //     return $this->setConnection('mysql')->hasMany(GudangMasuk::class);
    // }
    // public function hargapatokan()
    // {
    //     return $this->setConnection('mysql')->hasMany(HargaPatokan::class, 'barang_id', 'id');
    // }
    // public function purchasing()
    // {
    //     return $this->setConnection('mysql')->hasMany(Purchasing::class);
    // }
}
