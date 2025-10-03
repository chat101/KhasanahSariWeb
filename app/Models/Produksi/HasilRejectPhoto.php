<?php

namespace App\Models\Produksi;

use App\Models\Produksi\HasilReject;
use Illuminate\Database\Eloquent\Model;

class HasilRejectPhoto extends Model
{
    protected $table = 'hasil_reject_photos';

    protected $fillable = [
        'hasil_reject_id',
        'path',
        'url',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'taken_at',
        'uploaded_by',


    ];

    public function reject()
    {
        return $this->belongsTo(HasilReject::class, 'hasil_reject_id');
    }
}
