<?php

namespace App\Livewire\Produksi;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StokAwalOpname extends Component
{
    public string $tanggal;       // 'YYYY-MM-DD'
    public array  $rows = [];     // [{id,nama,system,real}]
    public array  $real = [];     // keyed by product id
    public array $alasan  = [];
    public array $counted  = [];

    public function mount(?string $tanggal = null)
    {
        $this->tanggal = $tanggal ?: now()->toDateString();
        $this->loadRows();
    }

    private function lastEndingBefore(string $tanggal, int $pid): float
    {
        return (float) (DB::table('stok_rekap_harian')
            ->where('mproducts_id', $pid)
            ->where('tanggal', '<', $tanggal)
            ->orderByDesc('tanggal')
            ->selectRaw('COALESCE(stok_akhir, stok_awal + masuk_hari - keluar_hari) AS akhir')
            ->value('akhir') ?? 0);
    }

    public function loadRows(): void
    {
        $produk = DB::table('mproducts')->select('id','nama')->orderBy('id')->get();
        $this->rows = [];
        $this->real = [];

        foreach ($produk as $p) {
            $system = $this->lastEndingBefore($this->tanggal, (int)$p->id);

            // jika sudah ada stok_awal_manual di T, tampilkan sbg default
            $manual = DB::table('stok_awal_manual')
                ->where('mproducts_id', $p->id)
                ->where('tanggal', $this->tanggal)
                ->value('qty');

            $this->rows[] = [
                'id'     => (int)$p->id,
                'nama'   => $p->nama,
                'system' => (float)$system,
                'manual' => $manual !== null ? (float)$manual : null,
            ];
            $this->real[$p->id] = $manual !== null ? (float)$manual : (float)$system;
        }
    }

    public function updatedTanggal(): void
    {
        $this->loadRows();
    }

    public function save(): void
    {
        $this->validate([
            'tanggal' => 'required|date',
            'real.*'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () {
            foreach ($this->real as $pid => $qty) {
                if ($qty === null || $qty === '') continue;

                // REPLACE stok_awal_manual untuk tanggal T
                DB::table('stok_awal_manual')->updateOrInsert(
                    ['mproducts_id' => (int)$pid, 'tanggal' => $this->tanggal],
                    ['qty' => (float)$qty]
                );
            }
        });

        session()->flash('message', 'Stok awal disimpan untuk '.$this->tanggal);
        $this->loadRows();
    }

    public function render()
    {
        return view('livewire.produksi.stok-awal-opname');
    }

}
