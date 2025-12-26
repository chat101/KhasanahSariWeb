<div class="space-y-4">

    {{-- HEADER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
                <div class="text-sm font-semibold">
                    KONTRIBUSI HARIAN TOKO
                    @if ($namaToko)
                        {{ $namaToko }}
                    @endif
                </div>
                <div class="text-xs text-gray-500">Outlet sesuai toko_id user (kasir/personil)</div>
            </div>

            <div class="flex flex-wrap items-end gap-2">
                @if (count($tokos) > 0)
                    <div class="flex flex-col gap-1">
                        <div class="text-xs text-gray-500">Pilih Toko</div>
                        <select wire:model="selectedTokoId" class="border rounded-lg px-2 py-1 text-xs w-48">
                            @foreach ($tokos as $t)
                                <option value="{{ $t['id'] }}">{{ strtoupper($t['nmtoko']) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button wire:click="load" wire:loading.attr="disabled"
                    class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="load">Tampilkan</span>
                    <span wire:loading wire:target="load">Mengambil...</span>
                </button>

                <div wire:loading wire:target="loadMissingLive" class="text-[11px] text-amber-700 mt-2">
                    Mengambil data LIVE untuk tanggal yang belum ada snapshot...
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="mt-3 text-xs text-red-600">{{ session('message') }}</div>
        @endif
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
        <table class="min-w-[1400px] w-full text-xs text-left">
            @php
                $fmtRp = fn($v) => number_format((int) ($v ?? 0), 0, ',', '.');

                $fmtPct = function ($v) {
                    if ($v === null || $v === '-') {
                        return '-';
                    }
                    return rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.') . '%';
                };

                $color = function ($v) {
                    $val = (float) ($v ?? 0);
                    if ($val < 0) {
                        return 'text-rose-600 font-medium';
                    }
                    if ($val > 0) {
                        return 'text-emerald-600 font-medium';
                    }
                    return 'text-gray-400';
                };

                $colorFooter = function ($v) {
                    $val = (float) ($v ?? 0);
                    if ($val < 0) {
                        return 'text-rose-600 font-bold';
                    }
                    if ($val > 0) {
                        return 'text-emerald-700 font-bold';
                    }
                    return 'text-gray-700 font-bold';
                };

                // ===== Footer % ala Harian Area =====
                // - basis = SUM(hrg)
                // - selisih% = SUM(selisih_rp) / (SUM(hrg) - SUM(selisih_rp))
                $sumByJenis = function (string $jenis) use ($rows) {
                    $c = collect($rows)->where('jenis', $jenis);

                    $hrg = (int) $c->sum('hrg');
                    $sel = (int) $c->sum('selisih_rp');

                    $baseline = $hrg - $sel; // ala harian area

                    $pct = fn(int $num, int $den) => $den > 0 ? round(($num / $den) * 100, 2) : null;

                    return [
                        'hrg' => $hrg,
                        'baseline' => $baseline,

                        'selisih_rp' => $sel,
                        'selisih_persen' => $pct($sel, $baseline),

                        'kontribusi_rp' => (int) $c->sum('kontribusi_rp'),

                        'disc_rp' => (int) $c->sum('disc_rp'),
                        'disc_persen' => $pct((int) $c->sum('disc_rp'), $hrg),

                        'retur_rp' => (int) $c->sum('retur_rp'),
                        'retur_persen' => $pct((int) $c->sum('retur_rp'), $hrg),

                        'gas_rp' => (int) $c->sum('gas_rp'),
                        'gas_persen' => $pct((int) $c->sum('gas_rp'), $hrg),

                        'telur_rp' => (int) $c->sum('telur_rp'),
                        'telur_persen' => $pct((int) $c->sum('telur_rp'), $hrg),

                        'loss_bahan' => (int) $c->sum('loss_bahan'),
                        'total_kontribusi' => (int) $c->sum('total_kontribusi'),
                    ];
                };

                $tTarget = $grandTotals['by_target'] ?? [];
                $tBL = $grandTotals['by_bulan_lalu'] ?? [];
            @endphp

            <thead class="text-[11px] uppercase text-gray-600 bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th rowspan="2" class="px-3 py-2 border-r text-center align-middle">TANGGAL</th>
                    <th rowspan="2" class="px-3 py-2 border-r text-center align-middle">JENIS</th>

                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Selisih %</th>
                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">(+/-) Rp</th>
                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Kontribusi</th>

                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">DISC MANUAL</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">RETUR</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">GAS</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">TELUR</th>

                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Loss bahan</th>
                    <th rowspan="2" class="px-3 py-2 text-right align-middle">Total kontribusi</th>
                </tr>
                <tr>
                    <th class="px-3 py-2 text-right border-r text-gray-500">%</th>
                    <th class="px-3 py-2 text-right border-r text-gray-500">Rp</th>

                    <th class="px-3 py-2 text-right border-r text-gray-500">%</th>
                    <th class="px-3 py-2 text-right border-r text-gray-500">Rp</th>

                    <th class="px-3 py-2 text-right border-r text-gray-500">%</th>
                    <th class="px-3 py-2 text-right border-r text-gray-500">Rp</th>

                    <th class="px-3 py-2 text-right border-r text-gray-500">%</th>
                    <th class="px-3 py-2 text-right border-r text-gray-500">Rp</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($rows as $i => $r)
                    @php
                        $prevDate = $rows[$i - 1]['tanggal'] ?? null;
                        $isNewTanggal = $i === 0 || $prevDate !== ($r['tanggal'] ?? null);
                        $isSecondRowSameDate = !$isNewTanggal;

                        $src = strtoupper($r['source'] ?? ($dataSource ?? 'LIVE'));
                    @endphp

                    <tr
                        class="{{ $r['jenis'] === 'BY TARGET' ? 'bg-amber-50/40' : '' }} {{ $isNewTanggal ? 'border-t-4 border-gray-400' : '' }} hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-2 font-medium text-gray-800">
                            {{ $isSecondRowSameDate ? '' : \Carbon\Carbon::parse($r['tanggal'])->format('d/m/Y') }}
                        </td>

                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="italic text-gray-700">{{ $r['jenis'] }}</span>
                                @if ($src === 'SNAPSHOT')
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">SNAP</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">LIVE</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['selisih_persen'] ?? 0) }}">{{ $fmtPct($r['selisih_persen'] ?? null) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['selisih_rp'] ?? 0) }}">{{ $fmtRp($r['selisih_rp'] ?? 0) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right font-semibold"><span
                                class="{{ $color($r['kontribusi_rp'] ?? 0) }}">{{ $fmtRp($r['kontribusi_rp'] ?? 0) }}</span>
                        </td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['disc_persen'] ?? 0) }}">{{ $fmtPct($r['disc_persen'] ?? null) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['disc_rp'] ?? 0) }}">{{ $fmtRp($r['disc_rp'] ?? 0) }}</span></td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['retur_persen'] ?? 0) }}">{{ $fmtPct($r['retur_persen'] ?? null) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['retur_rp'] ?? 0) }}">{{ $fmtRp($r['retur_rp'] ?? 0) }}</span>
                        </td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['gas_persen'] ?? 0) }}">{{ $fmtPct($r['gas_persen'] ?? null) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['gas_rp'] ?? 0) }}">{{ $fmtRp($r['gas_rp'] ?? 0) }}</span></td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['telur_persen'] ?? 0) }}">{{ $fmtPct($r['telur_persen'] ?? null) }}</span>
                        </td>
                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['telur_rp'] ?? 0) }}">{{ $fmtRp($r['telur_rp'] ?? 0) }}</span>
                        </td>

                        <td class="px-3 py-2 text-right"><span
                                class="{{ $color($r['loss_bahan'] ?? 0) }}">{{ $fmtRp($r['loss_bahan'] ?? 0) }}</span>
                        </td>

                        <td class="px-3 py-2 text-right font-bold text-base">
                            <span
                                class="{{ $color($r['total_kontribusi'] ?? 0) }}">{{ $fmtRp($r['total_kontribusi'] ?? 0) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="px-4 py-6 text-center text-gray-500 text-sm">
                            Belum ada data. Klik <b>Tampilkan</b>.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if(count($rows) > 0)
            <tfoot class="bg-gray-50 border-t-2 border-gray-300">

                {{-- TOTAL BY BULAN LALU (taruh dulu biar sama dengan detail) --}}
                <tr class="font-semibold text-xs bg-gray-100 hover:bg-gray-200 transition-colors">
                    <td class="px-3 py-3 text-gray-800">TOTAL</td>
                    <td class="px-3 py-3 italic text-gray-700">BY BULAN LALU</td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['selisih_rp'] ?? 0) }}">{{ $fmtPct($tBL['selisih_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['selisih_rp'] ?? 0) }}">{{ $fmtRp($tBL['selisih_rp'] ?? 0) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['kontribusi_rp'] ?? 0) }}">{{ $fmtRp($tBL['kontribusi_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['disc_rp'] ?? 0) }}">{{ $fmtPct($tBL['disc_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['disc_rp'] ?? 0) }}">{{ $fmtRp($tBL['disc_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['retur_rp'] ?? 0) }}">{{ $fmtPct($tBL['retur_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['retur_rp'] ?? 0) }}">{{ $fmtRp($tBL['retur_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['gas_rp'] ?? 0) }}">{{ $fmtPct($tBL['gas_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['gas_rp'] ?? 0) }}">{{ $fmtRp($tBL['gas_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['telur_rp'] ?? 0) }}">{{ $fmtPct($tBL['telur_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['telur_rp'] ?? 0) }}">{{ $fmtRp($tBL['telur_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tBL['loss_bahan'] ?? 0) }}">{{ $fmtRp($tBL['loss_bahan'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right font-black text-sm">
                        <span class="{{ $colorFooter($tBL['total_kontribusi'] ?? 0) }}">{{ $fmtRp($tBL['total_kontribusi'] ?? 0) }}</span>
                    </td>
                </tr>

                {{-- TOTAL BY TARGET (taruh setelahnya) --}}
                <tr class="font-semibold text-xs bg-indigo-50 hover:bg-indigo-100 transition-colors">
                    <td class="px-3 py-3 text-gray-800"></td> {{-- kosongkan biar mirip row ke-2 di detail --}}
                    <td class="px-3 py-3 italic text-indigo-700">BY TARGET</td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['selisih_rp'] ?? 0) }}">{{ $fmtPct($tTarget['selisih_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['selisih_rp'] ?? 0) }}">{{ $fmtRp($tTarget['selisih_rp'] ?? 0) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['kontribusi_rp'] ?? 0) }}">{{ $fmtRp($tTarget['kontribusi_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['disc_rp'] ?? 0) }}">{{ $fmtPct($tTarget['disc_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['disc_rp'] ?? 0) }}">{{ $fmtRp($tTarget['disc_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['retur_rp'] ?? 0) }}">{{ $fmtPct($tTarget['retur_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['retur_rp'] ?? 0) }}">{{ $fmtRp($tTarget['retur_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['gas_rp'] ?? 0) }}">{{ $fmtPct($tTarget['gas_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['gas_rp'] ?? 0) }}">{{ $fmtRp($tTarget['gas_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['telur_rp'] ?? 0) }}">{{ $fmtPct($tTarget['telur_persen'] ?? null) }}</span>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['telur_rp'] ?? 0) }}">{{ $fmtRp($tTarget['telur_rp'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right">
                        <span class="{{ $colorFooter($tTarget['loss_bahan'] ?? 0) }}">{{ $fmtRp($tTarget['loss_bahan'] ?? 0) }}</span>
                    </td>

                    <td class="px-3 py-3 text-right font-black text-sm">
                        <span class="{{ $colorFooter($tTarget['total_kontribusi'] ?? 0) }}">{{ $fmtRp($tTarget['total_kontribusi'] ?? 0) }}</span>
                    </td>
                </tr>

            </tfoot>
          @endif

        </table>
    </div>

    {{-- LISTENER --}}
    <div x-data x-on:kontribusi:load-missing.window="$wire.loadMissingLive()"></div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('kontribusi:load-missing', () => {
                @this.call('loadMissingLive');
            });

            Livewire.on('toast', (data) => {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: data.type ?? 'success',
                        title: data.message ?? 'OK',
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            });
        });
    </script>
</div>
