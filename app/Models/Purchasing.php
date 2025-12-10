<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\GudangMasuk;
use App\Models\Purchasing\PurchasingPayment;

class Purchasing extends Model
{
    public $table = "purchasing";
    protected $fillable = [
        'id',
        'gudangmasuk_id',
        'tgl_input',
        'user_id',
        'status_bayar',
        'grandtotal',
        'created_at',
        'updated_at',
    ];
    public function supplier()
    {
        return $this->belongsTo(MasterSupplier::class, 'supplier_id');
    }
    public function details()
    {
        return $this->hasMany(Purchasing_Details::class, 'purchasing_id');
    }
    public function gudang_masuk()
    {
        return $this->belongsTo(Gudang_Masuk::class, 'gudangmasuk_id');
    }
    // app/Models/Purchasing.php

    public function payments()
    {
        return $this->hasMany(PurchasingPayment::class, 'purchasing_id');
    }

    /**
     * total bayar (akumulasi cicilan)
     */
    public function getTotalBayarAttribute()
    {
        // pastikan relasi payments di-load untuk menghindari N+1
        return $this->payments->sum('jumlah_bayar');
    }

    /**
     * sisa hutang
     */
    public function getSisaHutangAttribute()
    {
        return max(0, $this->grandtotal - $this->total_bayar);
    }

    /**
     * scope untuk ambil data yang masih punya hutang
     */
    public function scopePiutang($query)
    {
        return $query->where('grandtotal', '>', 0)
            ->where(function ($q) {
                $q->where('status_bayar', 0)
                    ->orWhereNull('status_bayar');
            });
    }
    //Catatan: kalau status_bayar Anda 1 = lunas, 0 = belum → nanti kita update ke 1 kalau sisa hutang sudah 0.
}
