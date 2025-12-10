<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_id',
        'category_id',
        'tanggal',
        'tipe',
        'jumlah',
        'ref_no',
        'keterangan',
        'bukti',
        'created_by'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoriesTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getJenisTransaksiAttribute()
    {
        return $this->tipe === 'debit' ? 'Pemasukan' : 'Pengeluaran';
    }
}
