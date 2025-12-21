<div>
    {{-- FILTER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
           <div class="md:col-span-2">
    <label class="block mb-1 text-gray-500">Periode</label>

    <div class="flex flex-wrap md:flex-nowrap items-end gap-2">
        {{-- Tanggal Awal --}}
        <input type="date"
               wire:model.live="periodeAwal"
               class="border rounded-lg px-2 py-1 h-8">

        <span class="text-gray-400 text-[11px] pb-1">sd</span>

        {{-- Tanggal Akhir --}}
        <input type="date"
               wire:model.live="periodeAkhir"
               class="border rounded-lg px-2 py-1 h-8">

        {{-- INFO --}}
        @if($periodeAkhir === now()->toDateString())
            <span
                class="inline-flex items-center gap-1
                       h-8 px-2
                       text-[11px] text-amber-700
                       bg-amber-50 border border-amber-200
                       rounded-md whitespace-nowrap">
                ⚠️ Data hari ini belum tersedia, laporan sampai H-1
            </span>
        @endif

       
    </div>
</div>

            <div class="flex items-end gap-2 md:col-span-2 justify-end">
                <button wire:click="loadBulanLalu" wire:loading.attr="disabled" wire:target="loadBulanLalu"
                    class="relative px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] disabled:opacity-60">
                    <span wire:loading.remove wire:target="loadBulanLalu">
                        Tampilkan
                    </span>

                    <span wire:loading wire:target="loadBulanLalu" class="flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" fill="none" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
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


    {{-- TABLE (SAMA DENGAN TARGET) --}}
    <div class="relative bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">

        {{-- LOADING OVERLAY --}}
        <div wire:loading wire:target="loadBulanLalu"
            class="absolute inset-0 z-20 flex items-center justify-center
            bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-2 text-xs text-gray-700">
                <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" fill="none" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
                <span>Menghitung kontribusi per toko…</span>
            </div>
        </div>
        <table class="min-w-[1200px] w-full text-xs text-left">
            <thead class="text-[11px] uppercase text-gray-600">
                <tr class="border-b">
                    <th colspan="5" class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
                        by target proyeksi
                    </th>

                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">DISC
                        Manual</th>
                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Retur
                    </th>

                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Gas
                    </th>
                    <th colspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Telur
                    </th>
                    <th rowspan="2" class="px-3 py-2 text-center bg-gray-50 border-r">Loss
                        bahan</th>
                    <th rowspan="2" class="px-3 py-2 text-center bg-gray-50">total kontribusi
                    </th>
                </tr>

                <tr class="border-b">
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                        Area</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                        Outlet</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                        Selisih %</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                        (+/-) Rp</th>
                    <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">
                        kontribusi</th>

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
                @php
                    $pctVal = function ($v) {
                        if (is_null($v)) {
                            return null;
                        }
                        if (is_string($v)) {
                            $v = trim(str_replace('%', '', $v));
                        }
                        if ($v === '' || $v === '-') {
                            return null;
                        }
                        return (float) $v;
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

                    // ===== helper hitung total dari collection rows =====
                    $sumCols = function ($rows) {
                        return [
                            'selisih_rp' => (int) $rows->sum(fn($r) => (int) ($r['selisih_rp'] ?? 0)),
                            'kontribusi_rp' => (int) $rows->sum(fn($r) => (int) ($r['kontribusi_rp'] ?? 0)),
                            'sc_manual_rp' => (int) $rows->sum(fn($r) => (int) ($r['sc_manual_rp'] ?? 0)),
                            'retur_rp' => (int) $rows->sum(fn($r) => (int) ($r['retur_rp'] ?? 0)),
                            'gas_rp' => (int) $rows->sum(fn($r) => (int) ($r['gas_rp'] ?? 0)),
                            'telur_rp' => (int) $rows->sum(fn($r) => (int) ($r['telur_rp'] ?? 0)),
                            'loss_bahan' => (int) $rows->sum(fn($r) => (int) ($r['loss_bahan'] ?? 0)),
                            'total_kontribusi' => (int) $rows->sum(fn($r) => (int) ($r['total_kontribusi'] ?? 0)),
                        ];
                    };

                    // ===== grand total semua wilayah (untuk baris terakhir) =====
                    $grandAll = $sumCols(collect($rowsBulanLalu ?? []));

                    // ===== grouping wilayah -> area =====
                    $groupWilayah = collect($rowsBulanLalu ?? [])
                        ->sortBy([['wilayah_label', 'asc'], ['area_label', 'asc'], ['outlet', 'asc']])
                        ->groupBy(fn($r) => $r['wilayah_label'] ?? '-');
                @endphp

                @forelse($groupWilayah as $wilayah => $rowsWil)
                    @php
                        $totalWil = $sumCols($rowsWil);
                        // $clsWil =
                        //     $totalWil['total_kontribusi'] < 0 ? 'bg-rose-50 text-rose-700'
                        //     : ($totalWil['total_kontribusi'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600');

                        // group per area di wilayah ini
                        $groupArea = $rowsWil->groupBy(fn($r) => $r['area_label'] ?? '-');
                    @endphp

                    {{-- HEADER WILAYAH --}}
                    {{-- <tr class="bg-indigo-50 text-indigo-800 font-semibold">
                        <td colspan="14" class="px-3 py-2">
                            {{ $wilayah ?: '-' }}
                        </td>
                    </tr> --}}

                    @foreach ($groupArea as $area => $rowsArea)
                        @php
                            $totalArea = $sumCols($rowsArea);
                            $clsArea =
                                $totalArea['total_kontribusi'] < 0
                                    ? 'bg-rose-50 text-rose-700'
                                    : ($totalArea['total_kontribusi'] > 0
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-gray-50 text-gray-600');
                        @endphp

                        @foreach ($rowsArea as $r)
                            @php
                                $sp = $pctVal($r['selisih_persen'] ?? null);
                                $sr = (int) ($r['selisih_rp'] ?? 0);
                                $kr = (int) ($r['kontribusi_rp'] ?? 0);

                                $dmP = $pctVal($r['sc_manual_persen'] ?? null);
                                $dmR = (int) ($r['sc_manual_rp'] ?? 0);

                                $retP = $pctVal($r['retur_persen'] ?? null);
                                $retR = (int) ($r['retur_rp'] ?? 0);

                                $gasP = $pctVal($r['gas_persen'] ?? null);
                                $gasR = (int) ($r['gas_rp'] ?? 0);

                                $telP = $pctVal($r['telur_persen'] ?? null);
                                $telR = (int) ($r['telur_rp'] ?? 0);

                                $loss = (int) ($r['loss_bahan'] ?? 0);
                                $tk = (int) ($r['total_kontribusi'] ?? 0);
                            @endphp

                            <tr class="hover:bg-gray-50">
                                {{-- AREA --}}
                                <td class="px-3 py-2 border-r align-top">
                                    <div class="font-semibold text-gray-900">
                                        {{ $r['area_label'] ?? '-' }}
                                    </div>

                                    @php $pic = trim((string)($r['area_pic'] ?? '')); @endphp
                                    @if ($pic !== '')
                                        <div class="text-[10px] italic text-amber-600">
                                            {{ $pic }}
                                        </div>
                                    @endif
                                </td>

                                {{-- OUTLET --}}
                                <td class="px-3 py-2 border-r text-left font-medium">
                                    {{ $r['outlet'] ?? '-' }}
                                </td>

                                {{-- SELISIH % --}}
                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($sp) }}">
                                    {{ is_null($sp) ? '-' : number_format($sp, 2, ',', '.') }}
                                </td>

                                {{-- SELISIH RP --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($sr) }}">
                                    {{ $sr === 0 ? '-' : number_format($sr, 0, ',', '.') }}
                                </td>

                                {{-- KONTRIBUSI --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($kr) }}">
                                    {{ $kr === 0 ? '-' : number_format($kr, 0, ',', '.') }}
                                </td>

                                {{-- DISC % --}}
                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($dmP) }}">
                                    {{ is_null($dmP) ? '-' : number_format($dmP, 2, ',', '.') }}
                                </td>

                                {{-- DISC RP --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($dmR) }}">
                                    {{ $dmR === 0 ? '-' : number_format($dmR, 0, ',', '.') }}
                                </td>

                                {{-- RETUR % --}}
                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($retP) }}">
                                    {{ is_null($retP) ? '-' : number_format($retP, 2, ',', '.') }}
                                </td>

                                {{-- RETUR RP --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($retR) }}">
                                    {{ $retR === 0 ? '-' : number_format($retR, 0, ',', '.') }}
                                </td>

                                {{-- GAS % --}}
                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($gasP) }}">
                                    {{ is_null($gasP) ? '-' : number_format($gasP, 2, ',', '.') }}
                                </td>

                                {{-- GAS RP --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($gasR) }}">
                                    {{ $gasR === 0 ? '-' : number_format($gasR, 0, ',', '.') }}
                                </td>

                                {{-- TELUR % --}}
                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsPct($telP) }}">
                                    {{ is_null($telP) ? '-' : number_format($telP, 2, ',', '.') }}
                                </td>

                                {{-- TELUR RP --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($telR) }}">
                                    {{ $telR === 0 ? '-' : number_format($telR, 0, ',', '.') }}
                                </td>

                                {{-- LOSS --}}
                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($loss) }}">
                                    {{ $loss === 0 ? '-' : number_format($loss, 0, ',', '.') }}
                                </td>

                                {{-- TOTAL KONTRIBUSI --}}
                                <td class="px-3 py-2 text-right font-semibold {{ $clsRp($tk) }}">
                                    {{ $tk === 0 ? '-' : number_format($tk, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- SUBTOTAL AREA --}}
                      {{-- SUBTOTAL AREA --}}
@php
    $clsArea =
        $totalArea['total_kontribusi'] < 0
            ? 'bg-rose-50 text-rose-700'
            : ($totalArea['total_kontribusi'] > 0
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-gray-50 text-gray-600');
@endphp

<tr class="font-semibold {{ $clsArea }} border-t border-slate-300">
    <td colspan="2" class="px-3 py-2 border-r text-right text-gray-800">
        TOTAL AREA {{ $area }}
    </td>

    <td class="px-3 py-2 border-r text-center"></td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['selisih_rp'] ?? 0) }}">
        {{ (int)($totalArea['selisih_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['selisih_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['kontribusi_rp'] ?? 0) }}">
        {{ (int)($totalArea['kontribusi_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['kontribusi_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-center">-</td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['sc_manual_rp'] ?? 0) }}">
        {{ (int)($totalArea['sc_manual_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['sc_manual_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-center">-</td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['retur_rp'] ?? 0) }}">
        {{ (int)($totalArea['retur_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['retur_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-center">-</td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['gas_rp'] ?? 0) }}">
        {{ (int)($totalArea['gas_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['gas_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-center">-</td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['telur_rp'] ?? 0) }}">
        {{ (int)($totalArea['telur_rp'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['telur_rp'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 border-r text-right font-semibold {{ $clsRp($totalArea['loss_bahan'] ?? 0) }}">
        {{ (int)($totalArea['loss_bahan'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['loss_bahan'], 0, ',', '.') }}
    </td>

    <td class="px-3 py-2 text-right font-semibold {{ $clsRp($totalArea['total_kontribusi'] ?? 0) }}">
        {{ (int)($totalArea['total_kontribusi'] ?? 0) === 0 ? '-' : number_format((int)$totalArea['total_kontribusi'], 0, ',', '.') }}
    </td>
</tr>

                    @endforeach

                    {{-- SUBTOTAL WILAYAH --}}
                    {{-- <tr class="font-bold {{ $clsWil }} border-t-2">
                        <td colspan="2" class="px-3 py-2 border-r text-right">
                            TOTAL WILAYAH {{ $wilayah }}
                        </td>
                        <td class="px-3 py-2 border-r text-center"></td>

                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['selisih_rp'] === 0 ? '-' : number_format($totalWil['selisih_rp'], 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['kontribusi_rp'] === 0 ? '-' : number_format($totalWil['kontribusi_rp'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 border-r text-center">-</td>
                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['sc_manual_rp'] === 0 ? '-' : number_format($totalWil['sc_manual_rp'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 border-r text-center">-</td>
                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['retur_rp'] === 0 ? '-' : number_format($totalWil['retur_rp'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 border-r text-center">-</td>
                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['gas_rp'] === 0 ? '-' : number_format($totalWil['gas_rp'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 border-r text-center">-</td>
                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['telur_rp'] === 0 ? '-' : number_format($totalWil['telur_rp'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 border-r text-right">
                            {{ $totalWil['loss_bahan'] === 0 ? '-' : number_format($totalWil['loss_bahan'], 0, ',', '.') }}
                        </td>

                        <td class="px-3 py-2 text-right">
                            {{ $totalWil['total_kontribusi'] === 0 ? '-' : number_format($totalWil['total_kontribusi'], 0, ',', '.') }}
                        </td>
                    </tr> --}}
                @empty
                    <tr>
                        <td colspan="14" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td>
                    </tr>
                @endforelse

                {{-- GRAND TOTAL SEMUA WILAYAH --}}
                @php
                    $clsGrand =
                        $grandAll['total_kontribusi'] < 0
                            ? 'bg-rose-100 text-rose-800'
                            : ($grandAll['total_kontribusi'] > 0
                                ? 'bg-emerald-100 text-emerald-800'
                                : 'bg-gray-100 text-gray-800');
                @endphp
                <tr class="font-extrabold {{ $clsGrand }} border-t-4">
                    <td colspan="2" class="px-3 py-3 border-r text-right">
                        GRAND TOTAL
                    </td>
                    <td class="px-3 py-3 border-r text-center"></td>

                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['selisih_rp'] === 0 ? '-' : number_format($grandAll['selisih_rp'], 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['kontribusi_rp'] === 0 ? '-' : number_format($grandAll['kontribusi_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-center">-</td>
                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['sc_manual_rp'] === 0 ? '-' : number_format($grandAll['sc_manual_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-center">-</td>
                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['retur_rp'] === 0 ? '-' : number_format($grandAll['retur_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-center">-</td>
                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['gas_rp'] === 0 ? '-' : number_format($grandAll['gas_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-center">-</td>
                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['telur_rp'] === 0 ? '-' : number_format($grandAll['telur_rp'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 border-r text-right">
                        {{ $grandAll['loss_bahan'] === 0 ? '-' : number_format($grandAll['loss_bahan'], 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-3 text-right">
                        {{ $grandAll['total_kontribusi'] === 0 ? '-' : number_format($grandAll['total_kontribusi'], 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>


            {{-- @php
                $totSelisihRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['selisih_rp'] ?? 0));
                $totKontribusiRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['kontribusi_rp'] ?? 0));
                $totDiscManualRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['sc_manual_rp'] ?? 0));
                $totReturRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['retur_rp'] ?? 0));
                $totGasRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['gas_rp'] ?? 0));
                $totTelurRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['telur_rp'] ?? 0));
                $totLossRp = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['loss_bahan'] ?? 0));
                $totTotalKontribusi = collect($rowsBulanLalu ?? [])->sum(fn($r) => (int) ($r['total_kontribusi'] ?? 0));
            @endphp
            @php
                $clsFoot =
                    $totTotalKontribusi < 0
                        ? 'bg-rose-50 text-rose-700'
                        : ($totTotalKontribusi > 0
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-gray-50 text-gray-600');
            @endphp
            <tfoot class="border-t border-indigo-300 font-bold">
                <tr class="text-xs {{ $clsFoot }}">
                    <td colspan="2" class="px-3 py-2 border-r text-right">TOTAL</td>
                    <td class="px-3 py-2 border-r text-center"></td>

                    <td class="px-3 py-2 border-r text-right">
                        {{ $totSelisihRp === 0 ? '-' : number_format($totSelisihRp, 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-2 border-r text-right">
                        {{ $totKontribusiRp === 0 ? '-' : number_format($totKontribusiRp, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-2 border-r text-center">-</td>
                    <td class="px-3 py-2 border-r text-right">
                        {{ $totDiscManualRp === 0 ? '-' : number_format($totDiscManualRp, 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-2 border-r text-center">-</td>
                    <td class="px-3 py-2 border-r text-right">
                        {{ $totReturRp === 0 ? '-' : number_format($totReturRp, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-2 border-r text-center">-</td>
                    <td class="px-3 py-2 border-r text-right">
                        {{ $totGasRp === 0 ? '-' : number_format($totGasRp, 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-2 border-r text-center">-</td>
                    <td class="px-3 py-2 border-r text-right">
                        {{ $totTelurRp === 0 ? '-' : number_format($totTelurRp, 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-2 border-r text-right">
                        {{ $totLossRp === 0 ? '-' : number_format($totLossRp, 0, ',', '.') }}
                    </td>

                    <td class="px-3 py-2 text-right font-bold">
                        {{ $totTotalKontribusi === 0 ? '-' : number_format($totTotalKontribusi, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot> --}}
        </table>
    </div> {{-- If your happiness depends on money, you will never be happy with yourself. --}}
</div>
