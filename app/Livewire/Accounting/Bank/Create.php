<?php

namespace App\Livewire\Accounting\Bank;

use Livewire\Component;
use App\Models\Accounting\Bank;

class Create extends Component
{
    public $nama_bank, $nomor_rekening, $atas_nama, $saldo_awal = 0;

    protected $rules = [
        'nama_bank' => 'required',
        'nomor_rekening' => 'required',
        'atas_nama' => 'required',
        'saldo_awal' => 'numeric|min:0',
    ];

    public function save()
    {
        $this->validate();

        Bank::create([
            'nama_bank' => $this->nama_bank,
            'nomor_rekening' => $this->nomor_rekening,
            'atas_nama' => $this->atas_nama,
            'saldo_awal' => $this->saldo_awal,
        ]);

        session()->flash('success', 'Bank berhasil ditambahkan.');
        return redirect()->route('bank.index');
    }


    public function render()
    {
        return view('livewire.accounting.bank.create');
    }
}
