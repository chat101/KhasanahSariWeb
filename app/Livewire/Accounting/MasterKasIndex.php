<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Coa;
use Livewire\WithPagination;
use Livewire\Component;

class MasterKasIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $editId = null;

    public $kode;
    public $nama;
    public $normal_balance = 'D'; // kas normalnya Debet
    public $default_role;         // optional: kas_kecil, kas_bank, dll

    protected function rules()
    {
        return [
            'kode'           => 'required|string|max:50|unique:coa,kode,' . $this->editId,
            'nama'           => 'required|string|max:150',
            'normal_balance' => 'nullable|in:D,K',
            'default_role'   => 'nullable|string|max:50',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Coa::where('is_kas', true)
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('kode', 'like', $s)
                       ->orWhere('nama', 'like', $s)
                       ->orWhere('default_role', 'like', $s);
                });
            })
            ->orderBy('kode');

        $items = $query->paginate($this->perPage);

        return view('livewire.accounting.master-kas-index', [
            'items' => $items,
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->normal_balance = 'D';
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $row = Coa::where('is_kas', true)->findOrFail($id);
        $this->editId        = $row->id;
        $this->kode          = $row->kode;
        $this->nama          = $row->nama;
        $this->normal_balance = $row->normal_balance ?: 'D';
        $this->default_role  = $row->default_role;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $row = Coa::where('is_kas', true)->findOrFail($this->editId);
            $row->update([
                'kode'           => $this->kode,
                'nama'           => $this->nama,
                'tipe'           => 'aset',          // kas = aset
                'normal_balance' => $this->normal_balance,
                'is_kas'         => true,
                'default_role'   => $this->default_role ?: null,
            ]);

            $msg = 'Akun kas berhasil diupdate.';
        } else {
            Coa::create([
                'kode'           => $this->kode,
                'nama'           => $this->nama,
                'tipe'           => 'aset',
                'normal_balance' => $this->normal_balance,
                'is_kas'         => true,
                'default_role'   => $this->default_role ?: null,
            ]);

            $msg = 'Akun kas baru berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function delete($id)
    {
        $row = Coa::where('is_kas', true)->findOrFail($id);

        // optional: cek dulu apakah sudah dipakai di jurnal_detail
        // kalau sudah dipakai, sebaiknya tidak boleh dihapus

        $row->delete();

        $this->dispatch('notify', type: 'success', message: 'Akun kas berhasil dihapus.');
        $this->resetPage();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId        = null;
        $this->kode          = '';
        $this->nama          = '';
        $this->normal_balance = 'D';
        $this->default_role  = null;
    }

}
