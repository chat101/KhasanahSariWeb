<?php

namespace App\Livewire\Operasional\MasterTrendInflasi;

use Livewire\Component;
use App\Models\Operasional\MasterTrendInflasi;
use Illuminate\Validation\Rule;

class Index extends Component
{
    public int $tahunAktif;

    // Modal generate tahun
    public bool $openGenerate = false;
    public ?int $tahunBaru = null;
    public $defaultInflasi = 1.20; // bisa kamu ubah default

    // Form edit (tahun mengikuti tahunAktif)
    public ?int $editingId = null;
    public int $bulan = 1;
    public $trend = 0;
    public $inflasi = 0;

    public function mount(): void
    {
        $this->tahunAktif = (int) date('Y');
    }

    public function render()
    {
        $rows = MasterTrendInflasi::query()
            ->where('tahun', $this->tahunAktif)
            ->orderBy('bulan')
            ->get();

        $listTahun = MasterTrendInflasi::query()
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        return view('livewire.operasional.master-trend-inflasi.index', [
            'rows' => $rows,
            'listTahun' => $listTahun,
        ]);
    }

    public function updatedTahunAktif($value): void
    {
        $this->cancelEdit();
    }

    public function openGenerateModal(): void
    {
        $this->resetValidation();

        // otomatis isi tahun baru = tahunAktif + 1 (kalau masuk akal)
        $this->tahunBaru = $this->tahunAktif + 1;
        $this->defaultInflasi = 1.20;
        $this->openGenerate = true;
    }

    public function closeGenerateModal(): void
    {
        $this->openGenerate = false;
    }

    public function generateTahun(): void
    {
        $this->validate([
            'tahunBaru' => ['required', 'integer', 'min:2000', 'max:2100'],
            'defaultInflasi' => ['required', 'numeric', 'between:-999.99,999.99'],
        ]);

        $tahun = (int) $this->tahunBaru;

        foreach (range(1, 12) as $bln) {
            MasterTrendInflasi::firstOrCreate(
                ['tahun' => $tahun, 'bulan' => $bln],
                ['trend' => 0, 'inflasi' => (float) $this->defaultInflasi]
            );
        }

        $this->tahunAktif = $tahun;
        $this->closeGenerateModal();

        $this->dispatch('toast', type: 'success', message: "Tahun {$tahun} berhasil dibuat (12 bulan)");
    }

    public function startEdit(int $id): void
    {
        $row = MasterTrendInflasi::whereKey($id)->firstOrFail();

        $this->editingId = $row->id;
        $this->bulan = (int) $row->bulan;
        $this->trend = $row->trend;
        $this->inflasi = $row->inflasi;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'bulan', 'trend', 'inflasi']);
        $this->bulan = 1;
        $this->trend = 0;
        $this->inflasi = 0;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'bulan' => ['required','integer','min:1','max:12'],
            'trend' => ['required','numeric','between:-999.99,999.99'],
            'inflasi' => ['required','numeric','between:-999.99,999.99'],
        ]);

        $exists = MasterTrendInflasi::query()
            ->where('tahun', $this->tahunAktif)
            ->where('bulan', $this->bulan)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($exists) {
            $this->addError('bulan', 'Bulan ini sudah ada untuk tahun tersebut.');
            return;
        }

        MasterTrendInflasi::updateOrCreate(
            ['id' => $this->editingId],
            [
                'tahun' => $this->tahunAktif,
                'bulan' => $this->bulan,
                'trend' => $this->trend,
                'inflasi' => $this->inflasi,
            ]
        );

        $this->cancelEdit();
        $this->dispatch('toast', type:'success', message:'Data tersimpan');
    }

    public function delete(int $id): void
    {
        MasterTrendInflasi::whereKey($id)->delete();
        $this->dispatch('toast', type:'success', message:'Data dihapus');
    }

}
