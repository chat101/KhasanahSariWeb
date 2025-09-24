<?php

namespace App\Livewire\Master;

use App\Models\MasterToko;
use Livewire\WithPagination;

use Livewire\Component;

class Toko extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik
    public $nama, $alamat, $produkId;
    public $modal = false;

    protected $listeners = ['triggerDelete' => 'delete'];

    // Perbaiki rules validasi
    protected $rules = [
        'nama' => 'required|string',
        'alamat' => 'required|string', // pastikan harga adalah angka

    ];

    // Hapus confirmDelete, karena sudah ada listener triggerDelete
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


    public function render()
    {
        return view('livewire.master.toko', [
            'produks' => MasterToko::latest()->paginate(10)
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
        $this->nama = '';

        $this->produkId = null;
    }



    // Simpan atau update data supplier
    public function store()
    {
        $this->validate();

        // Menggunakan updateOrCreate untuk menambahkan atau memperbarui data
        MasterToko::updateOrCreate(['id' => $this->produkId], [
            'nmtoko' => $this->nama,
            'alamat' => $this->alamat,
        ]);

        session()->flash('message', $this->produkId ? 'Toko berhasil diperbarui.' : 'Toko berhasil ditambahkan.');

        $this->closeModal(); // Menutup modal setelah penyimpanan
        $this->resetInputFields(); // Reset input form
    }


    public function edit($id)
    {
        $produk = MasterToko::findOrFail($id);
        $this->produkId = $id;
        $this->nama = $produk->nmtoko; // Pastikan sesuai dengan kolom di database
        $this->alamat = $produk->alamat; // Pastikan sesuai dengan kolom di database
        // Pastikan sesuai dengan kolom di database

        $this->modal = true;
    }
}
