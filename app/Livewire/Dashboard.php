<?php

namespace App\Livewire;

use App\Models\Gudang_Masuk;
use App\Models\MasterBarang;
use App\Models\MasterSupplier;
use App\Models\Purchasing;
use App\Models\MasterToko;
use Illuminate\Support\Facades\DB;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\Wilayah;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
 
    
    public $jumlahBarang;
    public $jumlahSupplier;

    // New Metrics
    public $totalSalesToday = 0;
    public $totalCashInToday = 0;
    public $activeProOrders = 0;

    // Lists
    public $recentPurchases = [];
    public $recentCashIns = [];

    // Sales Achievement
    public $selectedDate; // legacy single-date (kept for compatibility)
    public $startDate;    // periode awal
    public $endDate;      // periode akhir
    public $tokoList = [];
    public $dashboardData = [];
    public $filteredDashboardData = [];
    public $overallAchievement = 0;
    public $lastSnapshotDate = null;
    public $actualDateShown = null;
    public $chartData = [];
    public $dailyBreakdown = [];
    public $selectedWilayah = 'all';
    public $wilayahOptions = [];

    
    public function mount()
    {
        $this->jumlahBarang = MasterBarang::count();
        $this->jumlahSupplier = MasterSupplier::count();

        // Sales Snapshot Today (from Chatbot Sales Data)
        // If 0, it might mean the snapshot hasn't run yet, but that's fine.
        // $this->totalSalesToday = \App\Models\Chatbot\SalesSnapshot::whereDate('date', today())->sum('total_sales');

        // Cash In Today (Setoran Masuk)
        $this->totalCashInToday = \App\Models\Finance\Setoran_Masuk::whereDate('tanggal_setoran', today())->sum('jumlah_uang');

        // Active Production Orders (Created Today)
        $this->activeProOrders = \App\Models\Produksi\Perintah_Produksi::whereDate('tanggal_perintah', today())->count();

        // Recent Purchases (Gudang Masuk) - Top 5
        $this->recentPurchases = Gudang_Masuk::with(['supplier'])
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Recent Setoran - Top 5
        $this->recentCashIns = \App\Models\Finance\Setoran_Masuk::with('tokos')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Sales Achievement Init
        $this->lastSnapshotDate = $this->getLastSnapshotDate();
        // Auto range: start of current month -> yesterday (H-1)
        $today = now();
        $this->startDate = $today->copy()->startOfMonth()->toDateString();
        $this->endDate   = $today->copy()->subDay()->toDateString();
        // Keep legacy single-date in sync with endDate
        $this->selectedDate = $this->endDate;
        $this->actualDateShown = $this->endDate;
        $this->loadTokos();
        $this->loadWilayahOptions();
        $this->applyWilayahFilter();
        $this->calculateAchievement();
    }

    private function getLastSnapshotDate(): ?string
    {
        // Try to find the latest tanggal_awal where snapshot job status is ok
        $d = DB::table('kontribusi_harian_jobs')
            ->where('status', 'ok')
            ->whereNotNull('tanggal_awal')
            ->orderByDesc('tanggal_awal')
            ->value('tanggal_awal');

        return $d ? (string)$d : null;
    }

    private function hasSnapshotForDate(string $tgl): bool
    {
        return DB::table('kontribusi_harian_job_rows')
            ->join('kontribusi_harian_jobs', 'kontribusi_harian_job_rows.job_id', '=', 'kontribusi_harian_jobs.id')
            ->where('kontribusi_harian_job_rows.tanggal', $tgl)
            ->where('kontribusi_harian_job_rows.jenis', 'BY TARGET')
            ->exists();
    }

    public function loadTokos()
    {
        $this->tokoList = MasterToko::where('status', '1')
            ->with(['area.wilayah'])
            ->orderBy('nmtoko')
            ->get(['id', 'nmtoko', 'area_id'])
            ->map(function ($toko) {
                return [
                    'id' => $toko->id,
                    'nmtoko' => $toko->nmtoko,
                    'area_id' => $toko->area_id,
                    'area_nama' => $toko->area->nama_area ?? '-',
                    'wilayah_id' => $toko->area->wilayah->id ?? null,
                    'wilayah_nama' => $toko->area->wilayah->nama_wilayah ?? '-',
                ];
            })
            ->toArray();

            // Refresh wilayah options after toko list loads to keep IDs aligned
            $this->loadWilayahOptions();
            $this->applyWilayahFilter();
    }

    public function calculateAchievement()
    {
        // Jika ada start/end date, gunakan periode; jika tidak, fallback ke single-date
        $start = Carbon::parse($this->startDate ?? $this->selectedDate)->toDateString();
        $end   = Carbon::parse($this->endDate   ?? $this->selectedDate)->toDateString();

        $date = Carbon::parse($end);
        $bulan = $date->month;
        $tahun = $date->year;

        $data = [];
        $totalTarget = 0;
        $totalActual = 0;

        foreach ($this->tokoList as $toko) {
            $tokoId = $toko['id'];

            // Proyeksi: sum dalam periode yang dipilih (lintas bulan/tahun)
            $proyeksi = MasterProyeksiKontribusi::where('toko_id', $tokoId)
                ->whereBetween('tanggal', [$start, $end])
                ->sum('rupiah');

            // Actual: sum snapshot sales_now dalam periode
            $actual = $this->getActualSalesRange($tokoId, $start, $end);

            $achievement = $proyeksi > 0 ? ($actual / $proyeksi) * 100 : 0;

            $data[] = [
                'toko_id' => $tokoId,
                'toko_nama' => $toko['nmtoko'],
                'wilayah_id' => $toko['wilayah_id'] ?? null,
                'wilayah_nama' => $toko['wilayah_nama'] ?? '-',
                'area_id' => $toko['area_id'] ?? null,
                'area_nama' => $toko['area_nama'] ?? '-',
                'target' => (int)$proyeksi,
                'actual' => (int)$actual,
                'achievement_pct' => round($achievement, 2),
                'status' => $achievement >= 100 ? 'success' : ($achievement >= 80 ? 'warning' : 'danger'),
            ];

            $totalTarget += $proyeksi;
            $totalActual += $actual;
        }

        // Sort by achievement descending
        usort($data, function($a, $b) {
            return $b['achievement_pct'] <=> $a['achievement_pct'];
        });

        $this->dashboardData = $data;
        $this->overallAchievement = $totalTarget > 0 ? round(($totalActual / $totalTarget) * 100, 2) : 0;
        // Debugging: log totals and first few rows to storage/logs/laravel.log
        try {
            Log::info('achievement-debug', [
                'startDate' => $start,
                'endDate' => $end,
                'totalTarget' => $totalTarget,
                'totalActual' => $totalActual,
                'rows_sample' => array_slice($data, 0, 10),
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }
        $this->applyWilayahFilter();
        $this->buildChartData();
        $this->buildDailyBreakdown($start, $end);
    }

    private function getActualSales(int $tokoId, string $tgl): int
    {
        try {
            // Defensive: check whether toko_id exists on jobs or job_rows table
            $schema = DB::getSchemaBuilder();
            $jobsHasTokoId = $schema->hasColumn('kontribusi_harian_jobs', 'toko_id');
            $rowsHasTokoId = $schema->hasColumn('kontribusi_harian_job_rows', 'toko_id');

            $query = DB::table('kontribusi_harian_job_rows')
                ->join('kontribusi_harian_jobs', 'kontribusi_harian_job_rows.job_id', '=', 'kontribusi_harian_jobs.id')
                ->where('kontribusi_harian_job_rows.tanggal', $tgl)
                ->where('kontribusi_harian_job_rows.jenis', 'BY TARGET');

            if ($jobsHasTokoId) {
                $query->where('kontribusi_harian_jobs.toko_id', $tokoId);
            } elseif ($rowsHasTokoId) {
                $query->where('kontribusi_harian_job_rows.toko_id', $tokoId);
            } else {
                Log::warning('getActualSales-missing-toko_id', ['toko_id' => $tokoId, 'tanggal' => $tgl]);
                return 0;
            }

            $snapshot = $query->select('kontribusi_harian_job_rows.payload')->first();

            if ($snapshot && $snapshot->payload) {
                $payload = json_decode($snapshot->payload, true);
                return (int) data_get($payload, 'sales_now', 0);
            }

            return 0;
        } catch (\Throwable $e) {
            Log::error('getActualSales-error', ['message' => $e->getMessage(), 'toko_id' => $tokoId, 'tanggal' => $tgl]);
            return 0;
        }
    }

    private function buildChartData()
    {
        $filtered = $this->filteredDashboardData ?: [];

        $labels = [];
        $targets = [];
        $actuals = [];

        foreach ($filtered as $item) {
            $labels[] = $item['toko_nama'];
            $targets[] = $item['target'];
            $actuals[] = $item['actual'];
        }

        $this->chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Target',
                    'data' => $targets,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Actual',
                    'data' => $actuals,
                    'backgroundColor' => 'rgba(75, 192, 75, 0.5)',
                    'borderColor' => 'rgba(75, 192, 75, 1)',
                    'borderWidth' => 2,
                ]
            ]
        ];
    }

    public function updatedSelectedWilayah(): void
    {
        $this->applyWilayahFilter();
        $this->buildChartData();
    }

    public function getFilteredDashboardData(): array
    {
        return $this->filteredDashboardData;
    }

    private function applyWilayahFilter(): void
    {
        if ($this->selectedWilayah === 'all') {
            $this->filteredDashboardData = $this->dashboardData;
            return;
        }

        $this->filteredDashboardData = array_values(array_filter($this->dashboardData, function ($row) {
            return (string)($row['wilayah_id'] ?? '') === (string)$this->selectedWilayah;
        }));
    }

    private function loadWilayahOptions(): void
    {
        // Prioritize wilayah that actually exist on the toko list to avoid mismatched IDs
        $fromTokos = collect($this->tokoList)
            ->filter(fn($t) => !empty($t['wilayah_id']) && !empty($t['wilayah_nama']))
            ->map(fn($t) => [
                'id' => (string)$t['wilayah_id'],
                'nama' => $t['wilayah_nama'],
            ])
            ->unique('id')
            ->values();

        $fallback = Wilayah::orderBy('nama_wilayah')
            ->get(['id', 'nama_wilayah'])
            ->map(fn($w) => ['id' => (string)$w->id, 'nama' => $w->nama_wilayah]);

        $merged = $fromTokos->isNotEmpty() ? $fromTokos : $fallback;

        $this->wilayahOptions = collect([
            ['id' => 'all', 'nama' => 'Semua Wilayah'],
        ])->concat($merged)->unique('id')->values()->toArray();
    }

    private function buildDailyBreakdown(string $start, string $end): void
    {
        try {
            // Target per hari - normalize tanggal to DATE format only
            $targetRows = MasterProyeksiKontribusi::whereBetween('tanggal', [$start, $end])
                ->select(DB::raw('DATE(tanggal) as tanggal'), DB::raw('SUM(rupiah) as total'))
                ->groupBy(DB::raw('DATE(tanggal)'))
                ->get();
            
            $targetsByDate = [];
            foreach ($targetRows as $row) {
                // Explicitly format to Y-m-d
                $dateKey = Carbon::parse($row->tanggal)->format('Y-m-d');
                $targetsByDate[$dateKey] = (int)$row->total;
            }

            // Actual per hari - get distinct dates in DATE format
            $distinctDates = DB::table('kontribusi_harian_job_rows')
                ->join('kontribusi_harian_jobs', 'kontribusi_harian_job_rows.job_id', '=', 'kontribusi_harian_jobs.id')
                ->where('kontribusi_harian_jobs.status', 'ok')
                ->where('kontribusi_harian_job_rows.jenis', 'BY TARGET')
                ->whereBetween('kontribusi_harian_job_rows.tanggal', [$start, $end])
                ->select(DB::raw('DISTINCT DATE(kontribusi_harian_job_rows.tanggal) as tanggal'))
                ->orderBy('tanggal')
                ->pluck('tanggal');

            $actualsByDate = [];
            foreach ($distinctDates as $tgl) {
                // Explicitly format to Y-m-d
                $dateKey = Carbon::parse($tgl)->format('Y-m-d');
                
                // Get ALL jobs for this date (one per toko) with status ok
                $jobsForDate = DB::table('kontribusi_harian_jobs')
                    ->where('status', 'ok')
                    ->whereRaw('DATE(tanggal_awal) = ?', [$dateKey])
                    ->pluck('id');

                if ($jobsForDate->isEmpty()) {
                    $actualsByDate[$dateKey] = 0;
                    continue;
                }

                // Sum sales from ALL jobs (all toko) for this date
                $rows = DB::table('kontribusi_harian_job_rows')
                    ->whereIn('job_id', $jobsForDate)
                    ->whereRaw('DATE(tanggal) = ?', [$dateKey])
                    ->where('jenis', 'BY TARGET')
                    ->pluck('payload');

                $sum = 0;
                foreach ($rows as $payload) {
                    $data = json_decode($payload, true);
                    $sum += (int) data_get($data, 'sales_now', 0);
                }
                
                $actualsByDate[$dateKey] = $sum;
            }

            // Get all unique dates using Collection
            $allDates = collect(array_merge(array_keys($targetsByDate), array_keys($actualsByDate)))
                ->unique()
                ->sort()
                ->values();

            Log::info('buildDailyBreakdown-allDates', ['count' => $allDates->count(), 'dates' => $allDates->toArray()]);

            // Build breakdown with guaranteed unique dates using Collection
            $breakdown = $allDates->map(function($tgl) use ($targetsByDate, $actualsByDate) {
                $target = (int)($targetsByDate[$tgl] ?? 0);
                $actual = (int)($actualsByDate[$tgl] ?? 0);
                $pct = $target > 0 ? round(($actual / $target) * 100, 2) : 0;
                
                return [
                    'date' => $tgl,
                    'target' => $target,
                    'actual' => $actual,
                    'achievement_pct' => $pct,
                ];
            })->toArray();

            $this->dailyBreakdown = $breakdown;
        } catch (\Throwable $e) {
            Log::error('buildDailyBreakdown-error', ['message' => $e->getMessage(), 'start' => $start, 'end' => $end, 'trace' => $e->getTraceAsString()]);
            $this->dailyBreakdown = [];
        }
    }



    private function getActualSalesRange(int $tokoId, string $start, string $end): int
    {
        try {
            $schema = DB::getSchemaBuilder();
            $jobsHasTokoId = $schema->hasColumn('kontribusi_harian_jobs', 'toko_id');
            $rowsHasTokoId = $schema->hasColumn('kontribusi_harian_job_rows', 'toko_id');

            $query = DB::table('kontribusi_harian_job_rows')
                ->join('kontribusi_harian_jobs', 'kontribusi_harian_job_rows.job_id', '=', 'kontribusi_harian_jobs.id')
                ->whereBetween('kontribusi_harian_job_rows.tanggal', [$start, $end])
                ->where('kontribusi_harian_job_rows.jenis', 'BY TARGET');

            if ($jobsHasTokoId) {
                $query->where('kontribusi_harian_jobs.toko_id', $tokoId);
            } elseif ($rowsHasTokoId) {
                $query->where('kontribusi_harian_job_rows.toko_id', $tokoId);
            } else {
                Log::warning('getActualSalesRange-missing-toko_id', ['toko_id' => $tokoId, 'start' => $start, 'end' => $end]);
                return 0;
            }

            $rows = $query->select('kontribusi_harian_job_rows.payload')->get();

            $sum = 0;
            foreach ($rows as $row) {
                if ($row && $row->payload) {
                    $payload = json_decode($row->payload, true);
                    $sum += (int) data_get($payload, 'sales_now', 0);
                }
            }
            return (int) $sum;
        } catch (\Throwable $e) {
            Log::error('getActualSalesRange-error', ['message' => $e->getMessage(), 'toko_id' => $tokoId, 'start' => $start, 'end' => $end]);
            return 0;
        }
    }

    public function render()
    {
        return view('dashboard');
    }
}
