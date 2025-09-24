<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\MasterProduct;
use Illuminate\Support\Facades\Auth;

class RekapWorkorder extends Component
{
    public array $produks = [];


    public string $tanggalProduksi;
    public $produk;
    public $produkCollection;
    public $produkList = [];



    public function mount()
{
    $this->tanggalProduksi = now()->format('Y-m-d');
    $this->produkList = MasterProduct::select('id', 'nama')->get()->toArray(); // hanya nama produk

}


    public function updatedTanggalProduksi($value)
{
    // Set tanggalProduksi otomatis sudah diupdate oleh Livewire
    $this->loadProduks();
}
    public function loadProduks()
{
    // 1) Ambil produk + detail perintah produksi (sesuai tanggal)
    $produkCollection = MasterProduct::whereHas('detailPerintahProduksi.perintahProduksi', function ($q) {
        $q->whereDate('tanggal_perintah', $this->tanggalProduksi);
    })
    ->with([
        'detailPerintahProduksi' => function ($query) {
            $query->whereHas('perintahProduksi', function ($q) {
                $q->whereDate('tanggal_perintah', $this->tanggalProduksi);
            })->with('perintahProduksi');
        },
        'produksiTambahan' => function ($q) {
            $q->whereHas('perintahProduksi', function ($sub) {
                $sub->whereDate('tanggal_perintah', $this->tanggalProduksi);
            });
        }
    ])
    ->get();

$this->produk = $produkCollection
    ->map(function ($produk) {
        // Hitung produksi utama & tambahan
        $produksiUtama = $produk->detailPerintahProduksi->sum('produksi_qty');
        $produksiTambahan = $produk->produksiTambahan->sum('qty_tambahan');

        // Hitung target utama & tambahan
        $targetUtama = $produk->detailPerintahProduksi->sum('target_produksi');
        $targetTambahan = $produk->produksiTambahan->sum('target_qty_tambahan');

        // Ubah model ke array
        $arr = $produk->toArray();

        // Simpan total gabungan
        $arr['total_produksi_qty'] = $produksiUtama + $produksiTambahan;
        $arr['total_target_produksi'] = $targetUtama + $targetTambahan;

        return $arr;
    })
    ->keyBy('id')
    ->toArray();
    // dd($this->produk);

}

    public function render()
    {
        return view('livewire.produksi.rekap-workorder', [
            'produks' => $this->produks,
        ]);
    }

}
