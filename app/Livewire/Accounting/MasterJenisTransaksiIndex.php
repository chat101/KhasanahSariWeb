<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\JournalTransactionType;
use Livewire\WithPagination;
use Livewire\Component;

class MasterJenisTransaksiIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $editId = null;

    public $code;
    public $nama;

    protected function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:journal_transaction_types,code,' . $this->editId,
            'nama' => 'required|string|max:150',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = JournalTransactionType::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where('code', 'like', $s)
                  ->orWhere('nama', 'like', $s);
            })
            ->orderBy('code');

        $items = $query->paginate($this->perPage);

        return view('livewire.accounting.master-jenis-transaksi-index', [
            'items' => $items,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $row = JournalTransactionType::findOrFail($id);
        $this->editId = $row->id;
        $this->code   = $row->code;
        $this->nama   = $row->nama;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $row = JournalTransactionType::findOrFail($this->editId);
            $row->update([
                'code' => $this->code,
                'nama' => $this->nama,
            ]);

            $msg = 'Jenis transaksi berhasil diupdate.';
        } else {
            JournalTransactionType::create([
                'code' => $this->code,
                'nama' => $this->nama,
            ]);

            $msg = 'Jenis transaksi baru berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('notify', type: 'success', message: $msg);

    }

    public function delete($id)
    {
        $row = JournalTransactionType::findOrFail($id);

        // optional: bisa tambahkan check kalau punya template, jangan hapus
        $row->delete();

        $this->dispatch('notify', type: 'success', message: 'Jenis transaksi berhasil dihapus.');
        $this->resetPage();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId = null;
        $this->code   = '';
        $this->nama   = '';
    }

}
