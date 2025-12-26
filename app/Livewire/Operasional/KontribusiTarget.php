<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJobRow;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\MasterTrendInflasi;
use App\Models\Operasional\TargetKontribusi;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Operasional\LossBahan;

class KontribusiTarget extends Component
{
    public array $tokosUser = [];

    public $periodeAwal;
    public $periodeAkhir;



    // ✅ hanya simpan key, hasil besar di cache
    public ?string $resultKey = null;

    public function mount(array $tokosUser = [], $periodeAwal = null, $periodeAkhir = null)
    {
        $this->tokosUser = $tokosUser;
        $this->periodeAwal  = $periodeAwal ?? now()->toDateString();
        $this->periodeAkhir = $periodeAkhir ?? now()->toDateString();
    }



    public function render()
    {
        $data = $this->resultKey ? Cache::get($this->resultKey) : null;

        return view('livewire.operasional.kontribusi-target', [
            'rowsByTargetView' => $data['rows'] ?? [],
        'grandTotalsView'  => $data['grandTotals'] ?? [],
        ]);
    }

    public function resetByTarget()
    {
        $this->resultKey = null;

        $this->periodeAwal  = now()->toDateString();
        $this->periodeAkhir = now()->toDateString();
    }

    private function pctVal($v): ?float
    {
        if (is_null($v)) return null;
        if (is_string($v)) {
            $v = trim(str_replace('%', '', $v));
            if ($v === '' || $v === '-') return null;
        }
        return is_numeric($v) ? (float)$v : null;
    }

    /**
     * ambil latest row per toko per tanggal dari DB
     * return: [toko_id => [payload,payload,...]] (list payload per hari)
     */
/**
 * Ambil latest row per toko per tanggal (BY TARGET) dari snapshot DB.
 * Return:
 *   [
 *     toko_id => [
 *        ['tgl'=>..., 'hrg'=>..., 'selisih_rp'=>..., 'disc_rp'=>..., ...],
 *        ...
 *     ],
 *     ...
 *   ]
 */
private function fetchDailyPayloads(array $tokoIds, string $start, string $end): array
{
    $tokoIds = array_values(array_unique(array_filter(array_map('intval', $tokoIds))));
    if (empty($tokoIds)) return [];

    $cacheKey = 'kontribusi_target:daily_payloads:v2:' . md5(json_encode($tokoIds) . "|$start|$end");

    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tokoIds, $start, $end) {

        $rows = KontribusiHarianJobRow::query()
            ->select([
                'id', 'job_id', 'tanggal', 'jenis',
                'selisih_rp', 'kontribusi_rp',
                'disc_rp', 'retur_rp', 'gas_rp', 'telur_rp',
                'loss_bahan', 'total_kontribusi',
                'payload', // ✅ ambil sales_now dari sini
            ])
            ->whereBetween('tanggal', [$start, $end])
            ->where('jenis', 'BY TARGET')
            ->whereHas('job', function ($q) use ($tokoIds) {
                $q->whereIn('toko_id', $tokoIds)
                  ->where('status', 'ok');
            })
            ->with(['job:id,toko_id'])
            ->orderBy('tanggal', 'asc')
            ->orderByDesc('id') // ✅ id besar = snapshot terbaru
            ->get();

        // dedup latest per (toko|tgl)
        $picked = []; // [ "toko|tgl" => payloadArray ]
        foreach ($rows as $r) {
            $tokoId = (int) ($r->job?->toko_id ?? 0);
            $tgl    = (string) ($r->tanggal ?? '');

            if ($tokoId <= 0 || $tgl === '') continue;

            $k = $tokoId . '|' . $tgl;
            if (isset($picked[$k])) continue; // sudah ambil yang paling baru (id desc)

            $p = is_array($r->payload) ? $r->payload : (array) $r->payload;

            // ✅ basis sales (wajib utk hitung % weighted)
            $hrg = (int) (
                $p['sales_now']
                ?? $p['sales']
                ?? $p['hrg']
                ?? 0
            );

            $picked[$k] = [
                'tgl' => $tgl,
                'hrg' => $hrg,

                'selisih_rp'    => (int) ($r->selisih_rp ?? 0),
                'kontribusi_rp' => (int) ($r->kontribusi_rp ?? 0),

                'disc_rp'  => (int) ($r->disc_rp ?? 0),
                'retur_rp' => (int) ($r->retur_rp ?? 0),
                'gas_rp'   => (int) ($r->gas_rp ?? 0),
                'telur_rp' => (int) ($r->telur_rp ?? 0),

                'loss_bahan'       => (int) ($r->loss_bahan ?? 0),
                'total_kontribusi' => (int) ($r->total_kontribusi ?? 0),
            ];
        }

        // group by toko_id
        $out = []; // [toko_id => [payload,payload,...]]
        foreach ($picked as $k => $payload) {
            [$tokoIdStr] = explode('|', $k, 2);
            $tokoId = (int) $tokoIdStr;

            $out[$tokoId] ??= [];
            $out[$tokoId][] = $payload;
        }

        // optional: sort payload per toko by tanggal asc (biar stabil)
        foreach ($out as $tid => $list) {
            usort($list, fn($a, $b) => strcmp($a['tgl'] ?? '', $b['tgl'] ?? ''));
            $out[$tid] = $list;
        }

        return $out;
    });
}




