<?php

namespace App\Livewire\Produksi\Laporan;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// === SESUAIKAN NAMESPACE MODEL2 INI DENGAN PROYEKMU ===
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Detail_Perintah_Produksi;
use App\Models\Produksi\Produksi_Tambahan;
use App\Models\Produksi\Perintah_Produksi;
use App\Models\Produksi\MsJobs;
use App\Models\Produksi\InputSelesai;
class LaporanJamSelesai extends Component
{
    public string $tanggalProduksi;

    public array $produk = [];
    public array $metodeList = [];
    public array $metodeSummary = [];
    public array $jobSummary = [];
    public array $lapKetepatan = [];

    /** job yang targetnya perlu dibagi tongbesar & patokan */
    public array $jobYangDibagiTongBesar = ['PECAH TELUR', 'GILING'];

    public function mount(): void
    {
        // $this->tanggalProduksi = null;

        // opsional: isi metode list
        $this->metodeList = MasterProduct::query()->select('metode')->whereNotNull('metode')->distinct()->pluck('metode')->filter()->values()->toArray();

        $this->cariData();
    }

    public function updatedTanggalProduksi(): void
    {
        $this->cariData();
    }
    public function render()
    {
        return view('livewire.produksi.laporan.laporan-jam-selesai', [
            'lapKetepatan' => $this->lapKetepatan, // untuk tabel laporan
            // 'tanggalProduksi' => $this->tanggalProduksi,
        ]);
    }

