<?php

namespace App\Livewire\Operasional;

use App\Models\Operasional\TargetKontribusi as OperasionalTargetKontribusi;
use Livewire\Component;

class TargetKontribusi extends Component
{
    public $search = '';

    // modal
    public $showModal = false;
    public $editId = null;

    // form
    public $kode = '';
    public $nama = '';
    public $tipe = 'PERSEN';
    public $nilai = 0;
    public $aktif = true;

    protected function rules()
    {
        return [
            'kode'  => 'required|string|max:50|unique:target_kontribusis,kode,' . $this->editId,
            'nama'  => 'required|string|max:100',
            'tipe'  => 'required|in:PERSEN,RUPIAH',
            'nilai' => 'required|numeric|min:0',
            'aktif' => 'boolean',
        ];
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit($id)
    {
        $row = OperasionalTargetKontribusi::findOrFail($id);

        $this->editId = $row->id;
        $this->kode   = $row->kode;
        $this->nama   = $row->nama;
        $this->tipe   = $row->tipe;
        $this->nilai  = (float) $row->nilai;
        $this->aktif  = (bool) $row->aktif;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function resetForm()
    {
        $this->editId = null;
        $this->kode = '';
        $this->nama = '';
        $this->tipe = 'PERSEN';
        $this->nilai = 0;
        $this->aktif = true;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // validasi tambahan tergantung tipe
        if ($this->tipe === 'PERSEN' && $this->nilai > 100) {
            $this->addError('nilai', 'Nilai persen tidak boleh > 100');
            return;
        }

        OperasionalTargetKontribusi::updateOrCreate(
            ['id' => $this->editId],
            [
                'kode'  => strtoupper(trim($this->kode)),
                'nama'  => trim($this->nama),
                'tipe'  => $this->tipe,
                'nilai' => $this->nilai,
                'aktif' => $this->aktif ? 1 : 0,
            ]
        );

        session()->flash('message', $this->editId ? 'Target berhasil diupdate.' : 'Target berhasil ditambahkan.');
        $this->showModal = false;
    }

    public function toggleAktif($id)
    {
        $row = OperasionalTargetKontribusi::findOrFail($id);
        $row->aktif = !$row->aktif;
        $row->save();

        session()->flash('message', 'Status target diubah.');
    }

    public function delete($id)
    {
        OperasionalTargetKontribusi::where('id', $id)->delete();
        session()->flash('message', 'Target dihapus.');
    }

    public function getRowsProperty()
    {
        return OperasionalTargetKontribusi::query()
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where('kode', 'like', $s)
                  ->orWhere('nama', 'like', $s);
            })
            ->orderBy('nama')
            ->get();
    }
    public function render()
    {
        return view('livewire.operasional.target-kontribusi', [
            'rows' => $this->rows,
        ]);
    }
}
