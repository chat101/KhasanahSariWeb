<?php

namespace App\Livewire\Hutang;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Purchasing;
use App\Models\MasterSupplier;
use App\Models\Purchasing\PurchasingPayment;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class HutangDagangIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $tanggalAwal;
    public $tanggalAkhir;
    public $supplierId = '';
    public $statusBayar = '0'; // default: belum lunas
    public $search = '';       // cari no faktur / no trans

    // SINGLE PAY
    public $showSinglePay = false;
    public $pay_purchasing_id;
    public $pay_tgl;
    public $pay_jumlah;
    public $pay_metode;
    public $pay_no_bukti;
    public $pay_keterangan;
    public $pay_sisa_sebelum = 0;

    // MULTI PAY
    public $showMultiPay = false;
    public $multi_supplier_id;
    public $multi_tgl;
    public $multi_total_uang;
    public $multi_no_bukti;
    public $multi_items = [];

    // tambahkan:
    public $multi_metode = 'KAS';
    public $multi_keterangan;

    public function mount()
    {
        // default filter: bulan ini
        $this->tanggalAwal  = now()->startOfMonth()->format('Y-m-d');
        $this->tanggalAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingTanggalAwal()
    {
        $this->resetPage();
    }
    public function updatingTanggalAkhir()
    {
        $this->resetPage();
    }
    public function updatingSupplierId()
    {
        $this->resetPage();
    }
    public function updatingStatusBayar()
    {
        $this->resetPage();
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Purchasing::with(['gudang_masuk.supplier'])
            ->when($this->statusBayar !== '', function ($q) {
                $q->where('status_bayar', $this->statusBayar);
            })
            ->when($this->tanggalAwal, function ($q) {
                $q->whereDate('tgl_input', '>=', $this->tanggalAwal);
            })
            ->when($this->tanggalAkhir, function ($q) {
                $q->whereDate('tgl_input', '<=', $this->tanggalAkhir);
            })
            ->when($this->supplierId, function ($q) {
                $q->whereHas('gudang_masuk', function ($gm) {
                    $gm->where('supplier_id', $this->supplierId);
                });
            })
            ->when($this->search, function ($q) {
                $q->whereHas('gudang_masuk', function ($gm) {
                    $gm->where('no_faktur', 'like', '%' . $this->search . '%')
                        ->orWhere('notrans', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('tgl_input', 'desc');

        $hutangs = $query->paginate(15);

        $suppliers = MasterSupplier::orderBy('nmsupp')->get();

        return view('livewire.hutang.hutang-dagang-index', [
            'hutangs'   => $hutangs,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Helper: hitung jatuh tempo & telat (dipakai di blade)
     */
    public function getJatuhTempo($purchasing)
    {
        $gm   = $purchasing->gudang_masuk;
        $supp = $gm?->supplier;

        if (!$gm) return null;

        $tempo = $supp?->tempo_hari ?? 0;

        return Carbon::parse($gm->tanggal)->addDays($tempo);
    }

    public function getHariTelat($purchasing)
    {
        $jatuhTempo = $this->getJatuhTempo($purchasing);

        if (!$jatuhTempo || $purchasing->status_bayar == 1) {
            return 0;
        }

        if (now()->lessThanOrEqualTo($jatuhTempo)) {
            return 0;
        }

        return $jatuhTempo->diffInDays(now());
    }
    public function openSinglePay($purchasingId)
    {
        $p = Purchasing::with('gudang_masuk.supplier', 'payments')->findOrFail($purchasingId);

        $this->pay_purchasing_id = $p->id;
        $this->pay_tgl           = now()->format('Y-m-d');
        $this->pay_sisa_sebelum  = $p->sisa_hutang;
        $this->pay_jumlah        = $p->sisa_hutang; // default lunasi
        $this->pay_metode        = 'KAS';
        $this->pay_no_bukti      = '';
        $this->pay_keterangan    = 'Pembayaran hutang faktur ' . $p->gudang_masuk->no_faktur;

        $this->showSinglePay = true;
    }
    public function closeSinglePay()
    {
        $this->showSinglePay = false;

        $this->reset([
            'pay_purchasing_id',
            'pay_tgl',
            'pay_jumlah',
            'pay_metode',
            'pay_no_bukti',
            'pay_keterangan',
            'pay_sisa_sebelum',
        ]);

        $this->resetErrorBag();
        $this->resetValidation();
    }
    public function updatedPayJumlah()
    {
        if (!$this->pay_purchasing_id) return;

        $p = Purchasing::find($this->pay_purchasing_id);
        if (!$p) return;

        $max = (float) ($p->sisa_hutang ?? 0);
        $val = (float) ($this->pay_jumlah ?? 0);

        if ($max > 0 && $val > $max) {
            $this->pay_jumlah = $max;

            $this->dispatch(
                'pay-over',
                max: number_format($max, 0, ',', '.')
            );
        }
    }
    public function saveSinglePay()
    {
        $this->validate([
            'pay_purchasing_id' => 'required|exists:purchasing,id',
            'pay_tgl'           => 'required|date',
            'pay_jumlah'        => 'required|numeric|min:1',
        ]);

        $p = Purchasing::with('payments')->findOrFail($this->pay_purchasing_id);

        $sisa = $p->sisa_hutang;
        if ($this->pay_jumlah > $sisa) {
            $this->addError(
                'pay_jumlah',
                'Jumlah bayar melebihi sisa hutang (' . number_format($sisa, 0, ',', '.') . ')'
            );

            $this->dispatch('notify-error', message: 'Jumlah bayar (' . number_format($this->pay_jumlah, 0, ',', '.')
                . ') melebihi sisa hutang (' . number_format($sisa, 0, ',', '.') . ').');
            return;
        }

        PurchasingPayment::create([
            'purchasing_id' => $p->id,
            'tanggal_bayar' => $this->pay_tgl,          // ✅ pakai field single pay
            'jumlah_bayar'  => $this->pay_jumlah,       // ✅ langsung dari input
            'metode_bayar'  => $this->pay_metode ?? 'KAS',
            'no_bukti'      => $this->pay_no_bukti,
            'keterangan'    => $this->pay_keterangan ?? 'Pembayaran hutang faktur ' . ($p->gudang_masuk->no_faktur ?? ''),
            'user_id'       => Auth::id(),
        ]);

        $p->refresh();
        if ($p->sisa_hutang <= 0) {
            $p->status_bayar = 1;
            $p->save();
        }

        session()->flash('message', 'Pembayaran hutang faktur berhasil disimpan.');

        $this->closeSinglePay();
        $this->resetPage();
    }
    public function openMultiPay()
    {
        $this->multi_supplier_id = '';
        $this->multi_tgl = now()->format('Y-m-d');
        $this->multi_total_uang = null;
        $this->multi_items = [];

        $this->multi_no_bukti = '';
        $this->multi_metode = 'KAS';
        $this->multi_keterangan = 'Pembayaran kolektif hutang';

        $this->resetErrorBag();
        $this->resetValidation();

        $this->showMultiPay = true;
    }

    // public function updatedMultiSupplierId()
    // {
    //     $this->loadMultiItems();
    // }

    // UBAH: dari protected → public
    public function loadMultiItems()
    {
        if (!$this->multi_supplier_id) {
            $this->multi_items = [];
            $this->multi_total_uang = null;
            return;
        }

        // reset total uang agar user input ulang setelah supplier dipilih
        $this->multi_total_uang = null;

        $purchasings = Purchasing::with('gudang_masuk.supplier')
            ->whereHas('gudang_masuk', function ($q) {
                $q->where('supplier_id', $this->multi_supplier_id);
            })
            ->where(function ($q) {
                $q->where('status_bayar', 0)->orWhereNull('status_bayar');
            })
            ->get()
            ->sortBy(function ($p) {
                $gm   = $p->gudang_masuk;
                $supp = $gm?->supplier;
                $tempoHari = $supp->tempo_hari ?? 0;

                if (!$gm || !$gm->tanggal) return Carbon::parse($p->tgl_input);

                return Carbon::parse($gm->tanggal)->addDays($tempoHari);
            });

        $this->multi_items = $purchasings->map(function ($p) {
            $gm   = $p->gudang_masuk;
            $supp = $gm?->supplier;

            $tempoHari = $supp->tempo_hari ?? 0;
            $jatuhTempo = $gm && $gm->tanggal
                ? Carbon::parse($gm->tanggal)->addDays($tempoHari)
                : Carbon::parse($p->tgl_input);

            return [
                'purchasing_id' => $p->id,
                'tgl_input'     => $p->tgl_input,
                'no_faktur'     => $gm->no_faktur ?? '',
                'notrans'       => $gm->notrans ?? '',
                'sisa'          => $p->sisa_hutang,
                'bayar'         => 0,
                'jatuh_tempo'   => $jatuhTempo->format('Y-m-d'),
            ];
        })->values()->toArray();
    }


    public function saveMultiPay()
    {
        $this->validate([
            'multi_supplier_id' => 'required',
            'multi_tgl'         => 'required|date',
            'multi_total_uang'  => 'required|numeric|min:1',
            'multi_metode'      => 'required|in:KAS,BANK',
        ]);

        if (empty($this->multi_items)) {
            $this->addError('multi_total_uang', 'Tidak ada faktur hutang untuk supplier ini.');
            $this->dispatch('notify-error', message: 'Tidak ada faktur hutang untuk supplier ini.');
            return;
        }

        $totalHutang = (float) $this->getMultiTotalHutang(); // asumsi sudah ada helper ini
        $totalUang   = (float) ($this->multi_total_uang ?? 0);

        // ✅ PENGAMAN: uang > total hutang → set ke max + notif + STOP simpan
        if ($totalHutang > 0 && $totalUang > $totalHutang) {
            // kunci nilainya jadi batas maksimal (biar sama dengan notif)
            $this->multi_total_uang = $totalHutang;

            // (opsional tapi enak) distribusi ulang biar konsisten
            $this->distributeMultiTotal();

            $this->addError('multi_total_uang', 'Total uang melebihi total hutang.');

            $this->dispatch('notify-error', message:
                'Total uang (' . number_format($totalUang, 0, ',', '.') . ') melebihi total hutang (' . number_format($totalHutang, 0, ',', '.') . '). ' .
                'Nilai total uang otomatis disesuaikan ke ' . number_format($totalHutang, 0, ',', '.') . '.'
            );

            return;
        }

        $totalInput = (float) collect($this->multi_items)->sum('bayar');

        if ($totalInput <= 0) {
            $this->addError('multi_total_uang', 'Belum ada nilai bayar per faktur.');
            return;
        }

        if ($totalInput > $totalUang) {
            $this->addError('multi_total_uang', 'Total bayar per faktur melebihi jumlah uang yang diinput.');
            return;
        }

        foreach ($this->multi_items as $item) {
            $bayar = (float) ($item['bayar'] ?? 0);
            if ($bayar <= 0) continue;

            $p = Purchasing::with('payments')->find($item['purchasing_id']);
            if (!$p) continue;

            $sisa = (float) ($p->sisa_hutang ?? 0);
            if ($bayar > $sisa) $bayar = $sisa;

            PurchasingPayment::create([
                'purchasing_id' => $p->id,
                'tanggal_bayar' => $this->multi_tgl,
                'jumlah_bayar'  => $bayar,
                'metode_bayar'  => $this->multi_metode ?? 'KAS',
                'no_bukti'      => $this->multi_no_bukti,
                'keterangan'    => $this->multi_keterangan ?? 'Pembayaran kolektif hutang',
                'user_id'       => Auth::id(),
            ]);

            $p->refresh();
            if ((float)$p->sisa_hutang <= 0) {
                $p->status_bayar = 1;
                $p->save();
            }
        }

        session()->flash('message', 'Pembayaran hutang kolektif berhasil disimpan.');

        $this->showMultiPay = false;
        $this->reset([
            'multi_supplier_id',
            'multi_tgl',
            'multi_total_uang',
            'multi_no_bukti',
            'multi_metode',
            'multi_keterangan',
            'multi_items',
        ]);
        $this->resetPage();
    }

    // public function updated($name, $value)
    // {
    //     if ($name === 'multi_total_uang') {
    //         $this->distributeMultiTotal();
    //     }
    // }

    // public function updatedMultiTotalUang()
    // {
    //     if (empty($this->multi_items)) {
    //         $this->distributeMultiTotal();
    //         return;
    //     }

    //     $totalHutang = collect($this->multi_items)->sum('sisa');

    //     if ($totalHutang > 0 && $this->multi_total_uang > $totalHutang) {
    //         $this->multi_total_uang = $totalHutang;

    //         $this->dispatch('multi-total-over', totalHutang: number_format($totalHutang, 0, ',', '.'));
    //     }

    //     $this->distributeMultiTotal();
    // }

    private function getMultiTotalHutang(): float
    {
        return (float) collect($this->multi_items)->sum('sisa');
    }
    // UBAH dari protected -> public
    public function distributeMultiTotal()
    {
        $total = (float) ($this->multi_total_uang ?? 0);

        if (empty($this->multi_items)) return;

        if ($total <= 0) {
            $this->multi_items = collect($this->multi_items)->map(function ($item) {
                $item['bayar'] = 0;
                return $item;
            })->toArray();
            return;
        }

        $items = $this->multi_items;

        foreach ($items as $i => $item) {
            if ($total <= 0) {
                $items[$i]['bayar'] = 0;
                continue;
            }

            $sisa  = (float) $item['sisa'];
            $bayar = min($sisa, $total);

            $items[$i]['bayar'] = $bayar;
            $total -= $bayar;
        }

        $this->multi_items = $items;
    }


    public function updatedMultiTotalUang()
    {
        // wajib pilih supplier dulu
        if (!$this->multi_supplier_id) {
            $this->multi_total_uang = null;
            $this->addError('multi_total_uang', 'Pilih supplier dulu.');
            return;
        }

        if (empty($this->multi_items)) {
            return;
        }

        $totalHutang = $this->getMultiTotalHutang();
        $totalUang   = (float) $this->multi_total_uang;

        // ⛔ REALTIME PENGAMAN
        if ($totalHutang > 0 && $totalUang > $totalHutang) {
            // koreksi langsung
            $this->multi_total_uang = $totalHutang;

            // notif TANPA menghentikan flow
            $this->dispatch(
                'notify-warning',
                message: 'Total uang melebihi total hutang. Otomatis disesuaikan ke '
                    . number_format($totalHutang, 0, ',', '.')
            );
        }

        // 🔥 langsung distribusi
        $this->distributeMultiTotal();
    }
}
