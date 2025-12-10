<?php

namespace App\Livewire\Purchasing;

use App\Models\User;
use Livewire\Component;
use App\Models\MsBarangHo;
use App\Models\Purchasing;
use App\Models\MasterBarang;
use App\Models\MasterSupplier;
use Illuminate\Support\Facades\DB;
use App\Models\Purchasing_Details;

use Illuminate\Support\Facades\Hash;

class RekapMasuk extends Component
{
    public $search;
    protected $paginationTheme = 'tailwind';

    public $showEditModal = false;
    public $isClosing = false;

    public $selectedId;

    public $tanggal;
    public $notrans;
    public $no_faktur;
    public $no_po;
    public $supplier;

    public $data = [];

    public $harga = [];
    public $diskon = [];
    public $ppn = [];
    public $total = [];
    public $grandTotal = '0.00';

    public $supplier_id;
    public $listSupplier = [];
    public $listBarang = [];
    public $barang_id = [];

    protected $listeners = [
        'verifyFinanceManager',
    ];
    /* ================================
       🔵 OPEN/CLOSE MODAL
    ================================== */

    public function openModal()
    {
        $this->showEditModal = true;
    }

    public function closeModal()
    {
        $this->isClosing = true;
        $this->dispatch('delay-hide-modal');
    }

    public function updatedBarangId($value, $index)
    {
        $barang = MsBarangHo::find($value);

        if ($barang) {
            $this->data[$index]['barang']['id'] = $barang->id;
            $this->data[$index]['barang']['nmbarang'] = $barang->nmbarang;

            // Jika ingin otomatis update satuan barang
            $this->data[$index]['satuan'] = $barang->sat_barang;

            // Jika ingin update harga default
            // $this->harga[$index] = number_format($barang->harga_default ?? 0, 2, ',', '.');

            // Recalculate total
            $this->hitungTotal($index);
        }
    }

    public function verifyFinanceManager($id, $email, $password)
    {
        $manager = User::where('email', $email)
            ->where('role', 'manager_finance') // ganti kalau nama rolenya beda
            ->first();

        if (! $manager) {
            // KIRIM 2 PARAMETER STRING
            $this->dispatch('swal:error',
                'Akses Ditolak',
                'Akun Manager Finance tidak ditemukan.'
            );
            return;
        }

        if (! Hash::check($password, $manager->password)) {
            $this->dispatch('swal:error',
                'Password Salah',
                'Password Manager Finance tidak sesuai.'
            );
            return;
        }

        // Kalau lolos semua → buka form edit
        $this->edit($id);
    }
    /* ================================
       🔵 LOAD DATA EDIT
    ================================== */

    public function edit($id)
    {
        $this->selectedId = $id;

        $data = Purchasing::with('details.barang', 'gudang_masuk.supplier')
            ->findOrFail($id);

        // HEADER
        $this->tanggal   = $data->gudang_masuk->tanggal;
        $this->notrans   = $data->gudang_masuk->notrans;
        $this->no_po     = $data->gudang_masuk->no_po;
        $this->no_faktur = $data->gudang_masuk->no_faktur;
        $this->supplier = $data->gudang_masuk->supplier->nmsupp; // opsional untuk display
        $this->listSupplier = MasterSupplier::orderBy('nmsupp')->get();
        $this->supplier_id = $data->gudang_masuk->supplier_id;
        // DETAIL
        $this->data = collect($data->details)->map(function ($item) {
            return [
                'detail_id' => $item->id,          // ⬅️ penting untuk update
                'no_urut'   => $item->no_urut,     // opsional kalau mau dipakai
                'barang' => [
                    'id'       => $item->barang->id ?? null,
                    'nmbarang' => $item->barang->nmbarang ?? '',
                ],
                'qty'    => $item->qty,
                'satuan' => $item->satuan,
                'harga'  => $item->harga,
                'diskon' => $item->diskon,
                'ppn'    => $item->ppn,
            ];
        })->toArray();
        $this->listBarang = MasterBarang::orderBy('nmbarang')->get();
        // FORMAT DATA UNTUK INPUT
        foreach ($this->data as $i => $item) {

            // Format angka → tampilan Indonesia
            $this->harga[$i]  = number_format($item['harga'], 2, ',', '.');
            $this->diskon[$i] = number_format($item['diskon'], 2, ',', '.');
            $this->ppn[$i]    = number_format($item['ppn'], 2, ',', '.');
            $this->barang_id[$i] = $item['barang']['id'];
            // Total awal
            $this->total[$i] = number_format(
                ($item['qty'] * $item['harga']) - $item['diskon'],
                2,
                '.',
                ''
            );
        }

        // Hitung grand total awal
        $this->hitungGrandTotal();

        $this->showEditModal = true;
    }


    /* ================================
       🔵 SANITASI ANGKA
    ================================== */

