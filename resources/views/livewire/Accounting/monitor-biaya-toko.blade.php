<div>
    <div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

            {{-- Header + Filter (compact) --}}
            <div
                class="px-3 py-2 border-b border-gray-200 dark:border-zinc-700
                        flex flex-col md:flex-row md:items-center md:justify-between gap-2">

                <div>
                    <h2 class="text-sm font-semibold flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
                        </svg>
                        <span>Monitoring Biaya per Toko</span>
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-0.5">
                        Periode {{ $startDate }} s/d {{ $endDate }}.
                    </p>
                </div>

                <div class="flex flex-col md:flex-row gap-2 md:items-end">

                    {{-- Pilih Toko --}}
                    <div>
                        <label class="block text-[10px] text-gray-500 dark:text-zinc-400 mb-0.5">Toko</label>
                        <select wire:model="tokoId"
                            class="w-44 px-2 py-1 rounded-md border border-gray-300 dark:border-zinc-600
                                   text-xs bg-white dark:bg-zinc-800">
                            <option value="">-- Pilih Toko --</option>
                            <option value="all">ALL TOKO</option> {{-- <-- Tambahan --}}
                            @foreach ($listToko as $t)
                                <option value="{{ $t->id }}">{{ $t->nmtoko }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="flex gap-2">
                        <div>
                            <label class="block text-[10px] text-gray-500 dark:text-zinc-400 mb-0.5">Mulai</label>
                            <input type="date" wire:model="startDate"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-zinc-600
                                       text-xs bg-white dark:bg-zinc-800 w-32">
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-500 dark:text-zinc-400 mb-0.5">Sampai</label>
                            <input type="date" wire:model="endDate"
                                class="px-2 py-1 rounded-md border border-gray-300 dark:border-zinc-600
                                       text-xs bg-white dark:bg-zinc-800 w-32">
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex gap-1">
                        <button wire:click="syncRealisasiFromApi"
                        wire:loading.attr="disabled"
                        wire:target="syncRealisasiFromApi"
                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md
                               text-[11px] font-semibold shadow-sm flex items-center gap-1">

                    <span wire:loading.remove wire:target="syncRealisasiFromApi">
                        Sync API
                    </span>

                    <svg wire:loading wire:target="syncRealisasiFromApi"
                         class="animate-spin h-3 w-3 text-white" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-30" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4" />
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                </button>

                        <button wire:click="openBudgetModal"
                            class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md
                                   text-[11px] font-semibold shadow-sm">
                            Budget
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ringkasan (compact) --}}
            {{-- Ringkasan (compact) --}}
            <div
                class="px-3 py-2 grid grid-cols-1 md:grid-cols-4 gap-2 text-xs border-b border-gray-100 dark:border-zinc-700">
                <div class="mt-0.5 text-xs font-semibold text-indigo-900 dark:text-indigo-50 truncate">
                    @if ($tokoId === 'all')
                        SEMUA TOKO
                    @else
                        {{ optional($listToko->firstWhere('id', $tokoId))->nmtoko ?? '-' }}
                    @endif
                </div>

                <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                    <div class="text-[10px] text-emerald-700 dark:text-emerald-200">Total Budget</div>
                    <div class="mt-0.5 text-sm font-semibold text-emerald-800 dark:text-emerald-100">
                        Rp {{ number_format($totalBudget, 0, ',', '.') }}
                    </div>
                </div>

                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                    <div class="text-[10px] text-amber-700 dark:text-amber-200">Total Biaya</div>
                    <div class="mt-0.5 text-sm font-semibold text-amber-800 dark:text-amber-100">
                        Rp {{ number_format($totalRealisasi, 0, ',', '.') }}
                    </div>
                </div>

                {{-- 🔵 CARD BARU: TOTAL PENJUALAN --}}
                <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-900/30">
                    <div class="text-[10px] text-sky-700 dark:text-sky-200">Total Penjualan</div>
                    <div class="mt-0.5 text-sm font-semibold text-sky-800 dark:text-sky-100">
                        Rp {{ number_format($totalPenjualan ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="relative">
                {{-- LOADING OVERLAY --}}
                {{-- LOADING OVERLAY --}}
                <div wire:loading.delay wire:target="tokoId, startDate, endDate, syncRealisasiFromApi"
                    class="absolute inset-0 bg-white/80 dark:bg-black/40 backdrop-blur-sm flex items-center justify-center z-30 rounded-lg">

                    <div class="flex flex-col items-center gap-2 animate-fade-in">
                        <svg class="animate-spin h-7 w-7 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-30" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>

                        <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">
                            Memuat data...
                        </span>
                    </div>
                </div>


                {{-- Tabel (compact) --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-[11px]">
                        <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1 text-left w-8">No</th>
                                <th class="px-2 py-1 text-left w-16">ID Akun</th>
                                <th class="px-2 py-1 text-left">Tipe</th>
                                <th class="px-2 py-1 text-right w-24">Budget</th>
                                <th class="px-2 py-1 text-right w-24">Realisasi</th>
                                <th class="px-2 py-1 text-right w-24">Sisa</th>
                                <th class="px-2 py-1 text-center w-32">Progress</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-zinc-700
                                   text-gray-700 dark:text-zinc-200">

                            @forelse ($items as $idx => $row)
                                @php
                                    $sisa = $row->budget - $row->realisasi;
                                    $persen = $row->budget > 0 ? ($row->realisasi / $row->budget) * 100 : 0;
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                    <td class="px-2 py-1 align-middle">{{ $idx + 1 }}</td>
                                    <td class="px-2 py-1 align-middle whitespace-nowrap">{{ $row->idakun_api }}</td>
                                    <td class="px-2 py-1 align-middle truncate max-w-[140px]">
                                        {{ $row->tipe_api ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1 align-middle text-right">
                                        {{ number_format($row->budget, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1 align-middle text-right">
                                        {{ number_format($row->realisasi, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1 align-middle text-right">
                                        {{ number_format($sisa, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-1 align-middle">
                                        <div class="flex flex-col gap-0.5">
                                            <div
                                                class="w-full h-1 rounded-full bg-gray-200 dark:bg-zinc-700 overflow-hidden">
                                                <div class="h-1 rounded-full
                                                @if ($persen < 70) bg-emerald-500
                                                @elseif($persen < 100) bg-amber-500
                                                @else bg-red-500 @endif"
                                                    style="width: {{ min($persen, 100) }}%"></div>
                                            </div>
                                            <div class="text-[9px] text-center text-gray-500 dark:text-zinc-400">
                                                {{ number_format($persen, 1, ',', '.') }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="px-3 py-4 text-center text-gray-500 dark:text-zinc-400 text-[11px]">
                                        Belum ada data budget / realisasi untuk filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if ($showBudgetModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center">
                {{-- overlay --}}
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

                {{-- dialog --}}
                <div
                    class="relative bg-white dark:bg-zinc-800 w-full max-w-xl
                    rounded-lg shadow-lg border border-gray-200 dark:border-zinc-700 p-4">

                    <div
                        class="flex items-center justify-between mb-3 border-b border-gray-200 dark:border-zinc-700 pb-2">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-zinc-100">
                                Atur Budget Bulanan
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">
                                Toko: {{ optional($listToko->firstWhere('id', $this->tokoId))->nmtoko }}<br>
                                Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('F Y') }}
                            </p>
                        </div>
                        <button wire:click="$set('showBudgetModal', false)"
                            class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-zinc-700">
                            <svg class="w-4 h-4 text-gray-500 dark:text-zinc-300" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 18L18 6M6 6L18 18" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="max-h-80 overflow-y-auto border border-gray-100 dark:border-zinc-700 rounded-md">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 dark:bg-zinc-900">
                                <tr>
                                    <th class="px-2 py-1 text-left">Akun</th>
                                    <th class="px-2 py-1 text-left ">Keterangan</th>
                                    <th class="px-2 py-1 text-right">Budget / Bulan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">

                                {{-- TAMBAHKAN BAGIAN INI --}}
                                @php
                                    $haris = [
                                        'senin' => 'Sen',
                                        'selasa' => 'Sel',
                                        'rabu' => 'Rab',
                                        'kamis' => 'Kam',
                                        'jumat' => 'Jum',
                                        'sabtu' => 'Sab',
                                        'minggu' => 'Min',
                                    ];
                                @endphp

                                @foreach ($items as $row)
                                    <tr class="bg-white dark:bg-zinc-800">
                                        <td class="px-2 py-1 whitespace-nowrap">
                                            {{ $row->idakun_api }}
                                        </td>
                                        <td class="px-2 py-1">
                                            {{ $row->tipe_api ?? ($row->ket_api ?? '-') }}
                                        </td>

                                        <td class="px-2 py-1 text-right">

                                            {{-- Input default (rupiah/persen) --}}
                                            <div class="flex items-center justify-end gap-1.5 mb-1">
                                                <select wire:model="budgetTypes.{{ $row->idakun_api }}"
                                                    class="text-[10px] border border-gray-300 dark:border-zinc-600 rounded px-1 py-0.5">
                                                    <option class="text-black" value="rupiah">Rp / hari</option>
                                                    <option class="text-black" value="persen">% dari penjualan</option>
                                                </select>

                                                <input type="text" x-data="moneyFormat()"
                                                    x-on:input="formatInput($event)"
                                                    class="w-24 text-right border border-gray-300 dark:border-zinc-600 rounded px-2 py-1 text-xs"
                                                    wire:model.live="budgetInputs.{{ $row->idakun_api }}"
                                                    placeholder="0">
                                            </div>

                                            {{-- Input per hari --}}
                                            <div class="grid grid-cols-2 gap-0.5 text-[9px]">
                                                @foreach ($haris as $keyHari => $labelHari)
                                                    <div class="flex items-center justify-between gap-1">
                                                        <span>{{ $labelHari }}</span>
                                                        <input type="text" x-data="moneyFormat()"
                                                            x-on:input="formatInput($event)"
                                                            class="w-20 text-right border border-gray-200 dark:border-zinc-700 rounded px-1 py-0.5 text-[9px]"
                                                            wire:model.live="dailyBudgets.{{ $row->idakun_api }}.{{ $keyHari }}"
                                                            placeholder="0">
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Fallback indicator --}}
                                            @if (!empty($fallbackInfo[$row->idakun_api]))
                                                <div class="text-[10px] text-amber-600 mt-1 italic">
                                                    {{ $fallbackInfo[$row->idakun_api] }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>


                    <div class="flex justify-end gap-2 mt-3">
                        <button type="button" wire:click="$set('showBudgetModal', false)"
                            class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded text-xs">
                            Batal
                        </button>
                        <button type="button" wire:click="saveBudgets"
                            class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs">
                            Simpan Budget
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
    @push('scripts')
        <script>
            function moneyFormat() {
                return {
                    formatInput(e) {
                        // hanya angka
                        let val = e.target.value.replace(/[^0-9]/g, '');

                        if (val === '') {
                            e.target.value = '';
                            return;
                        }

                        // tambahkan titik ribuan: 1000000 → 1.000.000
                        val = val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

                        e.target.value = val;
                    }
                }
            }
        </script>
    @endpush
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.25s ease-out both;
        }
        </style>

</div>
