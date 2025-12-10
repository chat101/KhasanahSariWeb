<?php

namespace App\Livewire\Accounting\Transaction;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\CategoriesTransaction;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Edit extends Component
{
    use WithFileUploads;
    public $formTitle = "Edit Transaksi";
    public $formAction = "update";
    public $submitButton = "Update";
    public $transaction;
    public $bank_id, $category_id, $tanggal, $tipe;
    public $jumlah, $ref_no, $keterangan;

    public $bukti;        // file baru
    public $old_bukti;    // file lama

    public function mount(BankTransaction $bankTransaction)
    {
        $this->transaction  = $bankTransaction;

        $this->bank_id      = $bankTransaction->bank_id;
        $this->category_id  = $bankTransaction->category_id;
        $this->tanggal      = $bankTransaction->tanggal;
        $this->tipe         = $bankTransaction->tipe;
        $this->jumlah       = $bankTransaction->jumlah;
        $this->ref_no       = $bankTransaction->ref_no;
        $this->keterangan   = $bankTransaction->keterangan;
        $this->old_bukti    = $bankTransaction->bukti; // simpan path lama
    }

    protected $rules = [
        'bank_id' => 'required',
        'tanggal' => 'required|date',
        'tipe' => 'required|in:debit,kredit',
        'jumlah' => 'required|numeric|min:1',
        'category_id' => 'nullable',
        'ref_no' => 'nullable',
        'keterangan' => 'nullable',
        'bukti' => 'nullable|file|max:2048',
    ];

    public function update()
    {
        $this->validate();

        $path = $this->old_bukti;

        // Jika ada upload bukti baru → replace lama
        if ($this->bukti) {

            // Hapus file lama jika ada
            if ($this->old_bukti && Storage::disk('public')->exists($this->old_bukti)) {
                Storage::disk('public')->delete($this->old_bukti);
            }

            // Simpan file baru
            $path = $this->bukti->store('bukti', 'public');
        }

        $this->transaction->update([
            'bank_id' => $this->bank_id,
            'category_id' => $this->category_id,
            'tanggal' => $this->tanggal,
            'tipe' => $this->tipe,
            'jumlah' => $this->jumlah,
            'ref_no' => $this->ref_no,
            'keterangan' => $this->keterangan,
            'bukti' => $path,
        ]);

        session()->flash('success', 'Transaksi berhasil diperbarui.');
        return redirect()->route('transaksi.index');
    }

    public function render()
    {
        return view('livewire.accounting.transaction.edit', [
            'banks' => Bank::all(),
            'categories' => CategoriesTransaction::all(),
        ]);
    }

}
