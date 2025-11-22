<?php

namespace App\Livewire\Produksi;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // ← untuk call Expo API
use App\Models\User;
use App\Models\UserPushToken;
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Notifications\PerintahProduksiUpdated;
use App\Events\PerintahProduksiCreated;
use Illuminate\Support\Facades\Log;

class PerintahProduksi extends Component
{
    public array $produks = [];
    public array $inputs = [];
    public array $readonly = [];
    public string $tanggalProduksi;
    public ?string $keteranganTambahan = null;

    // Modal tambahan
    public bool $showTambahModal = false;
    public int $produkIndex = 0;
    public array $produkTerpilih = [];
    public $jumlahTambahan;
    public $riwayatTambahan = [];
    public $stagedTambahan = []; // draft: array of rows
    public $selectedProductId = null; // id produk dari dropdown
    public $mproducts = [];
    public array $detailTambahan = [];
    public float $sumTongTambahanKe = 0.0;
    public int $sumPcsTambahanKe = 0;
    public ?int $tambahanKe = null;   // pilihan user (boleh null)
    public int $maxTambahanKe = 0;    // tambahan_ke terbesar di tanggal tsb
    public $perintah_produksi_id;

    public function mount()
    {
        // preload daftar produk untuk dropdown
        $this->mproducts = MasterProduct::select('id', 'nama', 'patokan')->orderBy('nama')->get()->toArray();
        $this->tanggalProduksi = now()->format('Y-m-d');
        $this->loadProduks();
    }

    public function updatedTanggalProduksi($value)
    {
        $this->loadProduks();
    }

    public function loadProduks()
    {
        // Cek apakah perintah tanggal ini sudah dikunci (status = 1)
        $locked = Perintah_Produksi::whereDate('tanggal_perintah', $this->tanggalProduksi)
            ->where('status', 1)
            ->exists();

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
        $tambahanMap = Produksi_Tambahan::whereHas('perintahProduksi', function ($q) {
            $q->whereDate('tanggal_perintah', $this->tanggalProduksi);
        })
            ->selectRaw('mproducts_id, SUM(qty_tambahan) as total_tambahan')
            ->groupBy('mproducts_id')
            ->pluck('total_tambahan', 'mproducts_id');

        // Reset state
        $this->produks = [];
        $this->inputs = [];
        $this->readonly = [];

        foreach ($produks as $produk) {
            $pid = $produk->id;

            $utama = (float) ($utamaMap[$pid] ?? 0);
            $tambahan = (float) ($tambahanMap[$pid] ?? 0);
            $total = $utama + $tambahan;

            $this->produks[$pid] = $produk->toArray();
            $this->inputs[$pid] = $total;

            // readonly jika sudah locked
            $this->readonly[$pid] = $locked;
        }
    }

    public function render()
    {
        return view('livewire.produksi.perintah-produksi', [
            'produks' => $this->produks,
        ]);
    }

    private function generateNoPerintahProduksi()
    {
        do {
            $no = now()->format('YmdHis') . rand(10, 99); // tambah 2 digit random supaya unik
        } while (Perintah_Produksi::where('no_perintah_produksi', $no)->exists());

        return $no;
    }

