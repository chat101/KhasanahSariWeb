<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // maks 5 MB
        ]);

        $path = $request->file('file')->store('tickets', 'public');

        return response()->json([
            'message' => 'Upload berhasil',
            'path' => '/storage/' . $path,
        ]);
    }
}
