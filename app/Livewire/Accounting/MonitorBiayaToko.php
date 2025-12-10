<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\MasterToko;
use App\Livewire\Master\Toko;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use App\Models\Accounting\BudgetBiaya;
use App\Models\Accounting\BudgetBiayaBulanan;

class MonitorBiayaToko extends Component
{
    public $tokoId;
    public $startDate;
    public $endDate;
    public $showBudgetModal = false;

    public $budgetInputs = [];   // [idakun => '1.000.000']
    public $budgetTypes  = [];   // [idakun => 'rupiah' / 'persen']
    public $dailyBudgets = [];   // [idakun => ['senin' => '1.000.000', ...]]
    public $fallbackInfo = [];   // [idakun => 'Pakai budget bulan Desember 2025']

    public $totalPenjualan = 0;

    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\MasterToko[] */
    public $listToko;

    public function mount()
    {
        $this->listToko = MasterToko::whereNull('status')
            ->orderBy('nmtoko')
            ->get();

        $this->tokoId    = $this->listToko->first()->id ?? null;
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate   = now()->toDateString();
    }

    public function syncRealisasiFromApi()
    {
        if (! $this->tokoId) {
            $this->dispatch('swal:error', 'Gagal', 'Silakan pilih toko atau pilih ALL.');
            return;
        }

        // reset total penjualan setiap kali sync
        $this->totalPenjualan = 0;

        // 🔹 MODE ALL TOKO
        if ($this->tokoId === 'all') {
            foreach ($this->listToko as $toko) {
                $this->syncSatuToko($toko);
            }

            $this->dispatch('swal:success', 'Berhasil', 'Realisasi & penjualan semua toko berhasil di-sync dari API.');
            return;
        }

        // 🔹 MODE SATU TOKO
        $toko = MasterToko::findOrFail($this->tokoId);
        $this->syncSatuToko($toko);

        $this->dispatch('swal:success', 'Berhasil', 'Realisasi & penjualan berhasil di-sync dari API.');
    }

