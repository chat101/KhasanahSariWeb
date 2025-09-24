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
    public $qtyTransaksi;
    public $jumlahTransaksi;
    public $jumlahSupplier;
    public function mount()
    {
        $this->jumlahBarang = MasterBarang::count();
        $this->jumlahSupplier = MasterSupplier::count();
        $this->qtyTransaksi = Gudang_Masuk::where('tanggal',today())->count();
        $this->jumlahTransaksi = Purchasing::where('tgl_input', today())->sum('grandtotal');
    }
    public function render()
    {
        return view('dashboard');
    }
}
