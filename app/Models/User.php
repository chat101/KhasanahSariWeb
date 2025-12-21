<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Operasional\Area;
use App\Models\Operasional\Wilayah;
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
    protected $fillable = ['name', 'email', 'password', 'role', 'divisi_id', 'area_id', 'wilayah_id'];

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
                'groups' => ['masterdata', 'gudang',  'accounting', 'purchasing', 'finance', 'produksi', 'teknisi', 'laporan','operasional'],
                'items'  => [], // kosong = semua item pada grup yang diizinkan
            ],
            'manager_finance' => [
                'groups' => ['gudang', 'purchasing', 'finance', 'laporan'],
                'items'  => [], // kosong = semua item pada grup yang diizinkan
            ],
            // Contoh: leaderproduksi hanya grup Produksi & Laporan
            // dan DI DALAMNYA hanya beberapa item

            'adminproduksi' => [
                'groups' => ['masterdata', 'produksi', 'laporan'],
                'items'  => [
                    'masterdata' => ['mproduk'],
                    'laporan' => ['lap-has-prod', 'lap-prod-minggu', 'ketepatanwkt', 'ketepatanwktbln', 'lap-prod-hari'],

                    // <- route names
                    // 'laporan'  => ['lap-has-prod','lap-prod-minggu'],
                ],
            ],
            'accounting' => [
                'groups' => ['accounting'],
                'items'  => [],
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
                    'finance' => ['uangmsk', 'masterrekening', 'bayarpiutang', 'biayainputpusat'],
                ],
            ],
            'leaderproduksi' => [
                'groups' => ['produksi', 'masterdata'],
                'items'  => [
                    'produksi' => ['selesaikanjob', 'produktifitas', 'work-order'],
                    'masterdata' => ['mproduk'],

                ],
            ],
            'teknisi' => [
                'groups' => ['produksi', 'teknisi'],
                'items'  => [],
            ],
            'area' => [
                'groups' => ['masterdata',  'operasional','accounting', 'laporan'],
                'items'  => [], // kosong = semua item pada grup yang diizinkan
            ],
            'wilayah' => [
                'groups' => ['masterdata',  'operasional','accounting', 'laporan'],
                'items'  => [], // kosong = semua item pada grup yang diizinkan
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
        return $q->whereHas('divisi', function ($d) use ($namaDivisi) {
            $d->where('nama_divisi', $namaDivisi);
        });
    }

//     auth()->user()->area?->nama_area;
// auth()->user()->wilayah?->nama_wilayah;
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

 /**
 * Wilayah efektif user:
 * - kalau punya area → wilayah dari area
 * - kalau punya wilayah → wilayah langsung
 */
public function wilayahLangsung()
{
    return $this->belongsTo(Wilayah::class, 'wilayah_id');
}
public function wilayah()
{
    if ($this->area_id) {
        return $this->area->wilayah();
    }

    return $this->wilayahLangsung();
}

public function scopeByUserWilayah($q, $user)
{
    if ($user->area_id) {
        return $q->where('area_id', $user->area_id);
    }

    if ($user->wilayah_id) {
        return $q->whereHas('area', function ($a) use ($user) {
            $a->where('wilayah_id', $user->wilayah_id);
        });
    }

    return $q; // admin pusat
}
public function getWilayahNamaAttribute()
{
    // kalau punya area, ambil wilayah dari area
    if ($this->area) {
        return $this->area->wilayah?->nama_wilayah;
    }
    // kalau tidak, ambil dari wilayah_id langsung
    return $this->wilayahLangsung?->nama_wilayah;
}

}
