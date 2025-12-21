<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\LossBahan;
use Livewire\Component;

class InputLossBahan extends Component
{
    public $tanggal;
    public ?int $nominal = null;
    public ?int $toko_id = null;
    public ?string $keterangan = null;

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
        // ✅ Semua toko aktif untuk admin
        return MasterToko::query()
            ->where('status', '1')          // kolom status aktif (sesuaikan)
            ->orderBy('nmtoko')           // kolom nama toko (sesuaikan)
            ->get(['id','nmtoko','api_id']);
    }

    public function save()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'toko_id' => 'required|integer',
            'nominal' => 'required|numeric|min:0',
        ]);

        $toko = MasterToko::findOrFail((int)$this->toko_id);

        LossBahan::create([
            'tanggal' => $this->tanggal,
            'toko_id' => $toko->id,
            'api_id'  => $toko->api_id ?? null,
            'nominal' => (int)$this->nominal,
            'keterangan' => $this->keterangan ?: null,
        ]);

        // ✅ reset
        $this->reset(['toko_id','nominal','keterangan']);

        // ✅ ini yang bikin Alpine ikut kosong
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
            ->with('toko')
            ->orderByDesc('tanggal')
            ->get();
    }

    public function render()
    {
        return view('livewire.operasional.input-loss-bahan');
    }
}
