<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalTransactionType extends Model
{
    protected $table = 'journal_transaction_types';

    protected $fillable = [
        'code',
        'nama',
    ];

    public function templates()
    {
        return $this->hasMany(JournalTransactionTemplate::class, 'transaction_type_id');
    }
}
