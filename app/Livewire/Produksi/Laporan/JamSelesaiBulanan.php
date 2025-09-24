<?php

namespace App\Livewire\Produksi\Laporan;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// === SESUAIKAN namespace model dengan punyamu ===
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\MsJobs;
use App\Models\Produksi\InputSelesai;

class JamSelesaiBulanan extends Component
{
    /** contoh: '2025-08' dari <input type="month"> */
    public string $periode;

    /** header hari di tabel: [1,2,...,N] */
    public array $days = [];

    /** rows tabel: tiap grup job 1 baris, cell = selisih menit */
    public array $rows = [];

    /** job yang targetnya perlu dibagi tongbesar & patokan */
    public array $jobYangDibagiTongBesar = ['PECAH TELUR', 'GILING'];

    public function mount(): void
    {
        $this->periode = now()->format('Y-m');
        $this->buildDays();
        $this->cariData();
    }

    public function updatedPeriode(): void
    {
        $this->buildDays();
        $this->cariData();
    }

    private function buildDays(): void
    {
        $base = Carbon::createFromFormat('Y-m', $this->periode) ?: now();
        $this->days = range(1, $base->daysInMonth);
    }

    public function render()
    {
        return view('livewire.produksi.laporan.jam-selesai-bulanan', [
            'rows' => $this->rows,
        ]);
    }

    /** (opsional) isi sesuai kebutuhan ekspor */
    public function export(): void
    {
        // TODO: generate excel/csv — sementara biarkan kosong.
        // Bisa taruh flash/alert kalau mau.
    }

