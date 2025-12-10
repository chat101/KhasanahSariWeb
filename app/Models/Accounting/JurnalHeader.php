<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JurnalHeader extends Model
{
    protected $table = 'jurnal_header';

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'keterangan',
        'ref_type',
        'ref_id',
    ];

    public function details()
    {
        return $this->hasMany(JurnalDetail::class, 'jurnal_header_id');
    }
}
