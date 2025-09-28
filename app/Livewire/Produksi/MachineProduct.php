<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use App\Models\Produksi\Machine;
use App\Models\Produksi\MasterProduct;
use Illuminate\Support\Facades\Request;

class MachineProduct extends Component
{
    public ?int $machineId = null;       // id mesin terpilih dari dropdown
    public ?Machine $machine = null;     // instance mesin terpilih
    public array $selectedProducts = []; // id produk yang tercentang
    public $allMachines;                 // koleksi semua mesin
    public $allProducts;                 // koleksi semua produk

    public function mount(?int $machineId = null): void
    {
        $this->allMachines = Machine::orderBy('nama')->get();
        $this->allProducts = MasterProduct::orderBy('nama')->get();

        // jangan otomatis buka modal
        $this->machineId = $machineId;
        $this->hydrateMachineState();
    }

    public function updatedMachineId(): void
    {
        $this->hydrateMachineState();
    }

    protected function hydrateMachineState(): void
    {
        $this->machine = $this->machineId ? Machine::with('products')->find($this->machineId) : null;

        if ($this->machine) {
            $this->selectedProducts = $this->machine->products->pluck('id')->toArray();
        } else {
            $this->selectedProducts = [];
        }
    }

    public function save(): void
    {
        $this->validate([
            'machineId' => ['required','exists:machines,id'],
            'selectedProducts' => ['array'],
            'selectedProducts.*' => ['integer','exists:mproducts,id'],
        ]);

        $machine = Machine::findOrFail($this->machineId);
        $machine->products()->sync($this->selectedProducts);

        $this->dispatch('notify', 'Pengaturan produk untuk mesin berhasil disimpan.');
        $this->hydrateMachineState(); // refresh state setelah save
    }


    public function render()
    {
        // inilah yang hilang → kita kirimkan data ke Blade
        $machines = Machine::with('products')->orderBy('nama')->get();

        return view('livewire.produksi.machine-product', [
            'machines' => $machines,
            'allProducts' => $this->allProducts,
            'machine' => $this->machine,
            'selectedProducts' => $this->selectedProducts,
        ]);
    }
}
