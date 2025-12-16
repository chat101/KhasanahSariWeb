<?php

namespace App\Livewire\Master;

use App\Models\MasterToko;
use App\Models\Operasional\Area;
use App\Models\Operasional\Wilayah;
use Livewire\WithPagination;

use Livewire\Component;

class Toko extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $modal = false;

    public $produkId; // id toko (edit)

    public $nama, $alamat, $status = 1,$apiid,$produksi_sendiri;
    public $wilayah_id, $area_id;

    public $areas = [];

    protected $listeners = ['triggerDelete' => 'delete'];

    protected function rules()
    {
        return [
            'nama'       => 'required|string|max:255',
            'alamat'     => 'nullable|string|max:255',
            'apiid'     => 'nullable|string|max:255',
            'wilayah_id' => 'required|exists:wilayah,id',
            'area_id'    => 'required|exists:area,id',
            'status'     => 'nullable|in:0,1',
            'produksi_sendiri' => 'nullable|in:0,1',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedWilayahId($value)
    {
        $this->area_id = null;
        $this->areas = $value
            ? Area::where('wilayah_id', $value)->orderBy('nama_area')->get()->toArray()
            : [];
    }

    public function render()
    {
        $query = MasterToko::query()
            ->with(['area.wilayah'])
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where('nmtoko', 'like', $s)
                  ->orWhere('alamat', 'like', $s)
                  ->orWhereHas('area', fn($a) => $a->where('nama_area', 'like', $s))
                  ->orWhereHas('area.wilayah', fn($w) => $w->where('nama_wilayah', 'like', $s));
            })
            ->latest();

        return view('livewire.master.toko', [
            'produks'  => $query->paginate(10),
            'wilayahs' => Wilayah::orderBy('nama_wilayah')->get(),
        ]);
    }

    public function openModal()
    {
        $this->resetInputFields();
        $this->modal = true;
    }

    public function closeModal()
    {
        $this->modal = false;
    }

    public function resetInputFields()
    {
        $this->resetValidation();

        $this->produkId = null;
        $this->nama = '';
        $this->apiid = '';

        $this->alamat = '';
        $this->status = 1;

        $this->wilayah_id = null;
        $this->area_id = null;
        $this->areas = [];
        $this->produksi_sendiri = 0;
    }

    public function store()
    {
        $this->validate();

        MasterToko::updateOrCreate(
            ['id' => $this->produkId],
            [
                'nmtoko'   => $this->nama,
                'api_id'   => $this->apiid,
                'alamat'   => $this->alamat,
                'status'   => $this->status,
                'area_id'  => $this->area_id,
                'produksi_sendiri' => $this->produksi_sendiri,
            ]
        );

        session()->flash('message', $this->produkId ? 'Toko berhasil diperbarui.' : 'Toko berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $toko = MasterToko::with('area.wilayah')->findOrFail($id);

        $this->produkId = $toko->id;
        $this->nama     = $toko->nmtoko;
        $this->nama     = $toko->nmtoko;
        $this->apiid    = $toko->api_id;

        $this->alamat   = $toko->alamat;
        $this->status   = (string)($toko->status ?? 1);

        $this->wilayah_id = optional($toko->area)->wilayah_id;
        $this->areas = $this->wilayah_id
            ? Area::where('wilayah_id', $this->wilayah_id)->orderBy('nama_area')->get()->toArray()
            : [];

        $this->area_id = $toko->area_id;
        $this->produksi_sendiri = (string)($toko->produksi_sendiri ?? 0);

        $this->modal = true;
    }

    public function delete($id)
    {
        MasterToko::findOrFail($id)->delete();

        session()->flash('message', 'Data berhasil dihapus!');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Data berhasil dihapus.'
        ]);
    }
}
