<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\Coa;
use Livewire\WithPagination;
use Livewire\Component;

class MasterAkunBiayaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $perPage = 10;

    public $showModal = false;
    public $editId = null;

    public $kode;
    public $nama;
    public $normal_balance = 'D'; // biaya normalnya di debet

    protected function rules()
    {
        return [
            'kode' => 'required|string|max:50|unique:coa,kode,' . $this->editId,
            'nama' => 'required|string|max:150',
            'normal_balance' => 'nullable|in:D,K',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Coa::where('tipe', 'biaya')
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->where('kode', 'like', $s)
                       ->orWhere('nama', 'like', $s);
                });
            })
            ->orderBy('kode');

        $items = $query->paginate($this->perPage);

        return view('livewire.accounting.master-akun-biaya-index', [
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

        $row = Coa::where('tipe', 'biaya')->findOrFail($id);
        $this->editId = $row->id;
        $this->kode   = $row->kode;
        $this->nama   = $row->nama;
        $this->normal_balance = $row->normal_balance ?: 'D';

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $row = Coa::where('tipe', 'biaya')->findOrFail($this->editId);
            $row->update([
                'kode'           => $this->kode,
                'nama'           => $this->nama,
                'normal_balance' => $this->normal_balance,
                'tipe'           => 'biaya',   // pastikan tetap biaya
                'is_kas'         => false,
            ]);

            $msg = 'Akun biaya berhasil diupdate.';
        } else {
            Coa::create([
                'kode'           => $this->kode,
                'nama'           => $this->nama,
                'tipe'           => 'biaya',
                'normal_balance' => $this->normal_balance,
                'is_kas'         => false,
            ]);

            $msg = 'Akun biaya baru berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('notify', type: 'success', message: $msg);
    }

    public function delete($id)
    {
        $row = Coa::where('tipe', 'biaya')->findOrFail($id);

        // optional: cek apakah sudah dipakai di jurnal_detail sebelum hapus

        $row->delete();

        $this->dispatch('notify', type: 'success', message: 'Akun biaya berhasil dihapus.');
        $this->resetPage();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId = null;
        $this->kode   = '';
        $this->nama   = '';
        $this->normal_balance = 'D';
    }

}
