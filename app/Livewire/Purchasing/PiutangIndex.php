<?php

namespace App\Livewire\Purchasing;

use App\Models\Purchasing;
use App\Models\Purchasing\PurchasingPayment;
use Livewire\WithPagination;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PiutangIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $showBayarModal = false;

    // data yang dipilih untuk dibayar
    public $purchasingId;
    public $nomorTransaksi;
    public $tanggalTransaksi;
    public $grandtotal;
    public $totalBayar;
    public $sisaHutang;

    // form pembayaran
    public $tanggal_bayar;
    public $jumlah_bayar;
    public $metode_bayar = 'Kas Kecil';
    public $keterangan;

    protected $rules = [
        'tanggal_bayar' => 'required|date',
        'jumlah_bayar'  => 'required|numeric|min:1',
        'metode_bayar'  => 'nullable|string|max:50',
        'keterangan'    => 'nullable|string',
    ];

    protected $listeners = [
        'refreshPiutang' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Purchasing::with('payments','gudang_masuk.supplier') // dan supplier jika ada
            ->piutang()
            ->orderBy('tgl_input', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('id', 'like', "%{$this->search}%")
                  ->orWhere('tgl_input', 'like', "%{$this->search}%");
                // tambah filter supplier kalau ada kolomnya
            });
        }

        $piutang = $query->paginate(10);

        $totalPiutang = $piutang->sum(function ($item) {
            return $item->sisa_hutang;
        });

        return view('livewire.purchasing.piutang-index', [
            'piutang' => $piutang,
            'totalPiutang' => $totalPiutang,
        ]);
    }

    public function openBayarModal($id)
    {
        $p = Purchasing::with('payments')->findOrFail($id);

        $this->purchasingId    = $p->id;
        $this->nomorTransaksi  = $p->id;
        $this->tanggalTransaksi = $p->tgl_input;
        $this->grandtotal      = $p->grandtotal;
        $this->totalBayar      = $p->total_bayar;
        $this->sisaHutang      = $p->sisa_hutang;

        // default tanggal_bayar hari ini
        $this->tanggal_bayar = now()->toDateString();
        $this->jumlah_bayar  = $this->sisaHutang; // default: bayar semua

        $this->keterangan     = null;
        $this->showBayarModal = true;
    }

    public function savePayment()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $purchasing = Purchasing::with('payments')->findOrFail($this->purchasingId);

            $sisa = $purchasing->sisa_hutang;
            if ($this->jumlah_bayar > $sisa) {
                $this->addError('jumlah_bayar', 'Nominal melebihi sisa hutang.');
                DB::rollBack();
                return;
            }

            $payment = PurchasingPayment::create([
                'purchasing_id' => $purchasing->id,
                'tanggal_bayar' => $this->tanggal_bayar,
                'jumlah_bayar'  => $this->jumlah_bayar,
                'metode_bayar'  => $this->metode_bayar,
                'keterangan'    => $this->keterangan,
            ]);

            // HITUNG ULANG SISA HUTANG
            $purchasing->refresh(); // load ulang relasi payments
            $sisaBaru = $purchasing->sisa_hutang;

            // update status_bayar jika lunas
            if ($sisaBaru <= 0) {
                $purchasing->status_bayar = 1; // 1 = lunas
                $purchasing->save();
            }

            /**
             * =========================================
             *  AUTO JURNAL KAS (SESUIKAN DENGAN SISTEM ANDA)
             * =========================================
             *
             * Contoh jika Anda punya model JurnalKas:
             *
             * JurnalKas::create([
             *     'tanggal'       => $this->tanggal_bayar,
             *     'tipe'          => 'keluar',
             *     'akun_kas'      => $this->metode_bayar, // Kas Kecil / Kas Bank
             *     'nominal'       => $this->jumlah_bayar,
             *     'keterangan'    => "Bayar hutang purchasing #{$purchasing->id}",
             *     'ref_type'      => 'purchasing_payment',
             *     'ref_id'        => $payment->id,
             * ]);
             */

            DB::commit();

            $this->showBayarModal = false;
            $this->dispatch('notify', type: 'success', message: 'Pembayaran hutang berhasil disimpan.');

            $this->emit('refreshPiutang');
        } catch (\Throwable $e) {
            DB::rollBack();
            // boleh dd($e) saat debug
            $this->dispatch('notify', type: 'success', message: 'Pembayaran hutang berhasil disimpan.');
        }
    }

}
