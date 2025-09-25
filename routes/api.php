<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\ProduksController;
// use App\Http\Controllers\Api\SelesaiDivisiController;
// use App\Http\Controllers\Api\AuthController;
// use App\Http\Controllers\Api\NotifyController;
// use App\Models\User;
// use Illuminate\Http\Request;
// Route::get('/produks/utama',        [ProduksController::class, 'utama']);
// Route::get('/produks/tambahan',     [ProduksController::class, 'tambahan']);
// Route::get('/produks/tambahan-max', [ProduksController::class, 'tambahanMax']);
// Route::get('/produks/summary',      [ProduksController::class, 'summary']); // opsional



// Route::get('/selesai-divisi',          [SelesaiDivisiController::class, 'index']);   // ?perintah_id=... atau ?tanggal=YYYY-MM-DD
// Route::post('/selesai-divisi/row',     [SelesaiDivisiController::class, 'saveRow']);
// Route::post('/selesai-divisi/group',   [SelesaiDivisiController::class, 'saveGroup']);


// Route::post('/auth/login', [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/auth/me',          [AuthController::class, 'me']);
//     Route::post('/auth/logout',     [AuthController::class, 'logout']);
//     Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
// });
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/notify/overview', [NotifyController::class, 'overview']);
// });
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/notify/overview', [ProduksController::class, 'notifyOverview']);

//     // ...rute lain...
// });
// Route::get('/ping', fn() => ['ok' => true]);
// Route::get('/produksi/load-produks', [ProduksController::class, 'loadProduks']);
// Route::post('/save-token', [User::class, 'saveToken']);
// Route::middleware('auth:sanctum')->post('/save-token', [\App\Http\Controllers\Api\PushTokenController::class, 'store']);



// Route::post('/debug/echo', function (Request $r) {
//     return response()->json(['ok' => true, 'body' => $r->all()]);
// });
// Route::middleware('auth:sanctum')->post('/push/register', function (Request $r) {
//     $user = $r->user();

//     $data = $r->validate([
//         'expo_token'  => 'required|string',
//         'native_token'=> 'nullable|string',
//         'device.brand'=> 'nullable|string',
//         'device.model'=> 'nullable|string',
//         'device.os_name'=> 'nullable|string',
//         'device.os_version'=> 'nullable|string',
//         'device.is_emulator'=> 'nullable|boolean',
//     ]);

//     // upsert per-user per-expo_token
//     $row = \App\Models\UserPushToken::updateOrCreate(
//         ['user_id' => $user->id, 'expo_token' => $data['expo_token']],
//         [
//             'native_token'   => $data['native_token'] ?? null,
//             'device_brand'   => data_get($data, 'device.brand'),
//             'device_model'   => data_get($data, 'device.model'),
//             'device_os'      => data_get($data, 'device.os_name'),
//             'device_os_ver'  => data_get($data, 'device.os_version'),
//             'is_emulator'    => data_get($data, 'device.is_emulator', false),
//             'last_seen_at'   => now(),
//         ]
//     );

//     return response()->json(['ok' => true, 'id' => $row->id]);
// });


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotifyController;
use App\Http\Controllers\Api\ProduksController;
use App\Http\Controllers\Api\PushTokenController;
use App\Http\Controllers\Api\HasilGilingController;
use App\Http\Controllers\Api\HasilRejectController;
use App\Http\Controllers\Api\SelesaiDivisiController;
use App\Models\Slide;
use App\Http\Controllers\Api\SlideController;
use App\Http\Controllers\Api\PenguranganController;




// --- Public endpoints (no auth) ---
Route::get('/ping', fn() => ['ok' => true]);

Route::get('/produks/utama',        [ProduksController::class, 'utama']);
Route::get('/produks/tambahan',     [ProduksController::class, 'tambahan']);
Route::get('/produks/tambahan-max', [ProduksController::class, 'tambahanMax']);
Route::get('/produks/summary',      [ProduksController::class, 'summary']);
Route::get('/produksi/load-produks', [ProduksController::class, 'loadProduks']);

