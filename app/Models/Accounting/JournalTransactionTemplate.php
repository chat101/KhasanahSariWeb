<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalTransactionTemplate extends Model
{
    protected $table = 'journal_transaction_templates';

    protected $fillable = [
        'transaction_type_id',
        'side',
        'order_no',
        'source_key',
    ];

    public function transactionType()
    {
        return $this->belongsTo(JournalTransactionType::class, 'transaction_type_id');
    }
}
