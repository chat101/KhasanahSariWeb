<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Produksi\HasilDivisi;
use App\Models\Produksi\MasterDivisi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Http\Request; // ✅ ini yang benar
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role','divisi_id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }



    /** Normalisasi string (lowercase + trim) */
    protected function norm(string $s): string
    {
        return mb_strtolower(trim($s));
    }
    public function allowedMenu(): array
    {
        return [
            // Admin: semua grup & semua item (tidak batasi item)
            'admin' => [
                'groups' => ['masterdata', 'gudang', 'purchasing', 'finance', 'produksi', 'laporan'],
                'items'  => [], // kosong = semua item pada grup yang diizinkan
            ],

            // Contoh: leaderproduksi hanya grup Produksi & Laporan
            // dan DI DALAMNYA hanya beberapa item

            'adminproduksi' => [
                'groups' => ['masterdata','produksi', 'laporan'],
                'items'  => [
                    'masterdata' => ['mproduk'],
                    'laporan' => ['lap-has-prod', 'lap-prod-minggu', 'ketepatanwkt', 'ketepatanwktbln','lap-prod-hari'],

                     // <- route names
                  // 'laporan'  => ['lap-has-prod','lap-prod-minggu'],
                ],
            ],

            // Gudang hanya grup Gudang & Laporan
            'gudang' => [
                'groups' => ['gudang', 'laporan'],
                'items'  => [
                    'gudang' => ['brgmsk', 'rekapbrgmsk'],

                ],
            ],
            'adminpurchasing' => [
                'groups' => ['gudang', 'purchasing', 'laporan', 'finance'],
                'items'  => [
                    'gudang' => ['brgmsk'],
                    'laporan' => ['lapbrgmsk'],
                    'purchasing' => ['listsuppmasuk'],
                    'finance' => ['uangmsk','masterrekening','bayarpiutang','biayainputpusat'],
                ],
            ],
            'leaderproduksi' => [
                'groups' => ['produksi','masterdata'],
                'items'  => [
                    'produksi' => ['selesaikanjob', 'produktifitas', 'work-order'],
                    'masterdata' => ['mproduk'],

                ],
            ],
        ];
    }
    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->map(fn(string $name) => Str::of($name)->substr(0, 1))->implode('');
    }
    // public function allowedMenuGroups(): array
    // {
    //     return match (strtolower($this->role ?? '')) {
    //         'admin' => ['master data', 'gudang', 'purchasing', 'produksi', 'laporan'],
    //         'produksi' => ['produksi', 'laporan'],
    //         'leaderproduksi' => ['produksi', 'laporan'],
    //         'gudang' => ['gudang', 'laporan'],
    //         'purchasing' => ['purchasing', 'laporan'],
    //         default => [],
    //     };
    // }

    public function canSeeGroup(string $heading): bool
    {
        $role = $this->norm($this->role ?? '');
        $conf = $this->allowedMenu();
        $groups = array_map(fn($g) => $this->norm($g), $conf[$role]['groups'] ?? []);
        return in_array($this->norm($heading), $groups, true);
    }

    /** Izinkan item tertentu di dalam sebuah grup */
    public function canSeeItem(string $group, string $routeName): bool
    {
        $role = $this->norm($this->role ?? '');
        $conf = $this->allowedMenu();
        $gKey = $this->norm($group);

        // kalau grup tidak diizinkan → false
        if (! $this->canSeeGroup($group)) return false;

        // jika tidak ada batasan item untuk grup ini → semua item OK
        $itemsMap = $conf[$role]['items'][$gKey] ?? null;
        if ($itemsMap === null) return true;

        // jika ada batasan → hanya route yang terdaftar yang boleh
        return in_array($routeName, $itemsMap, true);
    }

    // app/Http/Controllers/UserController.php
    public function saveToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = $request->user(); // kalau sudah pakai auth
        $user->expo_push_token = $request->token;
        $user->save();

        return response()->json(['success' => true]);
    }
    public function expoTokens()
    {
        return $this->hasMany(\App\Models\UserPushToken::class);
    }


    public function pushTokens()
    {
        return $this->hasMany(\App\Models\UserPushToken::class);
    }
    public function divisi()
    {
        return $this->belongsTo(MasterDivisi::class, 'divisi_id');
    }

    public function hasilDivisi()
    {
        return $this->hasMany(HasilDivisi::class, 'user_id');
    }
    public function scopeDivisi($q, string $namaDivisi)
{
    return $q->whereHas('divisi', function($d) use ($namaDivisi) {
        $d->where('nama_divisi', $namaDivisi);
    });
}

}