Route::get('/selesai-divisi',       [SelesaiDivisiController::class, 'index']); // ?perintah_id=... / ?tanggal=YYYY-MM-DD
Route::post('/selesai-divisi/row',  [SelesaiDivisiController::class, 'saveRow']);
Route::post('/selesai-divisi/group', [SelesaiDivisiController::class, 'saveGroup']);

Route::post('/auth/login',          [AuthController::class, 'login']);


Route::get('/slides', [SlideController::class, 'index']); // => /api/slides


// Debug echo (boleh dipertahankan saat dev)
Route::post('/debug/echo', function (Request $r) {
    return response()->json(['ok' => true, 'body' => $r->all()]);
});

// --- Protected endpoints (Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me',              [AuthController::class, 'me']);
    Route::post('/auth/logout',         [AuthController::class, 'logout']);
    Route::post('/auth/logout-all',     [AuthController::class, 'logoutAll']);

    // KEEP EXACTLY ONE notify overview
    Route::get('/notify/overview',      [NotifyController::class, 'overview']);

    // Push tokens
    Route::post('/push/register',       function (Request $r) {
        $user = $r->user();
        $data = $r->validate([
            'expo_token'      => 'required|string',
            'native_token'    => 'nullable|string',
            'device.brand'    => 'nullable|string',
            'device.model'    => 'nullable|string',
            'device.os_name'  => 'nullable|string',
            'device.os_version' => 'nullable|string',
            'device.is_emulator' => 'nullable|boolean',
        ]);

        $row = \App\Models\UserPushToken::updateOrCreate(
            ['user_id' => $user->id, 'expo_token' => $data['expo_token']],
            [
                'native_token'  => $data['native_token'] ?? null,
                'device_brand'  => data_get($data, 'device.brand'),
                'device_model'  => data_get($data, 'device.model'),
                'device_os'     => data_get($data, 'device.os_name'),
                'device_os_ver' => data_get($data, 'device.os_version'),
                'is_emulator'   => data_get($data, 'device.is_emulator', false),
                'last_seen_at'  => now(),
            ]
        );

        return response()->json(['ok' => true, 'id' => $row->id]);
    });

    // Optional: kalau memang ada versi di ProduksController, gunakan path berbeda
    // Route::get('/notify/overview-produk', [ProduksController::class, 'notifyOverview']);
});

// --- REMOVE this: wrong handler (Model as controller) ---

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hasil-giling', [HasilGilingController::class, 'index']);
    Route::get('/hasil-giling/{perintah_id}', [HasilGilingController::class, 'show']);
    Route::post('/hasil-giling', [HasilGilingController::class, 'store']); // <-- upsert
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rejects',  [HasilRejectController::class, 'index']);   // list per produk
    Route::post('/rejects', [HasilRejectController::class, 'store']);   // simpan (single/multi)
    Route::delete('/rejects/{id}', [HasilRejectController::class, 'destroy']); // hapus
    Route::get('/rejects/summary', [HasilRejectController::class, 'summary']);
    Route::get('/rejects/lists', [\App\Http\Controllers\Api\HasilRejectController::class, 'lists']);

});
Route::prefix('pengurangan')->group(function () {
    Route::get('/',                [PenguranganController::class, 'index']); // riwayat (paging)
    Route::post('/',               [PenguranganController::class, 'store']); // simpan batch
    Route::get('/rekap',           [PenguranganController::class, 'pengurangan']);
    Route::get('/max',             [PenguranganController::class, 'penguranganMax']);
    Route::get('/summary',         [PenguranganController::class, 'summaryPengurangan']);
    Route::get('/notify-overview', [PenguranganController::class, 'notifyOverviewPengurangan']);
    Route::get('/load',            [PenguranganController::class, 'loadPengurangan']);
});
