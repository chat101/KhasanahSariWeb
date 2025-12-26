<div class="space-y-4">
    {{-- FILTER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
            <div class="md:col-span-2">
                <label class="block mb-1 text-gray-500">Periode</label>

                <div class="flex flex-wrap md:flex-nowrap items-end gap-2">
                    <input type="date"
                           wire:model.live="periodeAwal"
                           class="border rounded-lg px-2 py-1 h-8">

                    <span class="text-gray-400 text-[11px] pb-1">sd</span>

                    <input type="date"
                           wire:model.live="periodeAkhir"
                           class="border rounded-lg px-2 py-1 h-8">

                    @if(($this->periodeAkhir ?? null) === now()->toDateString())
                        <span class="inline-flex items-center gap-1 h-8 px-2 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-md whitespace-nowrap">
                            ⚠️ Data hari ini belum tersedia, laporan sampai H-1
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-end gap-2 md:col-span-3 justify-end">
                <button wire:click="loadBulanLalu"
                        wire:loading.attr="disabled"
                        wire:target="loadBulanLalu"
                        class="relative px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] disabled:opacity-60">
                    <span wire:loading.remove wire:target="loadBulanLalu">Tampilkan</span>

                    <span wire:loading wire:target="loadBulanLalu" class="flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Memuat...
                    </span>
                </button>

                <button wire:click="resetBulanLalu"
                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-[11px]">
                    Reset
                </button>
            </div>
        </div>
    </div>

    @php
        // =====================
        // DATA DARI render()
        // =====================
        $rows = $rowsBulanLaluView ?? [];

        $pctVal = function ($v) {
            if (is_null($v)) return null;
            if (is_string($v)) $v = trim(str_replace('%', '', $v));
            if ($v === '' || $v === '-') return null;
            return is_numeric($v) ? (float)$v : null;
        };

        $clsPct = function ($p) {
            return is_null($p)
                ? 'text-gray-500'
                : ($p < 0
                    ? 'text-rose-700 bg-rose-50'
                    : ($p > 0
                        ? 'text-emerald-700 bg-emerald-50'
                        : 'text-gray-600 bg-gray-50'));
        };

        $clsRp = function ($rp) {
            $rp = (int) $rp;
            return $rp < 0
                ? 'text-rose-700 bg-rose-50'
                : ($rp > 0
                    ? 'text-emerald-700 bg-emerald-50'
                    : 'text-gray-600 bg-gray-50');
        };

        // =====================
        // SUBTOTAL helper (BULAN LALU)
        // - kalau kamu ingin WEIGHTED %, ganti implementasi avgPct di sini.
        // - saat ini mengikuti code kamu: % = AVG dari row.
        // =====================
        $sumCols = function ($rows) use ($pctVal) {
            $rows = collect($rows);

            $avgPct = function (string $key) use ($rows, $pctVal): ?float {
                $vals = $rows->map(fn($r) => $pctVal($r[$key] ?? null))
                    ->filter(fn($v) => !is_null($v))
                    ->values();

                if ($vals->isEmpty()) return null;
                return round((float)$vals->avg(), 2);
            };

            return [
                'selisih_persen'   => $avgPct('selisih_persen'),
                'selisih_rp'       => (int) $rows->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
                'kontribusi_rp'    => (int) $rows->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),

                'sc_manual_persen' => $avgPct('sc_manual_persen'),
                'sc_manual_rp'     => (int) $rows->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),

                'retur_persen'     => $avgPct('retur_persen'),
                'retur_rp'         => (int) $rows->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),

                'gas_persen'       => $avgPct('gas_persen'),
                'gas_rp'           => (int) $rows->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),

                'telur_persen'     => $avgPct('telur_persen'),
                'telur_rp'         => (int) $rows->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),

                'loss_bahan'       => (int) $rows->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
                'total_kontribusi' => (int) $rows->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
            ];
        };

        // grouping wilayah -> area
        $groupWilayah = collect($rows)
            ->sortBy([['wilayah_label','asc'], ['area_label','asc'], ['outlet','asc']])
            ->groupBy(fn($r) => $r['wilayah_label'] ?? '-');
    @endphp

    {{-- TABLE --}}
    <div class="relative bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">

        {{-- LOADING OVERLAY --}}
        <div wire:loading wire:target="loadBulanLalu"
             class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2 text-xs text-gray-700">
                <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <span>Menghitung kontribusi per toko…</span>
            </div>
        </div>

        <table class="min-w-[1200px] w-full text-xs text-left">
            <thead class="text-[11px] uppercase text-gray-600">
                <tr class="border-b">
                    <th colspan="5" class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
                        BY BULAN LALU
                    </th>

                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">DISC Manual</th>
                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Retur</th>
                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Gas</th>
                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Telur</th>
                    <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Loss bahan</th>
                    <th rowspan="2" class="px-3 py-2 text-center bg-gray-50">Total kontribusi</th>
                </tr>

                <tr class="border-b">
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Area</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Outlet</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Selisih %</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">(+/-) Rp</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Kontribusi</th>

                    <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                    <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                    <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                    <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                    <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                    <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>

                    <th class="px-3 py-2 text-center bg-gray-50 border-r">%</th>
                    <th class="px-3 py-2 text-center bg-gray-50 border-r">Rp</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse($groupWilayah as $wilayah => $rowsWil)
                    @php
                        $groupArea = $rowsWil->groupBy(fn($r) => $r['area_label'] ?? '-');
                    @endphp

                    @foreach($groupArea as $area => $rowsArea)
                        @foreach($rowsArea as $r)
                            @php
                                $sp  = $pctVal($r['selisih_persen'] ?? null);
                                $sr  = (int)($r['selisih_rp'] ?? 0);
                                $kr  = (int)($r['kontribusi_rp'] ?? 0);

                                $dmP = $pctVal($r['sc_manual_persen'] ?? null);
                                $dmR = (int)($r['sc_manual_rp'] ?? 0);

                                $retP = $pctVal($r['retur_persen'] ?? null);
                                $retR = (int)($r['retur_rp'] ?? 0);

                                $gasP = $pctVal($r['gas_persen'] ?? null);
                                $gasR = (int)($r['gas_rp'] ?? 0);

                                $telP = $pctVal($r['telur_persen'] ?? null);
                                $telR = (int)($r['telur_rp'] ?? 0);

                                $loss = (int)($r['loss_bahan'] ?? 0);
                                $tk   = (int)($r['total_kontribusi'] ?? 0);
                            @endphp

                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 border-r align-top">
                                    <div class="font-semibold text-gray-900">{{ $r['area_label'] ?? '-' }}</div>
                                    @php $pic = trim((string)($r['area_pic'] ?? '')); @endphp
                                    @if($pic !== '')
                                        <div class="text-[10px] italic text-amber-600">{{ $pic }}</div>
                                    @endif
                                </td>

                                <td class="px-3 py-2 border-r text-left font-medium">
                                    {{ $r['outlet'] ?? '-' }}
                                </td>

                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($sp) }}">
                                    {{ is_null($sp) ? '-' : number_format($sp, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($sr) }}">
                                    {{ $sr === 0 ? '-' : number_format($sr, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($kr) }}">
                                    {{ $kr === 0 ? '-' : number_format($kr, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($dmP) }}">
                                    {{ is_null($dmP) ? '-' : number_format($dmP, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($dmR) }}">
                                    {{ $dmR === 0 ? '-' : number_format($dmR, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($retP) }}">
                                    {{ is_null($retP) ? '-' : number_format($retP, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($retR) }}">
                                    {{ $retR === 0 ? '-' : number_format($retR, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($gasP) }}">
                                    {{ is_null($gasP) ? '-' : number_format($gasP, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($gasR) }}">
                                    {{ $gasR === 0 ? '-' : number_format($gasR, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($telP) }}">
                                    {{ is_null($telP) ? '-' : number_format($telP, 2, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($telR) }}">
                                    {{ $telR === 0 ? '-' : number_format($telR, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($loss) }}">
                                    {{ $loss === 0 ? '-' : number_format($loss, 0, ',', '.') }}
                                </td>

                                <td class="px-3 py-2 text-right font-semibold {{ $clsRp($tk) }}">
                                    {{ $tk === 0 ? '-' : number_format($tk, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- SUBTOTAL AREA (posisi kolom sama dengan detail) --}}
                        @php
                            $t = $sumCols($rowsArea);
                            $tkA = (int)($t['total_kontribusi'] ?? 0);
                            $clsArea = $tkA < 0 ? 'bg-rose-50 text-rose-700' : ($tkA > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600');

                            $v = fn($k) => $pctVal($t[$k] ?? null);
                        @endphp

                        <tr class="font-semibold {{ $clsArea }} border-t border-slate-300">
                            <td colspan="2" class="px-3 py-2 border-r text-right text-gray-800">
                                TOTAL AREA {{ $area }}
                            </td>

                            @php $x = $v('selisih_persen'); @endphp
                            <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($x) }}">
                                {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['selisih_rp'] ?? 0) }}">
                                {{ (int)($t['selisih_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['selisih_rp'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['kontribusi_rp'] ?? 0) }}">
                                {{ (int)($t['kontribusi_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['kontribusi_rp'], 0, ',', '.') }}
                            </td>

                            @php $x = $v('sc_manual_persen'); @endphp
                            <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($x) }}">
                                {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['sc_manual_rp'] ?? 0) }}">
                                {{ (int)($t['sc_manual_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['sc_manual_rp'], 0, ',', '.') }}
                            </td>

                            @php $x = $v('retur_persen'); @endphp
                            <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($x) }}">
                                {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['retur_rp'] ?? 0) }}">
                                {{ (int)($t['retur_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['retur_rp'], 0, ',', '.') }}
                            </td>

                            @php $x = $v('gas_persen'); @endphp
                            <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($x) }}">
                                {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['gas_rp'] ?? 0) }}">
                                {{ (int)($t['gas_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['gas_rp'], 0, ',', '.') }}
                            </td>

                            @php $x = $v('telur_persen'); @endphp
                            <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($x) }}">
                                {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['telur_rp'] ?? 0) }}">
                                {{ (int)($t['telur_rp'] ?? 0) === 0 ? '-' : number_format((int)$t['telur_rp'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($t['loss_bahan'] ?? 0) }}">
                                {{ (int)($t['loss_bahan'] ?? 0) === 0 ? '-' : number_format((int)$t['loss_bahan'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-2 text-right font-semibold {{ $clsRp($t['total_kontribusi'] ?? 0) }}">
                                {{ (int)($t['total_kontribusi'] ?? 0) === 0 ? '-' : number_format((int)$t['total_kontribusi'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="15" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td>
                    </tr>
                @endforelse
            </tbody>

            {{-- GRAND TOTAL (posisi kolom sama dengan detail) --}}
            @php
                // kalau component ngirim grandTotalsView, boleh dipakai.
                // kalau tidak, hitung dari rows.
                $gt = !empty($grandTotalsView ?? []) ? ($grandTotalsView ?? []) : $sumCols($rows);

                $tkG = (int)($gt['total_kontribusi'] ?? 0);
                $clsGrand = $tkG < 0 ? 'bg-rose-100 text-rose-800' : ($tkG > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800');

                $v = fn($k) => $pctVal($gt[$k] ?? null);
            @endphp

            <tfoot class="border-t-4">
                <tr class="font-extrabold {{ $clsGrand }}">
                    <td colspan="2" class="px-3 py-3 border-r text-right">GRAND TOTAL</td>

                    @php $x = $v('selisih_persen'); @endphp
                    <td class="px-3 py-3 border-r text-center font-semibold {{ $clsPct($x) }}">
                        {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['selisih_rp'] ?? 0) }}">
                        {{ (int)($gt['selisih_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['selisih_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['kontribusi_rp'] ?? 0) }}">
                        {{ (int)($gt['kontribusi_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['kontribusi_rp'], 0, ',', '.') }}
                    </td>

                    @php $x = $v('sc_manual_persen'); @endphp
                    <td class="px-3 py-3 border-r text-center font-semibold {{ $clsPct($x) }}">
                        {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['sc_manual_rp'] ?? 0) }}">
                        {{ (int)($gt['sc_manual_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['sc_manual_rp'], 0, ',', '.') }}
                    </td>

                    @php $x = $v('retur_persen'); @endphp
                    <td class="px-3 py-3 border-r text-center font-semibold {{ $clsPct($x) }}">
                        {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['retur_rp'] ?? 0) }}">
                        {{ (int)($gt['retur_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['retur_rp'], 0, ',', '.') }}
                    </td>

                    @php $x = $v('gas_persen'); @endphp
                    <td class="px-3 py-3 border-r text-center font-semibold {{ $clsPct($x) }}">
                        {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['gas_rp'] ?? 0) }}">
                        {{ (int)($gt['gas_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['gas_rp'], 0, ',', '.') }}
                    </td>

                    @php $x = $v('telur_persen'); @endphp
                    <td class="px-3 py-3 border-r text-center font-semibold {{ $clsPct($x) }}">
                        {{ is_null($x) ? '-' : number_format($x, 2, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['telur_rp'] ?? 0) }}">
                        {{ (int)($gt['telur_rp'] ?? 0) === 0 ? '-' : number_format((int)$gt['telur_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right font-semibold {{ $clsRp($gt['loss_bahan'] ?? 0) }}">
                        {{ (int)($gt['loss_bahan'] ?? 0) === 0 ? '-' : number_format((int)$gt['loss_bahan'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 text-right font-semibold {{ $clsRp($gt['total_kontribusi'] ?? 0) }}">
                        {{ (int)($gt['total_kontribusi'] ?? 0) === 0 ? '-' : number_format((int)$gt['total_kontribusi'], 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