    /** Bangun data selisih menit per hari per grup job untuk 1 bulan */
    public function cariData(): void
    {
        $this->rows = [];

        $start = Carbon::createFromFormat('Y-m', $this->periode) ?: now();
        $start = $start->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $daysInMonth = (int) $start->daysInMonth;

        // 1) Ambil Perintah Produksi bulan ini
        $perintah = Perintah_Produksi::whereBetween('tanggal_perintah', [$start->toDateString(), $end->toDateString()])->get(['id', 'tanggal_perintah']);

        if ($perintah->isEmpty()) {
            $this->rows = [];
            return;
        }

        $perintahDateMap = $perintah->pluck('tanggal_perintah', 'id')->toArray();
        $perintahIds = array_keys($perintahDateMap);

        // 2) Akumulasi target per (hari, produk)
        $productTotalsByDay = []; // [day][product_id] => total target

        $details = Detail_Perintah_Produksi::whereIn('perintah_produksi_id', $perintahIds)->get(['perintah_produksi_id', 'mproducts_id', 'target_produksi']);
        foreach ($details as $d) {
            $tgl = Carbon::make($perintahDateMap[$d->perintah_produksi_id] ?? null);
            if (!$tgl) {
                continue;
            }
            $day = (int) $tgl->day;
            $pid = (int) $d->mproducts_id;
            $productTotalsByDay[$day][$pid] = ($productTotalsByDay[$day][$pid] ?? 0) + (float) $d->target_produksi;
        }

        $tambahans = Produksi_Tambahan::whereIn('perintah_produksi_id', $perintahIds)->get(['perintah_produksi_id', 'mproducts_id', 'target_qty_tambahan']);
        foreach ($tambahans as $t) {
            $tgl = Carbon::make($perintahDateMap[$t->perintah_produksi_id] ?? null);
            if (!$tgl) {
                continue;
            }
            $day = (int) $tgl->day;
            $pid = (int) $t->mproducts_id;
            $productTotalsByDay[$day][$pid] = ($productTotalsByDay[$day][$pid] ?? 0) + (float) $t->target_qty_tambahan;
        }

        // 3) Metadata produk
        $meta = MasterProduct::select('id', 'tongbesar', 'patokan')->get()->keyBy('id');
        $tongbesar = $meta->pluck('tongbesar', 'id')->toArray();
        $patokan = $meta->pluck('patokan', 'id')->toArray();

        // 4) Mapping produk -> jobs
        $productJobMap = [];
        foreach (DB::table('msproduct_jobs')->select('msproducts_id', 'msjobs_id')->get() as $r) {
            $productJobMap[(int) $r->msproducts_id][] = (int) $r->msjobs_id;
        }

        // 5) Jobs
        $jobs = MsJobs::select('id', 'nama_job', 'group_job', 'unit', 'target', 'jam_mulai')->get()->keyBy('id');

        // 6) jml_orang cache per hari (senin,selasa,..) untuk semua job
        $jmlCache = []; // [hari] => [job_id => jml_orang]
        $getJml = function (string $hari) use (&$jmlCache) {
            if (!isset($jmlCache[$hari])) {
                $map = MsJobs::withSum(['jadwalDivisi as jml_orang' => fn($q) => $q->where('hari', $hari)], 'jumlah')
                    ->get()
                    ->pluck('jml_orang', 'id')
                    ->map(fn($v) => (int) ($v ?? 0))
                    ->toArray();
                $jmlCache[$hari] = $map;
            }
            return $jmlCache[$hari];
        };

        // 7) Actual (InputSelesai) ⇒ ambil MAX per (hari, job)
        $actualByDayJob = []; // [day][job_id] => Carbon
        $isRows = InputSelesai::whereIn('perintah_produksi_id', $perintahIds)->orderByDesc('id')->get();
        foreach ($isRows as $s) {
            $tgl = Carbon::make($perintahDateMap[$s->perintah_produksi_id] ?? null);
            if (!$tgl) {
                continue;
            }
            $day = (int) $tgl->day;
            $jid = (int) $s->msjobs_id;
            $ts = Carbon::make($s->waktu_selesai);
            if (!$ts) {
                continue;
            }

            $prev = $actualByDayJob[$day][$jid] ?? null;
            if (!($prev instanceof Carbon) || $ts->gt($prev)) {
                $actualByDayJob[$day][$jid] = $ts;
            }
        }

        // 8) Agregasi per grup per hari (planned MAX & actual MAX)
        $groupAggByDay = []; // [day][group] => ['planned'=>Carbon|null,'actual'=>Carbon|null]

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $start->copy()->day($day);
            $hari = strtolower($date->locale('id')->isoFormat('dddd'));
            $jmlMap = $getJml($hari);

            foreach ($jobs as $jobId => $job) {
                $groupName = $job->group_job ?? ($job->nama_job ?? '-');
                $targetPerOrang = (float) ($job->target ?? 0);
                $jamMulaiStr = trim((string) ($job->jam_mulai ?? '00:00')) ?: '00:00';
                $jmlOrang = (int) ($jmlMap[$jobId] ?? 0);

                // produksi hari ini untuk job tsb
                $produksi = 0.0;
                foreach ($productTotalsByDay[$day] ?? [] as $pid => $totalTarget) {
                    $jobIds = $productJobMap[$pid] ?? [];
                    if (!in_array($jobId, $jobIds, true)) {
                        continue;
                    }

                    $val = (float) $totalTarget;
                    $nameUp = strtoupper($job->nama_job ?? '');
                    if (in_array($nameUp, $this->jobYangDibagiTongBesar, true)) {
                        $val = $val / max((float) ($tongbesar[$pid] ?? 1), 1) / max((float) ($patokan[$pid] ?? 1), 1);
                    }
                    $produksi += $val;
                }

                $rasio = $targetPerOrang > 0 && $jmlOrang > 0 ? $produksi / ($targetPerOrang * $jmlOrang) : 0.0;
                $minutes = (int) round($rasio * 60.0);

                $jamMulai = Carbon::make($date->toDateString() . ' ' . $jamMulaiStr) ?? $date->copy()->startOfDay();
                $plannedTs = (clone $jamMulai)->addMinutes($minutes);

                $actualTs = $actualByDayJob[$day][$jobId] ?? null;

                $slot = $groupAggByDay[$day][$groupName] ?? ['planned' => null, 'actual' => null];

                // planned MAX
                if ($plannedTs instanceof Carbon) {
                    if (!($slot['planned'] instanceof Carbon) || $plannedTs->gt($slot['planned'])) {
                        $slot['planned'] = $plannedTs;
                    }
                }
                // actual MAX
                if ($actualTs instanceof Carbon) {
                    if (!($slot['actual'] instanceof Carbon) || $actualTs->gt($slot['actual'])) {
                        $slot['actual'] = $actualTs;
                    }
                }

                $groupAggByDay[$day][$groupName] = $slot;
            }
        }

        // 9) Susun rows: satu baris per grup, kolom hari berisi selisih (menit)
        $allGroups = [];
        foreach ($groupAggByDay as $day => $groups) {
            foreach (array_keys($groups) as $g) {
                $allGroups[$g] = true;
            }
        }
        $groupNames = array_keys($allGroups);
        sort($groupNames, SORT_NATURAL);

        $rows = [];
        $no = 1;
        foreach ($groupNames as $group) {
            $cells = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $agg = $groupAggByDay[$day][$group] ?? null;
                if (!$agg) {
                    continue;
                }
                $planned = $agg['planned'] ?? null;
                $actual = $agg['actual'] ?? null;

                if ($planned instanceof Carbon && $actual instanceof Carbon) {
                    // + = terlambat, - = lebih cepat
                    $cells[$day] = $planned->diffInMinutes($actual, false);
                }
            }

            $rows[] = [
                'no' => $no++,
                'produk' => $group, // label kolom kedua
                'days' => $cells, // map day => diff menit (signed)
            ];
        }

        $this->rows = $rows;
    }
}