private function aggPayloads(array $payloads): array
{
    $c = collect($payloads);

    $sumHrg = (int) $c->sum('hrg');
    $sumSel = (int) $c->sum('selisih_rp');
    $baseline = $sumHrg - $sumSel;

    $pct = fn(int $num, int $den) => $den > 0 ? round(($num / $den) * 100, 2) : null;

    $sumDisc  = (int) $c->sum('disc_rp');
    $sumRetur = (int) $c->sum('retur_rp');
    $sumGas   = (int) $c->sum('gas_rp');
    $sumTelur = (int) $c->sum('telur_rp');

    return [
        'hrg' => $sumHrg,
        'baseline' => $baseline,

        'selisih_rp'     => $sumSel,
        'selisih_persen' => $pct($sumSel, $baseline),

        'kontribusi_rp'  => (int) $c->sum('kontribusi_rp'),

        'disc_rp'        => $sumDisc,
        'disc_persen'    => $pct($sumDisc, $sumHrg),

        'retur_rp'       => $sumRetur,
        'retur_persen'   => $pct($sumRetur, $sumHrg),

        'gas_rp'         => $sumGas,
        'gas_persen'     => $pct($sumGas, $sumHrg),

        'telur_rp'       => $sumTelur,
        'telur_persen'   => $pct($sumTelur, $sumHrg),

        'loss_bahan'       => (int) $c->sum('loss_bahan'),
        'total_kontribusi' => (int) $c->sum('total_kontribusi'),
    ];
}


    private function makeResultKey(string $start, string $end, array $tokoIds): string
    {
        sort($tokoIds);
        $userId = (int) Auth::id();

        return 'kontribusi_bulan_lalu:result:v1:' . md5($userId . '|' . $start . '|' . $end . '|' . implode(',', $tokoIds));
    }
    private function fetchLossBahanByToko(array $tokoIds, string $start, string $end): array
    {
        $tokoIds = array_values(array_unique(array_filter(array_map('intval', $tokoIds))));
        if (empty($tokoIds)) return [];

        $cacheKey = 'loss_bahan:sum:v1:' . md5(json_encode($tokoIds) . "|$start|$end");

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tokoIds, $start, $end) {
            return LossBahan::query()
                ->selectRaw('toko_id, SUM(nominal) as total')
                ->whereBetween('tanggal', [$start, $end])
                ->whereIn('toko_id', $tokoIds)
                ->groupBy('toko_id')
                ->pluck('total', 'toko_id')
                ->map(fn($v) => (int) $v)
                ->toArray();
        });
    }
    public function loadByTarget()
    {
        $this->validate([
            'periodeAwal'  => 'required|date',
            'periodeAkhir' => 'required|date|after_or_equal:periodeAwal',
        ]);



        $start = Carbon::parse($this->periodeAwal)->toDateString();
        $end   = Carbon::parse($this->periodeAkhir)->toDateString();

        $tokoIdsRaw = collect($this->tokosUser)
            ->pluck('id')
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($tokoIdsRaw)) {
            $this->resultKey = null;
            return;
        }

        // ✅ FILTER TOKO AKTIF (status = 1)
        $tokoLocal = MasterToko::query()
            ->with(['area.wilayah'])
            ->whereIn('id', $tokoIdsRaw)
            ->where('status', "1")
            ->get()
            ->keyBy('id');

        $tokoIds = $tokoLocal->keys()->map(fn($v) => (int)$v)->values()->all();
        if (empty($tokoIds)) {
            $this->resultKey = null;
            return;
        }

        $picAreaByAreaId = User::query()
            ->select('name', 'area_id')
            ->whereNotNull('area_id')
            ->whereRaw("LOWER(TRIM(role)) = 'area'")
            ->orderBy('name')
            ->get()
            ->groupBy('area_id')
            ->map(fn($g) => $g->pluck('name')->filter()->implode(', '))
            ->toArray();


        $dailyByToko = $this->fetchDailyPayloads($tokoIds, $start, $end);
        $lossByToko  = $this->fetchLossBahanByToko($tokoIds, $start, $end);
        $rows = [];
        foreach ($tokoIds as $tokoId) {
            $tokoDb = $tokoLocal[$tokoId] ?? null;
            if (!$tokoDb) continue;

            $payloads = $dailyByToko[$tokoId] ?? [];
            if (empty($payloads)) continue;

            $agg  = $this->aggPayloads($payloads);
            $loss = (int) ($lossByToko[$tokoId] ?? 0); // ✅ ini yang benar (index = tokoId)

            $areaId = (int)($tokoDb->area_id ?? 0);
            $areaLabel = $tokoDb->area?->nama_area ?: '-';
            $areaPic = $areaId > 0 ? ($picAreaByAreaId[$areaId] ?? '') : '';

            $wilayahLabel = $tokoDb->area?->wilayah?->nama_wilayah
                ?: ($tokoDb->area?->wilayah_id ? 'WILAYAH-' . $tokoDb->area->wilayah_id : '-');

            $rows[] = [
                'wilayah_label' => $wilayahLabel,
                'area_label'    => $areaLabel,
                'area_pic'      => $areaPic,
                'outlet'        => (string)($tokoDb->nmtoko ?? '-'),

                'selisih_persen' => $agg['selisih_persen'],
                'selisih_rp'     => $agg['selisih_rp'],
                'kontribusi_rp'  => $agg['kontribusi_rp'],

                'sc_manual_persen' => $agg['disc_persen'],
                'sc_manual_rp'     => $agg['disc_rp'],

                'retur_persen' => $agg['retur_persen'],
                'retur_rp'     => $agg['retur_rp'],

                'gas_persen' => $agg['gas_persen'],
                'gas_rp'     => $agg['gas_rp'],

                'telur_persen' => $agg['telur_persen'],
                'telur_rp'     => $agg['telur_rp'],

                // ✅ loss dari tabel loss_bahans
                'loss_bahan' => $loss,

                // kalau loss itu pengurang, ubah jadi minus
                'total_kontribusi' => (int)($agg['total_kontribusi'] ?? 0) - $loss,
            ];
        }

        $rows = collect($rows)
            ->sortBy([
                ['wilayah_label', 'asc'],
                ['area_label', 'asc'],
                ['outlet', 'asc'],
            ])
            ->values()
            ->all();

        $avgPct = function (string $key) use ($rows): ?float {
            $vals = collect($rows)
                ->map(fn($r) => $this->pctVal($r[$key] ?? null))
                ->filter(fn($v) => !is_null($v))
                ->values();

            if ($vals->isEmpty()) return null;
            return round((float)$vals->avg(), 2);
        };

        $grandTotals = [
            'selisih_persen' => $avgPct('selisih_persen'),
            'selisih_rp' => (int) collect($rows)->sum('selisih_rp'),
            'kontribusi_rp' => (int) collect($rows)->sum('kontribusi_rp'),

            'sc_manual_persen' => $avgPct('sc_manual_persen'),
            'sc_manual_rp' => (int) collect($rows)->sum('sc_manual_rp'),

            'retur_persen' => $avgPct('retur_persen'),
            'retur_rp' => (int) collect($rows)->sum('retur_rp'),

            'gas_persen' => $avgPct('gas_persen'),
            'gas_rp' => (int) collect($rows)->sum('gas_rp'),

            'telur_persen' => $avgPct('telur_persen'),
            'telur_rp' => (int) collect($rows)->sum('telur_rp'),

            'loss_bahan' => (int) collect($rows)->sum('loss_bahan'),
            'total_kontribusi' => (int) collect($rows)->sum('total_kontribusi'),
        ];

        // ✅ simpan ke cache (anti corrupt data hydrate)
        $key = $this->makeResultKey($start, $end, $tokoIds);

        Cache::put($key, [
            'rows'        => $rows,
            'grandTotals'=> $grandTotals,
        ], now()->addMinutes(10));

        $this->resultKey = $key;
    }
}
