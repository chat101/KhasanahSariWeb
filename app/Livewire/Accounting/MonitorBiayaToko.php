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

    public $budgetInputs = []; // [idakun => '1.000.000']
    public $budgetTypes = []; // [idakun => 'rupiah' / 'persen']
    public $dailyBudgets = []; // [idakun => ['senin' => '1.000.000', ...]]
    public $fallbackInfo = []; // [idakun => 'Pakai budget bulan Desember 2025']
    public $hasSynced = false;
    public $syncedTokoId = null;
    public $syncedStartDate = null;
    public $syncedEndDate = null;
    public $totalPenjualan = 0;
    public $syncedMode = null; // 'single' | 'all'
    public $syncedTotalPenjualan = 0;

    /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\MasterToko[] */
    public $listToko;

    public function mount()
    {
        $this->listToko = MasterToko::where('status', '1')->orderBy('nmtoko')->get();

        $this->tokoId = $this->listToko->first()->id ?? null;
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

  public function syncRealisasiFromApi()
{
    if (!$this->tokoId) {
        $this->dispatch('swal:error', 'Gagal', 'Silakan pilih toko atau pilih ALL.');
        return;
    }

    // reset per sync
    $this->totalPenjualan = 0;

    if ($this->tokoId === 'all') {
        foreach ($this->listToko as $toko) {
            $this->syncSatuToko($toko);
        }

        $this->hasSynced = true;
        $this->syncedMode = 'all';
        $this->syncedTokoId = 'all';
        $this->syncedStartDate = $this->startDate;
        $this->syncedEndDate = $this->endDate;
        $this->syncedTotalPenjualan = $this->totalPenjualan;

        $this->dispatch('swal:success', 'Berhasil', 'Realisasi & penjualan SEMUA toko berhasil di-sync.');
        return;
    }

    // single toko
    $toko = MasterToko::findOrFail($this->tokoId);
    $this->syncSatuToko($toko);

    $this->hasSynced = true;
    $this->syncedMode = 'single';
    $this->syncedTokoId = $this->tokoId;
    $this->syncedStartDate = $this->startDate;
    $this->syncedEndDate = $this->endDate;
    $this->syncedTotalPenjualan = $this->totalPenjualan;

    $this->dispatch('swal:success', 'Berhasil', 'Realisasi & penjualan berhasil di-sync.');
}

    public function updated($name, $value)
    {
        if (in_array($name, ['tokoId', 'startDate', 'endDate'])) {
            $this->hasSynced = false;
            $this->syncedTokoId = null;
            $this->syncedStartDate = null;
            $this->syncedEndDate = null;
            $this->syncedTotalPenjualan = 0;
            $this->totalPenjualan = 0;
        }
    }
    private function extractDeskripsi(?string $ket): ?string
    {
        if (!$ket) {
            return null;
        }

        // ambil sebelum "XpX"
        $parts = explode('XpX', $ket, 2);

        return trim($parts[0] ?? $ket);
    }
    private function makeKey(string $idakun, ?string $deskripsi): string
    {
        return $idakun . '||' . ($deskripsi ?: '-');
    }

    private function splitKey(string $key): array
    {
        $parts = explode('||', $key, 2);
        return [$parts[0] ?? '', $parts[1] ?? '-'];
    }
    private function syncSatuToko(MasterToko $toko)
    {
        // ==========================
        // 1. SYNC BIAYA DARI API
        // ==========================
       $response = Http::get('https://api.khasanahsari-bakery.com/dw/biaya', [
    'startDate' => $this->startDate,
    'endDate'   => $this->endDate,
    'idcab' => $toko->api_id, // atau api_nama caba
]);

        if (!$response->ok()) {
            // mode ALL: jangan hentikan semua hanya karena 1 cabang error
            return;
        }

        $payload = $response->json();

        if (isset($payload['data']) && is_array($payload['data'])) {
            $payload = $payload['data'];
        }

        if (!is_array($payload)) {
            return;
        }

        $data = collect($payload);

        if ($data->isNotEmpty()) {
            $grouped = $data->groupBy(function ($row) {
                $idakun = is_array($row) ? $row['idakun'] ?? '' : $row->idakun ?? '';
                $ketRaw = is_array($row) ? $row['ket'] ?? null : $row->ket ?? null;
                $deskripsi = $this->extractDeskripsi($ketRaw) ?? '-';
                return $idakun . '||' . $deskripsi;
            });

            foreach ($grouped as $key => $rows) {
                [$idakun, $deskripsi] = explode('||', $key, 2);

                $totalRealisasi = $rows->sum(function ($row) {
                    $val = is_array($row) ? $row['totbiaya'] ?? 0 : $row->totbiaya ?? 0;
                    return $this->toNumber($val);
                });

                $first = $rows->first();
                $tipe = is_array($first) ? $first['tipe'] ?? null : $first->tipe ?? null;
                $ketRaw = is_array($first) ? $first['ket'] ?? null : $first->ket ?? null;

                BudgetBiaya::updateOrCreate(
                    [
                        'toko_id' => $toko->id,
                        'idakun_api' => $idakun,
                        'deskripsi' => $deskripsi, // ✅ pembeda baris
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate,
                    ],
                    [
                        'tipe_api' => $tipe,
                        'ket_api' => $ketRaw, // ✅ keterangan asli tetap disimpan
                        'realisasi' => $totalRealisasi,
                    ],
                );
            }
        }

        // ==========================
        // 2. SYNC PENJUALAN DARI API SUM-PENJUALAN
        // ==========================
        $penjualanResponse = Http::get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            // 'idcab' => $toko->api_id,
            'nmcab' => $toko->api_name, // atau api_nama cabang yang dipakai API kamu
        ]);

        if (!$penjualanResponse->ok()) {
            return;
        }

        $payloadPenjualan = $penjualanResponse->json();

        if (isset($payloadPenjualan['data']) && is_array($payloadPenjualan['data'])) {
            $payloadPenjualan = $payloadPenjualan['data'];
        }

        if (!is_array($payloadPenjualan)) {
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
        if (str_contains($value, '.') && !str_contains($value, ',')) {
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
    private function getBudgetWithFallback($tokoId, $idakun, $deskripsi, $tahun, $bulan)
    {
        while (true) {
            $found = BudgetBiayaBulanan::where('toko_id', $tokoId)
                ->where('idakun_api', $idakun)
                ->where('deskripsi', $deskripsi) // ✅
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
        if (!$this->tokoId || $this->tokoId === 'all') {
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
            ->orderBy('ket_api') // ✅ deskripsi
            ->get();

        $this->budgetInputs = [];
        $this->budgetTypes = [];
        $this->dailyBudgets = [];
        $this->fallbackInfo = [];

        foreach ($realisasi as $row) {
            $deskripsi = $row->ket_api ?? '-';
            $key = $this->makeKey($row->idakun_api, $deskripsi);

            $budgetRow = $this->getBudgetWithFallback(
                $this->tokoId,
                $row->idakun_api,
                $deskripsi, // ✅
                $tahun,
                $bulan,
            );

            if ($budgetRow) {
                $this->budgetInputs[$key] = number_format($budgetRow->budget ?? 0, 0, ',', '.');
                $this->budgetTypes[$key] = $budgetRow->jenis ?? 'rupiah';

                $this->dailyBudgets[$key] = [
                    'senin' => $budgetRow->senin ? number_format($budgetRow->senin, 0, ',', '.') : '',
                    'selasa' => $budgetRow->selasa ? number_format($budgetRow->selasa, 0, ',', '.') : '',
                    'rabu' => $budgetRow->rabu ? number_format($budgetRow->rabu, 0, ',', '.') : '',
                    'kamis' => $budgetRow->kamis ? number_format($budgetRow->kamis, 0, ',', '.') : '',
                    'jumat' => $budgetRow->jumat ? number_format($budgetRow->jumat, 0, ',', '.') : '',
                    'sabtu' => $budgetRow->sabtu ? number_format($budgetRow->sabtu, 0, ',', '.') : '',
                    'minggu' => $budgetRow->minggu ? number_format($budgetRow->minggu, 0, ',', '.') : '',
                ];

                $fb = $budgetRow->fallback_from ?? null;
                if ($fb && ($fb['tahun'] != $tahun || $fb['bulan'] != $bulan)) {
                    $this->fallbackInfo[$key] = 'Pakai budget bulan ' . \Carbon\Carbon::createFromDate($fb['tahun'], $fb['bulan'], 1)->translatedFormat('F Y');
                } else {
                    $this->fallbackInfo[$key] = null;
                }
            } else {
                $this->budgetInputs[$key] = '';
                $this->budgetTypes[$key] = 'rupiah';
                $this->dailyBudgets[$key] = [
                    'senin' => '',
                    'selasa' => '',
                    'rabu' => '',
                    'kamis' => '',
                    'jumat' => '',
                    'sabtu' => '',
                    'minggu' => '',
                ];
                $this->fallbackInfo[$key] = null;
            }
        }

        $this->showBudgetModal = true;
    }

    public function saveBudgets()
    {
        if (!$this->tokoId || $this->tokoId === 'all') {
            $this->dispatch('swal:error', 'Gagal', 'Silakan pilih satu toko dulu (bukan ALL).');
            return;
        }

        $start = \Carbon\Carbon::parse($this->startDate);
        $tahun = $start->year;
        $bulan = $start->month;

        foreach ($this->budgetInputs as $key => $value) {
            [$idakun, $deskripsi] = $this->splitKey($key);

            $numeric = $this->toNumber($value);
            $jenis = $this->budgetTypes[$key] ?? 'rupiah';

            $harian = $this->dailyBudgets[$key] ?? [];
            $senin = $this->toNumber($harian['senin'] ?? 0);
            $selasa = $this->toNumber($harian['selasa'] ?? 0);
            $rabu = $this->toNumber($harian['rabu'] ?? 0);
            $kamis = $this->toNumber($harian['kamis'] ?? 0);
            $jumat = $this->toNumber($harian['jumat'] ?? 0);
            $sabtu = $this->toNumber($harian['sabtu'] ?? 0);
            $minggu = $this->toNumber($harian['minggu'] ?? 0);

            if ($numeric <= 0 && $senin <= 0 && $selasa <= 0 && $rabu <= 0 && $kamis <= 0 && $jumat <= 0 && $sabtu <= 0 && $minggu <= 0) {
                continue;
            }

            BudgetBiayaBulanan::updateOrCreate(
                [
                    'toko_id' => $this->tokoId,
                    'idakun_api' => $idakun,
                    'deskripsi' => $deskripsi, // ✅
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'budget' => $numeric,
                    'jenis' => $jenis,
                    'senin' => $senin,
                    'selasa' => $selasa,
                    'rabu' => $rabu,
                    'kamis' => $kamis,
                    'jumat' => $jumat,
                    'sabtu' => $sabtu,
                    'minggu' => $minggu,

                    // optional simpan info tampil
                    'tipe_api' => null, // kalau mau isi, ambil dari $items saat render/open modal
                ],
            );
        }
        $this->showBudgetModal = false;

        $this->dispatch('swal:success', 'Berhasil', 'Budget bulanan berhasil disimpan.');
    }

    public function render()
    {
        $items = collect();
        $totalBudget = 0;
        $totalRealisasi = 0;

       if ($this->hasSynced) {

    if ($this->syncedMode === 'all') {
        $items = BudgetBiaya::where('start_date', $this->syncedStartDate)
            ->where('end_date', $this->syncedEndDate)
            ->orderBy('toko_id')
            ->orderBy('idakun_api')
            ->orderBy('deskripsi')
            ->get();
    } else {
        $items = BudgetBiaya::where('toko_id', $this->syncedTokoId)
            ->where('start_date', $this->syncedStartDate)
            ->where('end_date', $this->syncedEndDate)
            ->orderBy('idakun_api')
            ->orderBy('deskripsi')
            ->get();
    }

            $start = Carbon::parse($this->syncedStartDate);
            $end = Carbon::parse($this->syncedEndDate);
            $tahun = $start->year;
            $bulan = $start->month;

            $totalPenjualan = $this->syncedTotalPenjualan; // ✅ pakai hasil sync, bukan state lain

            $items = $items->map(function ($row) use ($start, $end, $tahun, $bulan, $totalPenjualan) {
                $budgetRow = $this->getBudgetWithFallback($row->toko_id, $row->idakun_api, $row->deskripsi ?? '-', $tahun, $bulan);

                $row->budget = 0;

                if (!$budgetRow) {
                    return $row;
                }

                $jenis = $budgetRow->jenis ?? 'rupiah';

                if ($jenis === 'persen') {
                    $row->budget = ($budgetRow->budget / 100) * $totalPenjualan;
                    return $row;
                }

                $mapHari = [
                    1 => $budgetRow->senin ?? 0,
                    2 => $budgetRow->selasa ?? 0,
                    3 => $budgetRow->rabu ?? 0,
                    4 => $budgetRow->kamis ?? 0,
                    5 => $budgetRow->jumat ?? 0,
                    6 => $budgetRow->sabtu ?? 0,
                    7 => $budgetRow->minggu ?? 0,
                ];

                $cursor = $start->copy();
                $totalBudgetLocal = 0;

                while ($cursor->lte($end)) {
                    $dow = $cursor->dayOfWeekIso;
                    $nominal = $mapHari[$dow] ?? 0;

                    if ($nominal <= 0 && ($budgetRow->budget ?? 0) > 0) {
                        $nominal = $budgetRow->budget;
                    }

                    $totalBudgetLocal += $nominal;
                    $cursor->addDay();
                }

                $row->budget = $totalBudgetLocal;
                return $row;
            });

            $totalBudget = $items->sum('budget');
            $totalRealisasi = $items->sum('realisasi');
        }

        return view('livewire.accounting.monitor-biaya-toko', [
            'items' => $items,
            'totalBudget' => $totalBudget,
            'totalRealisasi' => $totalRealisasi,
            'totalPenjualan' => $this->syncedTotalPenjualan, // tampilkan hasil sync
            'hasSynced' => $this->hasSynced,
        ]);
    }
}
