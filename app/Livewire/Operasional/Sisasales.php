<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\TargetKontribusi; // sesuaikan namespace model kamu
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Http;

class Sisasales extends Component
{
    /** daftar toko sesuai user */
    public $tokosUser = [];        // array of tokos (id, nmtoko, api_name, api_id)
    public $tokosUserNames = '';   // string tampil

    /** tab target */
    public $tanggalAwal;
    public $tanggalAkhir;
    public $rowsTarget = [];       // hasil agregasi per hari (nhari)
    public $sumNetoTarget = 0;

    /** tab bulan lalu */
    public $bulanLaluAwal;
    public $bulanLaluAkhir;
    public $rowsBulanLalu = [];
    public $sumNetoBulanLalu = 0;
    public $totalGas = 0;
    public $totalTelur = 0;
    public $totalLoss = 0;
    protected float $hppRatio = 0.56;
    public function mount()
    {

        $user = Auth::user();

        $tokos = MasterToko::query()
            ->forUser($user)
            ->orderBy('nmtoko')
            ->get(['id', 'nmtoko', 'api_name', 'api_id']);

        $this->tokosUser = $tokos->toArray();
        $this->tokosUserNames = $tokos->pluck('nmtoko')->implode(', ');

        // default bulan lalu (opsional)
        $this->bulanLaluAwal  = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $this->bulanLaluAkhir = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
    }
    private function getTargetKontribusi(string $kode): ?array
{
    $row = TargetKontribusi::query()
        ->where('kode', $kode)
        ->where('aktif', 1)
        ->first();

    if (!$row) return null;

    return [
        'tipe'  => strtoupper((string) $row->tipe),   // PERSEN / RUPIAH
        'nilai' => (float) $row->nilai,              // contoh 1.0 (artinya 1%)
    ];
}
private function fetchBiaya(string $apiName, string $start, string $end): array
{
    $json = Http::timeout(20)
        ->retry(2, 300)
        ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
            'startDate' => $start,
            'endDate'   => $end,
            'nmcab'     => $apiName,
        ])->json();

    return $json['data'] ?? [];
}

