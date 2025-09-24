<?php

namespace App\Models\Produksi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MasterDivisi extends Model
{
    protected  $table = "divisi";
    public function users()
    {
        return $this->hasMany(User::class, 'divisi_id');
    }
}
