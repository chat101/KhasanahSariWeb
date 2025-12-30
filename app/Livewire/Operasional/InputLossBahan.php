<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\MasterBarang;
use App\Models\Operasional\LossBahan;
use Livewire\Component;

class InputLossBahan extends Component
{
    public $tanggal;
    public ?int $nominal = null;
    public ?int $toko_id = null;
    public ?int $barang_id = null;
    public ?int $qty = null; // added
    public ?string $keterangan = null;

    // server-side search term
    public string $barangSearch = '';

    // keep dropdown open across Livewire updates
    public bool $openSearch = false;

    public $periodeAwal;
    public $periodeAkhir;

    public function mount()
    {
        $this->tanggal = now()->toDateString();
        $this->periodeAwal = now()->startOfMonth()->toDateString();
        $this->periodeAkhir = now()->toDateString();
    }

    public function getTokosProperty()
    {
        return MasterToko::query()
            ->where('status', '1')
            ->orderBy('nmtoko')
            ->get(['id','nmtoko','api_id']);
    }

    // server-side search: return matching msbarangs when user types
    public function getSearchBarangsProperty()
    {
        // if no search term, return top 15 items to show initial list
        if (!trim($this->barangSearch)) {
            return MasterBarang::query()
                ->orderBy('nmbarang')
                ->limit(15)
                ->get(['id', 'nmbarang', 'harga']);
        }

        return MasterBarang::query()
            ->where('nmbarang', 'like', '%' . $this->barangSearch . '%')
            ->orderBy('nmbarang')
            ->limit(15)
            ->get(['id', 'nmbarang', 'harga']);
    }

    // expose the selected barang model to view
    public function getSelectedBarangProperty()
    {
        if (!$this->barang_id) return null;
        return MasterBarang::query()->find($this->barang_id, ['id','nmbarang','harga']);
    }

    // recalculate when barang or qty changes
    public function updatedBarangId($value)
    {
        $this->recalculateNominal();
    }

    public function updatedQty($value)
    {
        $this->recalculateNominal();
    }

    // UX helpers: stepper for qty
    public function incrementQty()
    {
        if (!$this->qty) $this->qty = 1;
        $this->qty = (int) $this->qty + 1;
        $this->recalculateNominal();
    }

    public function decrementQty()
    {
        if (!$this->qty) $this->qty = 1;
        $this->qty = max(1, (int) $this->qty - 1);
        $this->recalculateNominal();
    }

    // called when barangSearch changes (Livewire side)
    public function updatedBarangSearch($value)
    {
        // open the results whenever user types or when search is cleared (we show top items)
        $this->openSearch = true;
    }

    protected function recalculateNominal()
    {
        if (!$this->barang_id || !$this->qty) {
            $this->nominal = null;
            return;
        }

        $barang = MasterBarang::find($this->barang_id);
        $harga = $barang->harga ?? 0;
        // ensure integers
        $this->nominal = (int) ($this->qty * (int) $harga);
    }

    // user selects a barang from search results
    public function selectBarang($id)
    {
        $b = MasterBarang::find($id);
        if (!$b) return;

        $this->barang_id = $b->id;
        $this->barangSearch = $b->nmbarang; // show selected name in search box

        $this->recalculateNominal();

        // close dropdown after selection
        $this->openSearch = false;
    }

    public function clearSelectedBarang()
    {
        $this->barang_id = null;
        $this->barangSearch = '';
        $this->nominal = null;

        $this->openSearch = false;
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'toko_id' => 'required|integer',
            'barang_id' => 'required|integer',
            'qty' => 'required|integer|min:1', // added
            'nominal' => 'required|numeric|min:0',
        ]);

        $toko = MasterToko::findOrFail((int)$this->toko_id);

        LossBahan::create([
            'tanggal' => $this->tanggal,
            'toko_id' => $toko->id,
            'api_id'  => $toko->api_id ?? null,
            'barang_id' => $this->barang_id,
            'qty' => $this->qty,
            'nominal' => (int)$this->nominal,
            'keterangan' => $this->keterangan ?: null,
        ]);

        // reset
        $this->reset(['toko_id','barang_id','qty','nominal','keterangan','barangSearch','openSearch']);

        $this->dispatch('loss-saved');

        $this->dispatch('swal:success', message: 'Loss bahan berhasil disimpan');
    }

    public function delete($id)
    {
        LossBahan::whereKey($id)->delete();
        $this->dispatch('swal:success', message: 'Data dihapus');
    }

    public function getRowsProperty()
    {
        return LossBahan::query()
            ->whereBetween('tanggal', [$this->periodeAwal, $this->periodeAkhir])
            ->with(['toko','barang'])
            ->orderByDesc('tanggal')
            ->get();
    }

    public function render()
    {
        return view('livewire.operasional.input-loss-bahan');
    }
}
