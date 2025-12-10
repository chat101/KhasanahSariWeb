<?php

namespace App\Livewire\Accounting\Bank;

use Livewire\Component;
use App\Models\Accounting\Bank;

class Edit extends Component
{
    public $bank;
    public $nama_bank, $nomor_rekening, $atas_nama, $saldo_awal;

    public function mount(Bank $bank)
    {
        $this->bank = $bank;
        $this->nama_bank = $bank->nama_bank;
        $this->nomor_rekening = $bank->nomor_rekening;
        $this->atas_nama = $bank->atas_nama;
        $this->saldo_awal = $bank->saldo_awal;
    }

    protected $rules = [
        'nama_bank' => 'required',
        'nomor_rekening' => 'required',
        'atas_nama' => 'required',
        'saldo_awal' => 'numeric|min:0',
    ];

    public function update()
    {
        $this->validate();

        $this->bank->update([
            'nama_bank' => $this->nama_bank,
            'nomor_rekening' => $this->nomor_rekening,
            'atas_nama' => $this->atas_nama,
            'saldo_awal' => $this->saldo_awal,
        ]);

        session()->flash('success', 'Bank berhasil diperbarui.');
        return redirect()->route('bank.index');
    }


    public function render()
    {
        return view('livewire.accounting.bank.edit');
    }
}
