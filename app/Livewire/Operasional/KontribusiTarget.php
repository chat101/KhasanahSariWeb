<?php

namespace App\Livewire\Operasional;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\TargetKontribusi;
use Illuminate\Support\Facades\Cache;

class KontribusiTarget extends Component
{
    public array $tokosUser = [];

    public $tanggalAwal;
    public $tanggalAkhir;
    public array $rowsTarget = [];
    public int $sumNetoTarget = 0;
    public array $totalsByArea = [];
    public array $grandTotals = [];
    protected float $hppRatio = 0.56;

    public function mount(array $tokosUser = [])
    {
        $this->tokosUser = $tokosUser;
        $this->tanggalAwal = now()->toDateString();
        $this->tanggalAkhir = now()->toDateString();
    }
    private function fetchPenjualanMany(array $apiIds, string $start, string $end): array
    {
        $apiIds = array_values(array_filter(array_unique(array_map('trim', $apiIds))));
        if (empty($apiIds)) {
            return [];
        }

        $cacheKey = 'kontribusi:penjualan:' . md5($start . '|' . $end . '|' . implode(',', $apiIds));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($apiIds, $start, $end) {
            $responses = Http::timeout(35)
                ->retry(2, 500)
                ->pool(function ($pool) use ($apiIds, $start, $end) {
                    foreach ($apiIds as $id) {
                        $pool->as($id)->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
                            'startDate' => $start,
                            'endDate' => $end,
                            'idcabang' => $id,
                        ]);
                    }
                });

            $out = [];
            foreach ($apiIds as $id) {
                $res = $responses[$id] ?? null;

                // ✅ kalau exception atau null -> kosong
                if (!$res instanceof Response) {
                    $out[$id] = [];
                    continue;
                }

                if (!$res->successful()) {
                    $out[$id] = [];
                    continue;
                }

                $json = $res->json();
                $out[$id] = $json['data'] ?? [];
            }

            return $out;
        });
    }
    private function extractDeskripsi(?string $ket): ?string
    {
        if (!$ket) {
            return null;
        }
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
                return $desc === $needle;
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

        $v = $isProduksiSendiri ? $t->nilai_produksi_sendiri ?? null : $t->nilai_non_produksi_sendiri ?? null;

        if ($v === null) {
            $v = $t->nilai ?? 0;
        }

        return (float) $v;
    }

    // private function fetchBiaya(string $apiName, string $start, string $end): array
    // {
    //     $json = Http::timeout(20)
    //         ->retry(2, 300)
    //         ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
    //             'startDate' => $start,
    //             'endDate'   => $end,
    //             'nmcab'     => $apiName,
    //         ])->json();

    //     return $json['data'] ?? [];
    // }
//  private function fetchBiayaMany(array $apiIds, string $start, string $end): array
// {
//     $apiIds = array_values(array_filter(array_unique(array_map('trim', $apiIds))));
//     if (empty($apiIds)) return [];

//     $cacheKey = 'kontribusi:biaya:idcab:' . md5($start.'|'.$end.'|'.implode(',', $apiIds));

//     return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($apiIds, $start, $end) {

//         $responses = Http::timeout(35)
//             ->retry(2, 500)
//             ->pool(function ($pool) use ($apiIds, $start, $end) {
//                 foreach ($apiIds as $idcab) {
//                     $pool->as($idcab)->get(
//                         'https://api.khasanahsari-bakery.com/dw/biaya',
//                         [
//                             'startDate' => $start,
//                             'endDate'   => $end,
//                             'idcab'     => $idcab,   // ✅ PAKAI IDCAB
//                         ]
//                     );
//                 }
//             });

//         $out = [];

//         foreach ($apiIds as $idcab) {
//             $res = $responses[$idcab] ?? null;

//             if (!$res instanceof Response || !$res->successful()) {
//                 $out[$idcab] = ['disc' => 0, 'gas' => 0, 'telur' => 0];
//                 continue;
//             }

//             $rows = $res->json('data') ?? [];

//             $disc = 0;
//             $gas  = 0;
//             $telur = 0;

//             foreach ($rows as $r) {
//                 $tipe = strtoupper(trim((string)($r['tipe'] ?? '')));
//                 $ket  = strtoupper(trim((string)($this->extractDeskripsi($r['ket'] ?? ''))));
//                 $val  = (int) $this->parseIntMoney($r['totbiaya'] ?? 0);

