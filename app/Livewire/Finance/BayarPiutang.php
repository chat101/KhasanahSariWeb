<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Finance\Piutang;
use Illuminate\Support\Facades\DB;
use App\Models\Finance\PembayaranPiutang;
use App\Models\MasterToko;

class BayarPiutang extends Component
{
    use WithPagination;

    public $filterTokoId = '';
    public $showPayModal = false;

    public $search = '';  // data
    // detail yang dipilih
    public $selectedPiutangId;
    public $selected;
    public bool $showPay = false;          // ← ganti/ tambahkan ini (bukan showPayModal)
    public $current = null;                // ← objek Piutang terpilih
    public $detailPembayaran = [];

    // Piutang terpilih (array untuk view)
    public $history = [];          // riwayat pembayaran

    // form bayar
    public $tgl_bayar;
    public $jumlah_bayar;
    public $metode;
    public $catatan;

    public bool $showConfirm = false;
    public ?float $enteredNominal = null;   // nominal yang dimasukkan user (float)
    public ?float $sisaNow = null;          // sisa saat ini (float)
    public ?float $selisih = null;          // entered - sisa (bisa plus/minus)
    public string $selisihLabel = '';

    protected $queryString = ['filterTokoId'];

    public function mount()
    {
        $this->tgl_bayar = now()->toDateString();
    }

    public function updatingFilterTokoId()
    {
        $this->resetPage();
    }
    public function updatingSearch()       // <— reset pagination saat ganti kata kunci
    {
        $this->resetPage();
    }
    public function openPay($id)
    {
        $p = Piutang::with('toko')
            ->withSum('pembayaran as total_bayar', 'jumlah_bayar')
            ->findOrFail($id);

        $this->current = $p;                      // <<< pakai current
        $this->selectedPiutangId = $p->id;        // (opsional) fallback

        $this->current->sisa = (float)$p->total_piutang - (float)($p->total_bayar ?? 0);

        $this->detailPembayaran = $p->pembayaran()
            ->latest('tgl_bayar')->get();

        // reset form
        $this->tgl_bayar    = now()->toDateString();
        $this->jumlah_bayar = '';
        $this->metode       = null;
        $this->catatan      = null;

        $this->showPay = true;
        $this->dispatch('focus-jumlah');
    }

    public function savePayment()
    {
        $piutangId = $this->current->id ?? null;

        $this->validate([
            'tgl_bayar'    => 'required|date',
            'jumlah_bayar' => ['required', function ($attr, $value, $fail) {
                $num = $this->parseMoney($value);
                if (!is_numeric($num) || $num <= 0) $fail('Nominal tidak valid.');
            }],
        ]);

        if (!$piutangId) {
            $this->dispatch('toast', message: 'Tidak ada piutang yang dipilih.');
            return;
        }

        $nominal = (float) $this->parseMoney($this->jumlah_bayar);

        // hitung sisa saat ini
        $p = Piutang::withSum('pembayaran as total_bayar', 'jumlah_bayar')->findOrFail($piutangId);
        $sudah = (float) ($p->total_bayar ?? 0);
        $sisa  = (float) $p->total_piutang - $sudah;

        // Simpan data untuk modal konfirmasi
      // simpan angka untuk modal konfirmasi
$this->enteredNominal = $nominal;
$this->sisaNow        = $sisa;
$this->selisih        = $nominal - $sisa; // >0: lebih, <0: kurang, =0: pas
$this->selisihLabel   = $this->selisih > 0 ? 'lebih' : ($this->selisih < 0 ? 'kurang' : 'pas');

// SELALU minta konfirmasi (pas/kurang/lebih)
$this->showConfirm = true;

        // Jika LEBIH atau KURANG: munculkan konfirmasi
        $this->showConfirm = true;
    }

    public function confirmOverpay(bool $proceed) // pakai nama lama agar Blade kamu tidak perlu diubah
    {
        $this->showConfirm = false;

        if (!$proceed) {
            // batal
            return;
        }

        $piutangId = $this->current->id ?? null;
        if (!$piutangId || $this->enteredNominal === null || $this->sisaNow === null || $this->selisih === null) return;

        // Simpan full nominal yang diinput user (boleh lebih/kurang)
        $this->persistPayment($piutangId, (float) $this->enteredNominal, (float) $this->selisih);
    }

    private function persistPayment(int $piutangId, float $nominal, float $selisih = 0.0)
    {
        DB::transaction(function () use ($piutangId, $nominal, $selisih) {
            $p = Piutang::lockForUpdate()
                ->withSum('pembayaran as total_bayar', 'jumlah_bayar')
                ->findOrFail($piutangId);

            // Siapkan catatan tambahan
            $noteExtra = null;
            if ($selisih > 0) {
                $noteExtra = 'Kelebihan bayar: ' . number_format($selisih, 0);
            } elseif ($selisih < 0) {
                $noteExtra = 'Kurang bayar: ' . number_format(abs($selisih), 0);
            }

            PembayaranPiutang::create([
                'piutang_id'   => $p->id,
                'tgl_bayar'    => $this->tgl_bayar,
                'jumlah_bayar' => $nominal,  // simpan sesuai input user
                'metode'       => $this->metode,
                'catatan'      => $noteExtra
                    ? trim(($this->catatan ? ($this->catatan . ' | ') : '') . $noteExtra)
                    : $this->catatan,
            ]);
        });

        // Refresh detail & tabel
        $this->openPay($piutangId);

        // Toast adaptif
        $msg = 'Pembayaran tersimpan.';
        if ($selisih > 0) {
            $msg .= ' Kelebihan: ' . number_format($selisih, 0);
        } elseif ($selisih < 0) {
            $msg .= ' Kurang: ' . number_format(abs($selisih), 0);
        }
        $this->dispatch('toast', message: $msg);

        // Reset input & state konfirmasi, lalu arahkan fokus
        $this->jumlah_bayar = '';
        $this->catatan      = '';
        $this->enteredNominal = $this->sisaNow = $this->selisih = null;
        $this->selisihLabel = '';
        $this->dispatch('focus-catatan'); // kalau mau fokus ke jumlah, ganti dengan event lain
    }

    private function parseMoney($value)
    {
        $v = trim((string) $value);
        $v = str_replace(' ', '', $v);
        if ($v === '') return null;

        if (str_contains($v, ',')) {    // Format ID: 5.000.000,50
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } else {                        // Format EN / angka polos
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '', $v);
        }
        return (float) $v;
    }


    public function render()
    {
        $query = Piutang::with('toko')
            ->withSum('pembayaran as total_bayar', 'jumlah_bayar')
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        if ($this->filterTokoId) {
            $query->where('toko_id', $this->filterTokoId);
        }

        // sisa = total_piutang - total_bayar != 0 (pakai WHERE subquery, aman untuk paginate)
        $query->whereRaw("
            COALESCE(piutang.total_piutang, 0)
            - COALESCE(
                (SELECT SUM(pp.jumlah_bayar)
                   FROM pembayaran_piutang pp
                  WHERE pp.piutang_id = piutang.id),
                0
            ) <> 0
        ");

        $rows = $query->paginate(12);

        return view('livewire.finance.bayar-piutang', [
            'rows' => $rows,
            'tokos' => MasterToko::orderBy('nmtoko')->get(['id', 'nmtoko']),
            'current' => $this->current,
            'detailPembayaran' => $this->detailPembayaran,
        ]);
    }
}
