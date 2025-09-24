<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\MasterProduct;

class WorkOrder extends Component
{
    public $dataProduksiGabungan = [];
    public string $tanggal;

    // 🔢 TOTALS (dipakai di Blade)
    public float $sumTongUtama = 0.0;
    public float $sumTongTambahan = 0.0;
    public int $sumPcsUtama = 0;
    public int $sumPcsTambahan = 0;
    public int $rowCount = 0;
    public int $maxTambahanKe = 0;
    public array $detailTambahanMax = [];
    public float $sumTongTambahanMax = 0.0;
    public int $sumPcsTambahanMax = 0;
    public array $dataUtama = [];
    public array $dataTambahan = [];

    // === sebelumnya kamu punya blok ini untuk satu "tambahanKe" terpilih
    public array $detailTambahan = [];
    public float $sumTongTambahanKe = 0.0;
    public int $sumPcsTambahanKe = 0;
    public ?int $tambahanKe = null;   // tetap dipertahankan bila mau fitur pilih satu "ke"
    public bool $locked = false;

    public int $rowCountUtama = 0;
    public int $rowCountTambahan = 0;

    // NEW: kumpulan tabel per tambahan_ke
    public array $perKe = []; // [ke => ['detail'=>[], 'sumTong'=>float, 'sumPcs'=>int]]

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->loadProduks();
    }

    public function updatedTanggal(): void
    {
        $this->loadProduks();
    }

    public function loadProduks(): void
    {
        $tgl = $this->tanggal;
        $this->maxTambahanKe        = 0;
        $this->detailTambahan       = [];
        $this->sumTongTambahanKe    = 0.0;
        $this->sumPcsTambahanKe     = 0;
        $this->perKe                = []; // NEW reset

        // ====== Preload dasar ======
        $produkMap = MasterProduct::query()
            ->select('id','nama','patokan')
            ->get()
            ->keyBy('id');

        $produks = $produkMap->values();

        // Lock info
        $this->locked = Perintah_Produksi::whereDate('tanggal_perintah', $tgl)
            ->where('status', 1)->exists();

        // ====== Agregasi UTAMA ======
        $utamaMap = Detail_Perintah_Produksi::whereHas('perintahProduksi', fn($q) => $q->whereDate('tanggal_perintah', $tgl))
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_utama')
            ->groupBy('mproducts_id')
            ->pluck('total_utama', 'mproducts_id');

        $prodTargetMap = Detail_Perintah_Produksi::whereHas('perintahProduksi', fn($q) => $q->whereDate('tanggal_perintah', $tgl))
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_produksi, SUM(target_produksi) as total_target')
            ->groupBy('mproducts_id')
            ->get()
            ->keyBy('mproducts_id');

        // ====== Agregasi TAMBAHAN (akumulasi harian) ======
        $tambahanMap = Produksi_Tambahan::whereHas('perintahProduksi', fn($q) => $q->whereDate('tanggal_perintah', $tgl))
            ->selectRaw('mproducts_id, SUM(qty_tambahan) as total_tambahan')
            ->groupBy('mproducts_id')
            ->pluck('total_tambahan', 'mproducts_id');

        // ====== Susun finalData per produk ======
        $finalData = $produks->map(function ($p) use ($utamaMap, $tambahanMap, $prodTargetMap) {
            $id            = (int) $p->id;
            $patokan       = (float) ($p->patokan ?? 0);
            $totalUtama    = (float) ($utamaMap[$id]      ?? 0);
            $totalTambahan = (float) ($tambahanMap[$id]   ?? 0);
            $prodRow       = $prodTargetMap[$id] ?? null;

            $totalProduksi = (float) ($prodRow->total_produksi ?? 0);
            $totalTarget   = (float) ($prodRow->total_target   ?? 0);

            return [
                'mproducts_id'      => $id,
                'nama'              => $p->nama ?? '-',
                'patokan'           => $patokan,
                'total_utama'       => $totalUtama,
                'konversiutama'     => $totalUtama * $patokan,
                'total_tambahan'    => $totalTambahan,
                'konversitambahan'  => round($totalTambahan * $patokan),
                'produksi_qty'      => $totalProduksi,
                'target_produksi'   => $totalTarget,
            ];
        });

        // ====== Set untuk tabel UTAMA ======
        $utamaVisible   = $finalData->filter(fn($r) => (float) $r['total_utama']    > 0)->values();
        $tambahanVisible= $finalData->filter(fn($r) => (float) $r['total_tambahan'] > 0)->values();

        $this->dataUtama      = $utamaVisible->all();
        $this->rowCountUtama  = $utamaVisible->count();
        $this->sumTongUtama   = (float) $utamaVisible->sum('total_utama');
        $this->sumPcsUtama    = (int)   round($utamaVisible->sum('konversiutama'));

        $this->dataTambahan       = $tambahanVisible->all();
        $this->rowCountTambahan   = $tambahanVisible->count();
        $this->sumTongTambahan    = (float) $tambahanVisible->sum('total_tambahan');
        $this->sumPcsTambahan     = (int)   round($tambahanVisible->sum('konversitambahan'));

        // ====== Bagian "per Tambahan Ke" (loop semua ke) ======
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $tgl)->first();

        if (!$perintah) {
            $this->maxTambahanKe = 0;
            return;
        }

        $perintahId          = (int) $perintah->id;
        $this->maxTambahanKe = (int) (Produksi_Tambahan::where('perintah_produksi_id', $perintahId)->max('tambahan_ke') ?? 0);

        // CHANGED: alih-alih hanya satu $tambahanKe, kita bangun semua ke = 1..max
        for ($ke = 1; $ke <= $this->maxTambahanKe; $ke++) {
            $rowsPerKe = Produksi_Tambahan::where('perintah_produksi_id', $perintahId)
                ->where('tambahan_ke', $ke)
                ->selectRaw('mproducts_id, SUM(qty_tambahan) as qty')
                ->groupBy('mproducts_id')
                ->get();

            $detail = $rowsPerKe->map(function ($r) use ($produkMap) {
                    $p        = $produkMap->get($r->mproducts_id);
                    $nama     = $p->nama ?? '-';
                    $patokan  = (float) ($p->patokan ?? 0);
                    $qtyTong  = (float) ($r->qty ?? 0);

                    return [
                        'nama'      => $nama,
                        'patokan'   => $patokan,
                        'qty_tong'  => $qtyTong,
                        'konversi'  => (int) round($qtyTong * $patokan),
                    ];
                })
                ->filter(fn($x) => (float) $x['qty_tong'] !== 0.0)
                ->sortBy('nama')
                ->values();

            $this->perKe[$ke] = [
                'detail'  => $detail->toArray(),
                'sumTong' => (float) $detail->sum('qty_tong'),
                'sumPcs'  => (int)   $detail->sum('konversi'),
            ];
        }

        // (opsional) set default pilihan user ke MAX (tetap kompatibel kalau masih dipakai)
        if ($this->tambahanKe === null && $this->maxTambahanKe > 0) {
            $this->tambahanKe = $this->maxTambahanKe;
        }
    }

    public function render()
    {
        $perintah = Perintah_Produksi::whereDate('tanggal_perintah', $this->tanggal ?? now())->first();
        $perintahId = $perintah?->id ?? null;

        $totalTambahanCount = $perintahId
            ? Produksi_Tambahan::where('perintah_produksi_id', $perintahId)->count('id')
            : 0;

        $notifKey = $perintahId ? "perintah_{$perintahId}" : 'perintah_none';

        $this->dispatch('tambahan-count', total: $totalTambahanCount, key: $notifKey);

        return view('livewire.produksi.work-order', [
            'totalTambahanCount' => $totalTambahanCount,
            'notifKey'           => $notifKey,
            'maxTambahanKe'      => $this->maxTambahanKe,
            // NEW: biar Blade bisa akses langsung (opsional, karena public prop juga auto tersedia)
            'perKe'              => $this->perKe,
        ]);
    }
}
