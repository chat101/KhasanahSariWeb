<?php

namespace App\Livewire\Dashboard;

use App\Models\MasterToko;
use App\Models\Operasional\MasterProyeksiKontribusi;
use App\Models\Operasional\Wilayah;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesAchievementDashboard extends Component
{
    public $selectedDate;
    public $tokoList = [];
    public $dashboardData = [];
    public $filteredDashboardData = [];
    public $overallAchievement = 0;
    public $chartData = [];
    public $selectedWilayah = 'all';
    public $wilayahOptions = [];

    private function normalizeWilayahName(?string $name): ?string
    {
        if (!$name) {
            return null;
        }
        return mb_strtoupper(trim($name));
    }

    public function mount()
    {
        // Default to H-1 when no date selected
        $this->selectedDate = now()->subDay()->toDateString();
        $this->loadTokos();
        $this->loadWilayahOptions();
        $this->applyWilayahFilter();
        $this->calculateAchievement();
    }

    public function loadTokos()
    {
        $this->tokoList = MasterToko::where('status', '1')
            ->with(['area.wilayah'])
            ->orderBy('nmtoko')
            ->get(['id', 'nmtoko', 'area_id'])
            ->map(function ($toko) {
                $area = $toko->area;
                $wilayah = $area?->wilayah;
                $wilayahNama = $wilayah->nama_wilayah ?? null;
                $wilayahKey = $this->normalizeWilayahName($wilayahNama);
                return [
                    'id' => $toko->id,
                    'nmtoko' => $toko->nmtoko,
                    'area_id' => $toko->area_id,
                    'area_nama' => $area->nama_area ?? '-',
                    'wilayah_id' => $wilayah->id ?? null,
                    'wilayah_nama' => $wilayahNama ?? '-',
                    'wilayah_key' => $wilayahKey,
                ];
            })
            ->toArray();

        $this->loadWilayahOptions();
        $this->applyWilayahFilter();
    }

    public function calculateAchievement()
    {
        $date = Carbon::parse($this->selectedDate);
        $tgl = $date->toDateString();
        $bulan = $date->month;
        $tahun = $date->year;

        $data = [];
        $totalTarget = 0;
        $totalActual = 0;

        $filterWilayah = $this->selectedWilayah === 'all'
            ? 'all'
            : $this->normalizeWilayahName($this->selectedWilayah);

        foreach ($this->tokoList as $toko) {
            $rowWilayahKey = $toko['wilayah_key'] ?? null;
            if ($filterWilayah !== 'all' && $rowWilayahKey !== $filterWilayah) {
                continue;
            }
            $tokoId = $toko['id'];

            // Get projected sales for this day
            $proyeksi = MasterProyeksiKontribusi::where('toko_id', $tokoId)
                ->where('tanggal', $tgl)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->sum('rupiah');

            // Get actual sales for this day
            // Using snapshot from KontribusiHarianJobRow if available
            $actual = $this->getActualSales($tokoId, $tgl);

            $achievement = $proyeksi > 0 ? ($actual / $proyeksi) * 100 : 0;

            $data[] = [
                'toko_id' => $tokoId,
                'toko_nama' => $toko['nmtoko'],
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
        $this->applyWilayahFilter();
        $this->buildChartData();
    }

    private function getActualSales(int $tokoId, string $tgl): int
    {
        // Try to get from snapshot table kontribusi_harian_job_rows
        $snapshot = DB::table('kontribusi_harian_job_rows')
            ->join('kontribusi_harian_jobs', 'kontribusi_harian_job_rows.job_id', '=', 'kontribusi_harian_jobs.id')
            ->where('kontribusi_harian_jobs.toko_id', $tokoId)
            ->where('kontribusi_harian_job_rows.tanggal', $tgl)
            ->where('kontribusi_harian_job_rows.jenis', 'BY TARGET')
            ->select('kontribusi_harian_job_rows.payload')
            ->first();

        if ($snapshot && $snapshot->payload) {
            $payload = json_decode($snapshot->payload, true);
            return (int) data_get($payload, 'sales_now', 0);
        }

        // Fallback - return 0
        return 0;
    }

    private function buildChartData()
    {
        $labels = [];
        $targets = [];
        $actuals = [];

        foreach ($this->filteredDashboardData as $item) {
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

    public function updatedSelectedDate()
    {
        $this->calculateAchievement();
    }

    public function updatedSelectedWilayah(): void
    {
        $this->applyWilayahFilter();
        $this->buildChartData();
    }

    private function applyWilayahFilter(): void
    {
        if ($this->selectedWilayah === 'all') {
            $this->filteredDashboardData = $this->dashboardData;
            return;
        }

        $this->filteredDashboardData = array_values(array_filter($this->dashboardData, function ($row) {
            $rowKey = $row['wilayah_key'] ?? null;
            $filterKey = $this->normalizeWilayahName($this->selectedWilayah);
            return $rowKey === $filterKey;
        }));
    }

    private function loadWilayahOptions(): void
    {
        $fromTokos = collect($this->tokoList)
            ->filter(fn($t) => !empty($t['wilayah_key']))
            ->map(fn($t) => [
                'id' => $t['wilayah_key'],
                'nama' => $t['wilayah_nama'],
            ])
            ->unique('id')
            ->values();

        $fallback = Wilayah::orderBy('nama_wilayah')
            ->get(['id', 'nama_wilayah'])
            ->map(function ($w) {
                $key = $this->normalizeWilayahName($w->nama_wilayah);
                return ['id' => $key ?? (string)$w->id, 'nama' => $w->nama_wilayah];
            });

        $merged = $fromTokos->isNotEmpty() ? $fromTokos : $fallback;

        $this->wilayahOptions = collect([
            ['id' => 'all', 'nama' => 'Semua Wilayah'],
        ])->concat($merged)->unique('id')->values()->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard.sales-achievement-dashboard');
    }
}
