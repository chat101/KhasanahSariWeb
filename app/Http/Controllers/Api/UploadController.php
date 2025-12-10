<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;   // ⬅️ tambahkan ini
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        // simpan ke disk public
        $path = $request->file('file')->store('tickets', 'public');

        // NORMALISASI: ubah backslash -> slash
        $path = str_replace('\\', '/', $path);          // hasil: "tickets/xxxxx.jpg"

        return response()->json([
            'message' => 'Upload berhasil',
            // PATH RELATIF => disimpan di DB / dipakai di API tickets
            'path' => $path,                             // "tickets/xxxxx.jpg"
            // kalau mau preview langsung dari form upload
            'url'  => url('api/slide-file/'.$path),      // "https://.../api/slide-file/tickets/xxxxx.jpg"
        ]);
    }

    // === NEW: serve file dari storage/public ===
    public function show($path)
    {
        // normalisasi
        $path = str_replace('\\', '/', $path);

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $file     = Storage::disk('public')->get($path);
        $fullPath = Storage::disk('public')->path($path);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return response($file, 200)->header('Content-Type', $mime);
    }
}
