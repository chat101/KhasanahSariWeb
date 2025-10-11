<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Produksi\HasilRejectPhoto;

class RejectPhotoController extends Controller
{
   /**
     * Simpan foto reject dari aplikasi mobile.
     */
    public function store(Request $request)
    {
        // fleksibel: terima "photo" / "image" / "file"
        $fileKey = null;
        foreach (['photo','image','file'] as $k) {
            if ($request->hasFile($k)) { $fileKey = $k; break; }
        }
        $fileKey = $fileKey ?: 'photo';

        $request->validate([
            'hasil_reject_id' => ['required','integer','exists:hasil_reject,id'],
            // pakai mimes supaya jpg/jpeg/heic/webp aman
            $fileKey          => ['required','file','mimes:jpg,jpeg,png,webp,heic,heif','max:12288'], // 12MB
        ]);

        $file = $request->file($fileKey);

        // simpan ke storage/app/public/rejects/...
        $path = $file->store('rejects', 'public'); // auto buat folder

        $photo = HasilRejectPhoto::create([
            'hasil_reject_id' => (int) $request->input('hasil_reject_id'),
            'path'            => $path,
            'url'             => null, // biarkan null; accessor akan bikin URL publik
            'mime_type'       => $file->getClientMimeType(),
            'size_bytes'      => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'Foto reject tersimpan.',
            'data'    => [
                'id'              => $photo->id,
                'hasil_reject_id' => $photo->hasil_reject_id,
                'public_url'      => $photo->public_url, // accessor di model
                'path'            => $photo->path,
                'mime_type'       => $photo->mime_type,
                'size_bytes'      => $photo->size_bytes,
            ],
        ], 201);
    }
}
