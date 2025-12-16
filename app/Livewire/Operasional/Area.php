<?php

namespace App\Livewire\Operasional;

use App\Models\Operasional\Area as OperasionalArea;
use App\Models\Operasional\Wilayah as OperasionalWilayah;
use Livewire\Component;
use Livewire\WithPagination;



class Area extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $filterWilayah = '';
    public $modal = false;

    public $editId = null;
    public $wilayah_id = '';
    public $nama_area = '';
    public $status = '1';

    protected function rules()
    {
        return [
            'wilayah_id' => 'required|exists:wilayah,id',
            'nama_area'  => 'required|string|max:255',
            'status'     => 'nullable|in:0,1',
        ];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterWilayah() { $this->resetPage(); }

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
        $this->wilayah_id = '';
        $this->nama_area = '';
        $this->status = '1';
    }

    public function edit($id)
    {
        $a = Area::findOrFail($id);

        $this->editId = $a->id;
        $this->wilayah_id = (string)$a->wilayah_id;
        $this->nama_area = $a->nama_area;
        $this->status = (string)($a->status ?? 1);

        $this->modal = true;
    }

    public function store()
    {
        $this->validate();

        OperasionalArea::updateOrCreate(
            ['id' => $this->editId],
            [
                'wilayah_id' => $this->wilayah_id,
                'nama_area'  => $this->nama_area,
                'status'     => $this->status,
            ]
        );

        session()->flash('message', $this->editId ? 'Area berhasil diperbarui.' : 'Area berhasil ditambahkan.');
        $this->closeModal();
        $this->resetForm();
    }

    public function delete($id)
    {
        $a = OperasionalArea::withCount('tokos')->findOrFail($id);
        if ($a->tokos_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Gagal!',
                'text' => 'Area masih dipakai oleh Toko. Pindahkan toko dulu.'
            ]);
            return;
        }

        $a->delete();

        session()->flash('message', 'Area berhasil dihapus.');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Area berhasil dihapus.'
        ]);
    }

    public function render()
    {
        $query = OperasionalArea::query()
            ->with('wilayah')
            ->when($this->filterWilayah, fn($q) => $q->where('wilayah_id', $this->filterWilayah))
            ->when($this->search, function ($q) {
                $s = '%'.$this->search.'%';
                $q->where('nama_area', 'like', $s)
                  ->orWhereHas('wilayah', fn($w) => $w->where('nama_wilayah', 'like', $s));
            })
            ->latest();

        return view('livewire.operasional.area', [
            'areas'    => $query->paginate(10),
            'wilayahs' => OperasionalWilayah::orderBy('nama_wilayah')->get(),
        ]);
    }

}
