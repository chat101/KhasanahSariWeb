<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'nama_bank',
        'nomor_rekening',
        'atas_nama',
        'saldo_awal',
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function getSaldoAkhirAttribute()
    {
        $debit = $this->transactions()->where('tipe', 'debit')->sum('jumlah');
        $kredit = $this->transactions()->where('tipe', 'kredit')->sum('jumlah');

        return $this->saldo_awal + $debit - $kredit;
    }
}