//                 if ($tipe === 'DISKON MANUAL') $disc += $val;
//                 if ($ket === 'GAS')           $gas  += $val;
//                 if ($ket === 'TELUR')         $telur += $val;
//             }

//             $out[$idcab] = [
//                 'disc'  => $disc,
//                 'gas'   => $gas,
//                 'telur' => $telur,
//             ];
//         }

//         return $out;
//     });
// }
private function fetchBiayaByIdcabMap(array $apiIds, string $start, string $end): array
{
    $apiIds = array_values(array_filter(array_unique(array_map('trim', $apiIds))));
    if (empty($apiIds)) return [];

    $cacheKey = 'kontribusi:biaya:allmap:' . md5($start.'|'.$end.'|'.implode(',', $apiIds));

    return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($apiIds, $start, $end) {

        // ✅ Fetch ALL sekali (karena API belum ada filter cab)
        $json = Http::timeout(60)
            ->retry(2, 800)
            ->get('https://api.khasanahsari-bakery.com/dw/biaya', [
                'startDate' => $start,
                'endDate'   => $end,
            ])->json();

        $rows = $json['data'] ?? [];

        // ✅ group by idcab dari API
        $group = collect($rows)->groupBy(fn($r) => trim((string)($r['idcab'] ?? '')));

        $out = [];
        foreach ($apiIds as $idcab) {
            $g = $group[$idcab] ?? collect();

            $disc = (int) $g->filter(fn($r) => strtoupper(trim((string)($r['tipe'] ?? ''))) === 'DISKON MANUAL')
                ->sum(fn($r) => $this->parseIntMoney($r['totbiaya'] ?? 0));

            $gas = (int) $g->filter(function ($r) {
                    $ket = strtoupper(trim((string)$this->extractDeskripsi($r['ket'] ?? '')));
                    return $ket === 'GAS';
                })
                ->sum(fn($r) => $this->parseIntMoney($r['totbiaya'] ?? 0));

            $telur = (int) $g->filter(function ($r) {
                    $ket = strtoupper(trim((string)$this->extractDeskripsi($r['ket'] ?? '')));
                    return $ket === 'TELUR';
                })
                ->sum(fn($r) => $this->parseIntMoney($r['totbiaya'] ?? 0));

            $out[$idcab] = ['disc' => $disc, 'gas' => $gas, 'telur' => $telur];
        }

        return $out;
    });
}
    // private function fetchPenjualan(string $apiId, string $start, string $end): array
    // {
    //     $json = Http::timeout(20)
    //         ->retry(2, 300)
    //         ->get('https://api.khasanahsari-bakery.com/dw/sum-penjualan', [
    //             'startDate' => $start,
    //             'endDate'   => $end,
    //             'idcabang'  => $apiId,
    //         ])->json();

    //     return $json['data'] ?? [];
    // }
    private function lossBahanMap(array $tokoIds, string $start, string $end): array
    {
        $tokoIds = array_values(array_unique(array_map('intval', $tokoIds)));
        if (empty($tokoIds)) {
            return [];
        }

        return \App\Models\Operasional\LossBahan::query()
            ->selectRaw('toko_id, SUM(nominal) as total')
            ->whereIn('toko_id', $tokoIds)
            ->whereBetween('tanggal', [$start, $end])
            ->groupBy('toko_id')
            ->pluck('total', 'toko_id')
            ->map(fn($v) => (int) $v)
            ->all();
    }

    // ✅ RETUR: ambil semua (tanpa nmcab), nanti dimap per outlet
    private function fetchReturAll(string $start, string $end): array
    {
        $cacheKey = 'kontribusi:retur:' . md5($start . '|' . $end);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($start, $end) {
            $json = Http::timeout(35)
                ->retry(2, 500)
                ->get('https://api.khasanahsari-bakery.com/dw/retur', [
                    'startDate' => $start,
                    'endDate' => $end,
                ])
                ->json();

            return $json['data'] ?? [];
        });
    }
    private function proyeksiMap(string $start, string $end): array
    {
        $start = Carbon::parse($start)->toDateString();
        $end = Carbon::parse($end)->toDateString();

        $q = MasterProyeksiKontribusi::query();

        if ($start === $end) {
            $q->whereDate('tanggal', $start);
        } else {
            $q->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end);
        }

        return $q->selectRaw('toko_id, SUM(rupiah) as total_rp')->groupBy('toko_id')->pluck('total_rp', 'toko_id')->map(fn($v) => (int) $v)->all();
    }

    // private function onlyTanggalJikaSingle($col, bool $isSingleDate, string $start)
    // {
    //     if (!$isSingleDate) {
    //         return $col;
    //     }

    //     return $col->filter(function ($r) use ($start) {
    //         $tgl = (string) ($r['tglinput'] ?? '');
    //         return $tgl !== '' && substr($tgl, 0, 10) === $start;
    //     });
    // }

    private function parseIntMoney($v): int
    {
        return (int) preg_replace('/[^\d\-]/', '', (string) ($v ?? 0));
    }

    private function targetToRp(?TargetKontribusi $t, bool $isProduksi, int $penjualanHrg): int
    {
        if (!$t) {
            return 0;
        }

        $tipe = strtoupper((string) ($t->tipe ?? ''));
        $nilai = $this->nilaiTargetByRule($t, $isProduksi);

        return $tipe === 'PERSEN' ? (int) round(((float) $penjualanHrg) * ($nilai / 100)) : (int) round($nilai);
    }

    private function selisihTargetReal(int $targetRp, int $realRp, int $penjualanHrg): array
    {
        $selisihRp = $targetRp - $realRp;
        $selisihP = $penjualanHrg > 0 ? round(($selisihRp / $penjualanHrg) * 100, 2) : null;
        return [$selisihRp, $selisihP];
    }

    // ✅ Map retur per outlet (pakai nmcab/outlet/cab), akumulasi jika dobel
    // private function mapReturByOutlet(array $returRows): array
    // {
    //     $map = [];

    //     foreach ($returRows as $row) {
    //         $outlet = strtoupper(trim($row['nmcab'] ?? ($row['outlet'] ?? ($row['cab'] ?? ''))));
    //         if ($outlet === '') {
    //             continue;
    //         }

    //         $rp = (int) ($row['retur_rp'] ?? ($row['nominal'] ?? ($row['rp'] ?? ($row['totretur'] ?? 0))));
    //         $map[$outlet] = ($map[$outlet] ?? 0) + $rp;
    //     }

    //     return $map;
    // }

    // ✅ % = rp / penjualan(hrg) * 100
    private function persenDariRp(?int $rp, ?int $penjualan): ?float
    {
        $rp = (int) ($rp ?? 0);
        $penjualan = (int) ($penjualan ?? 0);

        if ($penjualan <= 0) {
            return null;
        }
        return round(($rp / $penjualan) * 100, 2);
    }
    private function mapReturByIdcab(array $rows): array
    {
        $map = [];

        foreach ($rows as $r) {
            $tipe = strtoupper(trim((string) ($r['tipe'] ?? '')));
            if ($tipe !== 'RETUR') {
                continue;
            } // ✅ hanya tipe Retur

            $idcab = trim((string) ($r['idcab'] ?? ''));
            if ($idcab === '') {
                continue;
            }

            // nominal retur: pakai tot_hrg dari API
            $rp = (int) $this->parseIntMoney($r['tot_hrg'] ?? 0);

            $map[$idcab] = ($map[$idcab] ?? 0) + $rp;
        }

        return $map;
    }
