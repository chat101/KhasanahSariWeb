<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushTokenController extends Controller
{
    public function store(Request $r) {
        $r->validate([
          'token' => ['required','string'],
          'device_name' => ['nullable','string'],
          'platform' => ['nullable','string'],
        ]);
        $user = $r->user();
        $user->expoTokens()->updateOrCreate(
          ['token' => $r->token],
          ['device_name' => $r->device_name, 'platform' => $r->platform]
        );
        return response()->json(['ok' => true]);
      }
}
