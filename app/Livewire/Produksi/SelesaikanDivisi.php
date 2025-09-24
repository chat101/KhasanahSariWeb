<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\Perintah_Produksi;

class SelesaikanDivisi extends Component
{
    public $perintahProduksi;
    public function mount()
    {
        $this->perintahProduksi = Perintah_Produksi::with('user')
        // tampilkan HANYA perintah yang MASIH punya minimal 1 job belum selesai
        ->whereExists(function ($q) {
            $q->select(DB::raw(1))
              ->from('msjobs')                          // daftar job
              ->whereNotExists(function ($sub) {
                  $sub->select(DB::raw(1))
                      ->from('selesai_divisi')          // catatan selesai per divisi
                      ->whereColumn('selesai_divisi.msjobs_id', 'msjobs.id')
                      ->whereColumn('selesai_divisi.perintah_produksi_id', 'perintah_produksi.id')
                      ->whereNotNull('selesai_divisi.waktu_selesai'); // sudah isi waktu_selesai
              });
        })
        ->orderByDesc('tanggal_perintah')
        ->get();
    }
    public function render()
    {
        return view('livewire.produksi.selesaikan-divisi',[
            'perintahProduksi' => $this->perintahProduksi,
        ]);
    }
}
