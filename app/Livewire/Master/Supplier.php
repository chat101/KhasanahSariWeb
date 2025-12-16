<?php

namespace App\Livewire\Master;

use App\Models\MasterSupplier;
use Livewire\Component;
use Livewire\WithPagination;

class Supplier extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik
    public $nama, $telp, $alamat, $produkId;
    public $search = ''; // Make sure this is initialized
    public $modal = false; // untuk menampilkan modal
    // field ERP
    public $tempo_hari = 0;
    public $max_hutang;
    public $contact_person;
    public $email;
    public $is_aktif = true;
    // Listener untuk aksi hapus
    protected $listeners = ['triggerDelete' => 'delete'];

    // Validasi inputan
    protected $rules = [
        'nama'           => 'required|string',
        'telp'           => 'required|string',
        'alamat'         => 'required|string',
        'tempo_hari'     => 'required|integer|min:0',
        'max_hutang'     => 'nullable|numeric|min:0',
        'contact_person' => 'nullable|string',
        'email'          => 'nullable|email',
        'is_aktif'       => 'boolean',
    ];

    // Hapus data supplier berdasarkan id
    public function delete($id)
    {
        MasterSupplier::findOrFail($id)->delete();
        session()->flash('message', 'Data berhasil dihapus!');
    }

    // Render halaman supplier dengan pencarian
    public function render()
    {
        $produks = MasterSupplier::query();

        if ($this->search) {
            $produks = $produks->where(function ($query) {
                $query->where('nmsupp', 'like', '%' . $this->search . '%');
            });
        }

        $produks = $produks->orderBy('nmsupp', 'asc')->latest()->paginate(10);

        return view('livewire.master.supplier', [
            'produks' => $produks
        ]);
    }

    // Fungsi untuk membuka modal tambah/edit
    public function openModal()
    {
        $this->resetInputFields(); // Reset input form
        $this->modal = true;
    }

    // Fungsi untuk menutup modal
    public function closeModal()
    {
        $this->modal = false;
    }

    // Reset input fields
    public function resetInputFields()
    {
        $this->nama = '';
        $this->telp = '';
        $this->alamat = '';
        $this->produkId = null;

        $this->tempo_hari = 0;
        $this->max_hutang = null;
        $this->contact_person = null;
        $this->email = null;
        $this->is_aktif = true;
    }

    // Simpan atau update data supplier
    public function store()
    {
        $this->validate();

        $supp = $this->nama;

        // kalau edit: jangan bikin supplier_id baru
        if (!$this->produkId) {
            $kodesupp = substr($supp, 0, 3);
            $idsupp = $kodesupp . today()->format('Ymds');
        }

        MasterSupplier::updateOrCreate(
            ['id' => $this->produkId],
            [
                'supplier_id'   => $this->produkId ? MasterSupplier::find($this->produkId)->supplier_id ?? null : $idsupp,
                'nmsupp'        => $this->nama,
                'telpsupp'      => $this->telp,
                'suppalamat'    => $this->alamat,

                // field baru
                'tempo_hari'     => $this->tempo_hari,
                'max_hutang'     => $this->max_hutang,
                'contact_person' => $this->contact_person,
                'email'          => $this->email,
                'is_aktif'       => $this->is_aktif,
            ]
        );

        session()->flash('message', $this->produkId ? 'Supplier berhasil diperbarui.' : 'Supplier berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
    }

    // Fungsi untuk mengedit data supplier
    public function edit($id)
    {
        $produk = MasterSupplier::findOrFail($id);
        $this->produkId = $id;

        $this->nama = $produk->nmsupp;
        $this->telp = $produk->telpsupp;
        $this->alamat = $produk->suppalamat;

        // field baru
        $this->tempo_hari     = $produk->tempo_hari ?? 0;
        $this->max_hutang     = $produk->max_hutang;
        $this->contact_person = $produk->contact_person;
        $this->email          = $produk->email;
        $this->is_aktif       = (bool) ($produk->is_aktif ?? true);

        $this->modal = true;
    }

    // Mengatur ulang pencarian saat perubahan
    public function updatingSearch()
    {
        $this->resetPage();
    }

}
