<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JurnalDetail extends Model
{
    protected $table = 'jurnal_detail';

    protected $fillable = [
        'jurnal_header_id',
        'coa_id',
        'debet',
        'kredit',
    ];

    public function header()
    {
        return $this->belongsTo(JurnalHeader::class, 'jurnal_header_id');
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class, 'coa_id');
    }
}
