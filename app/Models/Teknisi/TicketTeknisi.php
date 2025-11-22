<?php

namespace App\Models\Teknisi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Produksi\MasterDivisi;
class TicketTeknisi extends Model
{
    use HasFactory;
    protected $table = 'tickets';
    protected $fillable = [
        'user_id',
        'divisi_id',
        'title',
        'description',
        'photo_paths',
        'category',
        'status',
        'action_note',
        'handled_at',
        'closed_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
        'closed_at'  => 'datetime',
        'photo_paths' => 'array',
    ];

    /** Relasi ke User pelapor */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Relasi ke Divisi */
    public function divisi()
    {
        return $this->belongsTo(MasterDivisi::class);
    }
}