    public function submit()
    {
        $this->validate(['tanggalProduksi' => 'required|date']);

        DB::beginTransaction();
        try {
            // Buat / ambil perintah produksi untuk tanggal aktif
            $perintahproduksi = Perintah_Produksi::firstOrCreate(
                ['tanggal_perintah' => $this->tanggalProduksi],
                [
                    'user_id' => Auth::id(),
                    'no_perintah_produksi' => $this->generateNoPerintahProduksi(),
                ]
            );

            $this->perintah_produksi_id = $perintahproduksi->id;

            // Simpan detail per produk
            foreach ($this->produks as $row) {
                $pid = $row['id'];
                $qty = (float) ($this->inputs[$pid] ?? 0);
                $target = (float) ($row['patokan'] ?? 0) * $qty;

                Detail_Perintah_Produksi::updateOrCreate(
                    [
                        'perintah_produksi_id' => $perintahproduksi->id,
                        'mproducts_id'         => $pid,
                    ],
                    [
                        'produksi_qty'    => $qty,
                        'target_produksi' => $target,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('swal:error', 'Gagal Menyimpan: ' . $e->getMessage());
            return;
        }

        // ======= setelah commit: kirim push =======
        event(new PerintahProduksiCreated($perintahproduksi));

        $tanggalLabel = \Carbon\Carbon::parse($this->tanggalProduksi)->translatedFormat('d M Y');

        // Ambil user target (kalau mau batasi role, aktifkan whereIn di bawah)
        $targetUserIds = \App\Models\User::query()
            ->whereIn('role', ['admin','adminproduksi', 'leaderproduksi'])
            ->where('divisi_id', '!=', 12)   // ← blokir teknisi
            ->whereHas('pushTokens')
            // ->whereKeyNot(Auth::id())           // HAPUS saat test sendiri
            ->pluck('id');

        $tokens = \App\Models\UserPushToken::whereIn('user_id', $targetUserIds)
            ->pluck('expo_token')
            ->unique()
            ->values();

        if ($tokens->isNotEmpty()) {
            try {
                $tokens->chunk(99)->each(function ($chunk) use ($perintahproduksi, $tanggalLabel) {
                    $messages = $chunk->map(fn($t) => [
                        'to'        => $t,
                        'title'     => 'Perintah Produksi Baru',
                        'body'      => "PP #{$perintahproduksi->id} untuk {$tanggalLabel}",
                        'data'      => [
                            'type'  => 'pp_created',
                            'pp_id' => $perintahproduksi->id,
                            'date'  => $tanggalLabel,
                        ],
                        'priority'  => 'high',
                        'channelId' => 'alerts',    // <-- WAJIB untuk Android
                        'ttl'       => 60 * 30,     // <-- jangan 0; simpan 30 menit
                        // 'sound'  => 'default',   // opsional; Android pakai sound dari channel
                    ])->values()->all();

                    $res = \Illuminate\Support\Facades\Http::acceptJson()->asJson()
                        ->post('https://exp.host/--/api/v2/push/send', $messages)
                        ->throw()
                        ->json();



                    // Pruning token yang invalid
                    if (isset($res['data']) && is_array($res['data'])) {
                        foreach ($res['data'] as $i => $ticket) {
                            if (($ticket['status'] ?? '') === 'error') {
                                $err = $ticket['details']['error'] ?? '';
                                if ($err === 'DeviceNotRegistered' || $err === 'MismatchSenderId') {
                                    $badToken = $messages[$i]['to'] ?? null; // index sama dengan request
                                    if ($badToken) {
                                        \App\Models\UserPushToken::where('expo_token', $badToken)->delete();
                                        \Illuminate\Support\Facades\Log::info('expo: pruned token', ['token' => $badToken, 'error' => $err]);
                                    }
                                }
                            }
                        }
                    }


                    // \Log::info('pp_created tokens', ['count' => $tokens->count(), 'sample' => $tokens->take(3)]);
                    \Illuminate\Support\Facades\Log::info('expo push pp_created', ['result' => $res]);
                });

                session()->flash('message', 'Data produksi disimpan & notifikasi dikirim.');
            } catch (\Throwable $ex) {
                \Illuminate\Support\Facades\Log::error('expo push error (pp_created)', ['err' => $ex->getMessage()]);
                session()->flash('message', 'Data produksi disimpan (push gagal dikirim).');
            }
        } else {
            // Tidak ada device terdaftar — aman, data tetap tersimpan
            \Illuminate\Support\Facades\Log::info('push: no tokens for pp_created', ['pp_id' => $perintahproduksi->id]);
            session()->flash('message', 'Data produksi disimpan.');
        }
    }


    public function openTambahModal($index)
    {
        $this->produkIndex = $index;
        $this->produkTerpilih = $this->produks[$index];

        // set dropdown default ke produk yang diklik
        $this->selectedProductId = $this->produkTerpilih['id'] ?? null;
        $this->jumlahTambahan = null;

        // buat/ambil perintah untuk tanggal aktif lalu set ID-nya
        $perintah = Perintah_Produksi::firstOrCreate(
            ['tanggal_perintah' => $this->tanggalProduksi],
            [
                'user_id' => Auth::id(),
                'no_perintah_produksi' => $this->generateNoPerintahProduksi(),
            ],
        );
        $this->perintah_produksi_id = $perintah->id;

        // riwayat tambahan
        $this->riwayatTambahan = Produksi_Tambahan::where('perintah_produksi_id', $perintah->id)
            ->orderByDesc('created_at')
            ->get();

        $this->showTambahModal = true;
        $this->dispatch('focus-input-tambahan');
    }

    protected function rules()
    {
        return [
            'selectedProductId' => ['required', 'integer'],
            'jumlahTambahan' => ['required', 'numeric', 'min:0.01'],
            'keteranganTambahan' => ['nullable', 'string', 'max:255'],
        ];
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
            'mproducts_id' => (int) $product['id'],
            'nama'         => $product['nama'],
            'qty'          => $qty,
            'target_qty'   => $tgt,
            'keterangan'   => $this->keteranganTambahan ?: null,
        ];

        $this->jumlahTambahan = null;
        $this->keteranganTambahan = null;
    }

    public function removeStaged(int $index): void
    {
        if (isset($this->stagedTambahan[$index])) {
            array_splice($this->stagedTambahan, $index, 1);
        }
    }

    public function simpanTambahan(): void
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
        $nextTambahanKe = null;

        DB::beginTransaction();
        try {
            // Kunci parent
            $parent = Perintah_Produksi::whereKey($perintahId)->lockForUpdate()->firstOrFail();

            // Hitung tambahan_ke berikutnya
            $currentMax = (int) DB::table('produksi_tambahan')
                ->where('perintah_produksi_id', $perintahId)
                ->max('tambahan_ke');
            $nextTambahanKe = $currentMax + 1;

            // Susun payload
            $now = now();
            $payload = [];
            foreach ($this->stagedTambahan as $r) {
                $payload[] = [
                    'produksi_tambahan_id' => $now->format('YmdHis') . '-EXTRA',
                    'tambahan_ke'          => $nextTambahanKe,
                    'perintah_produksi_id' => $perintahId,
                    'mproducts_id'         => (int) $r['mproducts_id'],
                    'qty_tambahan'         => (float) $r['qty'],
                    'target_qty_tambahan'  => (float) ($r['target_qty'] ?? 0),
                    'user_id'              => Auth::id(),
                    'keterangan'           => $r['keterangan'] ?? null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            DB::table('produksi_tambahan')->insert($payload);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('swal:error', 'Gagal: ' . $e->getMessage());
            return;
        }

        // === setelah commit: refresh UI + kirim notifikasi ===
        $this->riwayatTambahan = Produksi_Tambahan::with('product:id,nama')
            ->where('perintah_produksi_id', $perintahId)
            ->orderByDesc('id')
            ->get();

        $this->stagedTambahan   = [];
        $this->jumlahTambahan   = null;
        $this->keteranganTambahan = null;

        $this->dispatch('swal:success', "Tambahan ke-{$nextTambahanKe} disimpan");

        // (opsional) notifikasi Laravel bawaan
        $recipients = User::query()
            ->whereHas('pushSubscriptions')
            ->when(Auth::check(), fn($q) => $q->whereKeyNot(Auth::id()))
            ->get();

        foreach ($recipients as $u) {
            $u->notify(new PerintahProduksiUpdated(
                'Perintah Produksi (Giling Tambahan)',
                "Giling Tambahan ke-{$nextTambahanKe} untuk {$tanggalLabel}.",
                url('https://hoks.khasanah-bakery.com/work_order')
            ));
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
                $tokenList->chunk(99)->each(function ($chunk) use ($perintahId, $nextTambahanKe, $tanggalLabel) {
                    $messages = $chunk->map(fn($t) => [
                        'to'        => $t,
                        'title'     => 'Perintah Produksi – Tambahan',
                        'body'      => "Tambahan ke-{$nextTambahanKe} untuk {$tanggalLabel}",
                        'data'      => [
                            'type'      => 'pp_extra',
                            'pp_id'     => $perintahId,
                            'extra_no'  => $nextTambahanKe,
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
}
