<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    // GET /api/notify/overview
    // Ringkas: perintah terbaru, jumlah item WO Utama, dan max tambahan_ke
    public function overview(Request $request)
    {
        // Boleh dibatasi per role di sini kalau perlu
        $latest = Perintah_Produksi::orderBy('tanggal_perintah', 'desc')->first();

        if (!$latest) {
            return response()->json([
                'ok' => true,
                'wo_utama'    => null,
                'wo_tambahan' => null,
                'message'     => 'Belum ada perintah produksi sama sekali.'
            ]);
        }

        $utamaCount = (int) Detail_Perintah_Produksi::where('perintah_produksi_id', $latest->id)->count();

        $maxTambahan = (int) (Produksi_Tambahan::where('perintah_produksi_id', $latest->id)->max('tambahan_ke') ?? 0);

        return response()->json([
            'ok' => true,
            'wo_utama' => [
                'perintah_id'   => $latest->id,
                'tanggal'       => (string) $latest->tanggal_perintah,
                'row_count'     => $utamaCount,             // utk deteksi "ada data baru"
                'version'       => $latest->updated_at?->timestamp, // penanda perubahan
            ],
            'wo_tambahan' => [
                'perintah_id'     => $latest->id,
                'tanggal'         => (string) $latest->tanggal_perintah,
                'max_tambahan_ke' => $maxTambahan,
            ],
        ]);
    }
}
