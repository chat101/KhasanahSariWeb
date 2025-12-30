@php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Periode: ' . \Carbon\Carbon::parse($startDate ?? now())->translatedFormat('d F Y') . ' → ' . \Carbon\Carbon::parse($endDate ?? now())->translatedFormat('d F Y');
@endphp

<div class="bg-slate-950 text-slate-100 overflow-x-hidden min-h-screen">
    <!-- Background Ambient Effects -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[128px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/10 rounded-full blur-[128px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="relative z-10 w-full max-w-none px-4 sm:px-6 lg:px-8 xl:px-10 space-y-3 pb-6">
        
        <!-- === STATS GRID (Compact Row) === -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">

            <!-- Sales -->
            <div class="relative overflow-hidden rounded-lg md:rounded-xl bg-gradient-to-br from-indigo-900/50 to-slate-900 border border-indigo-500/20 shadow-lg group hover:border-indigo-500/40 transition-all p-2 md:p-4">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div class="min-w-0 flex-1">
                        <p class="text-[8px] md:text-[10px] uppercase tracking-wider text-indigo-300 font-semibold truncate">Sales</p>
                        <h3 class="text-sm md:text-2xl font-bold text-white mt-0.5 md:mt-1 shadow-glow-indigo truncate">Rp {{ number_format($totalSalesToday ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="hidden md:block p-1.5 bg-indigo-500/20 rounded-lg text-indigo-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="mt-1 md:mt-2 flex items-center text-[8px] md:text-[10px] text-indigo-400/80">
                    <span class="w-1 h-1 md:w-1.5 md:h-1.5 rounded-full bg-green-400 animate-pulse mr-1"></span> Live
                </div>
            </div>

            <!-- Cash In -->
            <div class="relative overflow-hidden rounded-lg md:rounded-xl bg-gradient-to-br from-emerald-900/50 to-slate-900 border border-emerald-500/20 shadow-lg group hover:border-emerald-500/40 transition-all p-2 md:p-4">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div class="min-w-0 flex-1">
                        <p class="text-[8px] md:text-[10px] uppercase tracking-wider text-emerald-300 font-semibold truncate">Cash In</p>
                        <h3 class="text-sm md:text-2xl font-bold text-white mt-0.5 md:mt-1 truncate">Rp {{ number_format($totalCashInToday ?? 0, 0, ',', '.') }}</h3>
                    </div>
                    <div class="hidden md:block p-1.5 bg-emerald-500/20 rounded-lg text-emerald-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-1 md:mt-2 text-[8px] md:text-[10px] text-emerald-400/80">Setoran</div>
            </div>

            <!-- Production -->
            <div class="relative overflow-hidden rounded-lg md:rounded-xl bg-gradient-to-br from-fuchsia-900/50 to-slate-900 border border-fuchsia-500/20 shadow-lg group hover:border-fuchsia-500/40 transition-all p-2 md:p-4">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div class="min-w-0 flex-1">
                        <p class="text-[8px] md:text-[10px] uppercase tracking-wider text-fuchsia-300 font-semibold truncate">Orders</p>
                        <h3 class="text-sm md:text-2xl font-bold text-white mt-0.5 md:mt-1">{{ $activeProOrders ?? 0 }}</h3>
                    </div>
                    <div class="hidden md:block p-1.5 bg-fuchsia-500/20 rounded-lg text-fuchsia-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-1 md:mt-2 text-[8px] md:text-[10px] text-fuchsia-400/80">Progress</div>
            </div>

            <!-- Combined Master -->
            <div class="grid grid-rows-2 gap-1 md:gap-2">
                <div class="bg-white/5 rounded-lg px-2 md:px-4 py-1.5 md:py-2 border border-white/5 flex items-center justify-between">
                    <span class="text-[9px] md:text-xs text-slate-400 font-medium">Items</span>
                    <span class="text-xs md:text-sm font-bold text-white">{{ $jumlahBarang ?? 0 }}</span>
                </div>
                <div class="bg-white/5 rounded-lg px-2 md:px-4 py-1.5 md:py-2 border border-white/5 flex items-center justify-between">
                    <span class="text-[9px] md:text-xs text-slate-400 font-medium">Suppliers</span>
                    <span class="text-xs md:text-sm font-bold text-white">{{ $jumlahSupplier ?? 0 }}</span>
                </div>
            </div>

        </div>

        <!-- === TABLES (Fill Remaining Height) === -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 md:gap-4 pb-6">

            <!-- Supply Table -->
            <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg md:rounded-xl border border-white/5 flex flex-col h-[300px] md:h-full overflow-hidden">
                <div class="px-3 md:px-4 py-2 md:py-3 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                    <h3 class="text-xs md:text-sm font-semibold text-slate-200">Recent Supply</h3>
                    <a href="{{ route('rekapbrgmsk') }}" class="text-[9px] md:text-[10px] font-medium text-blue-300 hover:text-white transition-colors">View All</a>
                </div>
                <div class="flex-1 overflow-y-auto w-full">
                    <table class="w-full text-[10px] md:text-xs text-left text-slate-400">
                        <thead class="text-[8px] md:text-[10px] text-slate-500 uppercase bg-black/20 sticky top-0 backdrop-blur-md">
                            <tr>
                                <th class="px-2 md:px-4 py-1.5 md:py-2">Date</th>
                                <th class="px-2 md:px-4 py-1.5 md:py-2">Supplier</th>
                                <th class="px-2 md:px-4 py-1.5 md:py-2">PO</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($recentPurchases as $purchase)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 whitespace-nowrap">{{ \Carbon\Carbon::parse($purchase->tanggal)->format('d/m') }}</td>
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 text-slate-300 font-medium truncate max-w-[80px] md:max-w-[120px]">{{ $purchase->supplier->nama_supplier ?? '-' }}</td>
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 truncate max-w-[60px] md:max-w-full">{{ $purchase->no_po ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-3 md:p-4 text-center italic text-[10px] md:text-xs">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cash Table -->
            <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg md:rounded-xl border border-white/5 flex flex-col h-[300px] md:h-full overflow-hidden">
                <div class="px-3 md:px-4 py-2 md:py-3 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                    <h3 class="text-xs md:text-sm font-semibold text-slate-200">Recent Cash In</h3>
                    <a href="{{ route('uangmsk') }}" class="text-[9px] md:text-[10px] font-medium text-emerald-300 hover:text-white transition-colors">View All</a>
                </div>
                <div class="flex-1 overflow-y-auto w-full">
                    <table class="w-full text-[10px] md:text-xs text-left text-slate-400">
                        <thead class="text-[8px] md:text-[10px] text-slate-500 uppercase bg-black/20 sticky top-0 backdrop-blur-md">
                            <tr>
                                <th class="px-2 md:px-4 py-1.5 md:py-2">Shop</th>
                                <th class="px-2 md:px-4 py-1.5 md:py-2 text-right">Amount</th>
                                <th class="px-2 md:px-4 py-1.5 md:py-2">Note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($recentCashIns as $cash)
                                <tr class="hover:bg-white/5 transition-colors">
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 text-slate-300 font-medium truncate max-w-[70px] md:max-w-[100px]">{{ $cash->tokos->nmtoko ?? '-' }}</td>
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 text-right font-mono text-emerald-400 text-[9px] md:text-xs">{{ number_format($cash->jumlah_uang, 0, ',', '.') }}</td>
                                    <td class="px-2 md:px-4 py-1.5 md:py-2.5 truncate max-w-[60px] md:max-w-[100px]">{{ $cash->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-3 md:p-4 text-center italic text-[10px] md:text-xs">No data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- === SALES ACHIEVEMENT SECTION === --}}
        <div class="space-y-3 md:space-y-4 pb-8">

            {{-- Header & Date Picker --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <h2 class="text-sm md:text-lg font-bold text-white">Sales Achievement</h2>
                
                <div class="text-[9px] md:text-xs text-slate-400">
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </div>
            </div>

            {{-- === SUMMARY CARDS === --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 md:gap-4">

                {{-- Overall Achievement --}}
                <div class="rounded-lg md:rounded-xl bg-gradient-to-br from-blue-900/50 to-slate-900 border border-blue-500/20 shadow-lg p-3 md:p-4">
                    <div class="text-blue-300 text-[9px] md:text-xs font-semibold uppercase">Achievement</div>
                    <div class="mt-1 md:mt-2 text-xl md:text-3xl font-bold text-blue-400">{{ $overallAchievement }}%</div>
                    <div class="mt-0.5 md:mt-1 text-[8px] md:text-[10px]
                        {{ $overallAchievement >= 100 ? 'text-green-400' : ($overallAchievement >= 80 ? 'text-yellow-400' : 'text-red-400') }}">
                        @if ($overallAchievement >= 100)
                            ✓ Tercapai
                        @elseif($overallAchievement >= 80)
                            ⚠ Hampir
                        @else
                            ✗ Belum
                        @endif
                    </div>
                </div>

                {{-- Total Target --}}
                <div class="rounded-lg md:rounded-xl bg-gradient-to-br from-green-900/50 to-slate-900 border border-green-500/20 shadow-lg p-3 md:p-4">
                    <div class="text-green-300 text-[9px] md:text-xs font-semibold uppercase">Target</div>
                    <div class="mt-1 md:mt-2 text-sm md:text-2xl font-bold text-green-400 truncate">Rp {{ number_format(collect($dashboardData)->sum('target'), 0, ',', '.') }}</div>
                </div>

                {{-- Total Actual --}}
                <div class="rounded-lg md:rounded-xl bg-gradient-to-br from-purple-900/50 to-slate-900 border border-purple-500/20 shadow-lg p-3 md:p-4">
                    <div class="text-purple-300 text-[9px] md:text-xs font-semibold uppercase">Actual</div>
                    <div class="mt-1 md:mt-2 text-sm md:text-2xl font-bold text-purple-400 truncate">Rp {{ number_format(collect($dashboardData)->sum('actual'), 0, ',', '.') }}</div>
                </div>
            </div>

            {{-- === CHART & TABLE === --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 md:gap-4">

                {{-- Chart --}}
                @if (count($dashboardData))
                    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg md:rounded-xl border border-white/5 shadow-lg p-3 md:p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2 md:mb-4">
                            <h3 class="text-xs md:text-sm font-semibold text-slate-200">Target vs Actual (Scrollable)</h3>
                            <select wire:model="selectedWilayah" class="text-xs md:text-sm bg-slate-800/80 border border-white/10 rounded-lg px-3 py-2 text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($wilayahOptions as $wilayah)
                                    <option value="{{ $wilayah['id'] }}">{{ $wilayah['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="chartDataPayload" data-chart='@json($chartData ?? ['labels' => [], 'datasets' => []])' class="hidden"></div>
                        <div class="relative max-h-[720px] overflow-y-auto">
                            <canvas id="achievementChart" class="w-full" wire:ignore></canvas>
                        </div>
                    </div>
                @endif

                {{-- Per-Day Breakdown Table --}}
                @if (count($dailyBreakdown ?? []))
                    <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg md:rounded-xl border border-white/5 overflow-hidden">
                        <div class="px-3 md:px-4 py-2 md:py-3 border-b border-white/5 bg-white/[0.02]">
                            <h3 class="text-xs md:text-sm font-semibold text-slate-200">Per-Day</h3>
                        </div>
                        <div class="overflow-y-auto max-h-[250px] md:max-h-[300px]">
                            <table class="w-full text-[10px] md:text-xs text-slate-400">
                                <thead class="text-[8px] md:text-[10px] uppercase bg-black/20 sticky top-0">
                                    <tr>
                                        <th class="px-2 md:px-3 py-1.5 md:py-2 text-left">Date</th>
                                        <th class="px-2 md:px-3 py-1.5 md:py-2 text-right">Target</th>
                                        <th class="px-2 md:px-3 py-1.5 md:py-2 text-right">Actual</th>
                                        <th class="px-2 md:px-3 py-1.5 md:py-2 text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @foreach ($dailyBreakdown as $d)
                                        <tr class="hover:bg-white/5">
                                            <td class="px-2 md:px-3 py-1.5 md:py-2 text-slate-300">{{ \Carbon\Carbon::parse($d['date'])->format('d/m') }}</td>
                                            <td class="px-2 md:px-3 py-1.5 md:py-2 text-right text-[9px] md:text-xs">{{ number_format($d['target']/1000, 0) }}K</td>
                                            <td class="px-2 md:px-3 py-1.5 md:py-2 text-right text-[9px] md:text-xs {{ $d['achievement_pct'] >= 100 ? 'text-green-400' : ($d['achievement_pct'] >= 80 ? 'text-yellow-400' : 'text-red-400') }}">{{ number_format($d['actual']/1000, 0) }}K</td>
                                            <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-bold text-[9px] md:text-xs {{ $d['achievement_pct'] >= 100 ? 'text-green-400' : ($d['achievement_pct'] >= 80 ? 'text-yellow-400' : 'text-red-400') }}">{{ $d['achievement_pct'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Table --}}
                <div class="bg-slate-900/50 backdrop-blur-sm rounded-lg md:rounded-xl border border-white/5 overflow-hidden">
                    <div class="px-3 md:px-4 py-2 md:py-3 border-b border-white/5 bg-white/[0.02]">
                        <h3 class="text-xs md:text-sm font-semibold text-slate-200">Per Toko</h3>
                    </div>

                    <div class="overflow-y-auto max-h-[250px] md:max-h-[300px]">
                        <table class="w-full text-[10px] md:text-xs text-slate-400">
                            <thead class="text-[8px] md:text-[10px] uppercase bg-black/20 sticky top-0">
                                <tr>
                                    <th class="px-2 md:px-3 py-1.5 md:py-2 text-left">Toko</th>
                                    <th class="px-2 md:px-3 py-1.5 md:py-2 text-right">Target</th>
                                    <th class="px-2 md:px-3 py-1.5 md:py-2 text-right">Actual</th>
                                    <th class="px-2 md:px-3 py-1.5 md:py-2 text-center">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($filteredDashboardData ?? [] as $row)
                                    <tr class="hover:bg-white/5">
                                        <td class="px-2 md:px-3 py-1.5 md:py-2 font-medium text-slate-300 truncate max-w-[80px] md:max-w-full">{{ $row['toko_nama'] }}</td>
                                        <td class="px-2 md:px-3 py-1.5 md:py-2 text-right text-[8px] md:text-[10px]">{{ number_format($row['target']/1000, 0) }}K</td>
                                        <td class="px-2 md:px-3 py-1.5 md:py-2 text-right font-semibold text-[8px] md:text-[10px]
                                            {{ $row['achievement_pct'] >= 100 ? 'text-green-400' : ($row['achievement_pct'] >= 80 ? 'text-yellow-400' : 'text-red-400') }}">
                                            {{ number_format($row['actual']/1000, 0) }}K
                                        </td>
                                        <td class="px-2 md:px-3 py-1.5 md:py-2 text-center font-bold text-[9px] md:text-xs
                                            {{ $row['achievement_pct'] >= 100 ? 'text-green-400' : ($row['achievement_pct'] >= 80 ? 'text-yellow-400' : 'text-red-400') }}">
                                            {{ $row['achievement_pct'] }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-3 md:p-4 text-center italic text-slate-500 text-[10px] md:text-xs">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Ensure chart re-inits on Livewire update AND immediately after
        document.addEventListener('livewire:update', () => {
            setTimeout(() => { initChart(); }, 100);
        });
        document.addEventListener('DOMContentLoaded', () => {
            setupChartObserver();
            setTimeout(() => { initChart(); }, 100);
        });
        function setupChartObserver() {
            const payloadEl = document.getElementById('chartDataPayload');
            if (!payloadEl) return;

            // Rebuild chart when data-chart attribute changes (e.g., filter wilayah)
            const observer = new MutationObserver((mutations) => {
                for (const m of mutations) {
                    if (m.type === 'attributes' && m.attributeName === 'data-chart') {
                        initChart();
                    }
                }
            });
            observer.observe(payloadEl, { attributes: true, attributeFilter: ['data-chart'] });
        }

        window.addEventListener('no-snapshot', (e) => {
            const d = e.detail || {};
            alert(`Tidak ada snapshot untuk ${d.requested}. Menampilkan data sampai ${d.fallback}`);
        });

        function initChart() {
            const canvas = document.getElementById('achievementChart');
            if (!canvas) return;

            if (window.achievementChart && typeof window.achievementChart.destroy === 'function') {
                window.achievementChart.destroy();
            }

            const payloadEl = document.getElementById('chartDataPayload');
            if (!payloadEl) return;

            let chartData;
            try {
                chartData = JSON.parse(payloadEl.dataset.chart || '{}');
            } catch (e) {
                console.error('Invalid chart data', e);
                return;
            }

            if (!chartData || !Array.isArray(chartData.datasets)) return;

            const rowCount = chartData.labels?.length ?? 0;
            const rowHeight = 22; // px per bar
            const canvasHeight = Math.max(320, rowCount * rowHeight);
            canvas.style.height = `${canvasHeight}px`;
            canvas.height = canvasHeight;

            const ctx = canvas.getContext('2d');
            window.achievementChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    datasets: {
                        bar: {
                            maxBarThickness: 18,
                            barPercentage: 0.9,
                            categoryPercentage: 0.9,
                        },
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#cbd5e1' }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.8)',
                            borderColor: '#64748b',
                            titleColor: '#f1f5f9',
                            bodyColor: '#cbd5e1',
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: { color: '#94a3b8' },
                            grid: { display: false }
                        },
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: '#94a3b8',
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                }
                            },
                            grid: { color: 'rgba(100, 116, 139, 0.1)' }
                        }
                    }
                }
            });
        }

    </script>
    </div>
    <!-- End Content Wrapper -->
</div>