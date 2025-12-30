<?php

namespace App\Models\Operasional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MasterToko;

class KurangSetoran extends Model
{
    protected $table = 'kurang_setorans';

    protected $fillable = [
        'toko_id',
        'tanggal',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal'  => 'date',
        'nominal'  => 'integer',
    ];

    /**
     * Relasi ke Master Toko
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(MasterToko::class, 'toko_id');
    }

    /**
     * Scope: Filter berdasarkan range tanggal
     */
    public function scopeByDateRange($query, string $start, string $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

    /**
     * Scope: Filter berdasarkan toko_id
     */
    public function scopeByToko($query, int $tokoId)
    {
        return $query->where('toko_id', $tokoId);
    }

    /**
     * Scope: Filter berdasarkan multiple toko
     */
    public function scopeByTokos($query, array $tokoIds)
    {
        return $query->whereIn('toko_id', $tokoIds);
    }
}
