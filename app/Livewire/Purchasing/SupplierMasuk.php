<?php

namespace App\Livewire\Purchasing;

use Livewire\Component;
use App\Models\Purchasing;
use App\Models\Gudang_Masuk;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SupplierMasuk extends Component
{
    use WithPagination;
    public $tanggal;
    public $notrans;
    public $qty = [];
    public $supplier;
    public $data = [];

    public $no_faktur;
    public $no_po;

    public $supplier_id;
    public $showEditModal = false;
    public $selectedId;
    public $search;
    protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik

    public $isClosing = false;

    public $harga = [];
    public $diskon = [];
    public $ppn = [];
    public $total = [];
    public $grandTotal = 0;

    // public function updatedQty($value, $key)
    // {
    //     $this->hitungTotal($key);
    // }
    public function updatedHarga($value, $index)
    {
        $this->hitungTotal($index);
        $this->dispatch('syncDraft', "editTransaksiDraft_{$this->notrans}", $this->harga, $this->diskon, $this->ppn);
    }
    public function updatedDiskon($value, $index)
    {
        $this->hitungTotal($index);
        $this->dispatch('syncDraft', "editTransaksiDraft_{$this->notrans}", $this->harga, $this->diskon, $this->ppn);
    }
    public function updatedPpn($value, $index)
    {
        $this->hitungTotal($index);
        $this->dispatch('syncDraft', "editTransaksiDraft_{$this->notrans}", $this->harga, $this->diskon, $this->ppn);
    }
    // public function hitungTotal($index)
    // {
    //     // $qty   = $this->qty[$index] ?? 0;
    //     $qty = $this->data[$index]['qty'] ?? 0;
    //     $harga = $this->harga[$index] ?? 0;
    //     $diskon = $this->diskon[$index] ?? 0;
    //     $ppn = $this->ppn[$index] ?? 0;

    //     $this->total[$index] = ((float)$qty * (float)$harga - (float)$diskon) + (((float)$qty * (float)$harga - (float)$diskon) * (float)$ppn) / 100;

    //     // Hitung ulang grand total
    //     (float)$this->grandTotal = array_sum($this->total);
    // }
    public function hitungTotal($index)
    {
        // Ambil dan pastikan semua nilai berupa string numerik
        $qty = $this->sanitizeNumber($this->data[$index]['qty'] ?? 0);
        $harga = $this->sanitizeNumber($this->harga[$index] ?? 0);
        $diskon = $this->sanitizeNumber($this->diskon[$index] ?? 0);
        $ppn = $this->sanitizeNumber($this->ppn[$index] ?? 0);

        $totalHarga = bcmul($qty, $harga, 2);
        $subtotal = bcsub($totalHarga, $diskon, 2);
        $ppnPersen = bcdiv($ppn, '100', 4);
        $ppnValue = bcmul($subtotal, $ppnPersen, 2);
        $total = bcadd($subtotal, $ppnValue, 2);

        $this->total[$index] = $total;

        $this->grandTotal = array_reduce(
            $this->total,
            function ($carry, $item) {
                return bcadd($carry, $this->sanitizeNumber($item), 2);
            },
            '0.00',
        );
    }

    private function sanitizeNumber($value): string
    {
        if (is_null($value) || $value === '') {
            return '0.00';
        }

        $value = (string) $value;

        // Format Indonesia → hapus titik ribuan dan ubah koma jadi titik
        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value); // hapus titik ribuan
            $value = str_replace(',', '.', $value); // ubah koma jadi titik
        }

        // Hapus karakter tak valid
        $value = preg_replace('/[^0-9.]/', '', $value);

        // Paksa format desimal 2 digit
        return number_format((float) $value, 2, '.', '');
    }

    public function getGrandTotalProperty()
    {
        return collect($this->data)->keys()->sum(fn($index) => $this->hitungTotal($index));
    }

    public function loadDraftFromLocalStorage()
    {
        $draft = json_decode(request()->draftData, true);

        if (is_array($draft)) {
            foreach ($draft as $i => $item) {
                $this->harga[$i] = $item['harga'] ?? 0;
                $this->diskon[$i] = $item['diskon'] ?? 0;
                $this->ppn[$i] = $item['ppn'] ?? 0;
            }
        }
    }

    public function closeModal()
    {
        $this->isClosing = true;

        // Tunggu animasi selesai sebelum menyembunyikan modal
        $this->dispatch('delay-hide-modal');
    }

    public function openModal()
    {
        $this->showEditModal = true;
    }

    public function render()
    {
        $suppliers = Gudang_Masuk::with('supplier')
            ->where('status', 0) // Eager load relasi 'supplier'
            ->when($this->search, function ($query) {
                $query
                    ->where('no_po', 'like', '%' . $this->search . '%')
                    ->orWhere('no_faktur', 'like', '%' . $this->search . '%')
                    ->orWhereHas('supplier', function ($query) {
                        $query->where('nmsupp', 'like', '%' . $this->search . '%'); // Kondisi untuk nama_supplier
                    });
            })
            ->paginate(25);
        return view('livewire.purchasing.supplier-masuk', [
            'suppliers' => $suppliers,
        ]);
    }

    public function updatedKontrakanFilter()
    {
        $this->resetPage(); // reset ke halaman pertama saat filter berubah
    }

    public function edit($id)
    {
        $this->selectedId = $id;
        $data = Gudang_Masuk::with('supplier')->findOrFail($id);

        $this->tanggal = $data->tanggal;
        $this->notrans = $data->notrans;
        $this->no_po = $data->no_po;
        $this->no_faktur = $data->no_faktur;
        $this->supplier = $data->supplier->nmsupp ?? '';
        // Detail barang masuk Gudang_Masuk ada relasi details di moddel
        $this->data = collect($data->details)
            ->map(function ($item) {
                return [
                    'barang' => [
                        'id' => $item->barang->id ?? null,
                        'nmbarang' => $item->barang->nmbarang ?? '',
                    ],
                    'qty' => $item->qty,
                    'satuan' => $item->satuan,
                    'harga' => $item->harga,
                    'diskon' => $item->diskon,
                    'ppn' => $item->ppn,
                ];
            })
            ->toArray();
        // ✅ Mapping ke property individual
        // foreach ($this->data as $index => $item) {
        //     $this->qty[$index] = $item['qty'];
        //     $this->harga[$index] = $item['harga'];
        //     $this->diskon[$index] = $item['diskon'];
        //     $this->ppn[$index] = $item['ppn'];
        // }
        // PANGGIL DISPATCH DI AKHIR
        $this->dispatch('transaksi-loaded', [
            'notrans' => $this->notrans,
            'no_po' => $this->no_po,
            'no_faktur' => $this->no_faktur,
            'supplier' => $this->supplier,
            'data' => $this->data,
        ]);
        $this->showEditModal = true;
        // ✅ Ini bagian penting buat trigger JS ambil localStorage berdasarkan notrans
    }
    // modal edit masuk supplier
    public function update()
    {
        $this->validate([
            'tanggal' => 'required|date',
            'no_po' => 'required|string',
            'no_faktur' => 'required|string',
            'supplier_id' => 'required|integer',
        ]);
        $data = Gudang_Masuk::findOrFail($this->selectedId);
        $data->update([
            'tanggal' => $this->tanggal,
            'no_po' => $this->no_po,
            'no_faktur' => $this->no_faktur,
            'supplier_id' => $this->supplier_id,
        ]);

        $this->showEditModal = false;
        session()->flash('message', 'Data berhasil diperbarui!');
    }
    public function hitungSemuaTotal()
    {
        foreach ($this->data as $index => $item) {
            $this->hitungTotal($index);
        }
    }
    public function simpan()
    {
        DB::beginTransaction();
        try {
            Log::info('Mulai simpan purchasing...');

            $this->hitungSemuaTotal();
            Log::info('Grand total:', ['grandTotal' => $this->grandTotal]);

            $gudangMasuk = Gudang_Masuk::where('notrans', $this->notrans)->first();
            if (!$gudangMasuk) {
                throw new \Exception('Data gudang tidak ditemukan');
            }

            Log::info('Gudang ditemukan:', ['id' => $gudangMasuk->id]);

            $purchasing = Purchasing::create([
                'gudangmasuk_id' => $gudangMasuk->id,
                'tgl_input' => $this->tanggal,
                'user_id' => Auth::user()->id,
                'grandtotal' => $this->grandTotal ?? '0.00',
                'status_bayar' => '1',
            ]);

            Log::info('Purchasing dibuat:', ['id' => $purchasing->id]);

            Gudang_Masuk::where('id', $gudangMasuk->id)->update(['status' => '1']);
            Log::info('Status gudang diupdate');

            if ($purchasing->wasRecentlyCreated) {
                foreach ($this->data as $index => $item) {
                    Log::info("Menyimpan detail ke-$index", [
                        'barang_id' => $item['barang']['id'] ?? null,
                        'qty' => $item['qty'] ?? null,
                        'harga' => $this->sanitizeNumber($this->harga[$index] ?? 0),
                        'total' => $this->total[$index] ?? null,
                    ]);

                    $purchasing->details()->create([
                        'purchasing_id' => $purchasing->id,
                        'no_urut' => $index + 1,
                        'barang_id' => $item['barang']['id'],
                        'qty' => $item['qty'],
                        'satuan' => $item['satuan'],
                        'harga' => $this->sanitizeNumber($this->harga[$index] ?? 0),
                        'diskon' => $this->sanitizeNumber($this->diskon[$index] ?? 0),
                        'ppn' => $this->sanitizeNumber($this->ppn[$index] ?? 0),
                        'total' => $this->sanitizeNumber($this->total[$index] ?? 0),
                    ]);
                }
                Log::info('Semua detail berhasil disimpan');
            }
            // versi refactoring
            // if ($purchasing->wasRecentlyCreated) {
            //     foreach ($this->data as $index => $item) {
            //         // Ambil dan sanitasi nilai-nilai numerik
            //         $harga  = $this->sanitizeNumber($this->harga[$index] ?? 0);
            //         $diskon = $this->sanitizeNumber($this->diskon[$index] ?? 0);
            //         $ppn    = $this->sanitizeNumber($this->ppn[$index] ?? 0);
            //         $total  = $this->sanitizeNumber($this->total[$index] ?? 0);
            //         $qty    = $this->sanitizeNumber($item['qty'] ?? 0);

            //         // Validasi minimal agar tidak simpan data kosong
            //         if (empty($item['barang']['id']) || $qty <= 0) {
            //             Log::warning("Baris ke-{$index} dilewati karena data tidak lengkap", ['data' => $item]);
            //             continue;
            //         }

            //         $purchasing->details()->create([
            //             'purchasing_id' => $purchasing->id,
            //             'no_urut'       => $index + 1,
            //             'barang_id'     => $item['barang']['id'],
            //             'qty'           => $qty,
            //             'satuan'        => $item['satuan'],
            //             'harga'         => $harga,
            //             'diskon'        => $diskon,
            //             'ppn'           => $ppn,
            //             'total'         => $total,
            //         ]);

            //         Log::info("Detail baris ke-{$index} berhasil disimpan", [
            //             'barang_id' => $item['barang']['id'],
            //             'qty'       => $qty,
            //             'harga'     => $harga,
            //             'total'     => $total,
            //         ]);
            //     }
            // }
            DB::commit();
            Log::info('Transaksi berhasil disimpan');

            $this->dispatch('swal:success', 'Berhasil menyimpan');
            $this->reset();
            $this->dispatch('closeModal');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal simpan purchasing: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}
