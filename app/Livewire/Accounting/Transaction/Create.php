<?php

namespace App\Livewire\Accounting\Transaction;

use Livewire\WithFileUploads;
use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\CategoriesTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithFileUploads;
    public $formTitle = "Tambah Transaksi";
    public $formAction = "save";
    public $submitButton = "Simpan";

    public $bank_id, $category_id, $tanggal, $tipe = 'debit';
    public $jumlah, $ref_no, $keterangan;
    public $bukti;

    protected $rules = [
        'bank_id' => 'required',
        'tanggal' => 'required|date',
        'tipe' => 'required|in:debit,kredit',
        'jumlah' => 'required|numeric|min:1',
        'category_id' => 'nullable',
        'keterangan' => 'nullable',
        'ref_no' => 'nullable',
        'bukti' => 'nullable|file|max:2048',
    ];

    public function save()
    {
        $this->validate();

        $path = $this->bukti ? $this->bukti->store('bukti', 'public') : null;

        BankTransaction::create([
            'bank_id' => $this->bank_id,
            'category_id' => $this->category_id,
            'tanggal' => $this->tanggal,
            'tipe' => $this->tipe,
            'jumlah' => $this->jumlah,
            'ref_no' => $this->ref_no,
            'keterangan' => $this->keterangan,
            'bukti' => $path,
          'created_by' => Auth::id(),

        ]);

        session()->flash('success', 'Transaksi berhasil ditambahkan.');
        return redirect()->route('transaksi.index');
    }

    public function render()
    {
        return view('livewire.accounting.transaction.create', [
            'banks' => Bank::all(),
            'categories' => CategoriesTransaction::all()
        ]);
    }

}
