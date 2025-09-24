<?php

namespace App\Livewire\Gudang;

use App\Models\Gudang_Masuk;
use App\Models\MasterSupplier;
use Livewire\Component;
use Livewire\WithPagination;

class RekapInputGudang extends Component
{
    use WithPagination;
    public $mode;
    public $notrans;
    public $tanggal;
    public $userName;
    public $no_po;
    public $no_faktur;
    public $supplier;
    public $supplier_id;
    public $suppid = [];
    public $search = '';
    public $rows = [];

    public $modal = false;
    protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik
    public function mount()
    {
        $this->suppid = MasterSupplier::all();
    }
    public function render()
    {
        return view('livewire.gudang.rekap-input-gudang', [
            'gudangMasuks' => Gudang_Masuk::where('status', 0)->paginate(15),
        ]);
    }

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

    public function edit($id)
    {
        $dataMasuk = Gudang_Masuk::findOrFail($id);
        $this->supplier_id = $dataMasuk->supplier_id;
        $this->tanggal = $dataMasuk->tanggal;
        $this->notrans = $dataMasuk->notrans;
        $this->userName = $dataMasuk->user->name;
        $this->no_po = $dataMasuk->no_po;
        $this->no_faktur = $dataMasuk->no_faktur;
        // Data detail (multi-barang)
        $this->rows = [];
        foreach ($dataMasuk->details as $detail) {
            $this->rows[] = [
                'id' => $detail->barang_id,
                'nmbarang' => $detail->barang->nmbarang ?? '',
                'qty' => $detail->qty,
                'satuan' => $detail->satuan,
                'gramasi' => $detail->gramasi,
            ];
        }

        $this->modal = true;
    }
    public function delete($id)
    {
        $gudangMasuk = Gudang_Masuk::with('details')->findOrFail($id);

        // Hapus semua detail terlebih dahulu
        $gudangMasuk->details()->delete();

        // Hapus data induknya
        $gudangMasuk->delete();

        session()->flash('message', 'Data berhasil dihapus!');
    }
    //     public function save()
    // {
    //     $dataMasuk = Gudang_Masuk::find($this->gudangMasukId); // pastikan ID disimpan
    //     $dataMasuk->update([
    //         'tanggal' => $this->tanggal,
    //         // ... lainnya
    //     ]);

    //     // Ambil semua detail lama
    //     $existingDetailIds = $dataMasuk->details()->pluck('id')->toArray();

    //     // Data baru dari form
    //     $newDetailIds = collect($this->rows)->pluck('detail_id')->filter()->toArray();

    //     // Hapus yang tidak ada di data baru
    //     $toDelete = array_diff($existingDetailIds, $newDetailIds);
    //     Gudang_Masuk_Detail::destroy($toDelete);

    //     // Simpan/update data detail baru
    //     foreach ($this->rows as $row) {
    //         Gudang_Masuk_Detail::updateOrCreate(
    //             ['id' => $row['detail_id'] ?? null],
    //             [
    //                 'gudang_masuk_id' => $dataMasuk->id,
    //                 'barang_id' => $row['id'],
    //                 'qty' => $row['qty'],
    //                 'satuan' => $row['satuan'],
    //                 'gramasi' => $row['gramasi'],
    //             ]
    //         );
    //     }

    //     $this->resetForm(); // custom function untuk clear input
    //     $this->emit('saved');
    // }
}
