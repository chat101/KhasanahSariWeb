<?php

namespace App\Models\Operasional;

use App\Models\MasterToko;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class MasterProyeksiKontribusi extends Model
{
    protected $table = 'master_proyeksi_kontribusis';

    protected $fillable = [
        'toko_id',
        'jenis',
        'tanggal',
        'qty',
        'rupiah',
        'periode_bulan',
        'periode_tahun',
        'batch_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'qty' => 'integer',
        'rupiah' => 'integer',
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
    ];

    public function toko(): BelongsTo
    {
        return $this->belongsTo(MasterToko::class, 'toko_id');
    }
}
