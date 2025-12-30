<div class="space-y-6 p-6">
    @php $filtered = $filteredDashboardData ?? []; @endphp

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Sales Achievement Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor pencapaian penjualan vs target proyeksi per toko</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model="selectedWilayah" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                @foreach($wilayahOptions as $opt)
                    <option value="{{ $opt['id'] }}">{{ $opt['nama'] }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="selectedDate" class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>
    </div>

    {{-- Overall Achievement Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md border-l-4 border-blue-500 p-6">
            <div class="text-gray-600 text-sm font-medium">Overall Achievement</div>
            <div class="mt-2 text-4xl font-bold text-blue-600">{{ $overallAchievement }}%</div>
            <div class="mt-2 text-xs text-gray-500">
                @if($overallAchievement >= 100)
                    <span class="text-green-600 font-semibold">✓ Target Tercapai</span>
                @elseif($overallAchievement >= 80)
                    <span class="text-yellow-600 font-semibold">⚠ Hampir Tercapai</span>
                @else
                    <span class="text-red-600 font-semibold">✗ Belum Tercapai</span>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md border-l-4 border-green-500 p-6">
            <div class="text-gray-600 text-sm font-medium">Total Target</div>
            <div class="mt-2 text-3xl font-bold text-green-600">Rp {{ number_format(collect($filtered)->sum('target'), 0, ',', '.') }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-md border-l-4 border-purple-500 p-6">
            <div class="text-gray-600 text-sm font-medium">Total Actual</div>
            <div class="mt-2 text-3xl font-bold text-purple-600">Rp {{ number_format(collect($filtered)->sum('actual'), 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Chart --}}
    @if(count($filtered) > 0)
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">Perbandingan Target vs Actual per Toko</h2>
            <div id="chartDataPayload" data-chart='@json($chartData ?? ['labels' => [], 'datasets' => []])' class="hidden"></div>
        </div>
        <div class="mb-6 overflow-y-auto max-h-[640px]">
            <canvas id="achievementChart" class="w-full" wire:ignore></canvas>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Detail Pencapaian per Toko</h2>
        </div>

        @if(count($filtered) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700">Toko</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Target</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Actual</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-700">Achievement</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($filtered as $item)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item['toko_nama'] }}</td>
                        <td class="px-6 py-4 text-right text-gray-700">Rp {{ number_format($item['target'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-gray-900 font-semibold">Rp {{ number_format($item['actual'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold @if($item['achievement_pct'] >= 100) text-green-600 @elseif($item['achievement_pct'] >= 80) text-yellow-600 @else text-red-600 @endif">
                                {{ $item['achievement_pct'] }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($item['achievement_pct'] >= 100)
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">✓ Success</span>
                            @elseif($item['achievement_pct'] >= 80)
                                <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">⚠ Warning</span>
                            @else
                                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">✗ Danger</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-6 text-center text-gray-500">
            <p>Tidak ada data untuk tanggal ini</p>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @if(count($filtered) > 0)
        // Initialize chart and set up observers for reactive updates
        document.addEventListener('DOMContentLoaded', setupChartObservers);

        function setupChartObservers() {
            const payloadEl = document.getElementById('chartDataPayload');
            // Run once on load
            initChart();

            if (!payloadEl) return;

            // Observe changes to data-chart attribute from Livewire updates
            const observer = new MutationObserver((mutations) => {
                for (const m of mutations) {
                    if (m.type === 'attributes' && m.attributeName === 'data-chart') {
                        initChart();
                    }
                }
            });
            observer.observe(payloadEl, { attributes: true, attributeFilter: ['data-chart'] });

            // Fallback: also refresh on Livewire DOM updates if event is available
            document.addEventListener('livewire:update', initChart);
        }

        function initChart() {
            const payloadEl = document.getElementById('chartDataPayload');
            const ctx = document.getElementById('achievementChart');
            if (!payloadEl || !ctx) return;

            let chartData;
            try {
                chartData = JSON.parse(payloadEl.dataset.chart || '{}');
            } catch (e) {
                console.error('invalid chart data', e);
                return;
            }

            if (!chartData || !Array.isArray(chartData.datasets)) return;

            if (window.achievementChart) {
                window.achievementChart.destroy();
            }

            window.achievementChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' +
                                        context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
        @endif
    </script>
</div>
