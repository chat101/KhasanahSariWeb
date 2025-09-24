<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use App\Models\Produksi\MsJobs;
use App\Models\Produksi\MasterProduct;
use Illuminate\Support\Facades\DB;

class SettingSubBagian extends Component
{
    public $jobs;
    public $msjobs_id;
    public $jadwals = [];

    public bool $showModal = false;
    public ?int $editingId = null;   // <- ID yang sedang diedit (null = mode tambah)


    /** form state untuk modal */
    public array $form = [
        'group_job'            => '',
        'nama_job'             => '',
        'jml_orang'            => null,
        'target'               => null,
        'unit'                 => 'Pcs',
        'jam_mulai'            => null,
        'deskripsi'            => '',
        'use_target_as_output' => false,
        'produk_ids'           => [],
    ];

    public $allProducts = [];
    public array $produks = [];

    public function mount()
    {
        $this->jobs = MsJobs::with('produkToJob.product')->get();
        $this->allProducts = MasterProduct::orderBy('nama')->get(['id', 'nama']);
    }
    protected function rules()
    {
        return [
            'form.group_job' => 'required',
            'form.nama_job'  => 'required|string|max:150',
            'form.jml_orang' => 'nullable|integer|min:0',
            'form.target'    => 'nullable|integer|min:0',
            'form.unit'      => 'nullable|string|max:20',
            'form.jam_mulai' => 'nullable',
            'form.deskripsi' => 'nullable',
            'form.use_target_as_output' => 'boolean',
            'form.produk_ids'  => 'array',
            'form.produk_ids.*'=> 'integer|exists:mproducts,id',
        ];
    }
    public function openModal(): void
    {
        $this->editingId = null; // mode tambah
        // reset form
        $this->form = [
            'group_job'            => '',
            'nama_job'             => '',
            'jml_orang'            => null,
            'target'               => null,
            'unit'                 => 'Pcs',
            'jam_mulai'            => null,
            'deskripsi'            => '',
            'use_target_as_output' => false,
            'produk_ids'           => [],
        ];
        $this->showModal = true;
    }
     /** ---------- EDIT ---------- */
     public function openEdit(int $id): void
     {
         $job = MsJobs::with('produkToJob')->findOrFail($id);

         $this->editingId = $job->id;
         $this->form = [
             'group_job'            => $job->group_job ?? 'LOYANG',
             'nama_job'             => $job->nama_job,
             'jml_orang'            => $job->jml_orang,
             'target'               => $job->target,
             'unit'                 => $job->unit ?? 'Pcs',
             'jam_mulai'            => $job->jam_mulai,
             'deskripsi'            => $job->deskripsi,
             'use_target_as_output' => (bool) $job->use_target_as_output,
             // ambil list produk dari pivot
             'produk_ids'           => $job->produkToJob->pluck('msproducts_id','msproducts_id')->keys()->map(fn($v)=>(int)$v)->all(),
             // ^ jika kolom pivot kamu "mproducts_id", ganti ke ->pluck('mproducts_id')
         ];

         $this->showModal = true;
     }
    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /** ---------- SIMPAN (CREATE/UPDATE) ---------- */
    public function saveJob(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->editingId) {
                // UPDATE
                $job = MsJobs::findOrFail($this->editingId);
                $job->update([
                    'group_job'            => $this->form['group_job'],
                    'nama_job'             => $this->form['nama_job'],
                    'jml_orang'            => $this->form['jml_orang'] ?? 0,
                    'target'               => $this->form['target'] ?? 0,
                    'unit'                 => $this->form['unit'] ?: 'Pcs',
                    'jam_mulai'            => $this->form['jam_mulai'],
                    'deskripsi'            => $this->form['deskripsi'] ?: null,
                    'use_target_as_output' => (bool) $this->form['use_target_as_output'],
                ]);

                // sync pivot (timestamps ikut kalau relasi pakai ->withTimestamps())
                $job->produkToJobSetting()->sync($this->form['produk_ids'] ?? []);
            } else {
                // CREATE
                $job = MsJobs::create([
                    'group_job'            => $this->form['group_job'],
                    'nama_job'             => $this->form['nama_job'],
                    'jml_orang'            => $this->form['jml_orang'] ?? 0,
                    'target'               => $this->form['target'] ?? 0,
                    'unit'                 => $this->form['unit'] ?: 'Pcs',
                    'jam_mulai'            => $this->form['jam_mulai'],
                    'deskripsi'            => $this->form['deskripsi'] ?: null,
                    'use_target_as_output' => (bool) $this->form['use_target_as_output'],
                ]);

                $job->produkToJobSetting()->sync($this->form['produk_ids'] ?? []);
            }
        });

        // refresh tabel
        $this->jobs = MsJobs::with('produkToJob.product')->get();

        $this->showModal = false;
        session()->flash('message', $this->editingId ? 'Bagian diperbarui.' : 'Bagian ditambahkan.');
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.produksi.setting-sub-bagian');
    }
}
