<?php

namespace App\Providers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\Produksi\Perintah_Produksi;
use App\Observers\PerintahProduksiObserver;
use App\Models\Produksi\Produksi_Tambahan;
use App\Observers\ProduksiTambahanObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    protected $listen = [
        \App\Events\PerintahProduksiCreated::class => [
            \App\Listeners\SendPerintahProduksiPush::class,
        ],
    ];
    public function boot(): void
    {
        //
        Blade::if('menugroup', function (string $heading) {
            $u = Auth::user();
            return $u && method_exists($u, 'canSeeGroup') && $u->canSeeGroup($heading);
        });

        Blade::if('menuitem', function (string $group, string $routeName) {
            $u = Auth::user();
            return $u && method_exists($u, 'canSeeItem') && $u->canSeeItem($group, $routeName);
        });

        Perintah_Produksi::observe(PerintahProduksiObserver::class);
        Produksi_Tambahan::observe(ProduksiTambahanObserver::class);
    }
}
