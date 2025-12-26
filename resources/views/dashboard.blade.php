<style>
    /* === Modern Glow Animation === */
    @keyframes gradient-xy {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    .animate-gradient-xy {
        background-size: 200% 200%;
        animation: gradient-xy 15s ease infinite;
    }
    
    /* Custom Scrollbar for dark theme */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: rgba(30, 41, 59, 0.2); 
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.4); 
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(99, 102, 241, 0.6); 
    }
</style>

<!-- Main Container: Fixed Height to prevent body scroll (Fit Screen) -->
<!-- Adjusted height to calc(100vh - 4rem) to account for layout padding -->
<div class="h-[calc(100vh-4rem)] bg-[#0f172a] relative overflow-hidden font-sans text-slate-100 selection:bg-indigo-500 selection:text-white flex flex-col p-4 gap-4 rounded-xl">
    
    <!-- Background Ambient Effects -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-600/10 rounded-full blur-[128px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-600/10 rounded-full blur-[128px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- === HEADER (Compact) === -->
    <header class="shrink-0 flex items-center justify-between z-10 bg-white/5 backdrop-blur-md rounded-xl px-4 py-3 border border-white/5 shadow-lg transition-all duration-300">
        <div class="flex items-center gap-3">
            <!-- Desktop Sidebar Toggle -->
            <button @click="$store.sidebar.toggle()" class="hidden lg:flex p-2 hover:bg-white/10 rounded-lg text-slate-300 transition-colors">
                <flux:icon name="bars-2" class="w-6 h-6" />
            </button>
            
            <div>
                <h1 class="text-xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-200 to-blue-200">
                    Dashboard
                </h1>
                <p class="text-slate-400 text-[10px] font-medium">{{ now()->format('l, d F Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
             <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 p-[1px]">
                    <div class="w-full h-full rounded-full bg-slate-900 flex items-center justify-center text-white font-bold text-[10px] uppercase">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                </div>
                <div class="flex flex-col text-right">
                    <span class="text-xs font-bold text-white leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-slate-400 uppercase tracking-wider">{{ auth()->user()->area?->nama_area ?? 'PUSAT' }}</span>
                </div>
            </div>
        </div>
    </header>

    <!-- === STATS GRID (Compact Row) === -->
    <div class="shrink-0 grid grid-cols-1 md:grid-cols-4 gap-4 z-10">
        
        <!-- Sales -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-indigo-900/50 to-slate-900 border border-indigo-500/20 shadow-lg group hover:border-indigo-500/40 transition-all p-4">
            <div class="flex justify-between items-start">
                <div>
                   <p class="text-[10px] uppercase tracking-wider text-indigo-300 font-semibold">Total Sales (Today)</p>
                   <h3 class="text-2xl font-bold text-white mt-1 shadow-glow-indigo">Rp {{ number_format($totalSalesToday ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="p-1.5 bg-indigo-500/20 rounded-lg text-indigo-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-2 flex items-center text-[10px] text-indigo-400/80">
                <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse mr-1.5"></span> Live
            </div>
        </div>

        <!-- Cash In -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-emerald-900/50 to-slate-900 border border-emerald-500/20 shadow-lg group hover:border-emerald-500/40 transition-all p-4">
            <div class="flex justify-between items-start">
                <div>
                   <p class="text-[10px] uppercase tracking-wider text-emerald-300 font-semibold">Cash In (Today)</p>
                   <h3 class="text-2xl font-bold text-white mt-1">Rp {{ number_format($totalCashInToday ?? 0, 0, ',', '.') }}</h3>
                </div>
                <div class="p-1.5 bg-emerald-500/20 rounded-lg text-emerald-300">
                     <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-2 text-[10px] text-emerald-400/80">
                Setoran Masuk
            </div>
        </div>

        <!-- Production -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-fuchsia-900/50 to-slate-900 border border-fuchsia-500/20 shadow-lg group hover:border-fuchsia-500/40 transition-all p-4">
             <div class="flex justify-between items-start">
                <div>
                   <p class="text-[10px] uppercase tracking-wider text-fuchsia-300 font-semibold">Active Orders</p>
                   <h3 class="text-2xl font-bold text-white mt-1">{{ $activeProOrders ?? 0 }}</h3>
                </div>
                 <div class="p-1.5 bg-fuchsia-500/20 rounded-lg text-fuchsia-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
            </div>
             <div class="mt-2 text-[10px] text-fuchsia-400/80">
                In Progress
            </div>
        </div>

        <!-- Combined Master -->
        <div class="grid grid-rows-2 gap-2">
            <div class="bg-white/5 rounded-lg px-4 py-2 border border-white/5 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Items</span>
                <span class="text-sm font-bold text-white">{{ $jumlahBarang ?? 0 }}</span>
            </div>
             <div class="bg-white/5 rounded-lg px-4 py-2 border border-white/5 flex items-center justify-between">
                <span class="text-xs text-slate-400 font-medium">Suppliers</span>
                <span class="text-sm font-bold text-white">{{ $jumlahSupplier ?? 0 }}</span>
            </div>
        </div>

    </div>

    <!-- === TABLES (Fill Remaining Height) === -->
    <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-2 gap-4 z-10">
        
        <!-- Supply Table -->
        <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl border border-white/5 flex flex-col h-full overflow-hidden">
            <div class="px-4 py-3 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <h3 class="text-sm font-semibold text-slate-200">Recent Supply In</h3>
                <a href="{{ route('rekapbrgmsk') }}" class="text-[10px] font-medium text-blue-300 hover:text-white transition-colors">View All</a>
            </div>
            <div class="flex-1 overflow-y-auto w-full">
                <table class="w-full text-xs text-left text-slate-400">
                    <thead class="text-[10px] text-slate-500 uppercase bg-black/20 sticky top-0 backdrop-blur-md">
                        <tr>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Supplier</th>
                            <th class="px-4 py-2">PO</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentPurchases as $purchase)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-2.5 whitespace-nowrap">{{ \Carbon\Carbon::parse($purchase->tanggal)->format('d/m') }}</td>
                            <td class="px-4 py-2.5 text-slate-300 font-medium truncate max-w-[120px]">{{ $purchase->supplier->nama_supplier ?? '-' }}</td>
                            <td class="px-4 py-2.5">{{ $purchase->no_po ?? '-' }}</td>
                        </tr>
                        @empty
                         <tr><td colspan="3" class="p-4 text-center italic">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cash Table -->
        <div class="bg-slate-900/50 backdrop-blur-sm rounded-xl border border-white/5 flex flex-col h-full overflow-hidden">
            <div class="px-4 py-3 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
                <h3 class="text-sm font-semibold text-slate-200">Recent Cash In</h3>
                <a href="{{ route('uangmsk') }}" class="text-[10px] font-medium text-emerald-300 hover:text-white transition-colors">View All</a>
            </div>
            <div class="flex-1 overflow-y-auto w-full">
                <table class="w-full text-xs text-left text-slate-400">
                     <thead class="text-[10px] text-slate-500 uppercase bg-black/20 sticky top-0 backdrop-blur-md">
                        <tr>
                            <th class="px-4 py-2">Shop</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                            <th class="px-4 py-2">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentCashIns as $cash)
                         <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-4 py-2.5 text-slate-300 font-medium truncate max-w-[100px]">{{ $cash->tokos->nmtoko ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-emerald-400">{{ number_format($cash->jumlah_uang, 0, ',', '.') }}</td>
                            <td class="px-4 py-2.5 truncate max-w-[100px]">{{ $cash->keterangan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-4 text-center italic">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
