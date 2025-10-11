<?php

namespace App\Models\Produksi;

use App\Models\Produksi\HasilReject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

      // helper akses url; fallback kalau kolom url kosong
      public function getPublicUrlAttribute(): string
      {
          if (!empty($this->url)) {
              return (string) $this->url;
          }
          if (empty($this->path)) {
              return '';
          }
          return asset('storage/' . ltrim($this->path, '/'));
      }


      // relasi balik (opsional)
      public function hasilReject()
      {
          return $this->belongsTo(HasilReject::class);
      }
}
