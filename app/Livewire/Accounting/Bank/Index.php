<?php

namespace App\Livewire\Accounting\Bank;

use Livewire\Component;
use App\Models\Accounting\Bank;

class Index extends Component
{
    public $search = '';

    public function delete($id)
    {
        Bank::findOrFail($id)->delete();
        session()->flash('success', 'Bank berhasil dihapus.');
    }


    public function render()
    {
        $banks = Bank::where('nama_bank', 'like', "%{$this->search}%")
        ->orWhere('nomor_rekening', 'like', "%{$this->search}%")
        ->orderBy('nama_bank')
        ->get();
        return view('livewire.accounting.bank.index', [
            'banks' => $banks,
        ]);
    }
}
