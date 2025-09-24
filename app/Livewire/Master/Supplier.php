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

    // Listener untuk aksi hapus
    protected $listeners = ['triggerDelete' => 'delete'];

    // Validasi inputan
    protected $rules = [
        'nama' => 'required|string', // ubah menjadi string jika bukan numeric
        'telp' => 'required|string', // ubah menjadi string jika bukan numeric
        'alamat' => 'required|string',
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
    }

    // Simpan atau update data supplier
    public function store()
    {
        $this->validate();
        $supp = $this->nama;
        $kodesupp = substr($supp, 0, 3);
        $idsupp = $kodesupp . today()->format('Ymds');;

        // Menggunakan updateOrCreate untuk menambahkan atau memperbarui data
        MasterSupplier::updateOrCreate(['id' => $this->produkId], [
            'supplier_id' =>  $idsupp, // Menyimpan id supplier
            'nmsupp' => $this->nama, // Menyimpan nama supplier
            'telpsupp' => $this->telp, // Menyimpan nomor telepon
            'suppalamat' => $this->alamat, // Menyimpan alamat
        ]);

        session()->flash('message', $this->produkId ? 'Supplier berhasil diperbarui.' : 'Supplier berhasil ditambahkan.');

        $this->closeModal(); // Menutup modal setelah penyimpanan
        $this->resetInputFields(); // Reset input form
    }

    // Fungsi untuk mengedit data supplier
    public function edit($id)
    {
        $produk = MasterSupplier::findOrFail($id);
        $this->produkId = $id;

        // Ambil data akun tanpa menggunakan explode
        $this->nama = $produk->nmsupp;
        $this->telp = $produk->telpsupp;
        $this->alamat = $produk->suppalamat;
        $this->modal = true; // Membuka modal saat mengedit
    }

    // Mengatur ulang pencarian saat perubahan
    public function updatingSearch()
    {
        $this->resetPage();
    }

}
