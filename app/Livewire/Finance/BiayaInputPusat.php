<?php

namespace App\Livewire\Finance;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\MasterToko;
use Livewire\Attributes\Url;
use App\Models\Finance\Piutang;
use Illuminate\Support\Facades\DB;
use App\Models\Finance\MasterKontrakan;
use App\Models\Finance\PembayaranPiutang;


class BiayaInputPusat extends Component
{
    // form binding
     // === STATE ===
     public ?string $tglPembayaran = null;

     #[Url(as: 'toko', except: '')]
     public ?int $selectedToko = null;

     #[Url(as: 'kat', except: '')]
     public string $selectedKategori = '';

     public ?int $selectedKontrakanId = null;
     public array $kontrakanOptions = [];

     public ?string $qty = null;          // boleh string (kamu masking di JS)
     public ?float  $piutang = null;      // readonly saat KONTRAKAN (nilai_sewa)
     public ?string $jumlahBayar = null;  // string karena masking; parse saat simpan

     public string $keterangan = '';

     public ?string $metode = null;       // kalau mau isi kolom 'metode' (opsional)
    // data history
    public $riwayatTambahan = [];

    public function mount()
    {
        $this->tglPembayaran = now()->toDateString();
        $this->loadHistory();
    }
    public function updatedSelectedKategori(): void
    {
        if ($this->selectedKategori === 'KONTRAKAN' && $this->selectedToko) {
            $this->loadKontrakanOptions();
        } else {
            $this->reset(['selectedKontrakanId','kontrakanOptions','piutang','jumlahBayar']);
        }
    }

    public function updatedSelectedToko(): void
    {
        if ($this->selectedKategori === 'KONTRAKAN' && $this->selectedToko) {
            $this->loadKontrakanOptions();
        } else {
            $this->reset(['selectedKontrakanId','kontrakanOptions','piutang','jumlahBayar']);
        }
    }
    public function updatedSelectedKontrakanId($id): void
    {
        if (!$id) { $this->piutang = null; return; }

        $k = MasterKontrakan::find($id);
        if ($k) {
            $this->piutang = (float)($k->nilai_sewa ?? 0);
            // $this->keterangan = trim(sprintf(
            //     'Kontrakan %s%s%s%s',
            //     $k->area ? $k->area : '',
            //     $k->jenis ? ' - '.$k->jenis : '',
            //     $k->bank ? ' - '.$k->bank : '',
            //     $k->no_rekening ? ' '.$k->no_rekening : ''
            // ));
        }
    }

    private function loadKontrakanOptions(): void
    {
        $this->selectedKontrakanId = null;
        $this->piutang = null;

        $this->kontrakanOptions = MasterKontrakan::query()
            ->where('toko_id', $this->selectedToko)
            ->orderBy('id')
            ->get()
            ->map(fn($k) => [
                'id'    => $k->id,
                'label' => sprintf(
                    // '%s | %s | %s %s — Sewa: %s',

                    ' %s %s — %s',
                    // $k->area ?? '-',
                    $k->jenis ?? '-',
                    $k->bank ?? '-',
                    $k->no_rekening ?? '',
                    number_format((float)($k->nilai_sewa ?? 0), 0, ',', '.')
                ),
            ])
            ->toArray();
    }

    public function updatedPiutang($value)
    {
        // buang titik/koma agar jadi angka murni
        $clean = str_replace(['.', ','], ['', '.'], $value);

        $this->piutang = is_numeric($clean) ? $clean : null;
    }
    public function submit()
    {
         // Validasi dasar
    $rules = [
        'tglPembayaran'     => 'required|date',
        'selectedToko'      => 'required|exists:tokos,id',
        'selectedKategori'  => 'required|string|in:GAS,KONTRAKAN,TELUR',
        'qty'               => 'nullable|numeric|min:0',
        'keterangan'        => 'nullable|string|max:500',
    ];

    // Per kategori
    if ($this->selectedKategori === 'KONTRAKAN') {
        $rules['jumlahBayar'] = 'required';           // string berformat → diparse
        // $rules['selectedKontrakanId'] = 'required|exists:finance_master_kontrakans,id'; // jika kamu pakai relasi kontrakan
        // 'piutang' sudah diisi otomatis nilai sewa → tidak perlu required di sini
    } else {
        $rules['piutang'] = 'required|numeric|min:0'; // kategori non-kontrakan isi manual
    }

    $this->validate($rules);

    // Parse angka dari input bertitik/koma
    $totalPiutang = (float) ($this->selectedKategori === 'KONTRAKAN'
        ? ($this->piutang ?? 0)                      // sudah numeric dari kontrakan
        : $this->toFloat((string)$this->piutang));   // manual

    $bayar = $this->selectedKategori === 'KONTRAKAN'
        ? $this->toFloat($this->jumlahBayar)
        : 0.0;

    // Validasi bisnis: jumlah bayar tidak boleh > tagihan (khusus kontrakan)
    // if ($this->selectedKategori === 'KONTRAKAN' && $bayar > $totalPiutang) {
    //     $this->addError('jumlahBayar', 'Jumlah bayar tidak boleh melebihi tagihan.');
    //     return;
    // }

    DB::transaction(function () use ($totalPiutang, $bayar) {
        $now = now();
        // 1) Simpan PIUTANG (gambar 2)
        $piutang = Piutang::create([
            'tanggal'       => $this->tglPembayaran,
            'trans_id'       => $now->format('YmdHis') . $this->selectedKategori,
            'toko_id'       => $this->selectedToko,
            'kategori'      => $this->selectedKategori,
            'qty'           => $this->qty ? (int)$this->qty : 0,
            'total_piutang' => $totalPiutang,
            'keterangan'    => $this->keterangan,
        ]);

        // 2) Jika KONTRAKAN → catat PEMBAYARAN (gambar 1)
        if ($this->selectedKategori === 'KONTRAKAN') {
            PembayaranPiutang::create([
                'piutang_id'   => $piutang->id,          // FK ke tabel piutang
                'tgl_bayar'    => $this->tglPembayaran,  // pakai tanggal input
                'jumlah_bayar' => $bayar,                // DECIMAL
                'metode'       => $this->metode,         // optional (nullable)
                'catatan'      => $this->keterangan,     // isi catatan dari keterangan
            ]);
        }
    });

    session()->flash('message', 'Data berhasil disimpan.');

    // Reset ringan: tetap pertahankan toko/kategori biar entry berikutnya cepat
    $this->reset(['qty','piutang','jumlahBayar','keterangan']);
    // kalau ingin reset toko/kategori juga, tambahkan di array reset di atas

    // refresh riwayat dan fokus
    $this->loadHistory();
    $this->dispatch('focus-toko');
    }

    private function loadHistory()
    {
        $this->riwayatTambahan = Piutang::with('toko')
            ->latest('created_at')
            ->take(10)
            ->get();
    }
    private function toFloat(null|string $v): float
    {
        if ($v === null || $v === '') return 0.0;
        $v = str_replace('.', '', $v);   // ribuan
        $v = str_replace(',', '.', $v);  // desimal
        return is_numeric($v) ? (float)$v : 0.0;
    }
    public function render()
    {
        $mtokos = MasterToko::orderBy('nmtoko')->get(['id','nmtoko'])->toArray();
        // $riwayatTambahan — sesuaikan query riwayat kamu
        $riwayatTambahan = collect();
        return view('livewire.finance.biaya-input-pusat', [
            'mtokos' =>  $mtokos,
            'riwayatTambahan' => $riwayatTambahan,
        ]);
    }
}
