<?php
// app/Services/KontribusiHarianTokoService.php
namespace App\Services;

use Carbon\Carbon;
use App\Models\MasterToko;
use App\Models\Operasional\LossBahan;
use App\Models\Operasional\KurangSetoran;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\TargetKontribusi;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KontribusiHarianTokoService
{
    protected float $hppRatio = 0.56;
    protected float $returHppRatio = 0.42;

    // ==============================
    // Helpers dasar
    // ==============================
    private function parseIntMoney($v): int
    {
        return (int) preg_replace('/[^\d\-]/', '', (string) ($v ?? 0));
    }

    private function applyHppRetur(int $rp): int
    {
        return (int) round($rp * $this->returHppRatio);
    }

    private function pctFromSum(int $sumRp, int $sumSales): ?float
    {
        if ($sumSales <= 0) return null;
        return round(($sumRp / $sumSales) * 100, 2);
    }

    private function persenDariRp(?int $rp, ?int $penjualan): ?float
    {
        $rp = (int)($rp ?? 0);
        $penjualan = (int)($penjualan ?? 0);
        if ($penjualan <= 0) return null;
        return round(($rp / $penjualan) * 100, 2);
    }

    /**
     * BY BULAN LALU (harian):
     * pct_fraction = (biaya_last / sales_last) - (biaya_now / sales_now)
     * rp          = pct_fraction * sales_now
     */
    private function diffPctAndRpBL(int $biayaNow, int $salesNow, int $biayaLast, int $salesLast): array
    {
        if ($salesNow <= 0 || $salesLast <= 0) return [null, 0];

        $pNow  = $biayaNow / $salesNow;    // fraction
        $pLast = $biayaLast / $salesLast;  // fraction

        $pctFraction = round($pLast - $pNow, 6);
        $rp          = (int) round($pctFraction * $salesNow);

        return [$pctFraction, $rp];
    }

    private function getTrendPersenUntukTanggal(string $tanggal): float
    {
        $d = Carbon::parse($tanggal);

        return (float) (MasterTrendInflasi::query()
            ->where('tahun', (int) $d->year)
            ->where('bulan', (int) $d->month)
            ->value('trend') ?? 0);
    }

    // ==============================
    // Target helpers
    // ==============================
    private function nilaiTargetByRule(?TargetKontribusi $t, bool $isProduksiSendiri): float
    {
        if (!$t) return 0;

        $pakaiRule = (int)($t->pakai_rule_produksi ?? 0) === 1;
        if (!$pakaiRule) return (float)($t->nilai ?? 0);

        $v = $isProduksiSendiri
            ? ($t->nilai_produksi_sendiri ?? null)
            : ($t->nilai_non_produksi_sendiri ?? null);

        if ($v === null) $v = $t->nilai ?? 0;
        return (float) $v;
    }

    private function targetToRp(?TargetKontribusi $t, bool $isProduksi, int $salesNow): int
    {
        if (!$t) return 0;

        $tipe  = strtoupper((string)($t->tipe ?? ''));
        $nilai = $this->nilaiTargetByRule($t, $isProduksi);

        return $tipe === 'PERSEN'
            ? (int) round(((float)$salesNow) * ($nilai / 100))
            : (int) round($nilai);
    }

    private function selisihTargetReal(int $targetRp, int $realRp, int $salesNow): array
    {
        $selisihRp = $targetRp - $realRp;
        $selisihP  = $salesNow > 0 ? round(($selisihRp / $salesNow) * 100, 2) : null;
        return [$selisihRp, $selisihP];
    }

    // ==============================
    // HTTP parsers (penjualan)
    // ==============================
    private function sumPenjualanFromJson($json): int
    {
        $data = data_get($json, 'data');

        if (is_array($data) && array_is_list($data)) $rows = $data;
        elseif (is_array($data)) $rows = [$data];
        else $rows = [];

        $sum = 0;
        foreach ($rows as $r) {
            $sum += $this->parseIntMoney($r['hrg'] ?? 0);
        }
        return (int)$sum;
    }

    private function fetchPenjualanSingleDay(string $apiId, string $tgl): ?int
    {
        $res = Http::timeout(25)->retry(2, 400)->get('https://api.khasanahsari-bakery.com/dw/penjualan', [
            'startDate' => $tgl, 'endDate' => $tgl, 'idcab' => $apiId,
        ]);

        if (!$res->successful()) return null;
        return $this->sumPenjualanFromJson($res->json());
    }

    private function fetchPenjualanHarianMany(string $apiId, array $dates): array
    {
        $dates = array_values(array_unique(array_filter($dates)));
        if ($apiId === '' || empty($dates)) return [];
        sort($dates);

        $key = 'kh_toko:penjualan_harian_many:v6:' . md5($apiId . '|' . json_encode($dates));

        return Cache::remember($key, now()->addMinutes(15), function () use ($apiId, $dates) {
            $map = array_fill_keys($dates, 0);

            foreach (array_chunk($dates, 50) as $chunk) {
                $responses = Http::timeout(45)->retry(2, 600)->pool(function ($pool) use ($apiId, $chunk) {
                    foreach ($chunk as $tgl) {
                        $pool->as($tgl)->get('https://api.khasanahsari-bakery.com/dw/penjualan', [
                            'startDate' => $tgl, 'endDate' => $tgl, 'idcab' => $apiId,
                        ]);
                    }
                });

                foreach ($chunk as $tgl) {
                    $res = $responses[$tgl] ?? null;

                    if (!$res instanceof Response || !$res->successful()) {
                        // fallback single
                        $single = Http::timeout(60)->retry(2, 800)->get('https://api.khasanahsari-bakery.com/dw/penjualan', [
                            'startDate' => $tgl, 'endDate' => $tgl, 'idcab' => $apiId,
                        ]);
                        $map[$tgl] = $single->successful() ? $this->sumPenjualanFromJson($single->json()) : 0;
                        continue;
                    }

                    $map[$tgl] = $this->sumPenjualanFromJson($res->json());
                }
            }

            return $map;
        });
    }

    // ==============================
    // HTTP parsers (biaya + retur)
    // ==============================
    private function fetchBiayaReturHarianMany(string $apiId, array $dates): array
    {
        $dates = array_values(array_unique(array_filter($dates)));
        if ($apiId === '' || empty($dates)) return [];
        sort($dates);

        $key = 'kh_toko:biaya_retur_many:v3:' . md5($apiId . '|' . json_encode($dates));

        return Cache::remember($key, now()->addMinutes(15), function () use ($apiId, $dates) {
            $map = [];
            foreach (array_chunk($dates, 50) as $chunk) {

                $responses = Http::timeout(45)->retry(2, 600)->pool(function ($pool) use ($apiId, $chunk) {
                    foreach ($chunk as $tgl) {
                        $pool->as('biaya_' . $tgl)->get('https://api.khasanahsari-bakery.com/dw/biaya', [
                            'startDate' => $tgl, 'endDate' => $tgl, 'idcab' => $apiId,
                        ]);

                        $pool->as('retur_' . $tgl)->get('https://api.khasanahsari-bakery.com/dw/retur', [
                            'startDate' => $tgl, 'endDate' => $tgl, 'idcab' => $apiId,
                        ]);
                    }
                });

                foreach ($chunk as $tgl) {
                    $map[$tgl] = ['disc' => 0, 'gas' => 0, 'telur' => 0, 'retur' => 0];

                    // biaya
                    $resBiaya = $responses['biaya_' . $tgl] ?? null;
                    $biayaPayload = ($resBiaya instanceof Response && $resBiaya->successful())
                        ? ($resBiaya->json('dataBiaya') ?? ($resBiaya->json('data') ?? null))
                        : null;

                    if (is_array($biayaPayload)) {
                        $map[$tgl]['disc']  += $this->parseIntMoney(data_get($biayaPayload, 'diskonManual.totbiaya', 0));
                        $map[$tgl]['gas']   += $this->parseIntMoney(data_get($biayaPayload, 'gas.totbiaya', 0));
                        $map[$tgl]['telur'] += $this->parseIntMoney(data_get($biayaPayload, 'telur.totbiaya', 0));
                    }

                    // retur
                    $resRetur = $responses['retur_' . $tgl] ?? null;
                    $returPayload = ($resRetur instanceof Response && $resRetur->successful())
                        ? $resRetur->json('data')
                        : null;

                    if (is_array($returPayload)) {
                        if (array_key_exists('total_hrg', $returPayload)) {
                            $map[$tgl]['retur'] += $this->parseIntMoney($returPayload['total_hrg'] ?? 0);
                        } elseif (array_is_list($returPayload)) {
                            foreach ($returPayload as $r) {
                                $map[$tgl]['retur'] += $this->parseIntMoney($r['total_hrg'] ?? ($r['tot_hrg'] ?? 0));
                            }
                        }
                    }
                }
            }

            return $map;
        });
    }

    // ==============================
    // DB maps (proyeksi & loss)
    // ==============================
    private function proyeksiMapHarian(int $tokoId, string $start, string $end): array
    {
        return MasterProyeksiKontribusi::query()
            ->where('toko_id', $tokoId)
            ->whereDate('tanggal', '>=', $start)
            ->whereDate('tanggal', '<=', $end)
            ->selectRaw('toko_id, DATE(tanggal) as tgl, SUM(rupiah) as total_rp')
            ->groupBy('toko_id', 'tgl')
            ->get()
            ->groupBy('toko_id')
            ->map(fn($g) => $g->pluck('total_rp', 'tgl')->map(fn($v) => (int)$v)->toArray())
            ->get($tokoId, []);
    }

    private function lossBahanMapHarian(int $tokoId, string $start, string $end): array
    {
        return LossBahan::query()
            ->where('toko_id', $tokoId)
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as tgl, SUM(nominal) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl')
            ->map(fn($v) => (int)$v)
            ->toArray();
    }

    /**
     * Fetch kurang setoran map per tanggal untuk range
     * Format: ['2025-12-27' => 150000, ...]
     */
    private function kurangSetoranMapHarian(int $tokoId, string $start, string $end): array
    {
        return KurangSetoran::query()
            ->where('toko_id', $tokoId)
            ->whereBetween('tanggal', [$start, $end])
            ->selectRaw('DATE(tanggal) as tgl, SUM(nominal) as total')
            ->groupBy('tgl')
            ->pluck('total', 'tgl')
            ->map(fn($v) => (int)$v)
            ->toArray();
    }

    // ==============================
    // Totals builder (anti average)
    // ==============================
    private function buildTotalsForJenis($rows, string $jenis): array
    {
        $r = $rows->where('jenis', $jenis);

        $sumSales = (int) $r->sum(function ($x) {
            // support dari row langsung atau payload
            return (int) ($x['sales_now'] ?? data_get($x, 'payload.sales_now', 0));
        });

        $sumSelisih = (int) $r->sum('selisih_rp');
        $sumKontrib = (int) $r->sum('kontribusi_rp');
        $sumDisc    = (int) $r->sum('disc_rp');
        $sumRetur   = (int) $r->sum('retur_rp');
        $sumGas     = (int) $r->sum('gas_rp');
        $sumTelur   = (int) $r->sum('telur_rp');
        $sumLoss    = (int) $r->sum('loss_bahan');
        $sumKurang  = (int) $r->sum('kurang_setoran');
        $sumTotal   = (int) $r->sum('total_kontribusi');

        return [
            'sales' => $sumSales,

            'selisih_rp' => $sumSelisih,
            'selisih_persen' => $this->pctFromSum($sumSelisih, $sumSales),

            'kontribusi_rp' => $sumKontrib,
            'kontribusi_persen' => $this->pctFromSum($sumKontrib, $sumSales),

            'disc_rp' => $sumDisc,
            'disc_persen' => $this->pctFromSum($sumDisc, $sumSales),

            'retur_rp' => $sumRetur,
            'retur_persen' => $this->pctFromSum($sumRetur, $sumSales),

            'gas_rp' => $sumGas,
            'gas_persen' => $this->pctFromSum($sumGas, $sumSales),

            'telur_rp' => $sumTelur,
            'telur_persen' => $this->pctFromSum($sumTelur, $sumSales),

            'loss_bahan' => $sumLoss,
            'loss_persen' => $this->pctFromSum($sumLoss, $sumSales),

            'kurang_setoran' => $sumKurang,

            'total_kontribusi' => $sumTotal,
            'total_persen' => $this->pctFromSum($sumTotal, $sumSales),
        ];
    }

    // ==============================
    // PUBLIC: hitung
    // ==============================
    public function hitung(int $tokoId, string $start, string $end): array
    {
        Log::channel('snapshot')->info('SERVICE_FILE', [
            'file' => __FILE__,
            'hash' => substr(@sha1_file(__FILE__) ?: '', 0, 12),
            'toko_id' => $tokoId,
            'start' => $start,
            'end' => $end,
        ]);

        $toko = MasterToko::with(['area'])->find($tokoId);
        if (!$toko) return ['rowsOut' => [], 'grandTotals' => [], 'namaToko' => ''];

        $namaToko = strtoupper(trim((string)($toko->nmtoko ?? '')));
        $apiId    = trim((string)($toko->api_id ?? ''));
        $area     = $toko->area?->nama_area ?: '-';
        $outlet   = $toko->nmtoko ?: '-';
        $isProduksi = ((int)($toko->produksi_sendiri ?? 0)) === 1;

        // ---- targets
        $targets = TargetKontribusi::query()
            ->where('aktif', 1)
            ->whereIn('kode', ['DISC_MANUAL', 'GAS', 'TELUR', 'RETUR'])
            ->get()
            ->keyBy('kode');

        $targetDisc  = $targets->get('DISC_MANUAL');
        $targetGas   = $targets->get('GAS');
        $targetTelur = $targets->get('TELUR');
        $targetRetur = $targets->get('RETUR');

        // ---- dates now + dates last
        $dates = [];
        $cur = Carbon::parse($start);
        $endC = Carbon::parse($end);
        while ($cur->lte($endC)) {
            $dates[] = $cur->toDateString();
            $cur->addDay();
        }

        $datesLast = array_map(fn($tgl) => Carbon::parse($tgl)->subMonthNoOverflow()->toDateString(), $dates);
        $allDates  = array_values(array_unique(array_merge($dates, $datesLast)));

        // ---- fetch maps
        $penjualanMap  = $this->fetchPenjualanHarianMany($apiId, $allDates);
        $biayaReturMap = $this->fetchBiayaReturHarianMany($apiId, $allDates);

        $proyeksiHarian = $this->proyeksiMapHarian($tokoId, $start, $end);
        $lossHarian     = $this->lossBahanMapHarian($tokoId, $start, $end);
        $kurangSetoranHarian = $this->kurangSetoranMapHarian($tokoId, $start, $end);

        $rowsOut = [];

        foreach ($dates as $tgl) {
            $tglLast = Carbon::parse($tgl)->subMonthNoOverflow()->toDateString();

            // ---- sales now & last
            $salesNow  = (int) ($penjualanMap[$tgl] ?? 0);
            $salesLast = (int) ($penjualanMap[$tglLast] ?? 0);

            // fallback last kalau key tidak ada
            if ($salesLast === 0 && !array_key_exists($tglLast, $penjualanMap)) {
                $fb = $this->fetchPenjualanSingleDay($apiId, $tglLast);
                if ($fb !== null) $salesLast = $fb;
            }

            // ---- biaya now (retur apply HPP 42%)
            $discNow  = (int) data_get($biayaReturMap, "$tgl.disc", 0);
            $gasNow   = (int) data_get($biayaReturMap, "$tgl.gas", 0);
            $telurNow = (int) data_get($biayaReturMap, "$tgl.telur", 0);

            $returNowRaw = (int) data_get($biayaReturMap, "$tgl.retur", 0);
            $returNow    = $this->applyHppRetur($returNowRaw);

            $lossNow = (int) ($lossHarian[$tgl] ?? 0);
            $kurangSetoranNow = (int) ($kurangSetoranHarian[$tgl] ?? 0);

            // =========================================================
            // BY TARGET
            // =========================================================
            $targetRp = (int) ($proyeksiHarian[$tgl] ?? 0);

            $selisihRpTarget  = $salesNow - $targetRp;
            $selisihPctTarget = $targetRp > 0 ? round(($selisihRpTarget / $targetRp) * 100, 2) : null;

            $kontribusiTarget = (int) round($selisihRpTarget * (1 - $this->hppRatio));

            $discTargetRp = $this->targetToRp($targetDisc,  $isProduksi, $salesNow);
            $gasTargetRp  = $this->targetToRp($targetGas,   $isProduksi, $salesNow);
            $telTargetRp  = $this->targetToRp($targetTelur, $isProduksi, $salesNow);

            $retTargetRaw = $this->targetToRp($targetRetur, $isProduksi, $salesNow);
            $retTargetRp  = $this->applyHppRetur($retTargetRaw);

            [$discSel] = $this->selisihTargetReal($discTargetRp, $discNow,  $salesNow);
            [$gasSel]  = $this->selisihTargetReal($gasTargetRp,  $gasNow,   $salesNow);
            [$telSel]  = $this->selisihTargetReal($telTargetRp,  $telurNow, $salesNow);
            [$retSel]  = $this->selisihTargetReal($retTargetRp,  $returNow, $salesNow);

            $totalKontribusiTarget =
                (int)$kontribusiTarget
                + (int)$discSel
                + (int)$retSel
                + (int)$gasSel
                + (int)$telSel
                - (int)$lossNow
                - (int)$kurangSetoranNow;

            $rowsOut[] = [
                'tanggal' => $tgl,
                'jenis' => 'BY TARGET',
                'area' => $area,
                'outlet' => $outlet,

                // IMPORTANT: simpan sales untuk hitung totals % by range
                'sales_now' => $salesNow,

                'selisih_persen' => $selisihPctTarget,
                'selisih_rp' => $selisihRpTarget,
                'kontribusi_rp' => $kontribusiTarget,

                'disc_persen'  => $this->persenDariRp($discSel, $salesNow),
                'disc_rp'      => (int)$discSel,

                'retur_persen' => $this->persenDariRp($retSel, $salesNow),
                'retur_rp'     => (int)$retSel,

                'gas_persen'   => $this->persenDariRp($gasSel, $salesNow),
                'gas_rp'       => (int)$gasSel,

                'telur_persen' => $this->persenDariRp($telSel, $salesNow),
                'telur_rp'     => (int)$telSel,

                'loss_bahan' => $lossNow,
                'kurang_setoran' => $kurangSetoranNow,
                'total_kontribusi' => $totalKontribusiTarget,
            ];

            // =========================================================
            // BY BULAN LALU
            // =========================================================
            $trendPct = $this->getTrendPersenUntukTanggal($tgl);
            $baseline = (int) round($salesLast * (1 + ($trendPct / 100)));

            $selisihRpBL  = $salesNow - $baseline;
            $selisihPctBL = $baseline > 0 ? round(($selisihRpBL / $baseline) * 100, 2) : null;

            $hppSel       = (int) round($selisihRpBL * $this->hppRatio);
            $kontribusiBL = (int) ($selisihRpBL - $hppSel);

            // biaya last (retur apply 42%)
            $discLast  = (int) data_get($biayaReturMap, "$tglLast.disc", 0);
            $gasLast   = (int) data_get($biayaReturMap, "$tglLast.gas", 0);
            $telurLast = (int) data_get($biayaReturMap, "$tglLast.telur", 0);

            $returLastRaw = (int) data_get($biayaReturMap, "$tglLast.retur", 0);
            $returLast    = $this->applyHppRetur($returLastRaw);

            // diff ratio harian
            [$discPctBL, $discRpBL] = $this->diffPctAndRpBL($discNow,  $salesNow, $discLast,  $salesLast);
            [$gasPctBL,  $gasRpBL ] = $this->diffPctAndRpBL($gasNow,   $salesNow, $gasLast,   $salesLast);
            [$telPctBL,  $telRpBL ] = $this->diffPctAndRpBL($telurNow, $salesNow, $telurLast, $salesLast);
            [$retPctBL,  $retRpBL ] = $this->diffPctAndRpBL($returNow, $salesNow, $returLast, $salesLast);

            $totalKontribusiBL =
                (int)$kontribusiBL
                + (int)$discRpBL
                + (int)$gasRpBL
                + (int)$telRpBL
                + (int)$retRpBL
                - (int)$lossNow
                - (int)$kurangSetoranNow;

            $rowsOut[] = [
                'tanggal' => $tgl,
                'jenis' => 'BY BULAN LALU',
                'area' => $area,
                'outlet' => $outlet,

                // IMPORTANT: simpan sales untuk hitung totals % by range
                'sales_now' => $salesNow,
                'sales_last' => $salesLast, // opsional (debug / future)

                'selisih_persen' => $selisihPctBL,
                'selisih_rp' => $selisihRpBL,
                'kontribusi_rp' => $kontribusiBL,

                // persen disimpan dalam % (bukan fraction) untuk tampil
                'disc_persen'  => is_null($discPctBL) ? null : round($discPctBL * 100, 2),
                'disc_rp'      => (int)$discRpBL,

                'retur_persen' => is_null($retPctBL) ? null : round($retPctBL * 100, 2),
                'retur_rp'     => (int)$retRpBL,

                'gas_persen'   => is_null($gasPctBL) ? null : round($gasPctBL * 100, 2),
                'gas_rp'       => (int)$gasRpBL,

                'telur_persen' => is_null($telPctBL) ? null : round($telPctBL * 100, 2),
                'telur_rp'     => (int)$telRpBL,

                'loss_bahan' => $lossNow,
                'kurang_setoran' => $kurangSetoranNow,
                'total_kontribusi' => $totalKontribusiBL,
            ];
        }

        $col = collect($rowsOut);

        // grandTotals anti-average: % dihitung dari total Rp / total Sales
        $grandTotals = [
            'by_target'     => $this->buildTotalsForJenis($col, 'BY TARGET'),
            'by_bulan_lalu' => $this->buildTotalsForJenis($col, 'BY BULAN LALU'),
        ];

        return [
            'rowsOut' => $rowsOut,
            'grandTotals' => $grandTotals,
            'namaToko' => $namaToko,
        ];
    }
}
