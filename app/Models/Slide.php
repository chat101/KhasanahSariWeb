<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    protected $fillable = [
        'title','image_path','link_url','position','is_active','starts_at','ends_at'
    ];

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
    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->image_path);
    }
}
