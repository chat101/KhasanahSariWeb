<?php

namespace App\Http\Controllers\Api;

use App\Models\Slide;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    // public function index()
    // {
    //     return Slide::published()->get()->map(fn($s) => [
    //         'id'  => (string) $s->id,
    //         'uri' => $s->url, // pastikan Storage::url() ok & storage:link sudah dibuat
    //     ]);
    // }

    public function index()
    {
        return Slide::published()->get()->map(function ($s) {
            $path = ltrim($s->image_path, '/');   // slides/xxx.jpg
            return [
                'id'  => (string) $s->id,
                'uri' => url('api/slide-file/' . $path),
            ];
        });
    }

    public function show($path)
    {
        // $path contoh: "slides/XXXXX.jpg"
        if (!Storage::disk('public')->exists($path)) {
            abort(404, "File not found");
        }

        $file     = Storage::disk('public')->get($path);
        $fullPath = Storage::disk('public')->path($path);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return response($file, 200)->header('Content-Type', $mime);
    }
}
