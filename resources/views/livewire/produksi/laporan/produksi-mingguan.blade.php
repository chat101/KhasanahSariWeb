<div class="p-4 space-y-4 bg-gray-900 text-gray-100 text-sm rounded-lg shadow-lg" x-data="{ activeTab: $wire.entangle('activeTab').live }">
    {{-- Date Range Filter --}}
    <div x-show="activeTab !== 'complain'" x-cloak
        class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 bg-black/30 p-3 rounded-lg">
        <div class="flex items-center gap-2">
            <div>
                <label class="text-xs text-gray-300">Dari</label>
                <input type="date" class="border rounded px-2 py-1 text-white-900" wire:model.live="tanggalAwal">
            </div>
            <div>
                <label class="text-xs text-gray-300">Sampai</label>
                <input type="date" class="border rounded px-2 py-1 text-white-900" wire:model.live="tanggalAkhir">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 rounded" wire:click="loadData">
                Terapkan
            </button>
            <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 rounded" wire:click="exportActive">
                Export
            </button>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex space-x-2 border-b border-gray-700">
        <!-- Brownis & Cake = BLUE glow -->
        <button @click="activeTab = 'browniscake'"
          :class="activeTab === 'browniscake'
            ? 'border-b-2 border-sky-500 text-sky-400 font-semibold relative rounded-t px-4 py-2 \
               before:content-[\'\'] before:absolute before:-inset-2 before:rounded-xl \
               before:bg-gradient-to-r before:from-sky-400 before:via-blue-500 before:to-indigo-600 \
               before:blur-lg before:opacity-80 before:-z-10 before:pointer-events-none \
               shadow-[0_0_12px_rgba(59,130,246,0.7)] [text-shadow:0_0_8px_rgba(59,130,246,0.8)]'
            : 'text-gray-400 hover:text-blue-300 px-4 py-2 rounded-t'"
          class="text-sm transition-all duration-300">
          Brownis & Cake
        </button>

        <!-- Kue Kering = GREEN glow -->
        <button @click="activeTab = 'kuker'"
          :class="activeTab === 'kuker'
            ? 'border-b-2 border-emerald-500 text-emerald-400 font-semibold relative rounded-t px-4 py-2 \
               before:content-[\'\'] before:absolute before:-inset-2 before:rounded-xl \
               before:bg-gradient-to-r before:from-emerald-400 before:via-teal-500 before:to-green-600 \
               before:blur-lg before:opacity-80 before:-z-10 before:pointer-events-none \
               shadow-[0_0_12px_rgba(16,185,129,0.7)] [text-shadow:0_0_8px_rgba(16,185,129,0.8)]'
            : 'text-gray-400 hover:text-emerald-300 px-4 py-2 rounded-t'"
          class="text-sm transition-all duration-300">
          Kue Kering
        </button>

        <!-- Complain = ROSE/ORANGE glow -->
        <button @click="activeTab = 'complain'"
          :class="activeTab === 'complain'
            ? 'border-b-2 border-rose-500 text-rose-400 font-semibold relative rounded-t px-4 py-2 \
               before:content-[\'\'] before:absolute before:-inset-2 before:rounded-xl \
               before:bg-gradient-to-r before:from-rose-400 before:via-fuchsia-500 before:to-orange-500 \
               before:blur-lg before:opacity-80 before:-z-10 before:pointer-events-none \
               shadow-[0_0_12px_rgba(244,63,94,0.7)] [text-shadow:0_0_8px_rgba(244,63,94,0.8)]'
            : 'text-gray-400 hover:text-rose-300 px-4 py-2 rounded-t'"
          class="text-sm transition-all duration-300">
          Complain
        </button>
      </div>



    {{-- Tab Content --}}
    <div class="mt-4 p-4 bg-black rounded-lg shadow border border-gray-700">
        {{-- Brownis & Cake --}}
        <div
        x-show="activeTab === 'browniscake'"
        x-cloak
        wire:key="browniscake-{{ $tanggalAwal }}-{{ $tanggalAkhir }}"
      >
            <div class="overflow-auto">
                <table
                    class="min-w-full text-left text-gray-200 border border-gray-700 bg-gray-800 rounded-lg text-xs sm:text-sm">
                    <thead class="bg-gray-700 text-white text-center">
                        <tr class="text-center">
                            <th class="w-5 px-2 py-2">No</th>
                            <th class="w-5 px-2 py-2">Produk</th>
                            <th class="w-10 px-2 py-2">Patokan</th>
                            <th class="w-20">Produksi (Tong)</th>
                            <th class="w-15 px-2 py-2 ">target Produksi</th>
                            <th class="w-10 px-2 py-2 ">Real</th>
                            <th class="w-20">Pengalihan Produk</th>
                            <th class="w-20">+- Target vs Real</th>
                            <th class="w-25">%</th>
                            <th class="w-15">Distrbusi</th>
                            <th class="w-24">Complain</th>
                            <th class="w-20">+- Real vs Distribusi</th>
                            <th class="w-20">Reject Produksi</th>
                            <th class="w-20">Reject dari Dekor</th>
                            <th class="w-15">Total Reject</th>
                            <th class="w-30">RP</th>
                            <th class="w-15">%</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-700 text-center">
                        @forelse($brownisCakeData as $i => $row)
                            @php
                                $isSubtotal = $row['nama'] === 'Subtotal NON-CAKE' || $row['nama'] === 'Subtotal CAKE';
                                $isGrandTotal = $row['nama'] === 'GRAND TOTAL';
                            @endphp

                            <tr
                                class="{{ $isGrandTotal
                                    ? 'relative bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-emerald-500/15 text-emerald-100 font-bold ring-1 ring-emerald-400/40 shadow-[0_0_28px_rgba(16,185,129,0.35)]'
                                    : ($isSubtotal
                                        ? 'relative bg-gradient-to-r from-amber-400/15 via-yellow-400/10 to-amber-400/15 text-yellow-100 font-semibold ring-1 ring-yellow-400/40 shadow-[0_0_28px_rgba(250,204,21,0.35)]'
                                        : 'hover:bg-gray-700/50 transition') }}">
                                <td class="px-2 py-2">
                                    {{ !$isSubtotal && !$isGrandTotal ? $i + 1 : '' }}
                                </td>
                                <td class="w-40 text-left">{{ $row['nama'] }}</td>
                                <td class="w-15 px-2 py-2">{{ $row['patokan'] ?? '-' }}</td>
                                <td class="w-20 px-2 py-2">
                                    {{ number_format((float) ($row['total_qty'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['total_target_produksi'] ?? 0), 0, ',', '.') }}</td>
                                <td class="w-10 px-2 py-2">
                                    {{ number_format((float) ($row['real_total'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['po_pengalihan'] ?? 0), 0, ',', '.') }}</td>
                                <td
                                    class="px-2 py-2 {{ ($row['target_vs_real'] ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                    {{ number_format((float) ($row['target_vs_real'] ?? 0), 0, ',', '.') }}
                                </td>
                                <td
                                    class="px-2 py-2 {{ ($row['percent_target_vs_real'] ?? 0) < 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                    {{ number_format((float) ($row['percent_target_vs_real'] ?? 0), 1, ',', '.') }} %
                                </td>
                                <td class="px-2 py-2">{{ number_format((float) ($row['dist'] ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2">{{ number_format((float) ($row['complain'] ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['realvsdist'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['returproduksi'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['returjadi'] ?? 0), 0, ',', '.') }}</td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['totalretur'] ?? 0), 0, ',', '.') }}</td>
                                        {{-- <td class="px-2 py-2">
                                            totalretur</td> --}}
                                <td class="px-2 py-2">Rp {{ number_format((float) ($row['hpp'] ?? 0), 2, ',', '.') }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ number_format((float) ($row['persenretur'] ?? 0), 2, ',', '.') }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="text-center text-gray-400 py-4">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- Kue Kering --}}
        <div x-show="activeTab === 'kuker'" x-cloak>
            <div class="overflow-auto">
                <table
                    class="min-w-full text-left text-gray-200 border border-gray-700 bg-gray-800 rounded-lg text-xs sm:text-sm">
                    <thead class="bg-gray-700 text-white text-center">
                        <tr>
                            <th class="px-2 py-2">No</th>
                            <th class="px-2 py-2">Produk</th>
                            <th class="px-2 py-2">Patokan</th>
                            <th class="w-20">Produksi (Tong)</th>
                            <th class="px-2 py-2">Target Produksi</th>
                            <th class="px-2 py-2">Hasil Produksi</th>
                            <th class="px-2 py-2">Reject</th>
                            <th class="px-2 py-2">Sample</th>
                            <th class="px-2 py-2">+-Target VS produksi</th>
                            <th class="px-2 py-2">STOK AWAL</th>
                            <th class="px-2 py-2">COMPLAIN</th>
                            <th class="px-2 py-2">DISTRIBUSI</th>
                            <th class="px-2 py-2">STOK SISTEM</th>
                            <th class="px-2 py-2 hidden">STOK AKTUAL</th>
                            <th class="px-2 py-2 hidden">+- SISTEM VS AKTUAL</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-700 text-center">
                        @forelse($kukerData as $i => $row)
                            @php
                                $isSubtotal = !empty($row['is_subtotal']);
                                $isGrandTotal = !empty($row['is_grandtotal']);
                                $isTotal = !empty($row['is_total']); // baris total biasa

                                $rowClass = $isGrandTotal
                                    ? 'relative bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-emerald-500/15 text-emerald-100 font-bold ring-1 ring-emerald-400/40 shadow-[0_0_28px_rgba(16,185,129,0.35)]'
                                    : ($isSubtotal
                                        ? 'relative bg-gradient-to-r from-amber-400/15 via-yellow-400/10 to-amber-400/15 text-yellow-100 font-semibold ring-1 ring-yellow-400/40 shadow-[0_0_28px_rgba(250,204,21,0.35)]'
                                        : ($isTotal
                                            ? 'relative bg-gradient-to-r from-sky-500/15 via-sky-500/10 to-sky-500/15 text-sky-100 font-semibold ring-1 ring-sky-400/40 shadow-[0_0_28px_rgba(56,189,248,0.35)]'
                                            : 'hover:bg-gray-700/50 transition'));
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td class="px-2 py-2">
                                    @if (empty($row['is_total']) && empty($row['is_subtotal']) && empty($row['is_grandtotal']))
                                        {{ $i + 1 }}
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-left">{{ $row['nama'] }}</td>
                                <td class="px-2 py-2">{{ $row['patokan'] }}</td>
                                {{-- <td class="px-2 py-2">patokan</td> --}}

                                <td class="px-2 py-2">{{ number_format($row['total_qty']) }}</td>
                                <td class="px-2 py-2">{{ number_format($row['total_target_produksi'] ?? 0) }}</td>
                                <td class="px-2 py-2">{{ number_format($row['real_total'] ?? 0) }}</td>
                                <td class="px-2 py-2">{{ number_format($row['totalretur']) }}</td>

                                <td class="px-2 py-2">{{ number_format($row['sample']) }}</td>
                                {{-- <td class="px-2 py-2">sample</td> --}}

                                <td class="px-2 py-2">{{ number_format($row['targetvsrealroker']) }}</td>
                                {{-- <td class="px-2 py-2">targetvsrealroker</td> --}}

                                <td class="px-2 py-2">{{ number_format($row['stok_awal_periode']) }}</td>
                                <td class="px-2 py-2">{{ number_format($row['complain']) }}</td>
                                {{-- <td class="px-2 py-2">complain</td> --}}

                                <td class="px-2 py-2">{{ number_format($row['dist']) }}</td>
                                {{-- <td class="px-2 py-2">dist</td> --}}

                                <td class="px-2 py-2">{{ number_format($row['stok_akhir_periode']) }}</td>
                                <td class="px-2 py-2 hidden">aktual</td>
                                {{-- <td class="px-2 py-2 hidden">{{ number_format($row['realvssistemroker']) }}</td> --}}
                                <td class="px-2 py-2 hidden">realvssistemroker</td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="px-2 py-3 text-center text-gray-400">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- Complain (placeholder) --}}
        <div x-show="activeTab === 'complain'" x-cloak>
            @livewire('produksi.complain')
        </div>

    </div>
</div>
