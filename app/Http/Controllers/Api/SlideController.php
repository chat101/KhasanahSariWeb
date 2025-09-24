<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slide;

class SlideController extends Controller
{
    public function index()
    {
        return Slide::published()->get()->map(fn($s) => [
            'id'  => (string) $s->id,
            'uri' => $s->url, // pastikan Storage::url() ok & storage:link sudah dibuat
        ]);
    }
}
