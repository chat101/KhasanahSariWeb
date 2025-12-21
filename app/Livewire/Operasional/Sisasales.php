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
    public $tokosUser = []; // array of tokos (id, nmtoko, api_name, api_id)
    public $tokosUserNames = ''; // string tampil

    /** tab target */
    public $tanggalAwal;
    public $tanggalAkhir;
    public $rowsTarget = []; // hasil agregasi per hari (nhari)
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

        // $tokos = MasterToko::query()
        //     ->forUser($user)
        //     ->orderBy('nmtoko')
        //     ->get(['id', 'nmtoko', 'api_name', 'api_id']);
        $tokos = MasterToko::query()
            ->forUser($user)
            ->orderBy('nmtoko')
            ->get(['id', 'nmtoko', 'api_name', 'api_id', 'produksi_sendiri']); // ✅ tambah ini
        $this->tokosUser = $tokos->toArray();
        $this->tokosUserNames = $tokos->pluck('nmtoko')->implode(', ');

        // default bulan lalu (opsional)
        $this->bulanLaluAwal = now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $this->bulanLaluAkhir = now()->subMonthNoOverflow()->endOfMonth()->toDateString();
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
    private function sumBiayaByExtracted($rows, string $needle): int
    {
        $needle = strtoupper(trim($needle));

        return (int) collect($rows)
            ->filter(function ($r) use ($needle) {
                $ket = (string) ($r['ket'] ?? '');
                $desc = strtoupper(trim((string) $this->extractDeskripsi($ket)));

                return $desc === $needle; // GAS / TELUR
            })
            ->sum(fn($r) => (int) preg_replace('/[^\d\-]/', '', (string) ($r['totbiaya'] ?? 0)));
    }
    private function nilaiTargetByRule(?TargetKontribusi $t, bool $isProduksiSendiri): float
    {
        if (!$t) {
            return 0;
        }

        $pakaiRule = (int) ($t->pakai_rule_produksi ?? 0) === 1;

        if (!$pakaiRule) {
            return (float) ($t->nilai ?? 0);
        }

        // ✅ pakai rule produksi
        $v = $isProduksiSendiri ? $t->nilai_produksi_sendiri ?? null : $t->nilai_non_produksi_sendiri ?? null;

        // fallback kalau kolom rule belum diisi
        if ($v === null) {
            $v = $t->nilai ?? 0;
        }

        return (float) $v;
    }
    private function getTargetKontribusi(string $kode): ?array
    {
        $row = TargetKontribusi::query()->where('kode', $kode)->where('aktif', 1)->first();

        if (!$row) {
            return null;
        }

        return [
            'tipe' => strtoupper((string) $row->tipe), // PERSEN / RUPIAH
            'nilai' => (float) $row->nilai, // contoh 1.0 (artinya 1%)
        ];
    }
    private function fetchBiaya(string $apiName, string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
                'startDate' => $start,
                'endDate' => $end,
                'nmcab' => $apiName,
            ])
            ->json();

        return $json['data'] ?? [];
    }

    private function sumBiayaByTipe(array $rows, string $tipe): int
    {
        $tipe = strtoupper(trim($tipe));

        return (int) collect($rows)->filter(fn($r) => strtoupper(trim((string) ($r['tipe'] ?? ''))) === $tipe)->sum(fn($r) => (int) ($r['totbiaya'] ?? 0));
    }
    private function hitungTargetRp($targetDisc, int $nilaiPenjualan): int
    {
        if (!$targetDisc) {
            return 0;
        }

        $tipe = strtoupper((string) $targetDisc->tipe);
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
        $end = Carbon::parse($end)->toDateString();

        $q = MasterProyeksiKontribusi::query();

        // ✅ kalau 1 tanggal, kunci 1 tanggal saja
        if ($start === $end) {
            $q->whereDate('tanggal', $start);
        } else {
            $q->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end);
        }

        return $q->selectRaw('toko_id, SUM(rupiah) as total_rp')->groupBy('toko_id')->pluck('total_rp', 'toko_id')->map(fn($v) => (int) $v)->all();
    }

    private function fetchPenjualan(string $apiId, string $start, string $end): array
    {
        $json = Http::timeout(20)
            ->retry(2, 300)
            ->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
                'startDate' => $start,
                'endDate' => $end,
                'idcabang' => $apiId, // ✅ pakai ID
            ])
            ->json();

        return $json['data'] ?? [];
    }

    /** gabung data semua toko -> grup per nhari */
    private function aggregateByHari(array $allRows): array
    {
        $grouped = collect($allRows)->groupBy(fn($r) => $r['nhari'] ?? null)->filter(fn($v, $k) => !empty($k));

        return $grouped
            ->map(function ($items, $nhari) {
                $neto = $items->sum(fn($r) => (int) ($r['neto'] ?? 0));
                $tgl = Carbon::createFromFormat('Ymd', $nhari);

                return [
                    'nhari' => $nhari,
                    'hari' => $tgl->locale('id')->translatedFormat('D'),
                    'tanggal' => $tgl->format('d-m-Y'),
                    'neto' => $neto,

                    // kalau nanti butuh:
                    'hpp' => $items->sum(fn($r) => (int) ($r['hpp'] ?? 0)),
                    'hrg' => $items->sum(fn($r) => (int) ($r['hrg'] ?? 0)),
                    'disc' => $items->sum(fn($r) => (int) ($r['disc'] ?? 0)),
                    'jmltrx' => $items->sum(fn($r) => (int) ($r['jmltrx'] ?? 0)),
                ];
            })
            ->sortBy('nhari')
            ->values()
            ->all();
    }

    // public function loadTarget()
    // {
    //     $this->validate([
    //         'tanggalAwal' => 'required|date',
    //         'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
    //     ]);

    //     $start = Carbon::parse($this->tanggalAwal)->toDateString();
    //     $end = Carbon::parse($this->tanggalAkhir)->toDateString();

    //     $isSingleDate = $start === $end;
    //     $nhariSingle = $isSingleDate ? Carbon::parse($start)->format('Ymd') : null;

    //     // total proyeksi upload per toko utk periode yg dipilih (range / 1 tanggal otomatis)
    //     $mapProyeksi = $this->proyeksiMap($start, $end);

    //     // ✅ master target DISC MANUAL (ambil sekali saja biar hemat query)
    //     $targets = TargetKontribusi::query()
    //         ->where('aktif', 1)
    //         ->whereIn('kode', ['DISC_MANUAL', 'GAS', 'TELUR'])
    //         ->get()
    //         ->keyBy('kode');

    //     $targetDisc = TargetKontribusi::where('aktif', 1)->where('kode', 'DISC_MANUAL')->first();
    //     $targetGas = TargetKontribusi::where('aktif', 1)->where('kode', 'GAS')->first();
    //     $targetTelur = TargetKontribusi::where('aktif', 1)->where('kode', 'TELUR')->first();
    //     $rows = [];
    //     $grand = 0;

    //     foreach ($this->tokosUser as $t) {
    //         $isProduksi = ((int) ($t['produksi_sendiri'] ?? 0)) === 1;
    //         $apiId = trim((string) ($t['api_id'] ?? ''));
    //         $apiName = trim((string) ($t['api_name'] ?? '')); // ✅ untuk dw/biaya
    //         $outlet = $t['nmtoko'] ?? '-';
    //         $tokoId = (int) ($t['id'] ?? 0);

    //         $neto = 0;
    //         $hrgApi = 0; // ✅ basis penjualan untuk selisih target proyeksi & target disc persen
    //         $biayaRows = [];
    //         $colBiaya = collect([]);

    //         if ($apiName !== '') {
    //             $biayaRows = $this->fetchBiaya($apiName, $start, $end);
    //             $colBiaya = collect($biayaRows);

    //             if ($isSingleDate) {
    //                 $colBiaya = $colBiaya->filter(function ($r) use ($start) {
    //                     $tgl = (string) ($r['tglinput'] ?? '');
    //                     return $tgl !== '' && substr($tgl, 0, 10) === $start; // YYYY-MM-DD
    //                 });
    //             }
    //         }
    //         if ($apiId !== '') {
    //             $data = $this->fetchPenjualan($apiId, $start, $end);

    //             $col = collect($data)->where('idcabang', $apiId);

    //             if ($isSingleDate) {
    //                 $col = $col->where('nhari', $nhariSingle);
    //             }

    //             $neto = $col->sum(fn($r) => (int) ($r['neto'] ?? 0));
    //             $hrgApi = $col->sum(fn($r) => (int) ($r['hrg'] ?? 0));
    //         }

    //         // ✅ kontribusi tetap
    //         // $potongan   = (int) round($neto * $this->hppRatio);
    //         // $kontribusi = $neto - $potongan;

    //         // ✅ proyeksi upload (rupiah)
    //         $targetRp = (int) ($mapProyeksi[$tokoId] ?? 0);

    //         // ✅ SELISIH MURNI: API(HRG) - Upload(RUPIAH)
    //         // ✅ SELISIH MURNI: API(HRG) - Upload(RUPIAH)
    //         $selisihRp = $hrgApi - $targetRp;
    //         $kontribusi = (int) round($selisihRp * (1 - $this->hppRatio));

    //         $selisihPersen = $targetRp > 0 ? round(($selisihRp / $targetRp) * 100, 2) : null;

    //         // =========================================================
    //         // ✅ DISC MANUAL (sementara): tampilkan TARGET saja
    //         // % dari DB, Rp = % x nilai penjualan (hrgApi)
    //         // =========================================================
    //         $discRealRp = 0;
    //         $discTargetPersen = null; // angka dari DB kalau tipe=PERSEN
    //         $discTargetRp = 0;
    //         $discSelisihRp = 0;
    //         $discSelisihPersen = null;

    //         // 1) REAL dari API dw/biaya (tipe = DISKON MANUAL)
    //         $discRealRp = (int) $colBiaya->filter(fn($r) => strtoupper(trim((string) ($r['tipe'] ?? ''))) === 'DISKON MANUAL')->sum(fn($r) => (int) preg_replace('/[^\d\-]/', '', (string) ($r['totbiaya'] ?? 0)));

    //         // 2) TARGET dari DB
    //         if ($targetDisc) {
    //             $tipe = strtoupper((string) ($targetDisc->tipe ?? ''));
    //             $nilai = (float) ($targetDisc->nilai ?? 0);

    //             if ($tipe === 'PERSEN') {
    //                 $discTargetPersen = $nilai; // contoh 1.00
    //                 $discTargetRp = (int) round(((float) $hrgApi) * ($nilai / 100));
    //             } else {
    //                 // RUPIAH
    //                 $discTargetPersen = null;
    //                 $discTargetRp = (int) round($nilai);
    //             }
    //         }

    //         // 3) SELISIH (target - real)
    //         $discSelisihRp = (int) $discTargetRp - (int) $discRealRp;

    //         // 4) persen selisih (selisihRp / penjualan)
    //         $discSelisihPersen = $hrgApi > 0 ? round(($discSelisihRp / $hrgApi) * 100, 2) : null;
    //         $sumBiayaTipe = function ($label) use ($colBiaya) {
    //             $label = strtoupper(trim($label));

    //             return (int) $colBiaya
    //                 ->filter(function ($r) use ($label) {
    //                     $tipe = strtoupper(trim((string) ($r['tipe'] ?? '')));
    //                     return $tipe === $label;
    //                 })
    //                 ->sum(function ($r) {
    //                     return (int) preg_replace('/[^\d\-]/', '', (string) ($r['totbiaya'] ?? 0));
    //                 });
    //         };

    //         // =========================================================
    //         // ✅ GAS: Rp = TARGET - REAL, % = (Rp / Penjualan) * 100
    //         // =========================================================
    //         $gasTargetRp = 0;
    //         $gasRealRp = 0;
    //         $gasSelisihRp = 0;
    //         $gasSelisihP = null;

    //         if ($targetGas) {
    //             $tipeGas = strtoupper((string) ($targetGas->tipe ?? ''));
    //             $nilaiGas = $this->nilaiTargetByRule($targetGas, $isProduksi);

    //             if ($tipeGas === 'PERSEN') {
    //                 $gasTargetRp = (int) round(((float) $hrgApi) * ($nilaiGas / 100));
    //             } else {
    //                 // RUPIAH
    //                 $gasTargetRp = (int) round($nilaiGas);
    //             }
    //         }

    //         // REAL dari API (dw/biaya) - sesuaikan label tipe jika beda
    //         $gasRealRp = $this->sumBiayaByExtracted($colBiaya, 'GAS');

    //         // Selisih
    //         $gasSelisihRp = (int) $gasTargetRp - (int) $gasRealRp;

    //         // Persen selisih terhadap penjualan
    //         $gasSelisihP = $hrgApi > 0 ? round(($gasSelisihRp / $hrgApi) * 100, 2) : null;
    //         // =========================================================
    //         // ✅ TELUR: Rp = TARGET - REAL, % = (Rp / Penjualan) * 100
    //         // =========================================================
    //         $telurTargetRp = 0;
    //         $telurRealRp = 0;
    //         $telurSelisihRp = 0;
    //         $telurSelisihP = null;

    //         if ($targetTelur) {
    //             $tipeTelur = strtoupper((string) ($targetTelur->tipe ?? ''));
    //             $nilaiTelur = $this->nilaiTargetByRule($targetTelur, $isProduksi);

    //             if ($tipeTelur === 'PERSEN') {
    //                 $telurTargetRp = (int) round(((float) $hrgApi) * ($nilaiTelur / 100));
    //             } else {
    //                 // RUPIAH
    //                 $telurTargetRp = (int) round($nilaiTelur);
    //             }
    //         }

    //         // REAL dari API (dw/biaya) - sesuaikan label tipe jika beda
    //         $telurRealRp = $this->sumBiayaByExtracted($colBiaya, 'TELUR');

    //         // Selisih
    //         $telurSelisihRp = (int) $telurTargetRp - (int) $telurRealRp;

    //         // Persen selisih terhadap penjualan
    //         $telurSelisihP = $hrgApi > 0 ? round(($telurSelisihRp / $hrgApi) * 100, 2) : null;
    //         // grand tetap kontribusi (sesuai punyamu)
    //         $grand += $kontribusi;
    //         // $netproyeksi += $kontribusi;

    //         // =========================================================
    //         // ✅ TOTAL KONTRIBUSI = SUM semua kolom Rp + loss bahan
    //         // =========================================================
    //         $totalKontribusi = (int) ($kontribusi ?? 0) + (int) ($discSelisihRp ?? 0) + (int) ($returRp ?? 0) + (int) ($gasRp ?? 0) + (int) ($telurRp ?? 0) + (int) ($lossBahan ?? 0);

    //         $rows[] = [
    //             'outlet' => $outlet,
    //             'neto' => $neto,
    //             // 'potongan_hpp'      => $potongan,
    //             // 'kontribusi_rp'     => $kontribusi,
    //             // 'total_kontribusi'  => $kontribusi,
    //             // kontribusi sekarang dari SELISIH
    //             'kontribusi_rp' => $kontribusi,
    //             'total_kontribusi' => $totalKontribusi,

    //             'selisih_rp' => $selisihRp,
    //             'selisih_persen' => $selisihPersen,

    //             'kontribusi_persen' => null,

    //             // ✅ isi DISC MANUAL
    //             // ✅ isi DISC MANUAL
    //             'sc_manual_persen' => $discSelisihPersen, // persen SELISIH
    //             'sc_manual_rp' => $discSelisihRp, // Rp SELISIH (target - real)

    //             'retur_persen' => null,
    //             'retur_rp' => null,
    //             'gas_rp' => 0,
    //             'telur_rp' => 0,
    //             'loss_bahan' => 0,
    //             'gas_persen' => $gasSelisihP,
    //             'gas_rp' => $gasSelisihRp,

    //             'telur_persen' => $telurSelisihP,
    //             'telur_rp' => $telurSelisihRp,
    //             // debug
    //             // '_dbg_hrg'          => $hrgApi,
    //             // '_dbg_target'       => $targetRp,
    //         ];
    //     }

    //     // kontribusi persen sesudah grand ketemu (tetap)
    //     if ($grand > 0) {
    //         foreach ($rows as &$r) {
    //             $r['kontribusi_persen'] = round((($r['kontribusi_rp'] ?? 0) / $grand) * 100, 2);
    //         }
    //         unset($r);
    //     }

    //     $this->rowsTarget = $rows;
    //     $this->sumNetoTarget = $grand;
    // }
    // fungsi loadTarget yang sudah jadi helper kecil

    private function onlyTanggalJikaSingle($col, bool $isSingleDate, string $start)
    {
        if (!$isSingleDate) {
            return $col;
        }

        return $col->filter(function ($r) use ($start) {
            $tgl = (string) ($r['tglinput'] ?? '');
            return $tgl !== '' && substr($tgl, 0, 10) === $start; // YYYY-MM-DD
        });
    }

    private function parseIntMoney($v): int
    {
        return (int) preg_replace('/[^\d\-]/', '', (string) ($v ?? 0));
    }

    /** hitung target Rp dari row target (bisa persen/rupiah + rule produksi) */
    private function targetToRp(?TargetKontribusi $t, bool $isProduksi, int $penjualanHrg): int
    {
        if (!$t) {
            return 0;
        }

        $tipe = strtoupper((string) ($t->tipe ?? ''));
        $nilai = $this->nilaiTargetByRule($t, $isProduksi);

        return $tipe === 'PERSEN' ? (int) round(((float) $penjualanHrg) * ($nilai / 100)) : (int) round($nilai);
    }

    /** selisih: target-real, persen selisih: (selisih/penjualan)*100 */
    private function selisihTargetReal(int $targetRp, int $realRp, int $penjualanHrg): array
    {
        $selisihRp = $targetRp - $realRp;
        $selisihP = $penjualanHrg > 0 ? round(($selisihRp / $penjualanHrg) * 100, 2) : null;

        return [$selisihRp, $selisihP];
    }

    public function loadTarget()
    {
        $this->validate([
            'tanggalAwal' => 'required|date',
            'tanggalAkhir' => 'required|date|after_or_equal:tanggalAwal',
        ]);

        $start = Carbon::parse($this->tanggalAwal)->toDateString();
        $end = Carbon::parse($this->tanggalAkhir)->toDateString();

        $isSingleDate = $start === $end;
        $nhariSingle = $isSingleDate ? Carbon::parse($start)->format('Ymd') : null;

        $mapProyeksi = $this->proyeksiMap($start, $end);

        // ambil target sekali saja
        $targets = TargetKontribusi::query()
            ->where('aktif', 1)
            ->whereIn('kode', ['DISC_MANUAL', 'GAS', 'TELUR'])
            ->get()
            ->keyBy('kode');

        $targetDisc = $targets->get('DISC_MANUAL');
        $targetGas = $targets->get('GAS');
        $targetTelur = $targets->get('TELUR');

        $rows = [];
        $grand = 0;

        foreach ($this->tokosUser as $t) {
            $isProduksi = ((int) ($t['produksi_sendiri'] ?? 0)) === 1;

            $apiId = trim((string) ($t['api_id'] ?? ''));
            $apiName = trim((string) ($t['api_name'] ?? ''));
            $outlet = $t['nmtoko'] ?? '-';
            $tokoId = (int) ($t['id'] ?? 0);

            // --------------------------
            // 1) Penjualan (neto/hrg)
            // --------------------------
            $neto = 0;
            $hrgApi = 0;

            if ($apiId !== '') {
                $data = $this->fetchPenjualan($apiId, $start, $end);

                $col = collect($data)->where('idcabang', $apiId);
                if ($isSingleDate) {
                    $col = $col->where('nhari', $nhariSingle);
                }

                $neto = $col->sum(fn($r) => (int) ($r['neto'] ?? 0));
                $hrgApi = $col->sum(fn($r) => (int) ($r['hrg'] ?? 0));
            }

            // --------------------------
            // 2) Biaya (dw/biaya)
            // --------------------------
            $colBiaya = collect([]);
            if ($apiName !== '') {
                $biayaRows = $this->fetchBiaya($apiName, $start, $end);
                $colBiaya = $this->onlyTanggalJikaSingle(collect($biayaRows), $isSingleDate, $start);
            }

            // --------------------------
            // 3) Target proyeksi & kontribusi by selisih
            // --------------------------
            $targetRp = (int) ($mapProyeksi[$tokoId] ?? 0);

            $selisihRp = $hrgApi - $targetRp;
            $selisihPersen = $targetRp > 0 ? round(($selisihRp / $targetRp) * 100, 2) : null;

            $kontribusi = (int) round($selisihRp * (1 - $this->hppRatio));
            $grand += $kontribusi;

            // --------------------------
            // 4) DISC MANUAL (target - real)
            // --------------------------
            $discRealRp = (int) $colBiaya->filter(fn($r) => strtoupper(trim((string) ($r['tipe'] ?? ''))) === 'DISKON MANUAL')->sum(fn($r) => $this->parseIntMoney($r['totbiaya'] ?? 0));

            $discTargetRp = $this->targetToRp($targetDisc, $isProduksi, $hrgApi);
            [$discSelisihRp, $discSelisihPersen] = $this->selisihTargetReal($discTargetRp, $discRealRp, $hrgApi);

            // --------------------------
            // 5) GAS (pakai deskripsi sebelum XpX)
            // --------------------------
            $gasRealRp = $this->sumBiayaByExtracted($colBiaya, 'GAS');
            $gasTargetRp = $this->targetToRp($targetGas, $isProduksi, $hrgApi);
            [$gasSelisihRp, $gasSelisihP] = $this->selisihTargetReal($gasTargetRp, $gasRealRp, $hrgApi);

            // --------------------------
            // 6) TELUR (pakai deskripsi sebelum XpX)
            // --------------------------
            $telurRealRp = $this->sumBiayaByExtracted($colBiaya, 'TELUR');
            $telurTargetRp = $this->targetToRp($targetTelur, $isProduksi, $hrgApi);
            [$telurSelisihRp, $telurSelisihP] = $this->selisihTargetReal($telurTargetRp, $telurRealRp, $hrgApi);

            // --------------------------
            // 7) RETUR & LOSS (sementara 0/null sesuai punyamu)
            // --------------------------
            $returRp = 0;
            $lossBahan = 0;

            // total kontribusi = sum semua kolom Rp + loss
            $totalKontribusi = (int) $kontribusi + (int) $discSelisihRp + (int) $returRp + (int) $gasSelisihRp + (int) $telurSelisihRp + (int) $lossBahan;

            $rows[] = [
                'outlet' => $outlet,
                'neto' => $neto,

                'selisih_rp' => $selisihRp,
                'selisih_persen' => $selisihPersen,

                'kontribusi_rp' => $kontribusi,
                'kontribusi_persen' => null,

                'sc_manual_rp' => $discSelisihRp,
                'sc_manual_persen' => $discSelisihPersen,

                'retur_rp' => null,
                'retur_persen' => null,

                'gas_rp' => $gasSelisihRp,
                'gas_persen' => $gasSelisihP,

                'telur_rp' => $telurSelisihRp,
                'telur_persen' => $telurSelisihP,

                'loss_bahan' => $lossBahan,
                'total_kontribusi' => $totalKontribusi,
            ];
        }

        // kontribusi persen setelah grand ketemu
        if ($grand != 0) {
            // boleh negatif juga
            foreach ($rows as &$r) {
                $r['kontribusi_persen'] = round(((float) ($r['kontribusi_rp'] ?? 0) / (float) $grand) * 100, 2);
            }
            unset($r);
        }

        $this->rowsTarget = $rows;
        $this->sumNetoTarget = $grand;
    }

    public function loadBulanLalu()
    {
        $this->validate([
            'bulanLaluAwal' => 'required|date',
            'bulanLaluAkhir' => 'required|date|after_or_equal:bulanLaluAwal',
        ]);

        $rows = [];
        $grand = 0;

        foreach ($this->tokosUser as $t) {
            $apiId = trim((string) ($t['api_id'] ?? ''));
            $outlet = $t['nmtoko'] ?? '-';

            $neto = 0;

            if ($apiId !== '') {
                $data = $this->fetchPenjualan($apiId, $this->bulanLaluAwal, $this->bulanLaluAkhir);

                $neto = collect($data)->where('idcabang', $apiId)->sum(fn($r) => (int) ($r['neto'] ?? 0));
            }

            // 🔥 PENTING: total dijumlahkan di sini
            $grand += $neto;

            $rows[] = [
                'outlet' => $outlet,
                'kontribusi_rp' => $neto,
                'total_kontribusi' => $neto,
            ];
        }

        $this->rowsBulanLalu = $rows;
        $this->sumNetoBulanLalu = $grand;
    }

    public function render()
    {
        return view('livewire.operasional.sisasales');
    }
}
