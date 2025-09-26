<?php


use App\Livewire\Dashboard;
use App\Livewire\Finance\BayarPiutang;
use App\Livewire\Finance\BiayaInputPusat;
use App\Livewire\Finance\MasterKontrakan;
use App\Livewire\Finance\MasterRekening;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Livewire\Master\Toko;
use App\Livewire\Master\Users;
use App\Livewire\Master\Barang;

use App\Livewire\Master\Supplier;
use App\Livewire\Produksi\Produk;



use App\Livewire\Settings\Profile;
use App\Livewire\Produksi\Complain;
use App\Livewire\Settings\Password;
use App\Livewire\Gudang\InputBrgMsk;

use App\Livewire\Produksi\WorkOrder;
use Illuminate\Support\Facades\View;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;
use App\Livewire\Finance\SetoranMasuk;
use App\Livewire\Laporan\LapBarangMasuk;

use App\Livewire\Produksi\Produktivitas;

use App\Livewire\Produksi\SettingBagian;
use App\Livewire\Gudang\RekapInputGudang;

use App\Livewire\Produksi\RekapWorkorder;
use App\Livewire\Produksi\StokAwalOpname;
use App\Livewire\Finance\UangMasukPeriode;
use App\Livewire\Produksi\HasilDistribusi;
use App\Livewire\Produksi\PenyesuaianStok;
use App\Livewire\Purchasing\SupplierMasuk;
use App\Livewire\Produksi\PerintahProduksi;
use App\Livewire\Produksi\SelesaikanDivisi;
use App\Livewire\Produksi\OpnamePenyesuaian;
use App\Livewire\Produksi\InputSelesaiDivisi;

use App\Livewire\Produksi\DaftarPerintahProduksi;
use App\Livewire\Produksi\HasilCounter;
use App\Livewire\Produksi\HasilDekor;
use App\Livewire\Produksi\Laporan\ProduksiMingguan;
use App\Livewire\Produksi\Laporan\JamSelesaiBulanan;
use App\Livewire\Produksi\Laporan\LaporanJamSelesai;
use App\Livewire\Produksi\Laporan\LaporanHasilProduksi;
use App\Livewire\Produksi\HasilDivisi;
use App\Livewire\Produksi\HasilGiling;
use App\Livewire\Produksi\HasilPoprok;
use App\Livewire\Produksi\Laporan\LapHarian;

Route::middleware(['auth']) // jika perlu
    ->get('/admin/slides', \App\Livewire\Slides\Manage::class)
    ->name('slides.manage');

    Route::get('/', function () {
        return view('welcome');
    })->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

    Route::get('dashboard', Dashboard::class)
        ->middleware(['auth', 'verified'])
        ->name('dashboard');
    Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
            // Route master
            Route::get('mtoko', Toko::class)->name('mtoko');
            Route::get('msupplier', Supplier::class)->name('msupplier');
            Route::get('mbarang', Barang::class)->name('mbarang');
            Route::get('muser', Users::class)->name('muser');


    // Route barang
    Route::get('barangmasuk', InputBrgMsk::class)->name('brgmsk');
    Route::get('listsuppmasuk', SupplierMasuk::class)->name('listsuppmasuk');
    Route::get('lapbrgmsk', LapBarangMasuk::class)->name('lapbrgmsk');
    Route::get('rekapbrgmsk', RekapInputGudang::class)->name('rekapbrgmsk');

            // Route Produksi
            Route::get('produktifitas', Produktivitas::class)->name('produktifitas');
            Route::get('perproduksi',PerintahProduksi::class)->name('perproduksi');
            Route::get('mproduk', Produk::class)->name('mproduk');
            Route::get('hasdist/{perintah_id}', HasilDistribusi::class)->name('hasdist');
            Route::get('listperproduksi', DaftarPerintahProduksi::class)->name('listperproduksi');
            Route::get('setjob', SettingBagian::class)->name('setjob');
            Route::get('selesaijob/{perintah_id}', InputSelesaiDivisi::class)->name('selesaijob');
            Route::get('selesaikanjob',SelesaikanDivisi::class)->name('selesaikanjob');
            Route::get('work_order', WorkOrder::class)->name('work-order');
            Route::get('rkp-work_order', RekapWorkorder::class)->name('rkp-work-order');
    //laporan produksi
    Route::get('lap-has-prod', LaporanHasilProduksi::class)->name('lap-has-prod');
    Route::get('lap-prod-minggu', ProduksiMingguan::class)->name('lap-prod-minggu');
    Route::get('lap-prod-hari', LapHarian::class)->name('lap-prod-hari');
    Route::get('opname-penyesuaian', OpnamePenyesuaian::class)->name('opnamepenyesuaian');
    Route::get('ketepatanwkt', LaporanJamSelesai::class)->name('ketepatanwkt');
    Route::get('ketepatanwktbln', JamSelesaiBulanan::class)->name('ketepatanwktbln');
    Route::get('komplen', Complain::class)->name('komplen');
    Route::get('hsldivisi', HasilDivisi::class)->name('hsldivisi');
    Route::get('hslglg/{perintah_id}', HasilGiling::class)->name('hslglg');
    Route::get('hsldekor/{perintah_id}', HasilDekor::class)->name('hsldekor');
    Route::get('hslpoprok/{perintah_id}', HasilPoprok::class)->name('hslpoprok');
    Route::get('hslcounter/{perintah_id}', HasilCounter::class)->name('hslcounter');






        // Route Finance
        Route::get('uangmsk', SetoranMasuk::class)->name('uangmsk');
        Route::get('uangmskperiode', UangMasukPeriode::class)->name('uangmskperiode');
        Route::get('biayainputpusat', BiayaInputPusat::class)->name('biayainputpusat');
        Route::get('bayarpiutang', BayarPiutang::class)->name('bayarpiutang');
        Route::get('masterrekening', MasterRekening::class)->name('masterrekening');







});
Route::middleware('auth')->group(function () {
    Route::post('/webpush/subscribe', function (Request $r) {
        $r->user()->updatePushSubscription(
            $r->endpoint,
            $r->keys['p256dh'] ?? null,
            $r->keys['auth'] ?? null
        );
        return response()->noContent();
    });

    Route::post('/webpush/unsubscribe', function (Request $r) {
        $r->user()->deletePushSubscription($r->endpoint);
        return response()->noContent();
    });
});

require __DIR__ . '/auth.php';