    public function cariData(): void
    {
        // === 1) Ambil semua produk beserta detail+tambahan & tanggal perintahnya (TANPA filter tanggal)
        $produkCollection = MasterProduct::with(['detailPerintahProduksi.perintahProduksi:id,tanggal_perintah', 'produksiTambahan.perintahProduksi:id,tanggal_perintah'])->get();

        // metadata produk
        $productMeta = MasterProduct::select('id', 'tongbesar', 'patokan')->get()->keyBy('id');
        $tongBesar = $productMeta->pluck('tongbesar', 'id')->toArray();
        $patokan = $productMeta->pluck('patokan', 'id')->toArray();

        // mapping produk -> job
        $productJobMap = [];
        foreach (DB::table('msproduct_jobs')->select('msproducts_id', 'msjobs_id')->get() as $r) {
            $productJobMap[(int) $r->msproducts_id][] = (int) $r->msjobs_id;
        }

        // === 2) Bangun total target per (tanggal, produk)
        $productTotalsByDate = []; // [Y-m-d][product_id] => total_target
        $perintahDateMap = []; // perintah_produksi_id => Y-m-d

        foreach ($produkCollection as $prd) {
            $pid = (int) $prd->id;

            // detail (utama)
            foreach ($prd->detailPerintahProduksi as $d) {
                $tgl = Carbon::make($d->perintahProduksi?->tanggal_perintah)?->toDateString();
                if (!$tgl) {
                    continue;
                }
                $perintahDateMap[(int) $d->perintah_produksi_id] = $tgl;

                $productTotalsByDate[$tgl][$pid] = ($productTotalsByDate[$tgl][$pid] ?? 0) + (float) ($d->target_produksi ?? 0);
            }

            // tambahan
            foreach ($prd->produksiTambahan as $t) {
                $tgl = Carbon::make($t->perintahProduksi?->tanggal_perintah)?->toDateString();
                if (!$tgl) {
                    continue;
                }
                $perintahDateMap[(int) $t->perintah_produksi_id] = $tgl;

                $productTotalsByDate[$tgl][$pid] = ($productTotalsByDate[$tgl][$pid] ?? 0) + (float) ($t->target_qty_tambahan ?? 0);
            }
        }

        // === 3) Preload MsJobs & cache jml_orang per hari
        $jobs = MsJobs::select('id', 'nama_job', 'group_job', 'unit', 'target', 'jam_mulai')->get()->keyBy('id');

        $jmlOrangCache = []; // [hari] => [job_id => jml_orang]
        $getJmlOrangForHari = function (string $hari) use (&$jmlOrangCache) {
            if (!isset($jmlOrangCache[$hari])) {
                $map = MsJobs::withSum(['jadwalDivisi as jml_orang' => fn($q) => $q->where('hari', $hari)], 'jumlah')
                    ->get()
                    ->pluck('jml_orang', 'id')
                    ->map(fn($v) => (int) ($v ?? 0))
                    ->toArray();
                $jmlOrangCache[$hari] = $map;
            }
            return $jmlOrangCache[$hari];
        };

        // === 4) Preload actual (InputSelesai) → agregasi ke (tanggal, job) = waktu_selesai MAX
        $actualByDateJob = []; // [Y-m-d][job_id] => ['waktu' => Carbon, 'keterangan' => ?]
        if (!empty($perintahDateMap)) {
            $rows = InputSelesai::whereIn('perintah_produksi_id', array_keys($perintahDateMap))->orderByDesc('id')->get();

            foreach ($rows as $s) {
                $tgl = $perintahDateMap[(int) $s->perintah_produksi_id] ?? null;
                $job = (int) $s->msjobs_id;
                $ts = Carbon::make($s->waktu_selesai);
                if (!$tgl || !$ts) {
                    continue;
                }

                $prev = $actualByDateJob[$tgl][$job]['waktu'] ?? null;
                if (!($prev instanceof Carbon) || $ts->gt($prev)) {
                    $actualByDateJob[$tgl][$job] = [
                        'waktu' => $ts,
                        'keterangan' => $s->keterangan,
                    ];
                }
            }
        }

        // === 5) Hitung planned per (tanggal, job), lalu agregasi per grup (planned MAX, actual MAX)
        $groupAggByDate = []; // [Y-m-d][group_job] => ['planned' => Carbon|null, 'actual' => Carbon|null, 'ket'=>?]

        foreach ($productTotalsByDate as $tgl => $pidTotals) {
            $hari = strtolower((Carbon::make($tgl) ?? now())->locale('id')->isoFormat('dddd'));
            $jmlMap = $getJmlOrangForHari($hari); // [job_id => jml_orang]

            foreach ($jobs as $jobId => $job) {
                $groupName = $job->group_job ?? ($job->nama_job ?? '-');
                $targetPerOrang = (float) ($job->target ?? 0);
                $jamMulaiStr = trim((string) ($job->jam_mulai ?? '00:00')) ?: '00:00';
                $jmlOrang = (int) ($jmlMap[$jobId] ?? 0);

                // akumulasi target_produksi dari produk yang terkait job ini (di tanggal tsb)
                $produksi = 0.0;
                foreach ($pidTotals as $pid => $totalTarget) {
                    $jobIdsForProduct = $productJobMap[$pid] ?? [];
                    if (!in_array($jobId, $jobIdsForProduct, true)) {
                        continue;
                    }

                    $val = (float) $totalTarget;
                    $namaJobUp = strtoupper($job->nama_job ?? '');
                    if (in_array($namaJobUp, ['PECAH TELUR', 'GILING'], true)) {
                        $val = $val / max((float) ($tongBesar[$pid] ?? 1), 1) / max((float) ($patokan[$pid] ?? 1), 1);
                    }
                    $produksi += $val;
                }

                // planned ts (anchor ke tanggal)
                $rasio = $targetPerOrang > 0 && $jmlOrang > 0 ? $produksi / ($targetPerOrang * $jmlOrang) : 0.0;
                $totalMenit = (int) round($rasio * 60.0);

                $jamMulai = Carbon::make("$tgl $jamMulaiStr") ?? (Carbon::make($tgl)?->startOfDay() ?? now()->startOfDay());
                $plannedTs = (clone $jamMulai)->addMinutes($totalMenit); // Carbon

                // actual ts (kalau ada)
                $actualTs = $actualByDateJob[$tgl][$jobId]['waktu'] ?? null;
                $ket = $actualByDateJob[$tgl][$jobId]['keterangan'] ?? null;

                // agregasi per grup (MAX untuk planned & actual)
                $slot = $groupAggByDate[$tgl][$groupName] ?? ['planned' => null, 'actual' => null, 'keterangan' => null];

                if ($plannedTs instanceof Carbon) {
                    if (!($slot['planned'] instanceof Carbon) || $plannedTs->gt($slot['planned'])) {
                        $slot['planned'] = $plannedTs;
                    }
                }
                if ($actualTs instanceof Carbon) {
                    if (!($slot['actual'] instanceof Carbon) || $actualTs->gt($slot['actual'])) {
                        $slot['actual'] = $actualTs;
                        $slot['keterangan'] = $ket;
                    }
                }

                $groupAggByDate[$tgl][$groupName] = $slot;
            }
        }

        // === 6) Susun rows laporan
        $rows = [];
        foreach ($groupAggByDate as $tgl => $groups) {
            foreach ($groups as $grp => $agg) {
                $planned = $agg['planned'];
                $actual = $agg['actual'];

                $selisih = null;
                $status = null;
                if ($planned instanceof Carbon && $actual instanceof Carbon) {
                    $selisih = $planned->diffInMinutes($actual, false); // + terlambat, - lebih cepat
                    $status = $selisih > 0 ? 'Terlambat' : ($selisih < 0 ? 'Lebih Cepat' : 'Tepat Waktu');
                }

                $rows[] = [
                    'tanggal_key'   => $tgl,                     // Y-m-d (untuk sort/group)
                    'tanggal_label' => \Carbon\Carbon::make($tgl)?->format('d-m-Y'),
                    'tanggal' => $tgl,
                    'kategori_job' => $grp,
                    'planned' => $planned instanceof Carbon ? $planned->format('H:i') : null,
                    'actual' => $actual instanceof Carbon ? $actual->format('H:i') : null,
                    'selisih_menit' => $selisih,
                    'status' => $status,
                    'keterangan' => $agg['keterangan'] ?? null,
                ];
            }
        }

        // urutkan: tanggal ASC, lalu grup ASC
        usort($rows, function ($a, $b) {
            $ak = $a['tanggal_key'] ?? '';
            $bk = $b['tanggal_key'] ?? '';
            $cmp = strcmp($bk, $ak);   // DESC
            return $cmp !== 0 ? $cmp : strcmp($a['kategori_job'] ?? '', $b['kategori_job'] ?? '');
        });

        $this->lapKetepatan = $rows;
    }
}
