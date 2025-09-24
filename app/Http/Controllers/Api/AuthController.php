<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Schema;

// class AuthController extends Controller
// {
//     /**
//      * POST /api/auth/login
//      * Body: { login: "email atau username", password: "****", device_name?: "expo-ios" }
//      */
//     public function login(Request $request)
//     {
//         $data = $request->validate([
//             'login'       => ['required', 'string'],
//             'password'    => ['required', 'string'],
//             'device_name' => ['nullable', 'string'],
//         ]);

//         $login = $data['login'];

//         // Cari berdasarkan email atau username (jika kolom username ada)
//         $userQuery = User::query();
//         $userQuery->where('email', $login);
//         if (Schema::hasColumn((new User)->getTable(), 'username')) {
//             $userQuery->orWhere('username', $login);
//         }
//         $user = $userQuery->first();

//         if (!$user || !Hash::check($data['password'], $user->password)) {
//             return response()->json([
//                 'ok'      => false,
//                 'message' => 'Kredensial salah.',
//             ], 401);
//         }

//         $token = $user->createToken($data['device_name'] ?? 'expo')->plainTextToken;

//         return response()->json([
//             'ok'         => true,
//             'token'      => $token,
//             'token_type' => 'Bearer',
//             'user'       => [
//                 'id'       => $user->id,
//                 'name'     => $user->name,
//                 'username' => Schema::hasColumn($user->getTable(), 'username') ? $user->username : null,
//                 'email'    => $user->email,
//                 'role'     => $user->role,   // <-- tambahkan ini
//             ],
//         ]);
//     }

//     /**
//      * GET /api/auth/me
//      * Header: Authorization: Bearer <token>
//      */
//     public function me(Request $request)
//     {
//         return response()->json([
//             'ok'   => true,
//             'user' => $request->user(),
//         ]);
//     }

//     /**
//      * POST /api/auth/logout
//      * Hapus token yang sedang dipakai.
//      */
//     public function logout(Request $request)
//     {
//         $request->user()->currentAccessToken()?->delete();

//         return response()->json([
//             'ok'      => true,
//             'message' => 'Logout berhasil.',
//         ]);
//     }

//     /**
//      * POST /api/auth/logout-all
//      * Hapus semua token milik user (keluar dari semua device).
//      */
//     public function logoutAll(Request $request)
//     {
//         $request->user()->tokens()->delete();

//         return response()->json([
//             'ok'      => true,
//             'message' => 'Semua sesi telah keluar.',
//         ]);
//     }
// }
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Throwable;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1) Validasi: terima login (email/username) + password + device_name opsional
        $data = $request->validate([
            'login'       => ['required', 'string'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        try {
            $login = trim($data['login']);

            // 2) Tentukan kolom pencarian
            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
            $user = User::query()
                ->when($isEmail, fn($q) => $q->where('email', $login))
                ->when(!$isEmail, fn($q) => $q->where('username', $login))
                ->first();

            // 3) Cek kredensial
            if (!$user || !Hash::check($data['password'], $user->password)) {
                // Jangan bocorkan mana yang salah (user atau password)
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // 4) Buat token Sanctum
            $token = $user->createToken($data['device_name'] ?? 'expo-app')->plainTextToken;

            // 5) Response konsisten & minimal
            return response()->json([
                'ok'         => true,
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username ?? null,
                    'email'    => $user->email,
                    'role'     => $user->role ?? null,
                    'divisi_id'  => $user->divisi_id,                               // ⬅️ penting
                    'divisi_nama'=> optional($user->divisi)->nama_divisi,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Login failed', [
                'msg'  => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }

    public function me(Request $request)
    {
        // balikan user minimal & aman
        $u = $request->user();
        return response()->json([
            'ok'   => true,
            'user' => [
                'id'       => $u->id,
                'name'     => $u->name,
                'username' => $u->username ?? null,
                'email'    => $u->email,
                'role'     => $u->role ?? null,
                'divisi_id'  => $u->divisi_id,                                   // ⬅️ penting
                'divisi_nama' => optional($u->divisi)->nama_divisi
                ?? optional($u->divisi)->nama, // ⬅️
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['ok' => true, 'message' => 'Logout berhasil']);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['ok' => true, 'message' => 'Semua sesi telah keluar']);
    }
}