//     private function aggregatePenjualanPerHari(array $penjualanById): array
// {
//     $out = [];
//     foreach ($penjualanById as $apiId => $rows) {
//         foreach ($rows as $r) {
//             $nhari = (string)($r['nhari'] ?? '');
//             if ($nhari === '') continue;

//             $out[$apiId][$nhari]['hrg']  = ($out[$apiId][$nhari]['hrg']  ?? 0) + (int)($r['hrg']  ?? 0);
//             $out[$apiId][$nhari]['neto'] = ($out[$apiId][$nhari]['neto'] ?? 0) + (int)($r['neto'] ?? 0);
//         }
//     }
//     return $out;
// }

// private function aggregateBiayaMany(array $biayaByName, bool $isSingleDate, string $start): array
// {
//     $out = [];
//     foreach ($biayaByName as $apiName => $rows) {
//         $disc = 0; $gas = 0; $telur = 0;

//         foreach ($rows as $r) {
//             if ($isSingleDate) {
//                 $tgl = (string)($r['tglinput'] ?? '');
//                 if ($tgl === '' || substr($tgl, 0, 10) !== $start) continue;
//             }

//             $nominal = $this->parseIntMoney($r['totbiaya'] ?? 0);

//             $tipe = strtoupper(trim((string)($r['tipe'] ?? '')));
//             if ($tipe === 'DISKON MANUAL') {
//                 $disc += $nominal;
//                 continue;
//             }

