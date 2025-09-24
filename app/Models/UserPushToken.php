<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPushToken extends Model
{
    protected $fillable = [
        'user_id','expo_token','native_token',
        'device_brand','device_model','device_os','device_os_ver',
        'is_emulator','last_seen_at'
    ];
    protected $casts = ['is_emulator' => 'boolean', 'last_seen_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }

}
