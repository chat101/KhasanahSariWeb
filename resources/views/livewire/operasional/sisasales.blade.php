<div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

<div
data-kontribusi-tabs
x-data="{
    tab: 'target',
    h: 0,
    syncH() { this.$nextTick(() => this.h = this.$refs.panel?.scrollHeight || 0) }
}"
x-init="syncH()"
class="space-y-4"
>

        {{-- HEADER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                        LK
                    </span>
                    Laporan Kontribusi
                </h2>
                <span class="text-[11px] text-gray-500">Monitoring kontribusi</span>
            </div>

            {{-- TAB --}}
            <div class="flex items-center gap-2 text-[11px]">
                <button type="button" @click="tab='target'; syncH()"
                    :class="tab === 'target' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg border border-gray-200">
                    Report by Target
                </button>

                <button type="button" @click="tab='bulanlalu'; syncH()"
                    :class="tab === 'bulanlalu' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-3 py-1.5 rounded-lg border border-gray-200">
                    By Bulan Lalu
                </button>
            </div>
        </div>

        {{-- WRAPPER (biar tinggi ikut animasi) --}}
        <div class="relative">
            <div x-ref="panel">

                {{-- ================= TAB 1: REPORT BY TARGET ================= --}}
                <div x-cloak x-show="tab==='target'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 absolute inset-0"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2">
                    <div class="space-y-4">

                        {{-- FILTER --}}
                        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
                                {{-- <div>
                                    <label class="block mb-1 text-gray-500">TOKO</label>

                                    <div class="w-full border rounded-lg px-2 py-2 bg-gray-50">
                                        @if (empty($tokosUser))
                                            <span class="text-[11px] text-gray-500">Tidak ada toko untuk user ini.</span>
                                        @else
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($tokosUser as $t)
                                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[11px] border border-indigo-100">
                                                        {{ $t['nmtoko'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex items-end justify-end">
                                        <div class="text-right text-[11px] text-gray-500">
                                            Total Neto:
                                            <span
                                                class="font-semibold text-black">{{ number_format($sumNetoTarget ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-1">
                                        Otomatis sesuai Area/Wilayah user.
                                    </div>
                                </div> --}}

                                <div class="md:col-span-2">
                                    <label class="block mb-1 text-gray-500">Periode</label>
                                    <div class="flex gap-2 items-center">
                                        <input type="date" wire:model.live="tanggalAwal"
                                            class="w-full border rounded-lg px-2 py-1">
                                        <span class="text-gray-400 text-[11px]">sd</span>
                                        <input type="date" wire:model.live="tanggalAkhir"
                                            class="w-full border rounded-lg px-2 py-1">
                                    </div>
                                </div>

                                <div class="flex items-end gap-2 md:col-span-2 justify-end">
                                    <button
                                    wire:click="loadTarget"
                                    wire:loading.attr="disabled"
                                    wire:target="loadTarget"
                                    class="relative px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="loadTarget">
                                        Tampilkan
                                    </span>

                                    <span wire:loading wire:target="loadTarget" class="flex items-center gap-1">
                                        <svg class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4" fill="none" />
                                            <path class="opacity-75" fill="currentColor"
                                                  d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                        </svg>
                                        Memuat...
                                    </span>
                                </button>
                                    <button wire:click="resetTarget"
                                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-[11px]">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TABLE --}}
                        <div class="relative bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">

                            {{-- LOADING OVERLAY --}}
                            <div
                            wire:loading
                            wire:target="loadTarget"
                            class="absolute inset-0 z-20 flex items-center justify-center
                                   bg-white/70 backdrop-blur-sm"
                        >
                            <div class="flex flex-col items-center gap-2 text-xs text-gray-700">
                                <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" fill="none"/>
                                    <path class="opacity-75" fill="currentColor"
                                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                </svg>
                                <span>Menghitung kontribusi per toko…</span>
                            </div>
                        </div>
                            <table class="min-w-[1200px] w-full text-xs text-left">
                                <thead class="text-[11px] uppercase text-gray-600">
                                    <tr class="border-b">

                                        <th colspan="4"
                                            class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
                                            by target proyeksi
                                        </th>

                                        <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">DISC Manual
                                        </th>
                                        <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Retur</th>

                                        <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Gas
                                        </th>
                                        <th colspan="1" class="px-3 py-2 text-center bg-gray-50 border-r">Telur
                                        </th>
                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Loss bahan
                                        </th>
                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50">total kontribusi
                                        </th>
                                    </tr>

                                    <tr class="border-b">
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Outlet
                                        </th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Selisih
                                            %</th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">(+/-)
                                            Rp</th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                                            kontribusi </th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">% </th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Rp</th>

                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse(($rowsTarget ?? []) as $r)
                                        <tr class="hover:bg-gray-50">

                                            <td class="px-3 py-2 border-r text-left font-medium">
                                                {{ $r['outlet'] ?? '-' }}
                                            </td>

                                            @php
                                            $sp = is_null($r['selisih_persen'] ?? null) ? null : (float) $r['selisih_persen'];
                                            $sr = (int) ($r['selisih_rp'] ?? 0);

                                            $clsP = is_null($sp) ? 'text-gray-500' : ($sp < 0 ? 'text-rose-700 bg-rose-50' : ($sp > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50'));
                                            $clsR = $sr < 0 ? 'text-rose-700 bg-rose-50' : ($sr > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');
                                        @endphp

                                        <td class="px-3 py-2 border-r text-center font-semibold {{ $clsP }}">
                                            @if(is_null($sp))
                                                -
                                            @else
                                                {{ number_format($sp, 2, ',', '.') }}
                                            @endif
                                        </td>

                                        <td class="px-3 py-2 border-r text-right font-semibold {{ $clsR }}">
                                            {{ $sr === 0 ? '-' : number_format($sr, 0, ',', '.') }}
                                        </td>


                                            <td class="px-3 py-2 border-r text-right font-semibold">
                                                {{ number_format($r['kontribusi_rp'] ?? 0, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 border-r text-center">
                                                {{ $r['kontribusi_persen'] ?? '-' }}</td>
                                            <td class="px-3 py-2 border-r text-right font-semibold"></td>

                                            <td class="px-3 py-2 border-r text-center">
                                                {{ is_null($r['sc_manual_persen'] ?? null) ? '-' : number_format($r['sc_manual_persen'], 2, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 border-r text-right font-semibold
                                            {{ ((int)($r['sc_manual_rp'] ?? 0)) < 0 ? 'text-rose-700 bg-rose-50' : (((int)($r['sc_manual_rp'] ?? 0)) > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50') }}">
                                            @php($v = (int)($r['sc_manual_rp'] ?? 0))
                                            {{ $v === 0 ? '-' : number_format($v, 0, ',', '.') }}
                                        </td>

                                            <td class="px-3 py-2 border-r text-center">{{ $r['retur_persen'] ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['retur_rp'] ?? 0, 0, ',', '.') }}</td>

                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['gas_rp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['telur_rp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['loss_bahan'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right font-semibold">
                                                {{ number_format($r['total_kontribusi'] ?? 0, 0, ',', '.') }}</td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="14" class="px-3 py-6 text-center text-gray-500">
                                                Belum ada data.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-indigo-50 border-t border-indigo-300 text-indigo-900 font-bold">
                                    <tr class="font-semibold text-xs">

                                        {{-- LABEL --}}
                                        <td class="px-3 py-2 border-r text-right" colspan="3">
                                            TOTAL
                                        </td>

                                        {{-- KONTRIBUSI --}}
                                        <td class="px-3 py-2 border-r text-right">
                                            {{ number_format($sumNetoTarget ?? 0, 0, ',', '.') }}
                                        </td>

                                        {{-- % --}}
                                        <td class="px-3 py-2 border-r text-center">-</td>
                                        <td class="px-3 py-2 border-r text-right">-</td>

                                        {{-- DISC MANUAL --}}
                                        <td class="px-3 py-2 border-r text-center">-</td>
                                        <td class="px-3 py-2 border-r text-right">-</td>

                                        {{-- RETUR --}}
                                        <td class="px-3 py-2 border-r text-center">-</td>
                                        <td class="px-3 py-2 border-r text-right">-</td>

                                        {{-- GAS --}}
                                        <td class="px-3 py-2 border-r text-right">
                                            {{ number_format($totalGas ?? 0, 0, ',', '.') }}
                                        </td>

                                        {{-- TELUR --}}
                                        <td class="px-3 py-2 border-r text-right">
                                            {{ number_format($totalTelur ?? 0, 0, ',', '.') }}
                                        </td>

                                        {{-- LOSS --}}
                                        <td class="px-3 py-2 border-r text-right">
                                            {{ number_format($totalLoss ?? 0, 0, ',', '.') }}
                                        </td>

                                        {{-- TOTAL KONTRIBUSI --}}
                                        <td class="px-3 py-2 text-right">
                                            {{ number_format($sumNetoTarget ?? 0, 0, ',', '.') }}
                                        </td>

                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    </div>
                </div>

                {{-- ================= TAB 2: BY BULAN LALU ================= --}}
                {{-- ================= TAB 2: BY BULAN LALU ================= --}}
                <div x-cloak x-show="tab==='bulanlalu'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 absolute inset-0"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-2">
                    <div class="space-y-4">

                        {{-- FILTER --}}
                        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
                                <div>
                                    <label class="block mb-1 text-gray-500">PILIH TOKO</label>
                                    <select wire:model.live="tokoBulanLalu"
                                        class="w-full border rounded-lg px-2 py-1">
                                        <option value="">Drop Down toko</option>
                                        @foreach ($listToko ?? [] as $t)
                                            <option value="{{ $t->kode }}">{{ $t->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block mb-1 text-gray-500">Periode (Bulan Lalu)</label>
                                    <div class="flex gap-2 items-center">
                                        <input type="date" wire:model.live="bulanLaluAwal"
                                            class="w-full border rounded-lg px-2 py-1">
                                        <span class="text-gray-400 text-[11px]">sd</span>
                                        <input type="date" wire:model.live="bulanLaluAkhir"
                                            class="w-full border rounded-lg px-2 py-1">
                                    </div>
                                </div>

                                <div class="flex items-end gap-2 md:col-span-2 justify-end">
                                    <button wire:click="loadBulanLalu"
                                        class="px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px]">
                                        Tampilkan
                                    </button>
                                    <button wire:click="resetBulanLalu"
                                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-[11px]">
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TABLE (SAMA DENGAN TARGET) --}}
                        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">
                            <table class="min-w-[1200px] w-full text-xs text-left">
                                <thead class="text-[11px] uppercase text-gray-600">
                                    <tr class="border-b">
                                        <th rowspan="2" class="px-3 py-2 bg-gray-50 border-r">HARI</th>
                                        <th rowspan="2" class="px-3 py-2 bg-gray-50 border-r">TANGGAL</th>

                                        <th colspan="4"
                                            class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
                                            by target proyeksi
                                        </th>

                                        <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">SC Manual
                                        </th>
                                        <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Retur
                                        </th>

                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Gas (Rp)
                                        </th>
                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Telur
                                            (Rp)</th>
                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Loss
                                            bahan</th>
                                        <th rowspan="2" class="px-3 py-2 text-center bg-gray-50">total kontribusi
                                        </th>
                                    </tr>

                                    <tr class="border-b">
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                                            Selisih %</th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">(+/-)
                                            Rp</th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                                            kontribusi %</th>
                                        <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Rp
                                        </th>

                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                                        <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse(($rowsBulanLalu ?? []) as $r)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 border-r text-[11px]">{{ $r['hari'] ?? '-' }}</td>
                                            <td class="px-3 py-2 border-r text-[11px]">{{ $r['tanggal'] ?? '-' }}</td>

                                            <td class="px-3 py-2 border-r text-center">
                                                {{ $r['selisih_persen'] ?? '-' }}</td>
                                            <td class="px-3 py-2 border-r text-right font-medium">
                                                {{ number_format($r['selisih_rp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 border-r text-center">
                                                {{ $r['kontribusi_persen'] ?? '-' }}</td>
                                            <td class="px-3 py-2 border-r text-right font-semibold">
                                                {{ number_format($r['kontribusi_rp'] ?? 0, 0, ',', '.') }}</td>

                                            <td class="px-3 py-2 border-r text-center">
                                                {{ $r['sc_manual_persen'] ?? '-' }}</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['sc_manual_rp'] ?? 0, 0, ',', '.') }}</td>

                                            <td class="px-3 py-2 border-r text-center">{{ $r['retur_persen'] ?? '-' }}
                                            </td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['retur_rp'] ?? 0, 0, ',', '.') }}</td>

                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['gas_rp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['telur_rp'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ number_format($r['loss_bahan'] ?? 0, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right font-semibold">
                                                {{ number_format($r['total_kontribusi'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="14" class="px-3 py-6 text-center text-gray-500">
                                                Belum ada data bulan lalu.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('message.processed', (message, component) => {
                // panggil syncH Alpine di root x-data
                const root = document.querySelector('[data-kontribusi-tabs]');
                if (!root) return;

                // akses alpine component dan panggil methodnya
                const x = Alpine.$data(root);
                if (x && typeof x.syncH === 'function') x.syncH();
            });
        });
    </script>
</div>
