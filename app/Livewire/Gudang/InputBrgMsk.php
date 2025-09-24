<?php

namespace App\Livewire\Gudang;

use Livewire\Component;
use App\Models\MsBarangHo;
use App\Models\Gudang_Masuk;
use App\Models\MasterBarang;
use App\Models\MasterSupplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InputBrgMsk extends Component
{
    // Form fields
    public $tanggal,
        $notrans,
        $no_po,
        $no_faktur,
        $operator_temp,
        $supplier,
        $idbarangdb,
        $idbarang,
        $barangid,
        $nmbarang,
        $qty,
        $kodetrans,
        $satuan,
        $gramasi,
        $rows = [],
        $suppliers,
        $barangError = false,
        $qtyError = false,
        $userName,
        $namabarang,
        $searchResults = [];
    protected $listeners = ['loadLocalRows'];
    public function mount()
    {
        // dd(Auth::user()->name); // debug dulu
        $this->userName = Auth::user()->name;
        $this->notrans = 'SUP' . now('Asia/Jakarta')->format('ymdHis');
        $this->tanggal = now('Asia/Jakarta')->format('Y-m-d');
        $this->suppliers = MasterSupplier::all();
        $this->supplier = ''; // default value
    }

    public function render()
    {
        return view('livewire.gudang.input-brg-msk', [
            'suppliers' => $this->suppliers,

            'barangList' => MasterBarang::all(),
        ]);
    }

    public function updatedNamabarang($value)
    {
        // logger("Ketik: " . $value);

        if (strlen($value) > 0) {
            $this->searchResults = MasterBarang::where('nmbarang', 'like', '%' . $value . '%')
                ->limit(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectBarang($id)
    {
        $barang = MasterBarang::with('detailbarang')->find($id); // Cari berdasarkan id
        // dd($barang);
        if ($barang) {
            $this->idbarang = $barang->barang_id; // field kode barang
            $this->barangid = $barang->id;
            $this->namabarang = $barang->nmbarang;
            $this->satuan = $barang->detailbarang->sat_barang ?? null;
            $this->gramasi = $barang->detailbarang->gramasi ?? null;
        }
        $this->searchResults = [];
        $this->dispatch('fokus-ke-qty');
    }

    public function addRow()
    {
        // Cek apakah idbarang, namabarang, atau qty kosong
        $errors = [];
        $this->barangError = false;
        $this->qtyError = false;

        if (!$this->namabarang) {
            $errors[] = 'Nama Barang';
            $this->barangError = true;
        }

        if (!$this->qty) {
            $errors[] = 'Jumlah';
            $this->qtyError = true;
        }
        if (!$this->satuan) {
            $errors[] = 'Satuan';
            $this->qtyError = true;
        }

        // if (!$this->namabarang) $errors[] = 'Nama Barang';
        // if (!$this->qty) $errors[] = 'Jumlah';

        if (!empty($errors)) {
            $message = 'Kolom ' . implode(' dan ', $errors) . ' harus diisi!';
            $this->dispatch('swal:error', 'Data Tidak Lengkap', $message);
            return;
        }

        $this->rows[] = [
            'id' => $this->barangid,
            'nmbarang' => $this->namabarang,
            'qty' => $this->qty,
            'satuan' => $this->satuan ?? '',
            'gramasi' => $this->gramasi,
        ];

        // Reset field input
        $this->reset(['namabarang', 'qty', 'barangid', 'idbarang', 'satuan', 'gramasi']);

        // Kirim rows ke JS untuk disimpan di localStorage

        $this->dispatch('updateLocalStorage', $this->rows);
    }
    public function loadLocalRows($rows)
    {
        $this->rows = $rows ?? [];
    }

    public function restoreSupplier($params)
    {
        $this->supplier = $params['supplier'] ?? null;
    }

    public function removeRow($index)
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows); // reset ulang indeks biar rapih
        $this->dispatch('updateLocalStorage', $this->rows);
    }
    protected $rules = [
        'tanggal' => 'required|date',
        'no_faktur' => 'required|string',
        'supplier' => 'required|integer',
        'rows.*.qty' => 'required|numeric|min:0.01',
    ];
    public function save()
    {
        // Validasi data
        $this->validate();
        // Cek jika rows kosong
        if (empty($this->rows)) {
            $this->dispatch('swal:error', 'Gagal Menyimpan', 'Daftar barang tidak boleh kosong.');
            return;
        }
        $existingFaktur = Gudang_Masuk::where('no_faktur', $this->no_faktur)->first();
        if ($existingFaktur) {
            $this->addError('no_faktur', 'Nomor Faktur sudah digunakan.');
            $this->dispatch('swal:error', 'Gagal Menyimpan', 'Nomor Faktur sudah ada dalam database.');
            return;
        }

        DB::beginTransaction();
        try {
            $barangMasuk = Gudang_Masuk::create([
                'tanggal' => $this->tanggal,
                'no_po' => $this->no_po,
                'no_faktur' => $this->no_faktur,
                'tanggal' => $this->tanggal,
                'notrans' => $this->notrans,
                'user_id' => Auth::user()->id,
                'status' => '0',
                'supplier_id' => $this->supplier,
            ]);

            if ($barangMasuk->wasRecentlyCreated) {
                foreach ($this->rows as $index => $row) {
                    $barangMasuk->details()->create([
                        'barang_masuk_id' => $barangMasuk->id,
                        'barang_id' => $row['id'],
                        'nmbarang' => $row['nmbarang'],
                        'qty' => $row['qty'],
                        'satuan' => $row['satuan'],
                        'gramasi' => $row['gramasi'],
                        'no_urut' => $index + 1, // tambahkan no urut
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    // Tambahkan proses update/create untuk MsBarangHo
                    MsBarangHo::updateOrCreate(
                        ['barang_id' => $row['id']],
                        [
                            'sat_barang' => $row['satuan'],
                            'gramasi' => $row['gramasi'],
                            'user_id' => Auth::user()->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            }
            DB::commit();
            // session()->flash('message', 'Data barang masuk berhasil disimpan.');
            $this->dispatch('swal:success', 'Berhasil menyimpan');

            $this->rows = [];
            $this->dispatch('clear-localstorage');
            $this->reset(['no_faktur', 'supplier', 'no_po']);
        } catch (\Exception $e) {
            DB::rollBack();
            // logger()->error('Gagal menyimpan data barang masuk: ' . $e->getMessage()); // Log ke storage/logs
            $this->dispatch('swal:error', 'Gagal Menyimpan' . $e->getMessage());
        }

        // Kosongkan rows dan localstorage (trigger js)

        // $this->reset(['no_po', 'no_faktur', 'supplier']);
    }
}
