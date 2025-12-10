<?php

namespace App\Livewire\Accounting\Transaction;

use App\Models\Accounting\Bank;
use App\Models\Accounting\BankTransaction;
use App\Models\Accounting\CategoriesTransaction;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithFileUploads;
    public $search = '';
    public $filterBank = '';
    public $filterKategori = '';
    public $filterTipe = '';
    public $showTransferModal = false;
    public $bank_from,
        $bank_to,
        $jumlah,
        $tanggal,
        $keterangan,
        $bukti;

    protected $rules = [
        'bank_from' => 'required|different:bank_to',
        'bank_to' => 'required',
        'jumlah' => 'required|numeric|min:1',
        'tanggal' => 'required|date',
        'keterangan' => 'nullable|string',
        'bukti' => 'nullable|file|max:2048'
    ];
    public function delete($id)
    {
        BankTransaction::findOrFail($id)->delete();
        session()->flash('success', 'Transaksi berhasil dihapus.');
    }
    public function saveTransfer()
    {
        $this->validate();

        // Ambil bank sumber & tujuan
        $bankSource = Bank::find($this->bank_from);
        $bankTarget = Bank::find($this->bank_to);

        // Hitung saldo bank sumber (sum debit - sum kredit)
        $saldoSource = $bankSource->transactions->sum(function ($tx) {
            return $tx->tipe === 'debit'
                ? $tx->jumlah
                : -$tx->jumlah;
        });
        // CEK SALDO
        if ($saldoSource < $this->jumlah) {
            $this->addError('jumlah', 'Saldo bank tidak mencukupi untuk melakukan transfer.');
            return;
        }

        // Upload bukti jika ada
        $path = $this->bukti
            ? $this->bukti->store('bukti_transfer', 'public')
            : null;

        // 1️⃣ KREDIT di bank sumber
        BankTransaction::create([
            'bank_id'     => $this->bank_from,
            'category_id' => null,
            'tanggal'     => $this->tanggal,
            'tipe'        => 'kredit',
            'jumlah'      => $this->jumlah,
            'keterangan'  => 'Transfer ke ' . $bankTarget->nama_bank . '. ' . $this->keterangan,
            'bukti'       => $path,
            'created_by'  => Auth::id()
        ]);

        // 2️⃣ DEBIT di bank tujuan
        BankTransaction::create([
            'bank_id'     => $this->bank_to,
            'category_id' => null,
            'tanggal'     => $this->tanggal,
            'tipe'        => 'debit',
            'jumlah'      => $this->jumlah,
            'keterangan'  => 'Transfer dari ' . $bankSource->nama_bank . '. ' . $this->keterangan,
            'bukti'       => $path,
            'created_by'  => Auth::id()
        ]);

        // Reset modal & input
        $this->reset(['bank_from', 'bank_to', 'jumlah', 'tanggal', 'keterangan', 'bukti']);
        $this->showTransferModal = false;

        session()->flash('success', 'Transfer berhasil diproses.');
    }


    public function render()
    {
        $banks = Bank::all();
        $categories = CategoriesTransaction::all();

        $transaksi = BankTransaction::with(['bank', 'category'])
            ->when($this->filterBank, fn($q) => $q->where('bank_id', $this->filterBank))
            ->when($this->filterKategori, fn($q) => $q->where('category_id', $this->filterKategori))
            ->when($this->filterTipe, fn($q) => $q->where('tipe', $this->filterTipe))
            ->where(function ($q) {
                $q->where('keterangan', 'like', "%{$this->search}%")
                    ->orWhere('ref_no', 'like', "%{$this->search}%");
            })
            ->orderBy('tanggal', 'desc')
            ->get();


        return view('livewire.accounting.transaction.index', compact('transaksi', 'banks', 'categories'));
    }
}