    private function syncSatuToko(MasterToko $toko)
    {
        // ==========================
        // 1. SYNC BIAYA DARI API
        // ==========================
        $response = Http::get('https://api.khasanahsari-bakery.com/dw/biaya', [
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'nmcab'     => $toko->api_name,
        ]);

        if (! $response->ok()) {
            // mode ALL: jangan hentikan semua hanya karena 1 cabang error
            return;
        }

        $payload = $response->json();

        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        if (! is_array($payload)) {
            return;
        }

        $data = collect($payload);

        if ($data->isNotEmpty()) {
            $grouped = $data->groupBy('idakun');

            foreach ($grouped as $idakun => $rows) {
                $totalRealisasi = $rows->sum(function ($row) {
                    if (is_array($row)) {
                        $val = $row['totbiaya'] ?? 0;
                    } elseif (is_object($row)) {
                        $val = $row->totbiaya ?? 0;
                    } else {
                        $val = $row;
                    }

                    return $this->toNumber($val);
                });

                $first = $rows->first();
                $tipe  = is_array($first) ? ($first['tipe'] ?? null) : ($first->tipe ?? null);
                $ket   = is_array($first) ? ($first['ket']  ?? null) : ($first->ket  ?? null);

                BudgetBiaya::updateOrCreate(
                    [
                        'toko_id'    => $toko->id,
                        'idakun_api' => $idakun,
                        'start_date' => $this->startDate,
                        'end_date'   => $this->endDate,
                    ],
                    [
                        'tipe_api'  => $tipe,
                        'ket_api'   => $ket,
                        'realisasi' => $totalRealisasi,
                    ]
                );
            }
        }

        // ==========================
        // 2. SYNC PENJUALAN DARI API SUM-PENJUALAN
        // ==========================
        $penjualanResponse = Http::get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'nmcab'     => $toko->api_name,
        ]);

        if (! $penjualanResponse->ok()) {
            return;
        }

        $payloadPenjualan = $penjualanResponse->json();

        if (isset($payloadPenjualan['data']) && is_array($payloadPenjualan['data'])) {
            $payloadPenjualan = $payloadPenjualan['data'];
        }

        if (! is_array($payloadPenjualan)) {
            return;
        }

        $rowsPenjualan = collect($payloadPenjualan);

        // API sum-penjualan mengembalikan data per hari (nhari), cukup jumlahkan hrg
        $totalTokoIni = $rowsPenjualan->sum(function ($row) {
            if (is_array($row)) {
                $val = $row['hrg'] ?? 0;
            } else {
                $val = $row->hrg ?? 0;
            }

            return $this->toNumber($val);
        });

        // akumulasikan ke totalPenjualan (ALL & single mode pakai variabel yang sama)
        $this->totalPenjualan += $totalTokoIni;
    }

    private function toNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = (string) $value;

        // buang semua kecuali angka dan pemisah
        $value = preg_replace('/[^0-9.,]/', '', $value);

        // kasus umum: "1.000.000"
        if (str_contains($value, '.') && ! str_contains($value, ',')) {
            $value = str_replace('.', '', $value); // 1.000.000 → 1000000
        } else {
            // backup kalau format lain
            if (str_contains($value, '.') && str_contains($value, ',')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        }

        return (float) $value;
    }

    /**
     * Ambil budget bulan ini; kalau tidak ada, fallback ke bulan-bulan sebelumnya.
     */
    private function getBudgetWithFallback($tokoId, $idakun, $tahun, $bulan)
    {
        while (true) {
            $found = BudgetBiayaBulanan::where('toko_id', $tokoId)
                ->where('idakun_api', $idakun)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->first();

            if ($found) {
                // simpan info dari bulan mana budget ini berasal
                $found->fallback_from = [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ];

                return $found;
            }

            // mundur 1 bulan
            $bulan--;
            if ($bulan <= 0) {
                $bulan = 12;
                $tahun--;
            }

            // batas keamanan: jangan cari lebih dari 3 tahun ke belakang
            if ($tahun < now()->year - 3) {
                return null;
            }
        }
    }

    public function openBudgetModal()
    {
        if (! $this->tokoId || $this->tokoId === 'all') {
            $this->dispatch('swal:error', 'Gagal', 'Silakan pilih satu toko dulu (bukan ALL).');
            return;
        }

        $start = \Carbon\Carbon::parse($this->startDate);
        $tahun = $start->year;
        $bulan = $start->month;

        $realisasi = BudgetBiaya::where('toko_id', $this->tokoId)
            ->where('start_date', $this->startDate)
            ->where('end_date', $this->endDate)
            ->orderBy('idakun_api')
            ->get();

        $this->budgetInputs = [];
        $this->budgetTypes  = [];
        $this->dailyBudgets = [];
        $this->fallbackInfo = [];

        foreach ($realisasi as $row) {
            // ambil budget bulan ini, fallback ke bulan sebelumnya jika kosong
            $budgetRow = $this->getBudgetWithFallback(
                $this->tokoId,
                $row->idakun_api,
                $tahun,
                $bulan
            );

            if ($budgetRow) {
                $this->budgetInputs[$row->idakun_api] = number_format($budgetRow->budget ?? 0, 0, ',', '.');
                $this->budgetTypes[$row->idakun_api]  = $budgetRow->jenis ?? 'rupiah';

                $this->dailyBudgets[$row->idakun_api] = [
                    'senin'  => $budgetRow->senin  ? number_format($budgetRow->senin, 0, ',', '.')  : '',
                    'selasa' => $budgetRow->selasa ? number_format($budgetRow->selasa, 0, ',', '.') : '',
                    'rabu'   => $budgetRow->rabu   ? number_format($budgetRow->rabu, 0, ',', '.')   : '',
                    'kamis'  => $budgetRow->kamis  ? number_format($budgetRow->kamis, 0, ',', '.')  : '',
                    'jumat'  => $budgetRow->jumat  ? number_format($budgetRow->jumat, 0, ',', '.')  : '',
                    'sabtu'  => $budgetRow->sabtu  ? number_format($budgetRow->sabtu, 0, ',', '.')  : '',
                    'minggu' => $budgetRow->minggu ? number_format($budgetRow->minggu, 0, ',', '.') : '',
                ];

                // indikator fallback: kalau sumber bulan ≠ bulan sekarang
                $fb = $budgetRow->fallback_from ?? null;
                if ($fb && ($fb['tahun'] != $tahun || $fb['bulan'] != $bulan)) {
                    $this->fallbackInfo[$row->idakun_api] =
                        'Pakai budget bulan ' .
                        \Carbon\Carbon::createFromDate($fb['tahun'], $fb['bulan'], 1)
                            ->translatedFormat('F Y');
                } else {
                    $this->fallbackInfo[$row->idakun_api] = null;
                }
            } else {
                // belum ada budget sama sekali, kosongkan
                $this->budgetInputs[$row->idakun_api] = '';
                $this->budgetTypes[$row->idakun_api]  = 'rupiah';
                $this->dailyBudgets[$row->idakun_api] = [
                    'senin'  => '',
                    'selasa' => '',
                    'rabu'   => '',
                    'kamis'  => '',
                    'jumat'  => '',
                    'sabtu'  => '',
                    'minggu' => '',
                ];
                $this->fallbackInfo[$row->idakun_api] = null;
            }
        }

        $this->showBudgetModal = true;
    }

    public function saveBudgets()
    {
        if (! $this->tokoId || $this->tokoId === 'all') {
            $this->dispatch('swal:error', 'Gagal', 'Silakan pilih satu toko dulu (bukan ALL).');
            return;
        }

        $start = \Carbon\Carbon::parse($this->startDate);
        $tahun = $start->year;
        $bulan = $start->month;

        foreach ($this->budgetInputs as $idakun => $value) {
            $numeric = $this->toNumber($value); // default per hari atau persen
            $jenis   = $this->budgetTypes[$idakun] ?? 'rupiah';

            // daily budgets, kalau tidak diisi anggap 0
            $harian = $this->dailyBudgets[$idakun] ?? [];

            $senin  = $this->toNumber($harian['senin']  ?? 0);
            $selasa = $this->toNumber($harian['selasa'] ?? 0);
            $rabu   = $this->toNumber($harian['rabu']   ?? 0);
            $kamis  = $this->toNumber($harian['kamis']  ?? 0);
            $jumat  = $this->toNumber($harian['jumat']  ?? 0);
            $sabtu  = $this->toNumber($harian['sabtu']  ?? 0);
            $minggu = $this->toNumber($harian['minggu'] ?? 0);

            // kalau semua kosong dan default 0, skip
            if (
                $numeric <= 0 &&
                $senin <= 0 && $selasa <= 0 && $rabu <= 0 &&
                $kamis <= 0 && $jumat <= 0 && $sabtu <= 0 && $minggu <= 0
            ) {
                continue;
            }

            BudgetBiayaBulanan::updateOrCreate(
                [
                    'toko_id'    => $this->tokoId,
                    'idakun_api' => $idakun,
                    'tahun'      => $tahun,
                    'bulan'      => $bulan,
                ],
                [
                    'budget' => $numeric, // default per hari (atau persen)
                    'jenis'  => $jenis,
                    'senin'  => $senin,
                    'selasa' => $selasa,
                    'rabu'   => $rabu,
                    'kamis'  => $kamis,
                    'jumat'  => $jumat,
                    'sabtu'  => $sabtu,
                    'minggu' => $minggu,
                ]
            );
        }

        $this->showBudgetModal = false;

        $this->dispatch('swal:success', 'Berhasil', 'Budget bulanan berhasil disimpan.');
    }

    public function render()
    {
        $items          = collect();
        $totalBudget    = 0;
        $totalRealisasi = 0;

        if ($this->tokoId) {

            // 1. Ambil realisasi dari BudgetBiaya
            if ($this->tokoId === 'all') {
                // ALL TOKO: ambil semua toko di periode ini
                $items = BudgetBiaya::where('start_date', $this->startDate)
                    ->where('end_date', $this->endDate)
                    ->orderBy('idakun_api')
                    ->get();
            } else {
                // SATU TOKO
                $items = BudgetBiaya::where('toko_id', $this->tokoId)
                    ->where('start_date', $this->startDate)
                    ->where('end_date', $this->endDate)
                    ->orderBy('idakun_api')
                    ->get();
            }

            // 2. Periode & bulan
            $start = \Carbon\Carbon::parse($this->startDate);
            $end   = \Carbon\Carbon::parse($this->endDate);
            $tahun = $start->year;
            $bulan = $start->month;

            $totalPenjualan = $this->totalPenjualan; // sudah diisi di syncRealisasiFromApi

            // 3. Hitung budget per baris (per toko + per akun) dengan fallback
            $items = $items->map(function ($row) use ($start, $end, $tahun, $bulan, $totalPenjualan) {

                // ambil budget bulan ini atau fallback dari bulan sebelumnya
                $budgetRow = $this->getBudgetWithFallback(
                    $row->toko_id,
                    $row->idakun_api,
                    $tahun,
                    $bulan
                );

                $row->budget       = 0;
                $row->budget_type  = $budgetRow->jenis ?? 'rupiah';
                $row->budget_daily = $budgetRow->budget ?? 0;

                if (! $budgetRow) {
                    return $row;
                }

                $jenis = $budgetRow->jenis ?? 'rupiah';

                // 🔹 CASE 1: PERSEN → % dari total penjualan (toko ini atau ALL, tergantung yang sudah diset)
                if ($jenis === 'persen') {
                    // contoh: budget = 10 → 10% dari totalPenjualan
                    $row->budget = ($budgetRow->budget / 100) * $totalPenjualan;
                    return $row;
                }

                // 🔹 CASE 2: RUPIAH → per hari (Sen–Min) + fallback ke budget per hari
                $mapHari = [
                    1 => $budgetRow->senin  ?? 0,
                    2 => $budgetRow->selasa ?? 0,
                    3 => $budgetRow->rabu   ?? 0,
                    4 => $budgetRow->kamis  ?? 0,
                    5 => $budgetRow->jumat  ?? 0,
                    6 => $budgetRow->sabtu  ?? 0,
                    7 => $budgetRow->minggu ?? 0,
                ];

                $cursor      = $start->copy();
                $totalBudget = 0;

                while ($cursor->lte($end)) {
                    $dow = $cursor->dayOfWeekIso; // 1=Senin ... 7=Minggu

                    $nominalHarian = $mapHari[$dow] ?? 0;

                    // kalau per-hari kosong, pakai budget sebagai flat per hari
                    if ($nominalHarian <= 0 && $budgetRow->budget > 0) {
                        $nominalHarian = $budgetRow->budget;
                    }

                    $totalBudget += $nominalHarian;
                    $cursor->addDay();
                }

                $row->budget = $totalBudget;

                return $row;
            });

            // 4. Kalau ALL, group by idakun_api dan sum budget + realisasi
            if ($this->tokoId === 'all') {
                $items = $items->groupBy('idakun_api')->map(function ($group) {
                    /** @var \App\Models\Accounting\BudgetBiaya $first */
                    $first = $group->first();

                    // objek gabungan per akun
                    $merged = (object) [
                        'idakun_api' => $first->idakun_api,
                        'tipe_api'   => $first->tipe_api,
                        'ket_api'    => $first->ket_api,
                        'budget'     => $group->sum('budget'),
                        'realisasi'  => $group->sum('realisasi'),
                    ];

                    return $merged;
                })->values();
            }

            // 5. Total
            $totalBudget    = $items->sum('budget');
            $totalRealisasi = $items->sum('realisasi');
        }

        return view('livewire.accounting.monitor-biaya-toko', [
            'items'          => $items,
            'totalBudget'    => $totalBudget,
            'totalRealisasi' => $totalRealisasi,
            'totalPenjualan' => $this->totalPenjualan,
        ]);
    }

}
