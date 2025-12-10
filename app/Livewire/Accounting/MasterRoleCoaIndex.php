<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Coa;
use Livewire\WithPagination;
use Livewire\Component;

class MasterRoleCoaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $perPage = 15;

    public $showModal = false;
    public $editId = null;

    public $kode;
    public $nama;
    public $tipe;
    public $default_role;

    // daftar role yang dipakai di template (bisa kamu tambah sendiri)
    public $roleOptions = [
        ''               => '- Tidak ada role -',
        'kas'            => 'kas',
        'pendapatan'     => 'pendapatan',
        'biaya'          => 'biaya',
        'piutang'        => 'piutang',
        'hutang_dagang'  => 'hutang_dagang',
        'modal'          => 'modal',
        'prive'          => 'prive',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Coa::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('kode', 'like', $s)
                       ->orWhere('nama', 'like', $s)
                       ->orWhere('default_role', 'like', $s)
                       ->orWhere('tipe', 'like', $s);
                });
            })
            ->orderBy('tipe')
            ->orderBy('kode');

        $items = $query->paginate($this->perPage);

        return view('livewire.accounting.master-role-coa-index', [
            'items' => $items,
        ]);
    }

    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $row = Coa::findOrFail($id);

        $this->editId      = $row->id;
        $this->kode        = $row->kode;
        $this->nama        = $row->nama;
        $this->tipe        = $row->tipe;
        $this->default_role = $row->default_role;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'default_role' => 'nullable|string|max:50',
        ]);

        $row = Coa::findOrFail($this->editId);

        $row->update([
            'default_role' => $this->default_role ?: null,
        ]);

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('notify', type: 'success', message: 'Role COA berhasil disimpan.');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId      = null;
        $this->kode        = '';
        $this->nama        = '';
        $this->tipe        = '';
        $this->default_role = '';
    }

}
