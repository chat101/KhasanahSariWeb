<div>
         {{-- FILTER --}}
                            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">


                                  <div class="md:col-span-2">
    <label class="block mb-1 text-gray-500">Periode</label>

    <div class="flex flex-wrap md:flex-nowrap items-end gap-2">
        {{-- Tanggal Awal --}}
        <input type="date"
               wire:model.live="tanggalAwal"
               class="border rounded-lg px-2 py-1 h-8">

        <span class="text-gray-400 text-[11px] pb-1">sd</span>

        {{-- Tanggal Akhir --}}
        <input type="date"
               wire:model.live="tanggalAkhir"
               class="border rounded-lg px-2 py-1 h-8">

        {{-- INFO --}}
        @if($tanggalAkhir === now()->toDateString())
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
                                        <button wire:click="loadTarget" wire:loading.attr="disabled"
                                            wire:target="loadTarget"
                                            class="relative px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] disabled:opacity-60">
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
                            <div
                                class="relative bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">

                                {{-- LOADING OVERLAY --}}
                                <div wire:loading wire:target="loadTarget"
                                    class="absolute inset-0 z-20 flex items-center justify-center
                                    bg-white/70 backdrop-blur-sm">
                                    <div class="flex flex-col items-center gap-2 text-xs text-gray-700">
                                        <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4" fill="none" />
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                        </svg>
                                        <span>Menghitung kontribusi per toko…</span>
                                    </div>
                                </div>
                                <table class="min-w-[1200px] w-full text-xs text-left">
                                    <thead class="text-[11px] uppercase text-gray-600">
                                        <tr class="border-b">
                                            <th colspan="5"
                                                class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
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
                                                AREA</th>
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
    $lastArea = null;

    $renderSubtotal = function ($label, $t) {
        if (!$t) return '';

        // helper class rp +/- (minus merah, plus hijau, nol abu)
        $clsRp = function ($rp) {
            $rp = (int) $rp;
            return $rp < 0
                ? 'text-rose-700 bg-rose-50'
                : ($rp > 0
                    ? 'text-emerald-700 bg-emerald-50'
                    : 'text-gray-600 bg-gray-50');
        };

        // warna row subtotal (pakai total_kontribusi)
        $tk = (int)($t['total_kontribusi'] ?? 0);
        $clsRow = $tk < 0
            ? 'bg-rose-50 text-rose-700'
            : ($tk > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600');

        ob_start(); ?>
            <tr class="font-semibold <?= $clsRow ?> border-t border-slate-300">
                <td colspan="2" class="px-3 py-2 border-r text-right text-gray-800">
                    TOTAL AREA <?= e($label) ?>
                </td>

                <td class="px-3 py-2 border-r text-center"></td>

                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['selisih_rp'] ?? 0) ?>">
                    <?= ((int)($t['selisih_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['selisih_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['kontribusi_rp'] ?? 0) ?>">
                    <?= ((int)($t['kontribusi_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['kontribusi_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-center">-</td>
                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['sc_manual_rp'] ?? 0) ?>">
                    <?= ((int)($t['sc_manual_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['sc_manual_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-center">-</td>
                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['retur_rp'] ?? 0) ?>">
                    <?= ((int)($t['retur_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['retur_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-center">-</td>
                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['gas_rp'] ?? 0) ?>">
                    <?= ((int)($t['gas_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['gas_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-center">-</td>
                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['telur_rp'] ?? 0) ?>">
                    <?= ((int)($t['telur_rp'] ?? 0) === 0) ? '-' : number_format((int)$t['telur_rp'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 border-r text-right font-semibold <?= $clsRp($t['loss_bahan'] ?? 0) ?>">
                    <?= ((int)($t['loss_bahan'] ?? 0) === 0) ? '-' : number_format((int)$t['loss_bahan'], 0, ',', '.') ?>
                </td>

                <td class="px-3 py-2 text-right font-semibold <?= $clsRp($t['total_kontribusi'] ?? 0) ?>">
                    <?= ($tk === 0) ? '-' : number_format($tk, 0, ',', '.') ?>
                </td>
            </tr>
        <?php
        return ob_get_clean();
    };
@endphp


                                        @forelse(($rowsTarget ?? []) as $r)
                                            @php
                                                $area = $r['area_label'] ?? '-';

                                                // kalau ganti area -> cetak subtotal area sebelumnya
                                                if ($lastArea !== null && $area !== $lastArea) {
                                                    $tPrev = $totalsByArea[$lastArea] ?? null;
                                                    echo $renderSubtotal($lastArea, $tPrev);
                                                }
                                                $lastArea = $area;

                                                $sp = is_null($r['selisih_persen'] ?? null) ? null : (float) $r['selisih_persen'];
                                                $sr = (int) ($r['selisih_rp'] ?? 0);
                                                $kr = (int) ($r['kontribusi_rp'] ?? 0);
                                                $v  = (int) ($r['sc_manual_rp'] ?? 0);

                                                $clsP = is_null($sp)
                                                    ? 'text-gray-500'
                                                    : ($sp < 0 ? 'text-rose-700 bg-rose-50' : ($sp > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50'));

                                                $clsR = $sr < 0 ? 'text-rose-700 bg-rose-50' : ($sr > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');
                                                $clsK = $kr < 0 ? 'text-rose-700 bg-rose-50' : ($kr > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');
                                                $clsV = $v  < 0 ? 'text-rose-700 bg-rose-50' : ($v  > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');

                                                $gasR = (int) ($r['gas_rp'] ?? 0);
                                                $telR = (int) ($r['telur_rp'] ?? 0);

                                                $clsGas = $gasR < 0 ? 'text-rose-700 bg-rose-50' : ($gasR > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');
                                                $clsTel = $telR < 0 ? 'text-rose-700 bg-rose-50' : ($telR > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');

                                                $retP = is_null($r['retur_persen'] ?? null) ? null : (float) $r['retur_persen'];
                                                $clsRetP = is_null($retP)
                                                    ? 'text-gray-500'
                                                    : ($retP < 0 ? 'text-rose-700 bg-rose-50' : ($retP > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50'));

                                                $gasP = is_null($r['gas_persen'] ?? null) ? null : (float) $r['gas_persen'];
                                                $telP = is_null($r['telur_persen'] ?? null) ? null : (float) $r['telur_persen'];

                                                $clsGasP = is_null($gasP)
                                                    ? 'text-gray-500'
                                                    : ($gasP < 0 ? 'text-rose-700 bg-rose-50' : ($gasP > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50'));

                                                $clsTelP = is_null($telP)
                                                    ? 'text-gray-500'
                                                    : ($telP < 0 ? 'text-rose-700 bg-rose-50' : ($telP > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50'));

                                                $tk = (int) ($r['total_kontribusi'] ?? 0);
                                                $clsTK = $tk < 0 ? 'text-rose-700 bg-rose-50' : ($tk > 0 ? 'text-emerald-700 bg-emerald-50' : 'text-gray-600 bg-gray-50');
                                            @endphp

                                            <tr class="hover:bg-gray-50">
                                                {{-- AREA --}}

                                                <td class="px-3 py-2 border-r align-top">
                                                    <div class="font-semibold text-gray-900">
                                                        {{ $r['area_label'] ?? '-' }}
                                                    </div>

                                                    @php $pic = trim((string)($r['area_pic'] ?? '')); @endphp
                                                    @if($pic !== '')
                                                        <div class="text-[10px] italic text-amber-600">
                                                            {{  $r['area_pic']}}
                                                        </div>
                                                    @endif
                                                </td>



                                                {{-- <td class="px-3 py-2 border-r text-left">
                                                    <div class="font-semibold text-gray-800">
                                                        {{ $r['area_label'] ?? '-' }}
                                                    </div>

                                                    @if(!empty($r['area_pic']))
                                                        <div class="text-[10px] text-gray-500 leading-tight">
                                                            PIC: {{ $r['area_pic'] }}
                                                        </div>
                                                    @endif
                                                </td> --}}

                                                {{-- OUTLET --}}
                                                <td class="px-3 py-2 border-r text-left font-medium">
                                                    {{ $r['outlet'] ?? '-' }}
                                                </td>

                                                {{-- Selisih % --}}
                                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsP }}">
                                                    @if (is_null($sp)) - @else {{ number_format($sp, 2, ',', '.') }} @endif
                                                </td>

                                                {{-- Selisih Rp --}}
                                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsR }}">
                                                    {{ $sr === 0 ? '-' : number_format($sr, 0, ',', '.') }}
                                                </td>

                                                {{-- Kontribusi Rp --}}
                                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsK }}">
                                                    {{ $kr === 0 ? '-' : number_format($kr, 0, ',', '.') }}
                                                </td>

                                                {{-- DISC % --}}
                                                <td class="px-3 py-2 border-r text-center">
                                                    {{ is_null($r['sc_manual_persen'] ?? null) ? '-' : number_format($r['sc_manual_persen'], 2, ',', '.') }}
                                                </td>

                                                {{-- DISC Rp --}}
                                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsV }}">
                                                    {{ $v === 0 ? '-' : number_format($v, 0, ',', '.') }}
                                                </td>

                                                {{-- RETUR % --}}
                                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsRetP }}">
                                                    {{ is_null($retP) ? '-' : number_format($retP, 2, ',', '.') }}
                                                </td>

                                                {{-- RETUR Rp --}}
                                                <td class="px-3 py-2 border-r text-right">
                                                    {{ number_format($r['retur_rp'] ?? 0, 0, ',', '.') }}
                                                </td>

                                                {{-- GAS % --}}
                                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsGasP }}">
                                                    {{ is_null($gasP) ? '-' : number_format($gasP, 2, ',', '.') }}
                                                </td>

                                                {{-- GAS Rp --}}
                                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsGas }}">
                                                    {{ $gasR === 0 ? '-' : number_format($gasR, 0, ',', '.') }}
                                                </td>

                                                {{-- TELUR % --}}
                                                <td class="px-3 py-2 border-r text-center font-semibold {{ $clsTelP }}">
                                                    {{ is_null($telP) ? '-' : number_format($telP, 2, ',', '.') }}
                                                </td>

                                                {{-- TELUR Rp --}}
                                                <td class="px-3 py-2 border-r text-right font-semibold {{ $clsTel }}">
                                                    {{ $telR === 0 ? '-' : number_format($telR, 0, ',', '.') }}
                                                </td>

                                                {{-- Loss --}}
                                                <td class="px-3 py-2 border-r text-right">
                                                    {{ number_format($r['loss_bahan'] ?? 0, 0, ',', '.') }}
                                                </td>

                                                {{-- Total Kontribusi --}}
                                                <td class="px-3 py-2 text-right font-semibold {{ $clsTK }}">
                                                    {{ $tk === 0 ? '-' : number_format($tk, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="14" class="px-3 py-6 text-center text-gray-500">
                                                    Belum ada data.
                                                </td>
                                            </tr>
                                        @endforelse

                                        {{-- subtotal area terakhir --}}
                                        @if(!empty($rowsTarget) && $lastArea !== null)
                                            @php
                                                $tLast = $totalsByArea[$lastArea] ?? null;
                                                echo $renderSubtotal($lastArea, $tLast);
                                            @endphp
                                        @endif
                                    </tbody>

                                    @php
                                        // pakai grandTotals kalau sudah disediakan dari Livewire, kalau belum fallback hitung dari rows
                                        $gt = $grandTotals ?? [
                                            'selisih_rp'       => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
                                            'kontribusi_rp'    => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
                                            'sc_manual_rp'     => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
                                            'retur_rp'         => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
                                            'gas_rp'           => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
                                            'telur_rp'         => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
                                            'loss_bahan'       => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
                                            'total_kontribusi' => collect($rowsTarget ?? [])->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
                                        ];

                                        $clsFoot = ((int)($gt['total_kontribusi'] ?? 0) < 0)
                                            ? 'bg-rose-50 text-rose-700'
                                            : (((int)($gt['total_kontribusi'] ?? 0) > 0)
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-gray-50 text-gray-600');
                                    @endphp

                                    <tfoot class="border-t border-indigo-300 font-bold">
                                        <tr class="text-xs {{ $clsFoot }}">
                                            <td class="px-3 py-2 border-r text-right" colspan="2">GRAND TOTAL</td>

                                            <td class="px-3 py-2 border-r text-center"></td>

                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['selisih_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['selisih_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['kontribusi_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['kontribusi_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-center">-</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['sc_manual_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['sc_manual_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-center">-</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['retur_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['retur_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-center">-</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['gas_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['gas_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-center">-</td>
                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['telur_rp'] ?? 0) === 0) ? '-' : number_format((int)$gt['telur_rp'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 border-r text-right">
                                                {{ ((int)($gt['loss_bahan'] ?? 0) === 0) ? '-' : number_format((int)$gt['loss_bahan'], 0, ',', '.') }}
                                            </td>

                                            <td class="px-3 py-2 text-right font-bold">
                                                {{ ((int)($gt['total_kontribusi'] ?? 0) === 0) ? '-' : number_format((int)$gt['total_kontribusi'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>


                                    {{-- @php
                                        $totSelisihRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['selisih_rp'] ?? 0),
                                        );
                                        $totKontribusiRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['kontribusi_rp'] ?? 0),
                                        );
                                        $totDiscManualRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['sc_manual_rp'] ?? 0),
                                        );
                                        $totReturRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['retur_rp'] ?? 0),
                                        );
                                        $totGasRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['gas_rp'] ?? 0),
                                        );
                                        $totTelurRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['telur_rp'] ?? 0),
                                        );
                                        $totLossRp = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['loss_bahan'] ?? 0),
                                        );
                                        $totTotalKontribusi = collect($rowsTarget ?? [])->sum(
                                            fn($r) => (int) ($r['total_kontribusi'] ?? 0),
                                        );
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
                                            <td class="px-3 py-2 border-r text-right">TOTAL</td>
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

                            </div>  {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
</div>
