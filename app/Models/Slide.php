<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    protected $fillable = [
        'title','image_path','link_url','position','is_active','starts_at','ends_at'
    ];
   // supaya 'image_url' ikut muncul otomatis kalau mau
   protected $appends = ['image_url'];
    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // scope aktif & urut
    public function scopePublished($q) {
        $now = now();
        return $q->where('is_active', true)
                 ->where(function($q) use ($now){
                     $q->whereNull('starts_at')->orWhere('starts_at','<=',$now);
                 })
                 ->where(function($q) use ($now){
                     $q->whereNull('ends_at')->orWhere('ends_at','>=',$now);
                 })
                 ->orderBy('position');
    }

    // url publik ke gambar
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }

        // gunakan route slide-file yang sudah kita pakai di RN & Postman
        return url('api/slide-file/' . ltrim($this->image_path, '/'));
    }
}
