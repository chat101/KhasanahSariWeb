<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class CategoriesTransaction extends Model
{
    protected $fillable = ['categories_transaction'];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class);
    }
}
