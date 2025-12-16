<?php

namespace App\Livewire\Operasional;
use App\Models\Operasional\Area as OperasionalArea;
use App\Models\Operasional\Wilayah as OperasionalWilayah;
use Livewire\WithPagination;

use Livewire\Component;


class Wilayah extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $modal = false;

    public $editId = null;
    public $nama_wilayah = '';
    public $status = '1';

    protected function rules()
    {
        return [
            'nama_wilayah' => 'required|string|max:255',
            'status'       => 'nullable|in:0,1',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }

    public function openModal()
    {
        $this->resetForm();
        $this->modal = true;
    }

    public function closeModal()
    {
        $this->modal = false;
    }

    public function resetForm()
    {
        $this->resetValidation();
        $this->editId = null;
        $this->nama_wilayah = '';
        $this->status = '1';
    }

    public function edit($id)
    {
        $w = OperasionalWilayah::findOrFail($id);

        $this->editId = $w->id;
        $this->nama_wilayah = $w->nama_wilayah;
        $this->status = (string)($w->status ?? 1);

        $this->modal = true;
    }

    public function store()
    {
        $this->validate();

        OperasionalWilayah::updateOrCreate(
            ['id' => $this->editId],
            [
                'nama_wilayah' => $this->nama_wilayah,
                'status'       => $this->status,
            ]
        );

        session()->flash('message', $this->editId ? 'Wilayah berhasil diperbarui.' : 'Wilayah berhasil ditambahkan.');

        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $w = OperasionalWilayah::withCount('areas')->findOrFail($id);
        if ($w->areas_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Gagal!',
                'text' => 'Wilayah masih memiliki Area. Hapus/ubah Area dulu.'
            ]);
            return;
        }

        $w->delete();

        session()->flash('message', 'Wilayah berhasil dihapus.');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Wilayah berhasil dihapus.'
        ]);
    }

    public function render()
    {
        $data = OperasionalWilayah::query()
            ->when($this->search, fn($q) => $q->where('nama_wilayah', 'like', '%'.$this->search.'%'))
            ->latest()
            ->paginate(10);

        return view('livewire.operasional.wilayah', [
            'wilayahs' => $data,
        ]);
    }

}
