<?php

namespace App\Console\Commands\Operasional;


use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\MasterToko;
use App\Models\Operasional\KontribusiHarianJob;
use App\Models\Operasional\KontribusiHarianJobRow;
use App\Services\KontribusiHarianTokoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SnapshotKontribusiHarianToko extends Command
{
  protected $signature = 'snapshot:kontribusi-harian-toko
        {--start= : YYYY-MM-DD}
        {--end= : YYYY-MM-DD}
        {--toko= : toko_id spesifik (optional)}';

    protected $description = 'Generate kontribusi harian toko dan simpan ke tabel snapshot';

    public function handle(KontribusiHarianTokoService $svc): int
    {
        $logger = Log::channel('snapshot');

        $runId = now()->format('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);

        // rekomendasi: jam 01:00 ambil H-1 biar stabil
        $start = $this->option('start') ?: now()->subDay()->toDateString();
        $end   = $this->option('end')   ?: now()->subDay()->toDateString();

        $t0 = microtime(true);

        $logger->info('SNAPSHOT START', [
            'run_id' => $runId,
            'start'  => $start,
            'end'    => $end,
            'toko'   => $this->option('toko'),
            'time'   => now()->toDateTimeString(),
        ]);

        // =========================
        // Ambil list toko target
        // =========================
        $q = MasterToko::query()
            ->whereNotNull('api_id')
            ->where('api_id', '!=', '');

        if ($this->option('toko')) {
            $q->where('id', (int) $this->option('toko'));
        }

        $tokos = $q->orderBy('id')->get(['id', 'nmtoko']);

        if (!$tokos || $tokos->isEmpty()) {
            $totalSec = microtime(true) - $t0;

            $logger->warning('SNAPSHOT NO TOKO', [
                'run_id' => $runId,
                'start'  => $start,
                'end'    => $end,
                'sec'    => round($totalSec, 3),
                'time'   => now()->toDateTimeString(),
            ]);

            $this->warn("Tidak ada toko yang diproses (api_id kosong / tidak ditemukan).");

            $logger->info('SNAPSHOT FINISH', [
                'run_id' => $runId,
                'start'  => $start,
                'end'    => $end,
                'ok'     => 0,
                'error'  => 0,
                'sec'    => round($totalSec, 3),
                'time'   => now()->toDateTimeString(),
            ]);

            return self::SUCCESS;
        }

        $ok = 0;
        $err = 0;

        foreach ($tokos as $toko) {
            $t1 = microtime(true);

            $logger->info('SNAPSHOT TOKO START', [
                'run_id'  => $runId,
                'toko_id' => $toko->id,
                'nama'    => $toko->nmtoko,
            ]);

            DB::beginTransaction();

            try {
                $job = KontribusiHarianJob::updateOrCreate(
                    [
                        'tanggal_awal'  => $start,
                        'tanggal_akhir' => $end,
                        'toko_id'       => $toko->id,
                    ],
                    [
                        'nama_toko' => strtoupper(trim((string) $toko->nmtoko)),
                        'status'    => 'running',
                        'error'     => null,
                    ]
                );

                // Bersihkan detail lama (anti relasi error)
                KontribusiHarianJobRow::where('job_id', $job->id)->delete();

                // Hitung (service)
                $result      = $svc->hitung($toko->id, $start, $end);
                $rowsOut     = $result['rowsOut'] ?? [];
                $grandTotals = $result['grandTotals'] ?? [];

                // Simpan rows
                foreach ($rowsOut as $r) {
                    KontribusiHarianJobRow::create([
                        'job_id' => $job->id,
                        'tanggal' => $r['tanggal'] ?? null,
                        'jenis' => $r['jenis'] ?? null,

                        'selisih_persen' => $r['selisih_persen'] ?? null,
                        'selisih_rp' => (int) ($r['selisih_rp'] ?? 0),
                        'kontribusi_rp' => (int) ($r['kontribusi_rp'] ?? 0),

                        'disc_persen' => $r['disc_persen'] ?? null,
                        'disc_rp' => (int) ($r['disc_rp'] ?? 0),

                        'retur_persen' => $r['retur_persen'] ?? null,
                        'retur_rp' => (int) ($r['retur_rp'] ?? 0),

                        'gas_persen' => $r['gas_persen'] ?? null,
                        'gas_rp' => (int) ($r['gas_rp'] ?? 0),

                        'telur_persen' => $r['telur_persen'] ?? null,
                        'telur_rp' => (int) ($r['telur_rp'] ?? 0),

                        'loss_bahan' => (int) ($r['loss_bahan'] ?? 0),
                        'total_kontribusi' => (int) ($r['total_kontribusi'] ?? 0),

                        'payload' => $r,
                    ]);
                }

                // Update job
                $job->update([
                    'grand_totals' => $grandTotals,
                    'status'       => 'ok',
                    'error'        => null,
                ]);

                DB::commit();

                $ok++;
                $dt = microtime(true) - $t1;

                $logger->info('SNAPSHOT TOKO OK', [
                    'run_id'  => $runId,
                    'toko_id' => $toko->id,
                    'rows'    => count($rowsOut),
                    'sec'     => round($dt, 3),
                ]);

                $this->info("OK toko_id={$toko->id} {$toko->nmtoko} rows=" . count($rowsOut) . " (" . number_format($dt, 3) . "s)");
            } catch (\Throwable $e) {
                DB::rollBack();

                // Simpan status error (tanpa pakai $job karena bisa gagal sebelum job kebentuk)
                KontribusiHarianJob::updateOrCreate(
                    [
                        'tanggal_awal'  => $start,
                        'tanggal_akhir' => $end,
                        'toko_id'       => $toko->id,
                    ],
                    [
                        'nama_toko' => strtoupper(trim((string) $toko->nmtoko)),
                        'status'    => 'error',
                        'error'     => $e->getMessage(),
                    ]
                );

                $err++;
                $dt = microtime(true) - $t1;

                $logger->error('SNAPSHOT TOKO ERROR', [
                    'run_id'  => $runId,
                    'toko_id' => $toko->id,
                    'sec'     => round($dt, 3),
                    'error'   => $e->getMessage(),
                ]);

                $this->error("ERR toko_id={$toko->id} {$toko->nmtoko} (" . number_format($dt, 3) . "s) : " . $e->getMessage());
            }
        }

        $totalSec = microtime(true) - $t0;

        $logger->info('SNAPSHOT FINISH', [
            'run_id' => $runId,
            'start'  => $start,
            'end'    => $end,
            'ok'     => $ok,
            'error'  => $err,
            'sec'    => round($totalSec, 3),
            'time'   => now()->toDateTimeString(),
        ]);

        $this->info("DONE run_id={$runId} ok={$ok} error={$err} total=" . number_format($totalSec, 3) . "s");

        return self::SUCCESS;
    }
}
