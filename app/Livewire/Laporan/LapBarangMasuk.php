<?php

namespace App\Livewire\Laporan;

use App\Models\Gudang_Masuk;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GudangMasukExport;

class LapBarangMasuk extends Component
{
    use WithPagination;
    public $search = '';
    public $tanggalAwal;
    public $tanggalAkhir;



    public function render()
    {
        $query = Gudang_Masuk::with(['supplier']);

        if ($this->tanggalAwal && $this->tanggalAkhir) {
            $query->whereBetween('tanggal', [$this->tanggalAwal, $this->tanggalAkhir]);
        }

        if ($this->search) {
            $query->whereHas('supplier', function ($q) {
                $q->where('nmsupp', 'like', '%' . $this->search . '%');
            });
        }

        $listSuppMasuk = $query->paginate(20);

        return view('livewire.laporan.lap-barang-masuk', compact('listSuppMasuk'));
    }


    // install dulu composer require maatwebsite/excel
    // Di config/app.php, cek bagian aliases:
    // 'aliases' => [
    // // ...
    //     'Excel' => Maatwebsite\Excel\Facades\Excel::class,],
    //     'providers' => [
    //     Maatwebsite\Excel\ExcelServiceProvider::class,
    // ],
    // use Maatwebsite\Excel\Facades\Excel;
    // php artisan make:export GudangMasukExport --model=Gudang_Masuk
    protected function validateTanggalRange()
    {
        // dd($this->tanggalAwal, $this->tanggalAkhir); // Debugging

        if (!$this->tanggalAwal || !$this->tanggalAkhir) {
            session()->flash('message', 'Tanggal awal dan akhir harus diisi.');
            return false;
        }

        if ($this->tanggalAkhir < $this->tanggalAwal) {
            session()->flash('message', 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.');
            return false;
        }

        return true;
    }
    public function exportExcel()
    {
        if (!$this->validateTanggalRange()) {
            return;
        }

        $filename = 'Barang Masuk-' . $this->tanggalAwal . '-sampai-' . $this->tanggalAkhir . '.xlsx';
        return Excel::download(new GudangMasukExport($this->tanggalAwal, $this->tanggalAkhir), $filename);
    }
}
