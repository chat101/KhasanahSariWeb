<?php

namespace App\Livewire\Produksi;

use App\Models\Produksi\MsJobs;
use App\Models\Produksi\MasterProduct;
use App\Models\Produksi\Produksi_Pengurangan;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\TargetProduksiViewExport;
use App\Exports\AnalisaArrayExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Produktifitas;
use App\Models\Produksi\InputSelesai; // ganti jika nama modelmu beda

// use Illuminate\Support\Carbon;
class Produktivitas extends Component
{
    public $produk;
    public $tanggalProduksi;
    public $produkList = [];
    public $metode = [];
    public $msjobs = [];
    public $metodeList = [];
    public $metodeSummary = [];
    public $jobSummary = [];
    public int $shiftMinutes = 480; // 8 jam = 480 menit (ubah kalau shift berbeda)
    public array $tableRows = [];
    public int $metodeTotalTong = 0;
    public int $metodeTotalPcs = 0;

    public function mount()
    {
        $this->msjobs = MsJobs::select('nama_job', 'jml_orang', 'target', 'unit', 'jam_mulai')->get()->toArray();
        $this->produkList = MasterProduct::select('id', 'nama')->get()->toArray(); // hanya nama produk
        $this->metodeList = MasterProduct::select('metode')->distinct()->whereNotNull('metode')->pluck('metode')->toArray();
        // dd(  $this->msjobs);
        $this->produk = []; // kosong dulu
        // kosong dulu
        $this->tanggalProduksi = now()->format('Y-m-d');
        // $this->resetJobSummary(); // isi data awal tanpa produksi & jml_orang
        // 🟢 Ambil langsung saat mount
        // $this->hitungJobSummaryManual();
        $this->cariData();
    }

    public function render()
    {
        return view('livewire.produksi.produktivitas', [
            'produkList' => $this->produk,
            'metodeSummary' => $this->metodeSummary,
            'produk' => $this->produk,
            'msjobs' => $this->msjobs,
            'jobSummary' => $this->jobSummary,
        ]);
    }
    public function resetJobSummary()
    {
        $this->jobSummary = MsJobs::select('id', 'nama_job', 'group_job', 'unit', 'target', 'jam_mulai')
            ->get()
            ->map(function ($job) {
                return [
                    'job_id' => $job->id,
                    'nama_job' => $job->nama_job,
                    'group_job' => $job->group_job,
                    'unit' => $job->unit,
                    'target' => $job->target,
                    'jam_mulai' => $job->jam_mulai,
                    'jml_orang' => 0,
                    'target_produksi' => 0,
                ];
            })
            ->toArray();
    }

    public function exportExcel()
    {
        $this->cariData();

        $filename = 'target_produksi_' . $this->tanggalProduksi . '.xlsx';

        return Excel::download(new TargetProduksiViewExport($this->tanggalProduksi, $this->produk, $this->produkList, $this->metodeList, $this->metodeSummary, $this->jobSummary), $filename);
    }

    public function exportAnalisaExcel()
    {
        // Pastikan data terisi
        $this->cariData();

        // Metadata produk untuk pembagi khusus (PECAH TELUR/GILING)
        $productData = MasterProduct::select('id', 'tongbesar', 'patokan')->get()->keyBy('id');
        $tongBesarProduk = $productData->pluck('tongbesar', 'id')->map(fn($v) => (float) $v)->toArray();
        $patokanProduk = $productData->pluck('patokan', 'id')->map(fn($v) => (float) $v)->toArray();

        // Mapping product -> jobs
        $msproductJobs = DB::table('msproduct_jobs')->select('msproducts_id', 'msjobs_id')->get();
        $productJobMap = [];
        foreach ($msproductJobs as $r) {
            $productJobMap[$r->msproducts_id][] = $r->msjobs_id;
        }

        $filename = 'analisa_' . $this->tanggalProduksi . '.xlsx';

        return Excel::download(
            new AnalisaArrayExport(
                tanggal: $this->tanggalProduksi,
                produk: $this->produk,
                produkList: $this->produkList,
                jobSummary: $this->jobSummary,
                productJobMap: $productJobMap,
                tongBesarProduk: $tongBesarProduk,
                patokanProduk: $patokanProduk,
                shiftMinutes: $this->shiftMinutes, // bisa ubah jadi 420/540/720 sesuai shift
            ),
            $filename,
        );
    }