    private function sanitizeNumber($value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        $value = trim((string)$value);

        // Hapus ribuan
        $value = str_replace('.', '', $value);

        // Ganti koma → titik
        $value = str_replace(',', '.', $value);

        // Hapus karakter illegal
        $value = preg_replace('/[^0-9.]/', '', $value);

        // Perbaiki jika ada lebih dari satu titik
        $parts = explode('.', $value);
        if (count($parts) > 2) {
            $decimal = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimal;
        }

        return number_format((float)$value, 2, '.', '');
    }


    /* ================================
       🔵 HITUNG TOTAL PER BARIS
    ================================== */

    public function hitungTotal($index)
    {
        $qty    = (float) $this->sanitizeNumber($this->data[$index]['qty'] ?? 0);
        $harga  = (float) $this->sanitizeNumber($this->harga[$index] ?? 0);
        $diskon = (float) $this->sanitizeNumber($this->diskon[$index] ?? 0);
        $ppn    = (float) $this->sanitizeNumber($this->ppn[$index] ?? 0);

        $subtotal    = $qty * $harga;
        $afterDiskon = $subtotal - $diskon;
        $ppnValue    = $ppn > 0 ? $afterDiskon * ($ppn / 100) : 0;

        // SIMPAN ANGKA MURNI (float)
        $this->total[$index] = $afterDiskon + $ppnValue;

        $this->hitungGrandTotal();
    }




    /* ================================
       🔵 HITUNG GRANDTOTAL
    ================================== */

    public function hitungGrandTotal()
    {
        $total = 0;

        foreach ($this->total as $t) {
            $total += (float) $t; // total[] sudah angka murni
        }

        $this->grandTotal = $total;
    }



    /* ================================
       🔵 LIVE UPDATE EVENT
    ================================== */
    public function updatedHarga($value, $index)
    {
        $this->harga[$index] = $value; // biarkan tampil tetap format Indo
        $this->hitungTotal($index);
    }

    public function updatedDiskon($value, $index)
    {
        $this->diskon[$index] = $value;
        $this->hitungTotal($index);
    }

    public function updatedPpn($value, $index)
    {
        $this->ppn[$index] = $value;
        $this->hitungTotal($index);
    }


    public function updatedData($value, $key)
    {
        if (str_contains($key, '.qty')) {
            $index = explode('.', $key)[0];
            $this->hitungTotal($index);
        }
    }


    /* ================================
       🔵 RENDER
    ================================== */

    public function render()
    {
        $suppliers = Purchasing::with('details', 'gudang_masuk.supplier')
            ->when($this->search, function ($query) {
                $query->whereHas('gudang_masuk', function ($gm) {
                    $gm->where('no_po', 'like', "%{$this->search}%")
                        ->orWhere('no_faktur', 'like', "%{$this->search}%")
                        ->orWhereHas('supplier', function ($s) {
                            $s->where('nmsupp', 'like', "%{$this->search}%");
                        });
                });
            })
            ->paginate(25);

        return view('livewire.purchasing.rekap-masuk', [
            'suppliers' => $suppliers,
        ]);
    }
    public function simpan()
{
    if (! $this->selectedId) {
        return;
    }

    DB::transaction(function () {
        // 🔹 1. UPDATE TABEL PURCHASING
        $purchasing = Purchasing::findOrFail($this->selectedId);

        // kalau mau sekalian update tanggal input
        $purchasing->tgl_input   = $this->tanggal;          // sesuaikan format
        $purchasing->grandtotal  = $this->grandTotal;       // sudah float
        // $purchasing->user_id  = auth()->id();            // kalau mau set ulang
        $purchasing->save();

        // 🔹 2. UPDATE TABEL PURCHASING_DETAILS
        foreach ($this->data as $index => $row) {
            if (empty($row['detail_id'])) {
                continue; // jaga-jaga kalau ada baris aneh
            }

            $detail = Purchasing_Details::findOrFail($row['detail_id']);

            $detail->barang_id = $this->barang_id[$index] ?? $row['barang']['id'];
            $detail->qty       = (float) $this->sanitizeNumber($row['qty'] ?? 0);
            $detail->satuan    = $row['satuan'] ?? $detail->satuan;

            $detail->harga  = (float) $this->sanitizeNumber($this->harga[$index]  ?? 0);
            $detail->diskon = (float) $this->sanitizeNumber($this->diskon[$index] ?? 0);
            $detail->ppn    = (float) $this->sanitizeNumber($this->ppn[$index]    ?? 0);
            $detail->total  = (float) ($this->total[$index] ?? 0);

            $detail->save();
        }
    });

    // 🔹 Notif & tutup modal
    $this->dispatch('swal:success',
        'Berhasil',
        'Data transaksi berhasil diperbarui.'
    );

    $this->closeModal();
}

}
