<div>
    @php
        $fmtRp  = fn($v) => number_format((int)$v, 0, ',', '.');
        $fmtPct = fn($v) => ($v === null || $v === '-') ? '-' : rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') . '%';

        $pct = function ($num, $den) use ($fmtPct) {
            $den = (float)$den;
            if ($den == 0.0) return '0%';
            return $fmtPct(((float)$num / $den) * 100);
        };

        $pctSelisih = function ($selisihRp, $hrg) use ($fmtPct) {
            $hrg = (float)$hrg;
            $sel = (float)$selisihRp;
            $baseline = $hrg - $sel;
            if ($baseline == 0.0) return '0%';
            return $fmtPct(($sel / $baseline) * 100);
        };

        $color = function ($v) {
            $val = (float)$v;
            if ($val < 0) return 'text-rose-600 font-medium';
            if ($val > 0) return 'text-emerald-600 font-medium';
            return 'text-gray-400';
        };

        /**
         * ==================================================
         * TRANSPOSE rows: dari [outlet][tgl][] => [tgl][outlet][]
         * (supaya tampilan bisa group by tanggal seperti gambar)
         * ==================================================
         */
        $rowsByTanggal = [];
        foreach (($rows ?? []) as $outletName => $byTanggal) {
            foreach (($byTanggal ?? []) as $tgl => $list) {
                $rowsByTanggal[$tgl] ??= [];
                $rowsByTanggal[$tgl][$outletName] = $list;
            }
        }
        ksort($rowsByTanggal);

        // fungsi ambil row per jenis
        $pickType = function(array $list, string $type) {
            foreach ($list as $r) {
                if (strtoupper(trim((string)($r['type'] ?? ''))) === $type) return $r;
            }
            return null;
        };
    @endphp

    <div class="space-y-4">

        {{-- FILTER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold flex items-center gap-2">
                        DETAIL KONTRIBUSI HARIAN AREA
                        @if($loadDuration)
                            <span class="text-[10px] text-gray-400 font-normal border border-gray-200 bg-gray-50 px-2 py-0.5 rounded-full">
                                {{ $loadDuration }}s
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">Menampilkan detail harian seluruh toko di area ini.</div>
                </div>

                <div class="flex flex-wrap items-end gap-4">
                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
                        <input type="date" wire:model="tanggalAwal"
                               class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="w-full sm:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
                        <input type="date" wire:model="tanggalAkhir"
                               class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="w-full sm:w-auto pb-0.5 flex items-center gap-2">
                        <button wire:click="load"
                                wire:loading.attr="disabled"
                                wire:target="load"
                                class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="load">Tampilkan</span>
                            <span wire:loading wire:target="load">Loading...</span>
                        </button>

                        <button wire:click="download"
                                wire:loading.attr="disabled"
                                wire:target="download"
                                class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                            <span wire:loading.remove wire:target="download">Download</span>
                            <span wire:loading wire:target="download">Menyiapkan...</span>
                        </button>
                    </div>
                </div>
            </div>

            @if(session()->has('message'))
                <div class="mt-3 text-xs text-red-600">{{ session('message') }}</div>
            @endif
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
            <table class="min-w-[1600px] w-full text-xs text-left">
                <thead class="text-[11px] uppercase text-gray-700 bg-gray-50 border-b-2 border-gray-200 font-semibold">
                    <tr>
                        {{-- sesuai gambar: TANGGAL dulu --}}
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">TANGGAL</th>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">OUTLET</th>
                        <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">JENIS</th>

                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Selisih %</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">(+/-) Rp</th>
                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Kontribusi</th>

                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">DISC MANUAL</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">RETUR</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">GAS</th>
                        <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">TELUR</th>

                        <th rowspan="2" class="px-3 py-2 text-right border-r align-middle bg-gray-100">LOSS BAHAN</th>
                        <th rowspan="2" class="px-3 py-2 text-right align-middle bg-gray-50">TOTAL KONTRIBUSI</th>
                    </tr>
                    <tr>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>

                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">%</th>
                        <th class="px-3 py-2 text-right border-r text-gray-600 bg-gray-50">RP</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($rowsByTanggal as $tgl => $byOutlet)

                    @php
                        // rowspan tanggal = total outlet * 2 baris (BL+TARGET)
                        $rowspanTanggal = collect($byOutlet)->count() * 2;
                        $printedTanggal = false;
                    @endphp

                    @foreach($byOutlet as $outletName => $list)
                        @php
                            $rBL = $pickType($list, 'BY BULAN LALU');
                            $rTG = $pickType($list, 'BY TARGET');
                        @endphp

                        {{-- BARIS 1: BY BULAN LALU --}}
                        <tr class="hover:bg-gray-50">
                            @if(!$printedTanggal)
                                <td rowspan="{{ $rowspanTanggal }}"
                                    class="px-3 py-2 border-r text-center align-top font-semibold bg-white text-gray-900 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($tgl)->format('d/m/Y') }}
                                </td>
                                @php $printedTanggal = true; @endphp
                            @endif

                            <td rowspan="2" class="px-3 py-2 border-r align-top font-bold bg-white text-gray-900 whitespace-nowrap">
                                {{ $outletName }}
                            </td>

                            <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY BULAN LALU</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['selisih_persen'] ?? 0) }}">{{ $fmtPct($rBL['selisih_persen'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['selisih_rp'] ?? 0) }}">{{ $fmtRp($rBL['selisih_rp'] ?? 0) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['kontribusi'] ?? 0) }}">{{ $fmtRp($rBL['kontribusi'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rBL['disc_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['disc_rp'] ?? 0) }}">{{ $fmtRp($rBL['disc_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rBL['retur_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['retur_rp'] ?? 0) }}">{{ $fmtRp($rBL['retur_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rBL['gas_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['gas_rp'] ?? 0) }}">{{ $fmtRp($rBL['gas_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rBL['telur_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['telur_rp'] ?? 0) }}">{{ $fmtRp($rBL['telur_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($rBL['loss_bahan'] ?? 0) }}">{{ $fmtRp($rBL['loss_bahan'] ?? 0) }}</td>
                            <td class="px-3 py-2 text-right font-bold {{ $color($rBL['total_kontribusi'] ?? 0) }}">{{ $fmtRp($rBL['total_kontribusi'] ?? 0) }}</td>
                        </tr>

                        {{-- BARIS 2: BY TARGET --}}
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-r text-[10px] text-gray-500 uppercase">BY TARGET</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['selisih_persen'] ?? 0) }}">{{ $fmtPct($rTG['selisih_persen'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['selisih_rp'] ?? 0) }}">{{ $fmtRp($rTG['selisih_rp'] ?? 0) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['kontribusi'] ?? 0) }}">{{ $fmtRp($rTG['kontribusi'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rTG['disc_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['disc_rp'] ?? 0) }}">{{ $fmtRp($rTG['disc_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rTG['retur_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['retur_rp'] ?? 0) }}">{{ $fmtRp($rTG['retur_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rTG['gas_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['gas_rp'] ?? 0) }}">{{ $fmtRp($rTG['gas_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($rTG['telur_pct'] ?? null) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['telur_rp'] ?? 0) }}">{{ $fmtRp($rTG['telur_rp'] ?? 0) }}</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($rTG['loss_bahan'] ?? 0) }}">{{ $fmtRp($rTG['loss_bahan'] ?? 0) }}</td>
                            <td class="px-3 py-2 text-right font-bold {{ $color($rTG['total_kontribusi'] ?? 0) }}">{{ $fmtRp($rTG['total_kontribusi'] ?? 0) }}</td>
                        </tr>
                    @endforeach

                @empty
                    <tr>
                        <td colspan="16" class="px-6 py-6 text-center text-gray-500">
                            Silakan pilih periode dan klik Tampilkan.
                        </td>
                    </tr>
                @endforelse
                </tbody>

                {{-- GRAND TOTAL tetap seperti sebelumnya --}}
                @if(!empty($grandTotals))
                    <tfoot class="text-xs font-bold bg-gray-100 border-t-2 border-gray-300">
                        <tr class="border-b">
                            <td colspan="3" class="px-3 py-2 text-center border-r bg-gray-200">GRAND TOTAL ALL (BY TARGET)</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['selisih_rp']) }}">
                                {{ $pctSelisih($grandTotals['target']['selisih_rp'], $grandTotals['target']['hrg']) }}
                            </td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['selisih_rp']) }}">{{ $fmtRp($grandTotals['target']['selisih_rp']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['kontribusi']) }}">{{ $fmtRp($grandTotals['target']['kontribusi']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['target']['disc'], $grandTotals['target']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['disc']) }}">{{ $fmtRp($grandTotals['target']['disc']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['target']['retur'], $grandTotals['target']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['retur']) }}">{{ $fmtRp($grandTotals['target']['retur']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['target']['gas'], $grandTotals['target']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['gas']) }}">{{ $fmtRp($grandTotals['target']['gas']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['target']['telur'], $grandTotals['target']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['telur']) }}">{{ $fmtRp($grandTotals['target']['telur']) }}</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['target']['loss']) }}">{{ $fmtRp($grandTotals['target']['loss']) }}</td>
                            <td class="px-3 py-2 text-right bg-yellow-50 {{ $color($grandTotals['target']['total']) }}">{{ $fmtRp($grandTotals['target']['total']) }}</td>
                        </tr>

                        <tr>
                            <td colspan="3" class="px-3 py-2 text-center border-r bg-gray-200">GRAND TOTAL ALL (BY BL)</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['selisih_rp']) }}">
                                {{ $pctSelisih($grandTotals['bl']['selisih_rp'], $grandTotals['bl']['hrg']) }}
                            </td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['selisih_rp']) }}">{{ $fmtRp($grandTotals['bl']['selisih_rp']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['kontribusi']) }}">{{ $fmtRp($grandTotals['bl']['kontribusi']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['bl']['disc'], $grandTotals['bl']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['disc']) }}">{{ $fmtRp($grandTotals['bl']['disc']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['bl']['retur'], $grandTotals['bl']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['retur']) }}">{{ $fmtRp($grandTotals['bl']['retur']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['bl']['gas'], $grandTotals['bl']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['gas']) }}">{{ $fmtRp($grandTotals['bl']['gas']) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-600">{{ $pct($grandTotals['bl']['telur'], $grandTotals['bl']['hrg']) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['telur']) }}">{{ $fmtRp($grandTotals['bl']['telur']) }}</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($grandTotals['bl']['loss']) }}">{{ $fmtRp($grandTotals['bl']['loss']) }}</td>
                            <td class="px-3 py-2 text-right bg-yellow-50 {{ $color($grandTotals['bl']['total']) }}">{{ $fmtRp($grandTotals['bl']['total']) }}</td>
                        </tr>
                    </tfoot>
                @endif

            </table>
        </div>
    </div>
</div>
