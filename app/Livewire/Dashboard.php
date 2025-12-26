<?php

namespace App\Livewire;

use App\Models\Gudang_Masuk;
use App\Models\MasterBarang;
use App\Models\MasterSupplier;
use App\Models\Purchasing;
use Livewire\Component;

class Dashboard extends Component
{
    public $jumlahBarang;
    public $jumlahSupplier;

    // New Metrics
    public $totalSalesToday = 0;
    public $totalCashInToday = 0;
    public $activeProOrders = 0;

    // Lists
    public $recentPurchases = [];
    public $recentCashIns = [];

    public function mount()
    {
        $this->jumlahBarang = MasterBarang::count();
        $this->jumlahSupplier = MasterSupplier::count();

        // Sales Snapshot Today (from Chatbot Sales Data)
        // If 0, it might mean the snapshot hasn't run yet, but that's fine.
        // $this->totalSalesToday = \App\Models\Chatbot\SalesSnapshot::whereDate('date', today())->sum('total_sales');

        // Cash In Today (Setoran Masuk)
        $this->totalCashInToday = \App\Models\Finance\Setoran_Masuk::whereDate('tanggal_setoran', today())->sum('jumlah_uang');

        // Active Production Orders (Created Today)
        $this->activeProOrders = \App\Models\Produksi\Perintah_Produksi::whereDate('tanggal_perintah', today())->count();

        // Recent Purchases (Gudang Masuk) - Top 5
        $this->recentPurchases = Gudang_Masuk::with(['supplier'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Recent Setoran - Top 5
        $this->recentCashIns = \App\Models\Finance\Setoran_Masuk::with('tokos')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('dashboard');
    }
}