private function sumBiayaByTipe(array $rows, string $tipe): int
{
    $tipe = strtoupper(trim($tipe));

    return (int) collect($rows)
        ->filter(fn($r) => strtoupper(trim((string)($r['tipe'] ?? ''))) === $tipe)
        ->sum(fn($r) => (int) ($r['totbiaya'] ?? 0));
}
private function hitungTargetRp($targetDisc, int $nilaiPenjualan): int
{
    if (!$targetDisc) return 0;

    $tipe  = strtoupper((string) $targetDisc->tipe);
    $nilai = (float) ($targetDisc->nilai ?? 0);

    if ($tipe === 'PERSEN') {
        return (int) round($nilaiPenjualan * ($nilai / 100));
    }

    // RUPIAH
    return (int) round($nilai);
}

    private function proyeksiMap(string $start, string $end): array
    {
        $start = Carbon::parse($start)->toDateString();
        $end   = Carbon::parse($end)->toDateString();

        $q = MasterProyeksiKontribusi::query();

        // ✅ kalau 1 tanggal, kunci 1 tanggal saja
        if ($start === $end) {
            $q->whereDate('tanggal', $start);
        } else {
            $q->whereDate('tanggal', '>=', $start)
              ->whereDate('tanggal', '<=', $end);
        }

        return $q->selectRaw('toko_id, SUM(rupiah) as total_rp')
            ->groupBy('toko_id')
            ->pluck('total_rp', 'toko_id')
            ->map(fn($v) => (int) $v)
            ->all();
    }

    private function fetchPenjualan(string $apiId, string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
                'startDate' => $start,
                'endDate'   => $end,
                'idcabang'  => $apiId,   // ✅ pakai ID
            ])->json();

        return $json['data'] ?? [];
    }

    /** gabung data semua toko -> grup per nhari */
    private function aggregateByHari(array $allRows): array
    {
        $grouped = collect($allRows)
            ->groupBy(fn($r) => $r['nhari'] ?? null)
            ->filter(fn($v, $k) => !empty($k));

        return $grouped->map(function ($items, $nhari) {
            $neto = $items->sum(fn($r) => (int)($r['neto'] ?? 0));
            $tgl  = Carbon::createFromFormat('Ymd', $nhari);

            return [
                'nhari'  => $nhari,
                'hari'   => $tgl->locale('id')->translatedFormat('D'),
                'tanggal' => $tgl->format('d-m-Y'),
                'neto'   => $neto,

                // kalau nanti butuh:
                'hpp'    => $items->sum(fn($r) => (int)($r['hpp'] ?? 0)),
                'hrg'    => $items->sum(fn($r) => (int)($r['hrg'] ?? 0)),
                'disc'   => $items->sum(fn($r) => (int)($r['disc'] ?? 0)),
                'jmltrx' => $items->sum(fn($r) => (int)($r['jmltrx'] ?? 0)),
            ];
        })->sortBy('nhari')->values()->all();
    }

    // public function loadTarget()
    // {
    //     $this->validate([
    //         'tanggalAwal'  => 'required|date',
    //         'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
    //     ]);

    //     $rows  = [];
    //     $grand = 0; // ✅ INI YANG KURANG

    //     foreach ($this->tokosUser as $t) {

    //         $apiId  = trim((string)($t['api_id'] ?? ''));
    //         $outlet = $t['nmtoko'] ?? '-';

    //         $neto = 0;
    //         $kontribusi = 0;
    //         $potongan = 0;

    //         if ($apiId !== '') {
    //             $data = $this->fetchPenjualan(
    //                 $apiId,
    //                 $this->tanggalAwal,
    //                 $this->tanggalAkhir
    //             );

    //             // NETO PER TOKO
    //             $neto = collect($data)
    //                 ->where('idcabang', $apiId)     // ✅ kunci per toko
    //                 ->sum(fn($r) => (int)($r['neto'] ?? 0));

    //             // POTONG HPP (65%)
    //             $potongan = (int) round($neto * $this->hppRatio);

    //             // KONTRIBUSI
    //             $kontribusi = $neto - $potongan;
    //         }

    //         // ✅ TOTAL SEMUA TOKO
    //         $grand += $kontribusi;

    //         $rows[] = [
    //             'outlet'            => $outlet,
    //             'neto'              => $neto,
    //             'potongan_hpp'      => $potongan,
    //             'kontribusi_rp'     => $kontribusi,
    //             'total_kontribusi'  => $kontribusi,

    //             'selisih_persen'    => null,
    //             'selisih_rp'        => null,
    //             'kontribusi_persen' => null,
    //             'sc_manual_persen'  => null,
    //             'sc_manual_rp'      => null,
    //             'retur_persen'      => null,
    //             'retur_rp'          => null,
    //             'gas_rp'            => 0,
    //             'telur_rp'          => 0,
    //             'loss_bahan'        => 0,
    //         ];
    //     }

    //     $this->rowsTarget      = $rows;
    //     $this->sumNetoTarget   = $grand; // sekarang isinya TOTAL KONTRIBUSI
    // }
    public function loadTarget()
    {
        $this->validate([
            'tanggalAwal'  => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
         ]);

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end   = Carbon::parse($this->tanggalAkhir)->toDateString();

        $isSingleDate = $start === $end;
        $nhariSingle  = $isSingleDate ? Carbon::parse($start)->format('Ymd') : null;

        // total proyeksi upload per toko utk periode yg dipilih (range / 1 tanggal otomatis)
        $mapProyeksi = $this->proyeksiMap($start, $end);

        // ✅ master target DISC MANUAL (ambil sekali saja biar hemat query)
        $targetDisc = \App\Models\Operasional\TargetKontribusi::query()
            ->where('aktif', 1)
            ->where('kode', 'DISC_MANUAL')
            ->first();

        $rows  = [];
        $grand = 0;

        foreach ($this->tokosUser as $t) {

            $apiId   = trim((string)($t['api_id'] ?? ''));
            $apiName = trim((string)($t['api_name'] ?? '')); // ✅ untuk dw/biaya
            $outlet  = $t['nmtoko'] ?? '-';
            $tokoId  = (int)($t['id'] ?? 0);

            $neto   = 0;
            $hrgApi = 0; // ✅ basis penjualan untuk selisih target proyeksi & target disc persen

            if ($apiId !== '') {
                $data = $this->fetchPenjualan($apiId, $start, $end);

                $col = collect($data)->where('idcabang', $apiId);

                if ($isSingleDate) {
                    $col = $col->where('nhari', $nhariSingle);
                }

                $neto   = $col->sum(fn($r) => (int)($r['neto'] ?? 0));
                $hrgApi = $col->sum(fn($r) => (int)($r['hrg'] ?? 0));
            }

            // ✅ kontribusi tetap
            $potongan   = (int) round($neto * $this->hppRatio);
            $kontribusi = $neto - $potongan;

            // ✅ proyeksi upload (rupiah)
            $targetRp = (int) ($mapProyeksi[$tokoId] ?? 0);

            // ✅ SELISIH MURNI: API(HRG) - Upload(RUPIAH)
            $selisihRp = $hrgApi - $targetRp;

            $selisihPersen = $targetRp > 0
                ? round(($selisihRp / $targetRp) * 100, 2)
                : null;

            // =========================================================
            // ✅ DISC MANUAL: REALISASI (dw/biaya) vs TARGET (master)
            // =========================================================
            $discRealRp = 0;

            if ($apiName !== '') {
                $biayaRows = $this->fetchBiaya($apiName, $start, $end);

                // kalau single date: filter tglinput (date saja)
                $colBiaya = collect($biayaRows);

                if ($isSingleDate) {
                    $colBiaya = $colBiaya->filter(function ($r) use ($start) {
                        $tgl = (string)($r['tglinput'] ?? '');
                        return $tgl !== '' && substr($tgl, 0, 10) === $start; // 'YYYY-MM-DD'
                    });
                }

                $discRealRp = $colBiaya
                    ->filter(function ($r) {
                        $tipe = strtoupper(trim((string)($r['tipe'] ?? '')));
                        return $tipe === 'DISKON MANUAL';
                    })
                    ->sum(function ($r) {
                        return (int) preg_replace('/[^\d\-]/', '', (string)($r['totbiaya'] ?? 0));
                    });
            }

            // target disc manual: persen => hrgApi * persen, rupiah => nilai
            $discTargetRp = $this->hitungTargetRp($targetDisc, (int)$hrgApi);

            // ✅ selisih disc: TARGET - REAL (biar real kosong tetap muncul target)
            $discSelisihRp = $discTargetRp - $discRealRp;
            // persen tampil hanya kalau master persen
            $discTargetPersen = (strtoupper((string)($targetDisc->tipe ?? '')) === 'PERSEN')
                ? (float) ($targetDisc->nilai ?? 0)
                : null;

            // grand tetap kontribusi (sesuai punyamu)
            $grand += $kontribusi;

            $rows[] = [
                'outlet'            => $outlet,
                'neto'              => $neto,
                'potongan_hpp'      => $potongan,
                'kontribusi_rp'     => $kontribusi,
                'total_kontribusi'  => $kontribusi,

                'selisih_rp'        => $selisihRp,
                'selisih_persen'    => $selisihPersen,

                'kontribusi_persen' => null,

                // ✅ isi DISC MANUAL
                'sc_manual_persen'  => $discTargetPersen,
                'sc_manual_rp'      => $discSelisihRp,

                'retur_persen'      => null,
                'retur_rp'          => null,
                'gas_rp'            => 0,
                'telur_rp'          => 0,
                'loss_bahan'        => 0,

                // debug
                // '_dbg_hrg'          => $hrgApi,
                // '_dbg_target'       => $targetRp,
            ];
        }

        // kontribusi persen sesudah grand ketemu (tetap)
        if ($grand > 0) {
            foreach ($rows as &$r) {
                $r['kontribusi_persen'] = round((($r['kontribusi_rp'] ?? 0) / $grand) * 100, 2);
            }
            unset($r);
        }

        $this->rowsTarget    = $rows;
        $this->sumNetoTarget = $grand;
    }






    public function loadBulanLalu()
    {
        $this->validate([
            'bulanLaluAwal'  => 'required|date',
            'bulanLaluAkhir' => 'required|date|after_or_equal:bulanLaluAwal',
        ]);

        $rows  = [];
        $grand = 0;

        foreach ($this->tokosUser as $t) {
            $apiId  = trim((string)($t['api_id'] ?? ''));
            $outlet = $t['nmtoko'] ?? '-';

            $neto = 0;

            if ($apiId !== '') {
                $data = $this->fetchPenjualan($apiId, $this->bulanLaluAwal, $this->bulanLaluAkhir);

                $neto = collect($data)
                    ->where('idcabang', $apiId)
                    ->sum(fn($r) => (int)($r['neto'] ?? 0));
            }

            // 🔥 PENTING: total dijumlahkan di sini
            $grand += $neto;

            $rows[] = [
                'outlet'            => $outlet,
                'kontribusi_rp'     => $neto,
                'total_kontribusi'  => $neto,
            ];
        }

        $this->rowsBulanLalu    = $rows;
        $this->sumNetoBulanLalu = $grand;
    }


    public function render()
    {
        return view('livewire.operasional.sisasales');
    }
}