    public function cariData()
    {
        // 1) Ambil produk + detail perintah produksi (sesuai tanggal)
        // 1) Ambil SEMUA master product, tapi batasi isi relasinya per tanggal
        $produkCollection = MasterProduct::query()
            ->with([
                'detailPerintahProduksi' => function ($q) {
                    $q->whereHas('perintahProduksi', function ($sub) {
                        $sub->whereDate('tanggal_perintah', $this->tanggalProduksi);
                    })->with('perintahProduksi');
                },
                'produksiTambahan' => function ($q) {
                    $q->whereHas('perintahProduksi', function ($sub) {
                        $sub->whereDate('tanggal_perintah', $this->tanggalProduksi);
                    })->with('perintahProduksi');
                },
            ])
            ->orderBy('nama')
            ->get();

        // 2) Agregasi pengurangan per produk BERDASARKAN TANGGAL (bukan perintahIds dari detail)
        $penguranganMap = DB::table('produksi_pengurangan as pg')
            ->join('perintah_produksi as pp', 'pp.id', '=', 'pg.perintah_produksi_id')
            ->whereDate('pp.tanggal_perintah', $this->tanggalProduksi)
            ->selectRaw('pg.mproducts_id,
            COALESCE(SUM(pg.qty_pengurangan),0)            AS sum_qty_pengurangan,
            COALESCE(SUM(pg.target_qty_pengurangan),0)     AS sum_target_pengurangan')
            ->groupBy('pg.mproducts_id')
            ->get()
            ->keyBy('mproducts_id');

        // 3) Hitung total per produk (target & qty), gabungkan utama + tambahan, lalu kurangi pengurangan
        $this->produk = $produkCollection
            ->map(function ($produk) use ($penguranganMap) {
                // Produksi
                $produksiUtama     = (float) $produk->detailPerintahProduksi->sum('produksi_qty');
                $produksiTambahan  = (float) $produk->produksiTambahan->sum('qty_tambahan');

                // Target
                $targetUtama       = (float) $produk->detailPerintahProduksi->sum('target_produksi');
                $targetTambahan    = (float) $produk->produksiTambahan->sum('target_qty_tambahan');

                // Pengurangan (fallback 0 kalau tidak ada)
                $rowPeng           = $penguranganMap->get($produk->id);
                $qtyPengurangan    = (float) ($rowPeng->sum_qty_pengurangan ?? 0);
                $targetPengurangan = (float) ($rowPeng->sum_target_pengurangan ?? 0);

                // Susun output sebagai array (jika Blade kamu akses dengan ['...'])
                $arr = $produk->toArray();
                $arr['total_produksi_qty']    = $produksiUtama + $produksiTambahan - $qtyPengurangan;
                $arr['total_target_produksi'] = $targetUtama   + $targetTambahan   - $targetPengurangan;

                return $arr;
            })
            ->keyBy('id')
            ->toArray();


        // Kumpulkan semua perintah_produksi_id yang relevan dari detail_perintah_produksi
        $perintahIds = [];
        $productPerintahMap = []; // produk_id => array perintah_produksi_id (unik)
        foreach ($this->produk as $p) {
            // dari detail_perintah_produksi
            if (!empty($p['detail_perintah_produksi'])) {
                foreach ($p['detail_perintah_produksi'] as $d) {
                    if (!empty($d['perintah_produksi_id'])) {
                        $perintahIds[] = $d['perintah_produksi_id'];
                        $productPerintahMap[$p['id']][] = $d['perintah_produksi_id'];
                    }
                }
            }
            // dari produksi_tambahan
            if (!empty($p['produksi_tambahan'])) {
                foreach ($p['produksi_tambahan'] as $t) {
                    // relasi eager-loaded: $t['perintah_produksi']['id'] atau $t['perintah_produksi_id'] (sesuaikan field aslinya)
                    $pid = $t['perintah_produksi_id'] ?? ($t['perintah_produksi']['id'] ?? null);
                    if (!empty($pid)) {
                        $perintahIds[] = $pid;
                        $productPerintahMap[$p['id']][] = $pid;
                    }
                }
            }
        }
        $perintahIds = array_values(array_unique($perintahIds));
        // rapikan map per produk -> unik
        foreach ($productPerintahMap as $k => $list) {
            $productPerintahMap[$k] = array_values(array_unique($list));
        }

        // 2) Hitung ringkasan metode (sama seperti sebelumnya)

        $this->metodeSummary = [];

        foreach ($this->metodeList as $metode) {
            $produksiQtyNet = 0.0; // TONG (NET: utama+tambahan - pengurangan)
            $targetProduksiNet = 0.0; // PCS (NET)

            foreach ($this->produk as $p) {
                if (($p['metode'] ?? null) !== $metode) {
                    continue;
                }

                $produksiQtyNet += (float) ($p['total_produksi_qty'] ?? 0);
                $targetProduksiNet += (float) ($p['total_target_produksi'] ?? 0);
            }

            // (opsional) cegah minus:
            $produksiQtyNet = max(0, $produksiQtyNet);
            $targetProduksiNet = max(0, $targetProduksiNet);

            $this->metodeSummary[] = [
                'metode' => $metode,
                'total_produksi_qty' => $produksiQtyNet,
                'total_target_produksi' => $targetProduksiNet,
            ];
        }

        // total kolom (jika dipakai di Blade)
        $this->metodeTotalTong = (int) round(collect($this->metodeSummary)->sum('total_produksi_qty'));
        $this->metodeTotalPcs = (int) round(collect($this->metodeSummary)->sum('total_target_produksi'));
        // 3) Ambil jumlah orang per job berdasarkan hari (sama seperti sebelumnya)
        $hari = strtolower(Carbon::parse($this->tanggalProduksi)->locale('id')->isoFormat('dddd'));

        $jobs = MsJobs::withSum(
            [
                'jadwalDivisi as jml_orang' => function ($q) use ($hari) {
                    $q->where('hari', $hari);
                },
            ],
            'jumlah',
        )->get();

        // 4) Preload product metadata (tongbesar, patokan)
        $productData = MasterProduct::select('id', 'tongbesar', 'patokan')->get()->keyBy('id');
        $tongBesarProduk = $productData->pluck('tongbesar', 'id')->toArray();
        $patokanProduk = $productData->pluck('patokan', 'id')->toArray();

        // 5) Preload mapping product => job (msproduct_jobs) sekali (hindari query di loop)
        $msproductJobs = DB::table('msproduct_jobs')->select('msproducts_id', 'msjobs_id')->get();
        // Buat map: produk_id => array(job_id, job_id, ...)
        $productJobMap = [];
        foreach ($msproductJobs as $r) {
            $productJobMap[$r->msproducts_id][] = $r->msjobs_id;
        }

        // 6) Preload selesai_divisi untuk semua perintah yang relevan (group by perintah-msjob)
        $selesaiMap = [];
        if (!empty($perintahIds)) {
            $selesaiRows = InputSelesai::whereIn('perintah_produksi_id', $perintahIds)->get();
            foreach ($selesaiRows as $s) {
                $key = $s->perintah_produksi_id . '-' . $s->msjobs_id;
                // simpan last atau first sesuai kebutuhan; di sini ambil yang terakhir
                $selesaiMap[$key] = [
                    'waktu_selesai' => $s->waktu_selesai,
                    'keterangan' => $s->keterangan,
                ];
            }
        }
        $selesaiPerJob = [];
        if (!empty($perintahIds)) {
            $rows = InputSelesai::whereIn('perintah_produksi_id', $perintahIds)
                ->orderByDesc('id') // ambil yang terbaru
                ->get();

            foreach ($rows as $s) {
                // simpan sekali saja per msjobs_id (yang paling baru karena urut desc)
                if (!isset($selesaiPerJob[$s->msjobs_id])) {
                    $selesaiPerJob[$s->msjobs_id] = [
                        'waktu_selesai' => $s->waktu_selesai,
                        'keterangan' => $s->keterangan,
                    ];
                }
            }
        }
        // 7) Prepare jobSummary jika belum ada (jika awalnya jobSummary sudah di-fill di mount)
        // Jika jobSummary belum diisi, buat dari semua MsJobs supaya tampil walau belum produksi
        if (empty($this->jobSummary)) {
            $this->jobSummary = MsJobs::orderBy('group_job')
                ->get()
                ->map(function ($j) {
                    return [
                        'job_id' => $j->id,
                        'nama_job' => $j->nama_job,
                        'group_job' => $j->group_job,
                        'unit' => $j->unit,
                        'target' => $j->target,
                        'jam_mulai' => $j->jam_mulai,
                        'jml_orang' => 0,
                        'target_produksi' => 0,
                        'waktu_selesai' => null,
                        // ⬇️ penting: bawakan flag dari DB
                        'use_target_as_output'  => (bool) $j->use_target_as_output,
                    ];
                })
                ->toArray();
        }

        // 8) Jobs yang perlu dibagi tongbesar/patokan
        $jobYangDibagiTongBesar = ['PECAH TELUR', 'GILING']; // uppercase untuk perbandingan

        // 9) Loop jobSummary dan hitung target_produksi & waktu_selesai dengan menggunakan maps
        $collectedWaktuSelesai = null;
        $collectedKeterangan = null;

        foreach ($this->jobSummary as &$jobRow) {
            // update jumlah orang dari jadwalDivisi preloaded ($jobs)
            $jobObj = $jobs->firstWhere('id', $jobRow['job_id']);
            $jobRow['jml_orang'] = $jobObj?->jml_orang ?? 0;

            $target_produksi = 0;
            $collectedWaktuSelesai = null; // bila ada lebih dari satu perintah, kamu bisa pilih kebijakan (first/last/avg)
            $collectedKeterangan = null; // ← WAJIB reset

            foreach ($this->produk as $produk) {
                $pid = $produk['id'];

                // cek apakah produk ini terkait dengan job ini via productJobMap
                // produk tidak terkait job ini → skip
                $jobIdsForProduct = $productJobMap[$pid] ?? [];
                if (!in_array($jobRow['job_id'], $jobIdsForProduct)) {
                    continue;
                }
                // Ambil target NET produk (utama + tambahan - pengurangan) yang sudah Anda hitung di atas:
                $target = (float) ($produk['total_target_produksi'] ?? 0);

                // pembagi khusus untuk job tertentu
                if (in_array(strtoupper($jobRow['nama_job']), $jobYangDibagiTongBesar, true)) {
                    $tongbesar = (float) max($tongBesarProduk[$pid] ?? 1, 1);
                    $patokan   = (float) max($patokanProduk[$pid] ?? 1, 1);
                    $target    = $target / $tongbesar / $patokan;
                }

                $target_produksi += $target;

                // Ambil waktu_selesai dari salah satu perintah terkait produk ini (prioritaskan yang terbaru).
                // Karena kita tak lagi loop per-detail, pilih perintah terakhir dari productPerintahMap jika ada:
                if (!empty($productPerintahMap[$pid])) {
                    // mis. ambil ID terakhir saja (atau bisa lakukan logika pemilihan lain)
                    $perintahIdKandidat = end($productPerintahMap[$pid]);
                    if (!empty($perintahIdKandidat)) {
                        $key = $perintahIdKandidat . '-' . $jobRow['job_id'];
                        if (!empty($selesaiMap[$key])) {
                            $collectedWaktuSelesai = $selesaiMap[$key]['waktu_selesai'];
                            $collectedKeterangan   = $selesaiMap[$key]['keterangan'];
                        }
                    }
                }
            }

            $jobRow['target_produksi'] = $target_produksi;
            // Fallback waktu_selesai jika belum ketemu dari map per-produk:
            if (empty($collectedWaktuSelesai) && isset($selesaiPerJob[$jobRow['job_id']])) {
                $collectedWaktuSelesai = $selesaiPerJob[$jobRow['job_id']]['waktu_selesai'];
                $collectedKeterangan   = $selesaiPerJob[$jobRow['job_id']]['keterangan'];
            }

            $jobRow['waktu_selesai'] = $collectedWaktuSelesai ?: null;
            $jobRow['keterangan']    = $collectedKeterangan ?: ($jobRow['keterangan'] ?? '');
        }
        unset($jobRow);
        // akhir foreach jobs
        // dd(   $this->jobSummary); // untuk debugging, bisa dihapus nanti
        // === ENRICH & GROUPING (PINDAHAN DARI BLADE) =============================
        $textColors = ['text-white', 'text-yellow-300', 'text-pink-300', 'text-green-300', 'text-red-400'];
        $groupColors = []; // mapping group => kelas
        $breakMinutes = 120;

        // sort by group_job agar subtotal rapi
        $jobsSorted = collect($this->jobSummary)->sortBy('group_job')->values()->all();

        // akumulator grup
        $groupOrang = 0;
        $groupJobCount = 0;
        $groupTotalMenit = 0.0;
        $groupJamMulai = null; // Carbon
        $groupMaxWaktuSelesai = null; // Carbon
        $groupTotalRasio = 0.0;

        $lastGroup = null;

        // output “siap saji” untuk Blade
        $this->tableRows = []; // <— INI yang nanti di-loop di Blade

        // helper untuk push subtotal & reset
        $pushSubtotal = function () use (&$groupOrang, &$groupJobCount, &$groupTotalMenit, &$groupJamMulai, &$groupMaxWaktuSelesai, &$groupTotalRasio, $breakMinutes, &$lastGroup) {
            if ($lastGroup === null) {
                return;
            }

            $rataOrang = $groupJobCount ? $groupOrang / max(1, $groupJobCount) : 0;
            $subtotalSelesai = $groupJamMulai?->copy()->addMinutes((int) round($groupTotalMenit));

            $hasAnyReal = !is_null($groupMaxWaktuSelesai);
            $rawMinutes = $hasAnyReal && $groupJamMulai ? $groupJamMulai->diffInMinutes($groupMaxWaktuSelesai) : 0;
            $actualMins = $hasAnyReal ? max(0, $rawMinutes - $breakMinutes) : 0;

            $percentPlanned = $groupTotalMenit > 0 ? ($groupTotalMenit / (8 * 60)) * 100 : 0;
            $workloadMinutes = (float) $groupTotalRasio * 60.0;
            $percentReal = $hasAnyReal && $actualMins > 0 ? ($workloadMinutes / $actualMins) * 100 : 0;

            // $this boleh dipakai langsung di sini
            $this->tableRows[] = [
                'row_type' => 'subtotal',
                'group_job' => $lastGroup,
                'rata_orang' => (int) round($rataOrang),
                'jam_mulai_fmt' => $groupJamMulai?->format('H:i'),
                'jam_selesai_plan_fmt' => $subtotalSelesai?->format('H:i'),
                'waktu_planned_text' => floor($groupTotalMenit / 60) . ' jam ' . ((int)$groupTotalMenit % 60) . ' menit',

                'selesai_real_fmt' => $groupMaxWaktuSelesai ? $groupMaxWaktuSelesai->format('H:i') : '-',
                'percent_planned' => round($percentPlanned, 2),
                'percent_real' => round($percentReal, 2),
            ];

            // reset akumulator
            $groupOrang = 0;
            $groupJobCount = 0;
            $groupTotalMenit = 0.0;
            $groupJamMulai = null;
            $groupMaxWaktuSelesai = null;
            $groupTotalRasio = 0.0;
        };

        $jobDenganDesimal = ['PECAH TELUR', 'GILING'];

        foreach ($jobsSorted as $j) {
            $group = $j['group_job'];
            if (!isset($groupColors[$group])) {
                $groupColors[$group] = $textColors[count($groupColors) % count($textColors)];
            }
            $textClass = $groupColors[$group];

            // pergantian grup => push subtotal sebelum masuk grup baru
            if ($lastGroup !== null && $lastGroup !== $group) {
                $pushSubtotal();
            }

            $jmlOrang = (int) ($j['jml_orang'] ?? 0);
            $produksi = (float) ($j['target_produksi'] ?? 0);
            // 👉 fleksibel: jika job menandai use_target_as_output, pakai msjobs.target
            if (!empty($j['use_target_as_output'])) {
                $produksi = (float) ($j['target'] ?? 0);
            }
            $target = (float) ($j['target'] ?? 0);

            $rasio = $target > 0 && $jmlOrang > 0 ? $produksi / ($target * $jmlOrang) : 0.0;
            $totalMenit = $rasio * 60.0;

            $jamMulai = $j['jam_mulai'] ? \Carbon\Carbon::parse($j['jam_mulai']) : null;
            $jamSelesai = $jamMulai ? $jamMulai->copy()->addMinutes($totalMenit) : null;

            $waktuSelesaiDb = !empty($j['waktu_selesai']) ? \Carbon\Carbon::parse($j['waktu_selesai']) : null;

            // akumulasi grup
            $groupOrang += $jmlOrang;
            $groupJobCount += 1;
            $groupTotalMenit += $totalMenit;
            $groupTotalRasio += (float) $rasio;
            if ($jamMulai && !$groupJamMulai) {
                $groupJamMulai = $jamMulai;
            }
            if ($waktuSelesaiDb && (!$groupMaxWaktuSelesai || $waktuSelesaiDb->gt($groupMaxWaktuSelesai))) {
                $groupMaxWaktuSelesai = $waktuSelesaiDb;
            }

            // formatting produksi (khusus)
            $produksiFormatted = in_array(strtoupper($j['nama_job']), $jobDenganDesimal, true) ? number_format((float) $produksi, 2, ',', '.') : number_format((int) $produksi, 0, ',', '.');

            // waktu planned text
            $jam = floor($totalMenit / 60);
            $menit = (int) $totalMenit % 60;
            $waktuPlannedText = trim(($jam > 0 ? $jam . ' jam' : '') . ' ' . ($menit > 0 ? $menit . ' menit' : ''));

            // push baris item
            $this->tableRows[] = [
                'row_type' => 'item',
                'group_job' => $group,
                'text_class' => $textClass,
                'nama_job' => $j['nama_job'],
                'jml_orang' => $jmlOrang,
                'produksi_fmt' => $produksiFormatted,
                'target' => $target,
                'unit' => $j['unit'],
                'jam_mulai_fmt' => $jamMulai?->format('H:i'),
                'jam_selesai_plan_fmt' => $jamSelesai?->format('H:i'),
                'waktu_planned_text' => $waktuPlannedText,
                'selesai_real_fmt' => $waktuSelesaiDb ? $waktuSelesaiDb->format('H:i') : '',
                'keterangan' => $j['keterangan'] ?? '',
            ];

            $lastGroup = $group;
        }

        // subtotal terakhir
        $pushSubtotal();
        // ========================================================================

        // OPTIONAL: kalau masih perlu `jobSummary` lama untuk hal lain, biarkan.
        // View nanti gunakan $tableRows.
    }
}
