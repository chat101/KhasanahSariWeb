<?php

namespace App\Livewire\Master;

// use Log;
use Livewire\Component;
use App\Models\MsBarangHo;
use App\Models\MasterBarang;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Barang extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik
    public $produkId, $nama, $kode, $jenis, $harga, $sat1, $sat2, $keterangan, $kode1, $kode2, $barang_id,$satuan,$gramasi,$idmsbrg;
    public $search = ''; // Make sure this is initialized
    public $mode;

    public $modal = false;

    protected $listeners = ['triggerDelete' => 'delete'];

    // Perbaiki rules validasi
    protected $rules = [
        'kode' => 'required|string|max:255',
        'jenis' => 'required|string|max:255',
        'nama' => 'required|string|max:255',
        'harga' => 'required|numeric',
        'sat1' => 'required|numeric',
        'sat2' => 'required|numeric',
        'keterangan' => 'required|string',
    ];

    // Hapus confirmDelete, karena sudah ada listener triggerDelete
    public function delete($id)
    {
        MasterBarang::findOrFail($id)->delete();
        session()->flash('message', 'Data berhasil dihapus!');
    }

    public function render()
    {
        // Log::info('Search keyword: ' . $this->search);
        $produks = MasterBarang::query();

        if ($this->search) {
            $produks = $produks->where(function ($query) {
                $query->where('nmbarang', 'like', '%' . $this->search . '%')->orWhere('barang_id', 'like', '%' . $this->search . '%');
            });
        }

        $produks = $produks->orderBy('nmbarang', 'asc')->latest()->paginate(10);

        return view('livewire.master.barang', [
            'produks' => $produks,
        ]);
    }

    public function openModal()
    {
        $this->mode = 'editBahan';
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
        $this->kode = '';
        $this->jenis = '';
        $this->sat1 = '';
        $this->sat2 = '';
        $this->harga = '';
        $this->keterangan = '';

        $this->produkId = null;
    }

    public function store()
    {
        $this->validate();

        MasterBarang::updateOrCreate(
            ['id' => $this->produkId],
            [
                'barang_id' => $this->kode,
                'nmbarang' => $this->nama,
                'jenis' => $this->jenis,
                'sat1' => $this->sat1,
                'sat2' => $this->sat2,
                'sat3' => 0,
                'sat4' => 0,
                'sat5' => 0,
                'harga' => $this->harga,
                'keterangan' => $this->keterangan,
            ],
        );

        session()->flash('message', $this->produkId ? 'Produk berhasil diupdate.' : 'Produk berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
        // Reset pagination ke halaman pertama setelah menyimpan
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->mode = 'editBahan';
        $produk = MasterBarang::findOrFail($id);
        $this->produkId = $id;
        $this->kode = $produk->barang_id;
        $this->nama = $produk->nmbarang; // Pastikan sesuai dengan kolom di database
        $this->harga = $produk->harga; // Pastikan sesuai dengan kolom di database
        $this->jenis = $produk->jenis; // Pastikan sesuai dengan kolom di database
        $this->sat1 = $produk->sat1; // Pastikan sesuai dengan kolom di database
        $this->sat2 = $produk->sat2; // Pastikan sesuai dengan kolom di database
        $this->harga = $produk->harga; // Pastikan sesuai dengan kolom di database
        $this->keterangan = $produk->keterangan; // Pastikan sesuai dengan kolom di database
        $this->modal = true;
    }

    public function editHO($id)
    {
        $this->mode = 'editHO';
        $this->produkId = $id;
        $produk = MasterBarang::with('detailbarang')->findOrFail($id);


        // dd($produk);

        // $this->produkId = $id;
        $this->barang_id = $produk->id?? null;
        $this->kode = $produk->barang_id?? null; // Pastikan sesuai dengan kolom di database
        $this->satuan = $produk->detailbarang->sat_barang ?? null;
        $this->gramasi = $produk->detailbarang->gramasi?? null;
        $this->modal = true;
    }
    public function storeHO()
    {
        // $this->validate();

        MsBarangHo::updateOrCreate(
            ['barang_id'=> $this->barang_id],            [
                'sat_barang' => $this->satuan,
                'gramasi' => $this->gramasi,
                'user_id' => Auth::user()->id,
                'created_at' => now(),

            ],
        );

        session()->flash('message', $this->produkId ? 'Produk berhasil diupdate.' : 'Produk berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
        // Reset pagination ke halaman pertama setelah menyimpan
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