//             $desc = strtoupper(trim((string)$this->extractDeskripsi((string)($r['ket'] ?? ''))));
//             if ($desc === 'GAS') $gas += $nominal;
//             elseif ($desc === 'TELUR') $telur += $nominal;
//         }

//         $out[$apiName] = ['disc_manual'=>$disc, 'gas'=>$gas, 'telur'=>$telur];
//     }
//     return $out;
// }

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

    //     $mapProyeksi = $this->proyeksiMap($start, $end);

    //     $targets = TargetKontribusi::query()
    //         ->where('aktif', 1)
    //         ->whereIn('kode', ['DISC_MANUAL', 'GAS', 'TELUR', 'RETUR']) // ✅ tambah 'RETUR'
    //         ->get()
    //         ->keyBy('kode');
    //     // ✅ kumpulkan id/nama sekali (DULU!)
    //     $apiIds = [];
    //     $apiNames = [];
    //     $tokoIds = [];

    //     foreach ($this->tokosUser as $t) {
    //         $apiIds[] = trim((string) ($t['api_id'] ?? ''));
    //         $apiNames[] = trim((string) ($t['api_name'] ?? '')); // pastikan key ini bener di tokosUser
    //         $tokoIds[] = (int) ($t['id'] ?? 0);
    //     }

    //     $apiIds = array_values(array_filter(array_unique($apiIds)));
    //     $apiNames = array_values(array_filter(array_unique($apiNames)));
    //     $tokoIds = array_values(array_filter(array_unique($tokoIds)));

    //     // ✅ baru ambil toko lokal + area
    //     $tokoLocal = \App\Models\MasterToko::query()
    //         ->with(['area']) // kalau ada wilayah: ->with(['area.wilayah'])
    //         ->whereIn('id', $tokoIds)
    //         ->get()
    //         ->keyBy('id');
    //     // ✅ ambil PIC AREA (role area) map: [area_id => "Nama1, Nama2"]
    //     $picAreaByAreaId = \App\Models\User::query()
    //         ->select('name', 'area_id')
    //         ->whereNotNull('area_id')
    //         ->whereRaw("LOWER(TRIM(role)) = 'area'")
    //         ->orderBy('name')->get()
    //         ->groupBy('area_id')
    //         ->map(fn($g) => $g
    //         ->pluck('name')
    //         ->filter()
    //         ->implode(', '))
    //         ->toArray();
    //     // ✅ loss bahan map (jangan di-comment)
    //     $lossMap = $this->lossBahanMap($tokoIds, $start, $end);
    //     $targetDisc = $targets->get('DISC_MANUAL');
    //     $targetGas = $targets->get('GAS');
    //     $targetTelur = $targets->get('TELUR');
    //     $targetRetur = $targets->get('RETUR'); // ✅ tambah ini

    //     // foreach ($this->tokosUser as $t) {
    //     //     $apiIds[] = (string) ($t['api_id'] ?? '');
    //     //     $apiNames[] = (string) ($t['api_name'] ?? '');
    //     //     $tokoIds[] = (int) ($t['id'] ?? 0);
    //     // }

    //     // ✅ batch hit API (paralel)
    //     $penjualanById = $this->fetchPenjualanMany($apiIds, $start, $end); // [api_id => rows]
    //   $biayaById = $this->fetchBiayaMany($apiIds, $start, $end);

    //     // ✅ retur 1x
    //     $returAll = $this->fetchReturAll($start, $end);
    //     $returMap = $this->mapReturByIdcab($returAll);

    //     // // ✅ loss bahan 1x untuk semua toko
    //     $lossMap = $this->lossBahanMap($tokoIds, $start, $end);

    //     $rows = [];
    //     $grand = 0;

    //     foreach ($this->tokosUser as $t) {
    //         $isProduksi = ((int) ($t['produksi_sendiri'] ?? 0)) === 1;

    //         $apiId = trim((string) ($t['api_id'] ?? ''));
    //         $apiName = trim((string) ($t['api_name'] ?? ''));
    //         $outlet = $t['nmtoko'] ?? '-';
    //         $tokoId = (int) ($t['id'] ?? 0);

    //         // 1) penjualan (ambil dari hasil batch)
    //         $neto = 0;
    //         $hrgApi = 0;

    //         if ($apiId !== '') {
    //             $data = $penjualanById[$apiId] ?? [];

    //             $col = collect($data)->where('idcabang', $apiId);
    //             if ($isSingleDate) {
    //                 $col = $col->where('nhari', $nhariSingle);
    //             }

    //             $neto = $col->sum(fn($r) => (int) ($r['neto'] ?? 0));
    //             $hrgApi = $col->sum(fn($r) => (int) ($r['hrg'] ?? 0));
    //         }

    //         // 2) biaya (ambil dari hasil batch)
    //         // $colBiaya = collect([]);
    //         // if ($apiName !== '') {
    //         //     $biayaRows = $biayaByName[$apiName] ?? [];
    //         //     $colBiaya = $this->onlyTanggalJikaSingle(collect($biayaRows), $isSingleDate, $start);
    //         // }

    //         // 3) target proyeksi
    //         $targetRp = (int) ($mapProyeksi[$tokoId] ?? 0);
    //         $selisihRp = $hrgApi - $targetRp;
    //         $selisihPersen = $targetRp > 0 ? round(($selisihRp / $targetRp) * 100, 2) : null;

    //         $kontribusi = (int) round($selisihRp * (1 - $this->hppRatio));
    //         $grand += $kontribusi;

    //         // 4) disc manual
    //         // $discRealRp = (int) $colBiaya->filter(fn($r) => strtoupper(trim((string) ($r['tipe'] ?? ''))) === 'DISKON MANUAL')->sum(fn($r) => $this->parseIntMoney($r['totbiaya'] ?? 0));
    //         $discRealRp  = (int) ($biayaById[$apiId]['disc']  ?? 0); 
    //         $discTargetRp = $this->targetToRp($targetDisc, $isProduksi, $hrgApi);
    //         [$discSelisihRp] = $this->selisihTargetReal($discTargetRp, $discRealRp, $hrgApi);
    //         $discPersenBySales = $this->persenDariRp($discSelisihRp, $hrgApi);

    //         // 5) gas
    //         // $gasRealRp = $this->sumBiayaByExtracted($colBiaya, 'GAS');
    //         $gasRealRp   = (int) ($biayaById[$apiId]['gas']   ?? 0);
    //         $gasTargetRp = $this->targetToRp($targetGas, $isProduksi, $hrgApi);
    //         [$gasSelisihRp] = $this->selisihTargetReal($gasTargetRp, $gasRealRp, $hrgApi);
    //         $gasPersenBySales = $this->persenDariRp($gasSelisihRp, $hrgApi);

    //         // 6) telur
    //         // $telurRealRp = $this->sumBiayaByExtracted($colBiaya, 'TELUR');
    //         $telurRealRp = (int) ($biayaById[$apiId]['telur'] ?? 0);
    //         $telurTargetRp = $this->targetToRp($targetTelur, $isProduksi, $hrgApi);
    //         [$telurSelisihRp] = $this->selisihTargetReal($telurTargetRp, $telurRealRp, $hrgApi);
    //         $telurPersenBySales = $this->persenDariRp($telurSelisihRp, $hrgApi);

    //         // 7) loss bahan (dari map)
    //         $lossBahan = (int) ($lossMap[$tokoId] ?? 0);

    //         // 8) retur (mapping pakai apiName kalau ada)
    //         // 8) RETUR (Realisasi dari API by idcab)
    //         $returRealRp = 0;
    //         if ($apiId !== '') {
    //             $returRealRp = (int) ($returMap[$apiId] ?? 0); // ini REALISASI
    //         }

    //         // ✅ Target retur dari master target_kontribusis (cek produksi sendiri / tidak)
    //         $returTargetRp = $this->targetToRp($targetRetur, $isProduksi, $hrgApi);

    //         // ✅ Selisih yang ditampilkan
    //         [$returSelisihRp, $returSelisihP] = $this->selisihTargetReal($returTargetRp, $returRealRp, $hrgApi);
    //         $returRp = $returSelisihRp;
    //         $returPersen = $this->persenDariRp($returRp, $hrgApi);

    //         $tokoDb = $tokoLocal[$tokoId] ?? null;

    //         $areaId = (int) ($tokoDb?->area_id ?? 0);
    //         $areaLabel = $tokoDb?->area?->nama_area ?: '-';
    //         $areaPic = $areaId > 0 ? $picAreaByAreaId[$areaId] ?? '' : '';
    //         $areaLabel = $tokoDb?->area?->nama_area ?: '-';
    //         // kalau mau wilayah juga:
    //         // $wilayahLabel = $tokoDb?->area?->wilayah?->nama_wilayah ?: '-';
    //         $totalKontribusi = (int) $kontribusi + (int) $discSelisihRp + (int) $returSelisihRp + (int) $gasSelisihRp + (int) $telurSelisihRp + (int) $lossBahan;

    //         $rows[] = [
    //             'area_label' => $areaLabel,
    //             'area_pic' => $areaPic, // ✅ tambah ini
    //             'outlet' => $outlet,
    //             'neto' => $neto,

    //             'selisih_rp' => $selisihRp,
    //             'selisih_persen' => $selisihPersen,

    //             'kontribusi_rp' => $kontribusi,
    //             'kontribusi_persen' => null,

    //             'sc_manual_rp' => $discSelisihRp,
    //             'sc_manual_persen' => $discPersenBySales,

    //             'retur_rp' => $returRp,
    //             'retur_persen' => $returPersen,
    //             'gas_rp' => $gasSelisihRp,
    //             'gas_persen' => $gasPersenBySales,

    //             'telur_rp' => $telurSelisihRp,
    //             'telur_persen' => $telurPersenBySales,

    //             'loss_bahan' => $lossBahan,
    //             'total_kontribusi' => $totalKontribusi,
    //         ];
    //     }

    //     if ($grand != 0) {
    //         foreach ($rows as &$r) {
    //             $r['kontribusi_persen'] = round(((float) ($r['kontribusi_rp'] ?? 0) / (float) $grand) * 100, 2);
    //         }
    //         unset($r);
    //     }
    //     $rows = collect($rows)
    //         ->sort(function ($a, $b) {
    //             $aa = strcasecmp($a['area_label'] ?? '', $b['area_label'] ?? '');
    //             if ($aa !== 0) {
    //                 return $aa;
    //             }
    //             return strcasecmp($a['outlet'] ?? '', $b['outlet'] ?? '');
    //         })
    //         ->values()
    //         ->all();

    //     $this->totalsByArea = collect($rows)
    //         ->groupBy(fn($r) => $r['area_label'] ?? '-')
    //         ->map(function ($g) {
    //             return [
    //                 'selisih_rp' => $g->sum(fn($r) => (int) ($r['selisih_rp'] ?? 0)),
    //                 'kontribusi_rp' => $g->sum(fn($r) => (int) ($r['kontribusi_rp'] ?? 0)),
    //                 'sc_manual_rp' => $g->sum(fn($r) => (int) ($r['sc_manual_rp'] ?? 0)),
    //                 'retur_rp' => $g->sum(fn($r) => (int) ($r['retur_rp'] ?? 0)),
    //                 'gas_rp' => $g->sum(fn($r) => (int) ($r['gas_rp'] ?? 0)),
    //                 'telur_rp' => $g->sum(fn($r) => (int) ($r['telur_rp'] ?? 0)),
    //                 'loss_bahan' => $g->sum(fn($r) => (int) ($r['loss_bahan'] ?? 0)),
    //                 'total_kontribusi' => $g->sum(fn($r) => (int) ($r['total_kontribusi'] ?? 0)),
    //             ];
    //         })
    //         ->toArray();

    //     $this->grandTotals = [
    //         'selisih_rp' => collect($rows)->sum(fn($r) => (int) ($r['selisih_rp'] ?? 0)),
    //         'kontribusi_rp' => collect($rows)->sum(fn($r) => (int) ($r['kontribusi_rp'] ?? 0)),
    //         'sc_manual_rp' => collect($rows)->sum(fn($r) => (int) ($r['sc_manual_rp'] ?? 0)),
    //         'retur_rp' => collect($rows)->sum(fn($r) => (int) ($r['retur_rp'] ?? 0)),
    //         'gas_rp' => collect($rows)->sum(fn($r) => (int) ($r['gas_rp'] ?? 0)),
    //         'telur_rp' => collect($rows)->sum(fn($r) => (int) ($r['telur_rp'] ?? 0)),
    //         'loss_bahan' => collect($rows)->sum(fn($r) => (int) ($r['loss_bahan'] ?? 0)),
    //         'total_kontribusi' => collect($rows)->sum(fn($r) => (int) ($r['total_kontribusi'] ?? 0)),
    //     ];

    //     $this->rowsTarget = $rows;
    //     $this->sumNetoTarget = $grand;
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

    $mapProyeksi = $this->proyeksiMap($start, $end);

    $targets = TargetKontribusi::query()
        ->where('aktif', 1)
        ->whereIn('kode', ['DISC_MANUAL', 'GAS', 'TELUR', 'RETUR'])
        ->get()
        ->keyBy('kode');

    $targetDisc  = $targets->get('DISC_MANUAL');
    $targetGas   = $targets->get('GAS');
    $targetTelur = $targets->get('TELUR');
    $targetRetur = $targets->get('RETUR');

    // ✅ kumpulkan sekali
    $apiIds  = collect($this->tokosUser)->pluck('api_id')->map(fn($v)=>trim((string)$v))->filter()->unique()->values()->all();
    $tokoIds = collect($this->tokosUser)->pluck('id')->map(fn($v)=>(int)$v)->filter()->unique()->values()->all();

    // ✅ toko lokal + area
    $tokoLocal = \App\Models\MasterToko::query()
        ->with(['area'])
        ->whereIn('id', $tokoIds)
        ->get()
        ->keyBy('id');

    // ✅ PIC AREA map
    $picAreaByAreaId = \App\Models\User::query()
        ->select('name', 'area_id')
        ->whereNotNull('area_id')
        ->whereRaw("LOWER(TRIM(role)) = 'area'")
        ->orderBy('name')
        ->get()
        ->groupBy('area_id')
        ->map(fn($g) => $g->pluck('name')->filter()->implode(', '))
        ->toArray();

    // ✅ API batch
    $penjualanById = $this->fetchPenjualanMany($apiIds, $start, $end);

    // ✅ BIAYA: fetch ALL sekali lalu map by idcab
    $biayaById = $this->fetchBiayaByIdcabMap($apiIds, $start, $end);

    // ✅ RETUR: fetch all sekali lalu map idcab
    $returAll = $this->fetchReturAll($start, $end);
    $returMap = $this->mapReturByIdcab($returAll);

    // ✅ LOSS
    $lossMap = $this->lossBahanMap($tokoIds, $start, $end);

    $rows  = [];
    $grand = 0;

    foreach ($this->tokosUser as $t) {
        $isProduksi = ((int)($t['produksi_sendiri'] ?? 0)) === 1;

        $apiId  = trim((string)($t['api_id'] ?? ''));
        $outlet = $t['nmtoko'] ?? '-';
        $tokoId = (int)($t['id'] ?? 0);

        // 1) penjualan
        $neto   = 0;
        $hrgApi = 0;

        if ($apiId !== '') {
            $data = $penjualanById[$apiId] ?? [];
            $col  = collect($data)->where('idcabang', $apiId);
            if ($isSingleDate) $col = $col->where('nhari', $nhariSingle);

            $neto   = (int) $col->sum(fn($r) => (int)($r['neto'] ?? 0));
            $hrgApi = (int) $col->sum(fn($r) => (int)($r['hrg'] ?? 0));
        }

        // 2) target proyeksi
        $targetRp      = (int)($mapProyeksi[$tokoId] ?? 0);
        $selisihRp     = $hrgApi - $targetRp;
        $selisihPersen = $targetRp > 0 ? round(($selisihRp / $targetRp) * 100, 2) : null;

        $kontribusi = (int) round($selisihRp * (1 - $this->hppRatio));
        $grand += $kontribusi;

        // 3) biaya real dari map by idcab
        $discRealRp  = (int)($biayaById[$apiId]['disc']  ?? 0);
        $gasRealRp   = (int)($biayaById[$apiId]['gas']   ?? 0);
        $telurRealRp = (int)($biayaById[$apiId]['telur'] ?? 0);

        // 4) selisih vs target
        $discTargetRp = $this->targetToRp($targetDisc, $isProduksi, $hrgApi);
        [$discSelisihRp] = $this->selisihTargetReal($discTargetRp, $discRealRp, $hrgApi);
        $discPersenBySales = $this->persenDariRp($discSelisihRp, $hrgApi);

        $gasTargetRp = $this->targetToRp($targetGas, $isProduksi, $hrgApi);
        [$gasSelisihRp] = $this->selisihTargetReal($gasTargetRp, $gasRealRp, $hrgApi);
        $gasPersenBySales = $this->persenDariRp($gasSelisihRp, $hrgApi);

        $telurTargetRp = $this->targetToRp($targetTelur, $isProduksi, $hrgApi);
        [$telurSelisihRp] = $this->selisihTargetReal($telurTargetRp, $telurRealRp, $hrgApi);
        $telurPersenBySales = $this->persenDariRp($telurSelisihRp, $hrgApi);

        // 5) loss
        $lossBahan = (int)($lossMap[$tokoId] ?? 0);

        // 6) retur real by idcab
        $returRealRp = $apiId !== '' ? (int)($returMap[$apiId] ?? 0) : 0;
        $returTargetRp = $this->targetToRp($targetRetur, $isProduksi, $hrgApi);
        [$returSelisihRp] = $this->selisihTargetReal($returTargetRp, $returRealRp, $hrgApi);
        $returPersen = $this->persenDariRp($returSelisihRp, $hrgApi);

        // 7) area + pic
        $tokoDb    = $tokoLocal[$tokoId] ?? null;
        $areaId    = (int)($tokoDb?->area_id ?? 0);
        $areaLabel = $tokoDb?->area?->nama_area ?: '-';
        $areaPic   = $areaId > 0 ? ($picAreaByAreaId[$areaId] ?? '') : '';

        $totalKontribusi =
            (int)$kontribusi
            + (int)$discSelisihRp
            + (int)$returSelisihRp
            + (int)$gasSelisihRp
            + (int)$telurSelisihRp
            + (int)$lossBahan;

        $rows[] = [
            'area_label' => $areaLabel,
            'area_pic'   => $areaPic,
            'outlet'     => $outlet,
            'neto'       => $neto,

            'selisih_rp'     => $selisihRp,
            'selisih_persen' => $selisihPersen,

            'kontribusi_rp'     => $kontribusi,
            'kontribusi_persen' => null,

            'sc_manual_rp'     => $discSelisihRp,
            'sc_manual_persen' => $discPersenBySales,

            'retur_rp'     => $returSelisihRp,
            'retur_persen' => $returPersen,

            'gas_rp'     => $gasSelisihRp,
            'gas_persen' => $gasPersenBySales,

            'telur_rp'     => $telurSelisihRp,
            'telur_persen' => $telurPersenBySales,

            'loss_bahan'       => $lossBahan,
            'total_kontribusi' => $totalKontribusi,
        ];
    }

    if ($grand != 0) {
        foreach ($rows as &$r) {
            $r['kontribusi_persen'] = round(((float)($r['kontribusi_rp'] ?? 0) / (float)$grand) * 100, 2);
        }
        unset($r);
    }

    $rows = collect($rows)
        ->sort(function($a, $b) {
            $aa = strcasecmp($a['area_label'] ?? '', $b['area_label'] ?? '');
            if ($aa !== 0) return $aa;
            return strcasecmp($a['outlet'] ?? '', $b['outlet'] ?? '');
        })
        ->values()
        ->all();

    $this->totalsByArea = collect($rows)
        ->groupBy(fn($r) => $r['area_label'] ?? '-')
        ->map(function ($g) {
            return [
                'selisih_rp'       => $g->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
                'kontribusi_rp'    => $g->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
                'sc_manual_rp'     => $g->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
                'retur_rp'         => $g->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
                'gas_rp'           => $g->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
                'telur_rp'         => $g->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
                'loss_bahan'       => $g->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
                'total_kontribusi' => $g->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
            ];
        })
        ->toArray();

    $this->grandTotals = [
        'selisih_rp'       => collect($rows)->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
        'kontribusi_rp'    => collect($rows)->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
        'sc_manual_rp'     => collect($rows)->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
        'retur_rp'         => collect($rows)->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
        'gas_rp'           => collect($rows)->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
        'telur_rp'         => collect($rows)->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
        'loss_bahan'       => collect($rows)->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
        'total_kontribusi' => collect($rows)->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
    ];

    $this->rowsTarget    = $rows;
    $this->sumNetoTarget = $grand;
}

    public function resetTarget()
    {
        $this->rowsTarget = [];
        $this->sumNetoTarget = 0;
        $this->tanggalAwal = now()->toDateString();
        $this->tanggalAkhir = now()->toDateString();
    }

    public function render()
    {
        return view('livewire.operasional.kontribusi-target');
    }
}
