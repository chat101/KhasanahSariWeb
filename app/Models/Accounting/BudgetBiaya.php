<?php

namespace App\Models\Accounting;

use App\Livewire\Master\Toko;
use Illuminate\Database\Eloquent\Model;

class BudgetBiaya extends Model
{
    protected $table = 'budget_biaya';

    protected $fillable = [
        'toko_id',
        'idakun_api',
        'tipe_api',
        'ket_api',
        'start_date',
        'end_date',
        'budget',
        'realisasi',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}
