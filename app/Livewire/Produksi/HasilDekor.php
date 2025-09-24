<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\HasilDekor as HasilDekorModel;
use App\Models\Produksi\HasilDivisi;

class HasilDekor extends Component
{
    public string $tanggalProduksi;
    public array $perintahproduksi = [];
    public ?int $perintah_id = null;
    public array $realisasi = [];

    protected $rules = [
        'realisasi.*' => 'nullable|integer|min:0',
    ];

    public function mount($perintah_id = null)
    {
        $this->perintah_id = $perintah_id ?: null;

        if ($this->perintah_id) {
            $perintah = Perintah_Produksi::find($this->perintah_id);
            $this->tanggalProduksi = $perintah?->tanggal_perintah ?? now()->toDateString();
            $this->loadDataFromNet();
        } else {
            $this->tanggalProduksi = now()->toDateString();
            $this->updatedTanggalProduksi($this->tanggalProduksi);
        }
    }

    /**
     * Dipicu saat input tanggal berubah (Flatpickr -> $wire.set('tanggalProduksi', ...))
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
            $this->perintah_id   = null;
            $this->perintahproduksi = [];
            $this->realisasi     = [];
            session()->flash('message', 'Tidak ada perintah pada tanggal ' . $date);
        }
    }

    /**
     * Loader berbasis tabel hasil_giling (relasi via perintah_produksi_id).
     * Menampilkan hanya baris yang punya hasil bersih (produksi_qty + qty_tambahan - qty_pengurangan) > 0.
     */
    /**
     * Loader berbasis mproducts/dpp (bukan hasil_giling).
     * Menampilkan hanya produk yang punya PRODUKSI BERSIH:
     *   qty_total = dpp.produksi_qty + t.qty_tambahan - p.qty_pengurangan  > 0
     * Prefill "realisasi" dari hasil_giling (kalau sudah pernah disimpan).
     */
    private function loadDataFromNet(): void
    {
        if (!$this->perintah_id) {
            $this->perintahproduksi = [];
            $this->realisasi = [];
            return;
        }

        // Sub agregasi pengurangan & tambahan per produk
        $subPengurangan = DB::table('produksi_pengurangan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                     SUM(qty_pengurangan) AS qty_pengurangan,
                     SUM(target_qty_pengurangan) AS total_pengurangan')
            ->where('perintah_produksi_id', $this->perintah_id)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        $subTambahan = DB::table('produksi_tambahan')
            ->selectRaw('mproducts_id, perintah_produksi_id,
                     SUM(qty_tambahan) AS qty_tambahan,
                     SUM(target_qty_tambahan) AS total_tambahan')
            ->where('perintah_produksi_id', $this->perintah_id)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        // Basis: master product + DPP per perintah, prefill realisasi dari hasil_giling
        $rows = DB::table('mproducts as mp')
            ->leftJoin('detail_perintah_produksi as dpp', function ($j) {
                $j->on('dpp.mproducts_id', '=', 'mp.id')
                    ->where('dpp.perintah_produksi_id', '=', $this->perintah_id);
            })
            ->leftJoinSub($subPengurangan, 'p', function ($j) {
                $j->on('p.mproducts_id', '=', 'mp.id');
            })
            ->leftJoinSub($subTambahan, 't', function ($j) {
                $j->on('t.mproducts_id', '=', 'mp.id');
            })
            ->leftJoin('hasil_divisi as hd', function ($j) {
                $j->on('hd.mproducts_id', '=', 'mp.id')
                    ->where('hd.perintah_produksi_id', '=', $this->perintah_id)
                    ->where('hd.divisi_id', '=', 7);   // ✅ filter divisi
            })
            ->whereRaw('
        (COALESCE(dpp.produksi_qty,0)
         + COALESCE(t.qty_tambahan,0)
         - COALESCE(p.qty_pengurangan,0)) > 0
    ')
            ->selectRaw('
        mp.id  AS mproducts_id,
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

        -- hanya dari divisi_id=6
        hd.qty_hasil AS realisasi,

        mp.nama  AS master_product_nama,
        mp.urutan AS urutan
    ', [$this->perintah_id])
            ->orderBy('mp.urutan')
            ->get();


        $this->perintahproduksi = $rows->map(fn($r) => (array) $r)->toArray();

        // Prefill input realisasi (null = placeholder "0" di UI)
        $this->realisasi = [];
        foreach ($this->perintahproduksi as $i => $row) {
            $this->realisasi[$i] = $row['realisasi']; // bisa null/angka
        }
    }
    public function submit()
    {
        $this->validate();

        $rows = collect($this->perintahproduksi)->map(fn($r) => is_array($r) ? (object)$r : $r);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $idx => $row) {
                $qty = $this->realisasi[$idx] ?? null;
                if ($qty === null || $qty === '') continue;

                HasilDivisi::updateOrCreate(
                    [
                        'perintah_produksi_id' => $this->perintah_id,
                        'mproducts_id'         => $row->mproducts_id,
                        'divisi_id' => 7,

                    ],
                    [
                        'qty_hasil' => (int) $qty,

                        'user_id'   => Auth::id(),
                    ]
                );
            }
        });

        session()->flash('success', 'Realisasi giling berhasil disimpan.');
        $this->dispatch('toast', type: 'success', message: 'Tersimpan!');

        // Refresh agar total/selisih di tabel ikut terbarui
        $this->loadDataFromNet();
    }


    public function render()
    {
        return view('livewire.produksi.hasil-dekor', [
            'perintahproduksi' => $this->perintahproduksi,
        ]);
    }
}
