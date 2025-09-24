<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\Perintah_Produksi;
use Illuminate\Support\Collection;

class RekapHasilDivisi extends Component
{
    public string $tanggalProduksi;
    private Collection $perintahproduksi;
    public ?int   $perintah_id = null;

    /** input realisasi per baris (index sama dengan urutan tampilan) */
    public array $realisasi = [];

    protected $rules = [
        'realisasi.*' => 'nullable|integer|min:0',
    ];

    /* ===================== LIFECYCLE ===================== */

    public function mount($perintah_id = null)
    {
        $this->perintah_id = $perintah_id ?: null;

        if ($this->perintah_id) {
            $perintah = Perintah_Produksi::find($this->perintah_id);
            $this->tanggalProduksi = $perintah?->tanggal_perintah ?? now()->toDateString();
            $this->loadDataFromNet(); // load berdasar perintah_id diketahui
        } else {
            $this->tanggalProduksi = now()->toDateString();
            $this->updatedTanggalProduksi($this->tanggalProduksi); // cari perintah terbaru di tanggal tsb
        }
    }

    /**
     * Dipanggil otomatis saat $tanggalProduksi diubah dari Flatpickr (wire.set).
     */
    public function updatedTanggalProduksi($date)
    {
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $date)
            ->orderByDesc('id')
            ->first();

        if ($perintah) {
            $this->perintah_id = $perintah->id;
            $this->loadDataFromNet();
            session()->flash('message', 'Data dimuat untuk tanggal ' . $date);
        } else {
            $this->perintah_id       = null;
            $this->perintahproduksi  = collect();
            $this->realisasi         = [];
            session()->flash('message', 'Tidak ada perintah pada tanggal ' . $date);
        }
    }

    /* ===================== DATA LOADER ===================== */

    /**
     * Muat data grid untuk $this->perintah_id
     * - Hanya produk dengan produksi bersih > 0
     * - Agregasi hasil per stage (counter/giling/dekor/poprok)
     * - Prefill input realisasi dari hasil_giling
     */
    private function loadDataFromNet(): void
    {
        if (!$this->perintah_id) {
            $this->perintahproduksi = collect();
            $this->realisasi = [];
            return;
        }

        // sub pengurangan & tambahan (tetap)
        $subPengurangan = DB::table('produksi_pengurangan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                         SUM(qty_pengurangan) AS qty_pengurangan,
                         SUM(target_qty_pengurangan) AS total_pengurangan')
            ->where('perintah_produksi_id', $this->perintah_id)
            ->groupBy('mproducts_id','perintah_produksi_id');

        $subTambahan = DB::table('produksi_tambahan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                         SUM(qty_tambahan) AS qty_tambahan,
                         SUM(target_qty_tambahan) AS total_tambahan')
            ->where('perintah_produksi_id', $this->perintah_id)
            ->groupBy('mproducts_id','perintah_produksi_id');

        // ✅ Sub: hasil_divisi → SUM per mproducts_id tanpa join users/divisi
        // ganti ID sesuai tabel 'divisi' kamu (contoh: 2=giling, 6=poprok, 4=dekor)
        $subHasilDivisi = DB::table('hasil_divisi as hd')
            ->selectRaw("
                hd.mproducts_id,
                hd.perintah_produksi_id,
                SUM(CASE WHEN hd.divisi_id = 2 THEN hd.qty_hasil ELSE 0 END) AS qty_giling,
                SUM(CASE WHEN hd.divisi_id = 6 THEN hd.qty_hasil ELSE 0 END) AS qty_poprok,
                SUM(CASE WHEN hd.divisi_id = 7 THEN hd.qty_hasil ELSE 0 END) AS qty_dekor
            ")
            ->where('hd.perintah_produksi_id', $this->perintah_id)
            ->groupBy('hd.mproducts_id','hd.perintah_produksi_id');

        // ✅ Sub: hasil_counter (tanpa join lain)
        $subHasilCounter = DB::table('hasil_counter as hc')
            ->selectRaw('hc.mproducts_id, hc.perintah_produksi_id, SUM(hc.qty_hasil) AS qty_counter')
            ->where('hc.perintah_produksi_id', $this->perintah_id)
            ->groupBy('hc.mproducts_id','hc.perintah_produksi_id');

        // Query utama
        $rows = DB::table('mproducts as mp')
            ->leftJoin('detail_perintah_produksi as dpp', function ($j) {
                $j->on('dpp.mproducts_id', '=', 'mp.id')
                  ->where('dpp.perintah_produksi_id', '=', $this->perintah_id);
            })
            ->leftJoinSub($subPengurangan,  'p',  fn($j)=>$j->on('p.mproducts_id','=','mp.id'))
            ->leftJoinSub($subTambahan,     't',  fn($j)=>$j->on('t.mproducts_id','=','mp.id'))
            ->leftJoinSub($subHasilDivisi,  'hd', function($j){
                $j->on('hd.mproducts_id','=','mp.id')
                  ->where('hd.perintah_produksi_id', $this->perintah_id);
            })
            ->leftJoinSub($subHasilCounter, 'hc', function($j){
                $j->on('hc.mproducts_id','=','mp.id')
                  ->where('hc.perintah_produksi_id', $this->perintah_id);
            })

            ->whereRaw('
                (COALESCE(dpp.produksi_qty,0)
                 + COALESCE(t.qty_tambahan,0)
                 - COALESCE(p.qty_pengurangan,0)) > 0
            ')
            ->selectRaw('
                mp.id AS mproducts_id,
                COALESCE(dpp.perintah_produksi_id, ?) AS perintah_produksi_id,

                COALESCE(dpp.produksi_qty,0)  AS produksi_qty,
                COALESCE(t.qty_tambahan,0)    AS qty_tambahan,
                COALESCE(p.qty_pengurangan,0) AS qty_pengurangan,
                (COALESCE(dpp.produksi_qty,0)
                 + COALESCE(t.qty_tambahan,0)
                 - COALESCE(p.qty_pengurangan,0)) AS qty_total,

                COALESCE(dpp.target_produksi,0) AS target_produksi,
                COALESCE(t.total_tambahan,0)    AS total_tambahan,
                COALESCE(p.total_pengurangan,0) AS total_pengurangan,
                (COALESCE(dpp.target_produksi,0)
                 + COALESCE(t.total_tambahan,0)
                 - COALESCE(p.total_pengurangan,0)) AS sisa_target,

                -- hasil per divisi
                COALESCE(hd.qty_giling,0)  AS qty_giling,
                COALESCE(hc.qty_counter,0) AS qty_counter,
                COALESCE(hd.qty_poprok,0)  AS qty_poprok,
                COALESCE(hd.qty_dekor,0)   AS qty_dekor,

                mp.nama  AS master_product_nama,
                mp.urutan AS urutan
            ', [$this->perintah_id])
            ->orderBy('mp.urutan')
            ->get();

        // pakai Collection & pastikan index 0..N agar sinkron dengan $index di Blade
        $rows = $rows->values();
        $this->perintahproduksi = $rows;

        // prefill input kalau perlu (contoh: dari qty_giling)
        $this->realisasi = [];
        foreach ($rows as $i => $r) {
            $this->realisasi[$i] = (int) $r->qty_giling;
        }
    }








    /* ===================== RENDER ===================== */

    public function render()
    {
        return view('livewire.produksi.rekap-hasil-divisi', [
            'perintahproduksi' => $this->perintahproduksi,
            // $this->realisasi digunakan langsung di Blade input
        ]);
    }
}
