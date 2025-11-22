<?php

namespace App\Livewire\Produksi;

use App\Models\Produksi\Perintah_Produksi;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Produksi_Pengurangan;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\User;
use App\Models\UserPushToken;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // ← untuk call Expo API
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DaftarPerintahProduksi extends Component
{
    use WithPagination;
    public array $produks = [];
    public $modal = false;
    public $search;
    public $showConfirm = false;
    public $targetId = null;
    protected $paginationTheme = 'tailwind';
    public $showEditModal = false;
    public $selectedPerintahId = null;
    public $mproducts = [];
    public string $tanggalProduksi;
    public int $produkIndex = 0;
    public array $produkTerpilih = [];
    public $selectedProductId = null; // id produk dari dropdown
    public $jumlahTambahan;
    public $perintah_produksi_id;
    public array $stagedTambahan = [];
    public ?string $keteranganTambahan = null;
    public $riwayatPengurangan = [];

    public function mount()
    {
        // preload daftar produk untuk dropdown
        $this->mproducts = MasterProduct::select('id', 'nama', 'patokan')->orderBy('nama')->get()->toArray();
        $this->tanggalProduksi = now()->format('Y-m-d');
        $this->loadProduks();
        // dd(  $this->loadProduks());
    }

    public function openEditModal($id)
    {
        $this->selectedPerintahId = $id;
        $this->perintah_produksi_id = $id; // <— WAJIB: ini yang dicek saat simpan

        $this->resetErrorBag();
        $this->resetValidation();

        // Pakai collection (jangan toArray) biar bisa ->created_at di Blade
        $this->riwayatPengurangan = Produksi_Pengurangan::with('product:id,nama')
            ->where('perintah_produksi_id', $id)
            ->latest()
            ->get();

        // reset input modal
        $this->selectedProductId    = null;
        $this->jumlahTambahan       = null;
        $this->keteranganTambahan   = null;
        $this->stagedTambahan       = [];
        $this->showEditModal = true;
    }

    public function stageTambahan(): void
    {
        $this->validate([
            'selectedProductId' => ['required', 'integer'],
            'jumlahTambahan' => ['required', 'numeric', 'min:0.01'],
            'keteranganTambahan' => ['nullable', 'string', 'max:255'],
        ]);

        $product = collect($this->mproducts)->firstWhere('id', (int) $this->selectedProductId);
        if (!$product) {
            $this->dispatch('swal:error', 'Produk tidak ditemukan.');
            return;
        }

        $qty = (float) $this->jumlahTambahan;
        $tgt = (float) ($product['patokan'] ?? 0) * $qty;

        $this->stagedTambahan[] = [
            'mproducts_id' => (int) $product['id'], // WAJIB: per baris
            'nama' => $product['nama'],
            'qty' => $qty,
            'target_qty' => $tgt,
            'keterangan' => $this->keteranganTambahan ?: null,
        ];

        $this->jumlahTambahan = null;
        $this->keteranganTambahan = null;
    }

    /** Hapus baris draft */
    public function removeStaged(int $index): void
    {
        if (isset($this->stagedTambahan[$index])) {
            array_splice($this->stagedTambahan, $index, 1);
        }
    }

    public function loadProduks()
    {
        // Cek apakah perintah tanggal ini sudah dikunci (status = 1)
        $locked = Perintah_Produksi::whereDate('tanggal_perintah', $this->tanggalProduksi)->where('status', 1)->exists();

        // Ambil semua produk
        $produks = MasterProduct::all();

        // Ambil akumulasi qty utama per produk (pada tanggal yang sama)
        $utamaMap = Detail_Perintah_Produksi::whereHas('perintahProduksi', function ($q) {
            $q->whereDate('tanggal_perintah', $this->tanggalProduksi);
        })
            ->selectRaw('mproducts_id, SUM(produksi_qty) as total_utama')
            ->groupBy('mproducts_id')
            ->pluck('total_utama', 'mproducts_id');

        // Ambil akumulasi qty tambahan per produk (pada tanggal yang sama)
        $tambahanMap = Produksi_Pengurangan::whereHas('perintahProduksi', function ($q) {
            $q->whereDate('tanggal_perintah', $this->tanggalProduksi);
        })
            ->selectRaw('mproducts_id, SUM(qty_pengurangan) as total_pengurangan')
            ->groupBy('mproducts_id')
            ->pluck('total_pengurangan', 'mproducts_id', 'keterangan');

        // Reset state agar bersih
        $this->produks = [];


        foreach ($produks as $produk) {
            $pid = $produk->id;

            $utama = (float) ($utamaMap[$pid] ?? 0);
            $tambahan = (float) ($tambahanMap[$pid] ?? 0);
            $total = $utama + $tambahan;

            // Simpan pakai KEY = product id (bukan index) agar konsisten dengan Blade
            $this->produks[$pid] = $produk->toArray();
        }
    }
    public function simpanPengurangan(): void
    {
        if (empty($this->stagedTambahan)) {
            $this->dispatch('swal:error', 'Draft kosong.');
            return;
        }

        $perintahId = (int) $this->perintah_produksi_id;
        if ($perintahId <= 0) {
            $this->dispatch('swal:error', 'perintah_produksi_id belum di-set.');
            return;
        }
        $tanggalLabel = Carbon::parse($this->tanggalProduksi)->translatedFormat('d M Y');
        DB::beginTransaction();
        try {
            $productIds = array_values(array_unique(array_map(
                fn($r) => (int) $r['mproducts_id'],
                $this->stagedTambahan
            )));

            $nextKe = [];
            foreach ($productIds as $pid) {
                $maxKe = DB::table('produksi_pengurangan')
                    ->where('perintah_produksi_id', $perintahId)
                    ->where('mproducts_id', $pid)
                    ->lockForUpdate()
                    ->max('pengurangan_ke');
                $nextKe[$pid] = (int) ($maxKe ?? 0);
            }

            $now = now();
            $payload = [];
            foreach ($this->stagedTambahan as $r) {
                $pid = (int) $r['mproducts_id'];
                $ke  = ++$nextKe[$pid];

                $payload[] = [
                    // jika perlu unik:
                    'produksi_pengurangan_id' => now()->format('YmdHis') . '-REVS',
                    'pengurangan_ke'             => $ke,
                    'perintah_produksi_id'       => $perintahId,
                    'mproducts_id'               => $pid,
                    'qty_pengurangan'            => (float) $r['qty'],
                    'target_qty_pengurangan'     => (float) $r['target_qty'],
                    'user_id'                    => Auth::id(),
                    'keterangan'                 => $r['keterangan'] ?? null,
                    'created_at'                 => $now,
                    'updated_at'                 => $now,
                ];
            }

            DB::table('produksi_pengurangan')->insert($payload);
            DB::commit();

            // Refresh riwayat (pakai collection)
            if ($this->selectedProductId) {
                $this->riwayatPengurangan = Produksi_Pengurangan::with('product:id,nama')
                    ->where('perintah_produksi_id', $perintahId)
                    // ->where('mproducts_id', (int) $this->selectedProductId)
                    ->orderByDesc('id')
                    ->get();
            } else {
                $this->riwayatPengurangan = [];
            }

            // Bersihkan draft & tutup modal
            $this->stagedTambahan      = [];
            $this->jumlahTambahan      = null;
            $this->keteranganTambahan  = null;
            // $this->showEditModal       = false;

            session()->flash('message', 'Pengurangan produksi tersimpan.');
            $this->dispatch('swal:success', 'Pengurangan disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Gagal simpan produksi_pengurangan', ['error' => $e->getMessage()]);
            $this->dispatch('swal:error', 'Gagal simpan: ' . $e->getMessage());
        }

        // Kirim Expo push (dengan channelId + TTL) — di luar transaksi
        $tokenList = UserPushToken::whereIn(
            'user_id',
            User::whereHas('pushTokens')
                ->where('divisi_id', '!=', 12)   // ← teknisi tidak boleh terima
                ->pluck('id')
        )->pluck('expo_token')
        ->unique()
        ->values();
        if ($tokenList->isNotEmpty()) {
            try {
                $tokenList->chunk(99)->each(function ($chunk) use ($perintahId, $ke, $tanggalLabel) {
                    $messages = $chunk->map(fn($t) => [
                        'to'        => $t,
                        'title'     => 'Perintah Produksi – Pengurangan',
                        'body'      => "Pengurangan ke-{$ke} untuk {$tanggalLabel}",
                        'data'      => [
                            'type'      => 'pp_extra',
                            'pp_id'     => $perintahId,
                            'extra_no'  => $ke,
                            'date'      => $tanggalLabel,
                        ],
                        'priority'  => 'high',
                        'channelId' => 'alerts',
                        'ttl'       => 60 * 30,
                    ])->values()->all();

                    $res = Http::acceptJson()->asJson()
                        ->post('https://exp.host/--/api/v2/push/send', $messages)
                        ->throw()
                        ->json();
                    // $res = Http::acceptJson()->asJson()
                    // ->post('https://exp.host/--/api/v2/push/send', $messages)
                    // ->throw()
                    // ->json();

                    // Pruning token yang invalid
                    if (isset($res['data']) && is_array($res['data'])) {
                        foreach ($res['data'] as $i => $ticket) {
                            if (($ticket['status'] ?? '') === 'error') {
                                $err = $ticket['details']['error'] ?? '';
                                if ($err === 'DeviceNotRegistered' || $err === 'MismatchSenderId') {
                                    $badToken = $messages[$i]['to'] ?? null; // index sama dengan request
                                    if ($badToken) {
                                        \App\Models\UserPushToken::where('expo_token', $badToken)->delete();
                                        Log::info('expo: pruned token', ['token' => $badToken, 'error' => $err]);
                                    }
                                }
                            }
                        }
                    }

                    Log::info('expo push pp_extra', ['result' => $res]);
                });
            } catch (\Throwable $ex) {
                Log::error('expo push error (pp_extra)', ['err' => $ex->getMessage()]);
            }
        }
    }

    public function confirmSelesai(int $id): void
    {
        $this->targetId = $id;
        $this->showConfirm = true;
    }

    public function selesaiProduksi(): void
    {
        if (!$this->targetId) {
            session()->flash('message', 'Perintah tidak valid.');
            $this->showConfirm = false;
            return;
        }

        $perintah = Perintah_Produksi::find($this->targetId);

        if (!$perintah) {
            session()->flash('message', 'Data perintah produksi tidak ditemukan.');
            $this->showConfirm = false;
            return;
        }

        // Toggle: 1 -> 0, 0/NULL -> 1
        $perintah->status = $perintah->status == 1 ? 0 : 1;
        $perintah->save();

        // Reset state modal
        $this->reset(['showConfirm', 'targetId']);

        // Optional: refresh pagination/list (kalau perlu)
        // $this->resetPage();

        session()->flash('message', 'Status produksi berhasil diperbarui.');
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
        // kosongkan input yang ingin direset saat modal dibuka
    }

    public function render()
    {
        return view('livewire.produksi.daftar-perintah-produksi', [
            'perintahProduksi' => Perintah_Produksi::with('user')->latest()->paginate(10),
        ]);
    }
}
