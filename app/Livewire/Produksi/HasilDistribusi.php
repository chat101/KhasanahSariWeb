<?php

namespace App\Livewire\Produksi;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\MasterProduct;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\Hasil_Produksi;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;

class HasilDistribusi extends Component
{
    public array $perintahproduksi = [];
    public $inputs = [];
    public string $tanggalProduksi;
    public $perintah_id;
    public function mount($perintah_id)
    {
        $this->perintah_id = $perintah_id;
        $perintah = Perintah_Produksi::find($perintah_id);
        $this->tanggalProduksi = $perintah ? $perintah->tanggal_perintah : now()->format('Y-m-d');
        // $this->perintahproduksi = Detail_Perintah_Produksi::with('masterProduct')->where('perintah_produksi_id', $perintah_id)->get()->toArray();
        $subPengurangan = DB::table('produksi_pengurangan')
            ->selectRaw('mproducts_id, perintah_produksi_id, SUM(target_qty_pengurangan) AS total_pengurangan')
            ->where('perintah_produksi_id', $perintah_id)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        $subTambahan = DB::table('produksi_tambahan')
            ->selectRaw('mproducts_id, perintah_produksi_id, SUM(target_qty_tambahan) AS total_tambahan')
            ->where('perintah_produksi_id', $perintah_id)
            ->groupBy('mproducts_id', 'perintah_produksi_id');

        $this->perintahproduksi = DB::table('mproducts as mp')
            ->leftJoin('detail_perintah_produksi as dpp', function ($j) use ($perintah_id) {
                $j->on('dpp.mproducts_id', '=', 'mp.id')
                    ->where('dpp.perintah_produksi_id', '=', $perintah_id);
            })
            ->leftJoinSub($subPengurangan, 'p', function ($j) {
                $j->on('p.mproducts_id', '=', 'mp.id');
            })
            ->leftJoinSub($subTambahan, 't', function ($j) {
                $j->on('t.mproducts_id', '=', 'mp.id');
            })
            ->selectRaw('
            mp.id as mproducts_id,
            COALESCE(dpp.perintah_produksi_id, ?) AS perintah_produksi_id,
            COALESCE(dpp.target_produksi, 0)      AS target_produksi,
            COALESCE(p.total_pengurangan, 0)      AS total_pengurangan,
            COALESCE(t.total_tambahan, 0)         AS total_tambahan,
            (COALESCE(dpp.target_produksi,0)
             + COALESCE(t.total_tambahan,0)
             - COALESCE(p.total_pengurangan,0))   AS sisa_target,
            mp.nama AS master_product_nama,
            mp.urutan AS urutan
        ', [$perintah_id])
            ->orderBy('mp.urutan')
            ->get()
            ->toArray();
        foreach ($this->perintahproduksi as $index => $produk) {
            $this->inputs[$index] = array_fill(0, 12, 0);
        }
        // dd($this->produk);
    }

    public function render()
    {
        return view('livewire.produksi.hasil-distribusi', [
            'perintahproduksi' => $this->perintahproduksi,
        ]);
    }

    public function sumColumns($rowIndex, array $columns): float
    {
        $total = 0;
        foreach ($columns as $col) {
            $value = $this->inputs[$rowIndex][$col] ?? 0;
            $total += is_numeric($value) ? (float) $value : 0;
        }
        return $total;
    }
    public function sumWithOperators($rowIndex, array $ops): float
    {
        $total = 0;
        foreach ($ops as $op) {
            $val = (float) ($this->inputs[$rowIndex][$op['col']] ?? 0);
            $total += $op['op'] === '-' ? -$val : $val;
        }
        return $total;
    }
    private function zeroFillInputs(): void
    {
        // 12 = jumlah kolom input per baris
        $rows = count($this->perintahproduksi);
        $this->inputs = array_fill(0, $rows, array_fill(0, 12, 0));
    }
    public function submit(): void
    {
        $perintahId = (int) $this->perintah_id;

        // CEK DUPLIKAT
        // ⬇️ Cek duplikat + bersihkan state & LS + toast WARNING lalu keluar
    if (Hasil_Produksi::where('perintah_produksi_id', $perintahId)->exists()) {
        $this->zeroFillInputs();
        $this->dispatch('clear-draft', scope: 'hasil_distribusi-' . $this->perintah_id);
        $this->dispatch('notify', type: 'warning', text: 'Data untuk perintah ini sudah pernah disimpan. Input ditolak.');
        return;
    }

        $tanggal = \Carbon\Carbon::parse($this->tanggalProduksi)->toDateString();

        try {
            DB::transaction(function () use ($perintahId, $tanggal) {
                // ====== SIMPAN HASIL_PRODUKSI ======
                foreach ($this->perintahproduksi as $i => $row) {
                    $inp = $this->inputs[$i] ?? [];

                    $po        = (int) ($inp[0]  ?? 0);
                    $pengalihan= (int) ($inp[1]  ?? 0);
                    $penyes    = (int) ($inp[2]  ?? 0);
                    $gojek     = (int) ($inp[3]  ?? 0);
                    $complain  = (int) ($inp[4]  ?? 0);
                    $pabrik    = (int) ($inp[5]  ?? 0);
                    $retProd   = (int) ($inp[6]  ?? 0);
                    $retJadi   = (int) ($inp[7]  ?? 0);
                    $ser       = (int) ($inp[8]  ?? 0);
                    $lain      = (int) ($inp[9]  ?? 0);
                    $sample    = (int) ($inp[10] ?? 0);
                    $real      = (int) ($inp[11] ?? 0);

                    $totalRetur = $retProd + $retJadi;
                    $sblm       = $po + $pengalihan + $penyes + $gojek + $pabrik + $ser + $lain + $sample;
                    $total      = $po + $pengalihan + $penyes + $gojek + $complain + $pabrik + $ser + $lain + $sample - $totalRetur;

                    $mproductId = (int) data_get($row, 'mproducts_id', 0);

                    Hasil_Produksi::updateOrCreate(
                        ['perintah_produksi_id' => $perintahId, 'mproducts_id' => $mproductId],
                        [
                            'real'             => $real,
                            'po_sistem'        => $po,
                            'po_pengalihan'    => $pengalihan,
                            'po_penyesuaian'   => $penyes,
                            'gojek'            => $gojek,
                            'complain'         => $complain,
                            'penjualan_pabrik' => $pabrik,
                            'retur_produksi'   => $retProd,
                            'retur_jadi'       => $retJadi,
                            'total_retur'      => $totalRetur,
                            'ser'              => $ser,
                            'lain_lain'        => $lain,
                            'sblm_complain'    => $sblm,
                            'total'            => $total,
                            'sample'           => $sample,
                            'updated_at'       => now(),
                            'created_at'       => now(),
                        ],
                    );
                }

                // ====== REFRESH REKAP STOK HARIAN ($tanggal) ======
                // (biarkan bagian agregasi & upsert yang sudah kamu tulis)
                // ...
            });

            // ====== BERHASIL ======
            // Kosongkan state server agar input di DOM ikut kosong saat re-render
            $this->zeroFillInputs();

            // Bersihkan draft sesuai perintah_id (localStorage)
            $this->dispatch('clear-draft', scope: 'hasil_distribusi-' . $this->perintah_id);

            // Toast success
            $this->dispatch('notify', type: 'success', text: 'Hasil distribusi & rekap stok harian tersimpan.');
        }
        catch (\Throwable $e) {
            $this->reset('inputs');
            $this->dispatch('clear-draft', scope: 'hasil_distribusi-' . $this->perintah_id);
            $this->dispatch('notify', type: 'error', text: 'Gagal menyimpan. Silakan coba lagi.');
            logger()->error('Submit Hasil Distribusi gagal', ['err' => $e->getMessage()]);

        }
    }

}
