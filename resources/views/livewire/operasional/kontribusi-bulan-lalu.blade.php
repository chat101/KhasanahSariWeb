<div class="space-y-4">
    {{-- FILTER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <div>
                <div class="text-sm font-semibold flex items-center gap-2">
                    KONTRIBUSI BULAN LALU
                </div>
                <div class="text-xs text-gray-500">Laporan kontribusi per toko periode bulan lalu (diurutkan dari toko terendah ke tertinggi).</div>
            </div>

            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
                    <input type="date" wire:model="periodeAwal"
                           class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                <div class="w-full sm:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
                    <input type="date" wire:model="periodeAkhir"
                           class="w-full sm:w-40 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="w-full sm:w-auto pb-0.5 flex items-center gap-2">
                    <button wire:click="loadBulanLalu"
                            wire:loading.attr="disabled"
                            wire:target="loadBulanLalu"
                            class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="loadBulanLalu">Tampilkan</span>
                        <span wire:loading wire:target="loadBulanLalu">Loading...</span>
                    </button>

                    <button wire:click="loadAndDownload"
                            wire:loading.attr="disabled"
                            wire:target="loadAndDownload"
                            class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="loadAndDownload">📥 Download Excel</span>
                        <span wire:loading wire:target="loadAndDownload">Menyiapkan...</span>
                    </button>

                    <button wire:click="resetBulanLalu"
                            class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-sm font-medium transition-colors shadow-sm">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $fmtRp  = fn($v) => number_format((int)$v, 0, ',', '.');
        $fmtPct = fn($v) => ($v === null || $v === '-') ? '-' : rtrim(rtrim(number_format((float)$v, 2, '.', ''), '0'), '.') . '%';

        $pctVal = function ($v) {
            if (is_null($v)) return null;
            if (is_string($v)) $v = trim(str_replace('%', '', $v));
            if ($v === '' || $v === '-') return null;
            return is_numeric($v) ? (float)$v : null;
        };

        $color = function ($v) {
            $val = (float)$v;
            if ($val < 0) return 'text-rose-600 font-medium';
            if ($val > 0) return 'text-emerald-600 font-medium';
            return 'text-gray-400';
        };

        $rows = $rowsBulanLaluView ?? [];

        // grouping wilayah -> area
        $groupWilayah = collect($rows)
            ->sortBy([['wilayah_label','asc'], ['area_label','asc'], ['outlet','asc']])
            ->groupBy(fn($r) => $r['wilayah_label'] ?? '-');
    @endphp

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
        <table class="min-w-[1600px] w-full text-xs text-left">
            <thead class="text-[11px] uppercase text-gray-700 bg-gray-50 border-b-2 border-gray-200 font-semibold">
                <tr>
                    <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">WILAYAH</th>
                    <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">AREA</th>
                    <th rowspan="2" class="px-3 py-2 border-r text-center align-middle bg-gray-100">OUTLET</th>

                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Selisih %</th>
                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">(+/-) Rp</th>
                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle">Kontribusi</th>

                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">DISC MANUAL</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">RETUR</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">GAS</th>
                    <th colspan="2" class="px-3 py-2 text-center border-r border-b bg-gray-100">TELUR</th>

                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle bg-gray-100">LOSS BAHAN</th>
                    <th rowspan="2" class="px-3 py-2 text-right border-r align-middle bg-red-50 border-red-200 text-red-700">KURANG SETORAN</th>
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
            @forelse($groupWilayah as $wilayah => $rowsWil)
                @php
                    $groupArea = $rowsWil->groupBy(fn($r) => $r['area_label'] ?? '-');
                @endphp

                @foreach($groupArea as $area => $rowsArea)
                    @php
                        // Row-span wilayah: hitung total rows (toko + area subtotal)
                        $tokoCount = $rowsArea->count();
                        $rowspanWilayah = $tokoCount + 1; // +1 untuk area subtotal
                        $printedWilayah = false;
                    @endphp

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
                            $ks   = (int)($r['kurang_setoran'] ?? 0);
                            $tk   = (int)($r['total_kontribusi'] ?? 0);
                        @endphp

                        <tr class="hover:bg-gray-50">
                            @if(!$printedWilayah)
                                <td rowspan="{{ $rowspanWilayah }}"
                                    class="px-3 py-2 border-r text-center align-top font-semibold bg-white text-gray-900 whitespace-nowrap">
                                    {{ $wilayah }}
                                </td>
                                @php $printedWilayah = true; @endphp
                            @endif

                            <td class="px-3 py-2 border-r text-left align-top font-semibold text-gray-900">
                                {{ $area }}
                            </td>

                            <td class="px-3 py-2 border-r text-left font-bold text-gray-900">
                                {{ $r['outlet'] ?? '-' }}
                            </td>

                            <td class="px-3 py-2 text-right border-r {{ $color($sp) }}">{{ $fmtPct($sp) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($sr) }}">{{ $sr === 0 ? '-' : $fmtRp($sr) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($kr) }}">{{ $kr === 0 ? '-' : $fmtRp($kr) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($dmP) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($dmR) }}">{{ $dmR === 0 ? '-' : $fmtRp($dmR) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($retP) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($retR) }}">{{ $retR === 0 ? '-' : $fmtRp($retR) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($gasP) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($gasR) }}">{{ $gasR === 0 ? '-' : $fmtRp($gasR) }}</td>

                            <td class="px-3 py-2 text-right border-r text-gray-500">{{ $fmtPct($telP) }}</td>
                            <td class="px-3 py-2 text-right border-r {{ $color($telR) }}">{{ $telR === 0 ? '-' : $fmtRp($telR) }}</td>

                            <td class="px-3 py-2 text-right border-r {{ $color($loss) }}">
                                @if($loss > 0)
                                    <button type="button" class="underline decoration-dashed underline-offset-4" wire:click="openLossModal('{{ addslashes($r['outlet'] ?? '') }}', '{{ $periodeAwal }}', '{{ $periodeAkhir }}', {{ $loss }}, {{ $r['toko_id'] ?? 0 }})" title="Klik untuk melihat detail barang">
                                        {{ $fmtRp($loss) }}
                                    </button>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-3 py-2 text-right border-r font-medium text-red-600">
                                {{ $ks === 0 ? '-' : ('-' . $fmtRp($ks)) }}
                            </td>

                            <td class="px-3 py-2 text-right font-bold {{ $color($tk) }}">
                                {{ $tk === 0 ? '-' : $fmtRp($tk) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- SUBTOTAL AREA --}}
                    @php
                        $sumCols = function($rows) use ($pctVal) {
                            $rows = collect($rows);

                            $avgPct = function(string $key) use ($rows, $pctVal): ?float {
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
                                'kurang_setoran'   => (int) $rows->sum(fn($r) => (int)($r['kurang_setoran'] ?? 0)),
                                'total_kontribusi' => (int) $rows->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
                            ];
                        };

                        $t = $sumCols($rowsArea);
                        $tkA = (int)($t['total_kontribusi'] ?? 0);
                        $clsArea = $tkA < 0 ? 'bg-rose-50 text-rose-700' : ($tkA > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-50 text-gray-600');
                        $v = fn($k) => $pctVal($t[$k] ?? null);
                    @endphp

                    <tr class="font-semibold {{ $clsArea }} border-t border-slate-300">
                        <td colspan="2" class="px-3 py-2 border-r text-right text-gray-800">
                            SUBTOTAL AREA: {{ $area }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($v('selisih_persen')) }}">
                            {{ $fmtPct($v('selisih_persen')) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['selisih_rp'] ?? 0) }}">
                            {{ (int)($t['selisih_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['selisih_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['kontribusi_rp'] ?? 0) }}">
                            {{ (int)($t['kontribusi_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['kontribusi_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($v('sc_manual_persen')) }}">
                            {{ $fmtPct($v('sc_manual_persen')) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['sc_manual_rp'] ?? 0) }}">
                            {{ (int)($t['sc_manual_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['sc_manual_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($v('retur_persen')) }}">
                            {{ $fmtPct($v('retur_persen')) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['retur_rp'] ?? 0) }}">
                            {{ (int)($t['retur_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['retur_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($v('gas_persen')) }}">
                            {{ $fmtPct($v('gas_persen')) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['gas_rp'] ?? 0) }}">
                            {{ (int)($t['gas_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['gas_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($v('telur_persen')) }}">
                            {{ $fmtPct($v('telur_persen')) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['telur_rp'] ?? 0) }}">
                            {{ (int)($t['telur_rp'] ?? 0) === 0 ? '-' : $fmtRp($t['telur_rp']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r {{ $color($t['loss_bahan'] ?? 0) }}">
                            {{ (int)($t['loss_bahan'] ?? 0) === 0 ? '-' : $fmtRp($t['loss_bahan']) }}
                        </td>

                        <td class="px-3 py-2 text-right border-r font-medium text-red-600">
                            @php $ksArea = (int)($t['kurang_setoran'] ?? 0); @endphp
                            {{ $ksArea === 0 ? '-' : ('-' . $fmtRp($ksArea)) }}
                        </td>

                        <td class="px-3 py-2 text-right font-bold {{ $color($t['total_kontribusi'] ?? 0) }}">
                            {{ (int)($t['total_kontribusi'] ?? 0) === 0 ? '-' : $fmtRp($t['total_kontribusi']) }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="16" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td>
                </tr>
            @endforelse
            </tbody>

            {{-- GRAND TOTAL --}}
            @php
                $gt = $grandTotalsView ?? [];
                $tkG = (int)($gt['total_kontribusi'] ?? 0);
                $clsGrand = $tkG < 0 ? 'bg-rose-100 text-rose-800' : ($tkG > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800');
                $v = fn($k) => $pctVal($gt[$k] ?? null);
            @endphp

            <tfoot class="border-t-4">
                <tr class="font-extrabold {{ $clsGrand }}">
                    <td colspan="3" class="px-3 py-3 border-r text-right">GRAND TOTAL</td>

                    <td class="px-3 py-3 text-right border-r {{ $color($v('selisih_persen')) }}">
                        {{ $fmtPct($v('selisih_persen')) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['selisih_rp'] ?? 0) }}">
                        {{ (int)($gt['selisih_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['selisih_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['kontribusi_rp'] ?? 0) }}">
                        {{ (int)($gt['kontribusi_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['kontribusi_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($v('sc_manual_persen')) }}">
                        {{ $fmtPct($v('sc_manual_persen')) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['sc_manual_rp'] ?? 0) }}">
                        {{ (int)($gt['sc_manual_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['sc_manual_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($v('retur_persen')) }}">
                        {{ $fmtPct($v('retur_persen')) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['retur_rp'] ?? 0) }}">
                        {{ (int)($gt['retur_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['retur_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($v('gas_persen')) }}">
                        {{ $fmtPct($v('gas_persen')) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['gas_rp'] ?? 0) }}">
                        {{ (int)($gt['gas_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['gas_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($v('telur_persen')) }}">
                        {{ $fmtPct($v('telur_persen')) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['telur_rp'] ?? 0) }}">
                        {{ (int)($gt['telur_rp'] ?? 0) === 0 ? '-' : $fmtRp($gt['telur_rp']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r {{ $color($gt['loss_bahan'] ?? 0) }}">
                        {{ (int)($gt['loss_bahan'] ?? 0) === 0 ? '-' : $fmtRp($gt['loss_bahan']) }}
                    </td>

                    <td class="px-3 py-3 text-right border-r font-medium text-red-600">
                        @php $ksGrand = (int)($gt['kurang_setoran'] ?? 0); @endphp
                        {{ $ksGrand === 0 ? '-' : ('-' . $fmtRp($ksGrand)) }}
                    </td>

                    <td class="px-3 py-3 text-right {{ $color($gt['total_kontribusi'] ?? 0) }}">
                        {{ (int)($gt['total_kontribusi'] ?? 0) === 0 ? '-' : $fmtRp($gt['total_kontribusi']) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- LOSS MODAL --}}
    @if($showLossModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/40" wire:click="closeLossModal"></div>

            <div class="relative z-50 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-200 p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Detail Loss Bahan</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Outlet: <span class="font-semibold text-gray-800">{{ $lossModalOutlet }}</span></div>
                        @if($lossModalTanggal)
                            <div class="text-[11px] text-gray-500">Periode: <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($periodeAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($periodeAkhir)->format('d/m/Y') }}</span></div>
                        @endif
                    </div>
                    <button wire:click="closeLossModal" class="text-gray-400 hover:text-gray-600 text-sm">✕</button>
                </div>

                <div class="mt-4 max-h-72 overflow-y-auto text-sm space-y-3">
                    @if(!empty($lossModalItems))
                        @php
                            $currentDate = null;
                            $groupedByDate = [];
                            foreach ($lossModalItems as $item) {
                                $tgl = $item['tanggal'] ?? '';
                                if (!isset($groupedByDate[$tgl])) {
                                    $groupedByDate[$tgl] = [];
                                }
                                $groupedByDate[$tgl][] = $item;
                            }
                        @endphp
                        
                        @foreach($groupedByDate as $tgl => $items)
                            <div>
                                <div class="text-gray-600 text-xs font-semibold mb-2">
                                    {{ \Carbon\Carbon::parse($tgl)->format('d M Y') }}
                                </div>
                                @foreach($items as $item)
                                    <div class="flex items-center justify-between pl-3 pb-2 border-b border-gray-100 ml-2">
                                        <div class="flex-1">
                                            <div class="text-gray-900 font-medium">{{ $item['barang'] ?? '-' }}</div>
                                        </div>
                                        <div class="text-right ml-4">
                                            <div class="text-gray-900 font-semibold">{{ number_format((int)($item['qty'] ?? 0), 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @elseif((int)($lossModalTotal ?? 0) > 0)
                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-4 text-center">
                            <div class="text-[11px] text-gray-500 mb-1">Total Loss</div>
                            <div class="text-lg font-semibold text-gray-900">{{ number_format((int)$lossModalTotal, 0, ',', '.') }}</div>
                            <div class="text-[11px] text-gray-500 mt-1">(tidak ada rincian barang)</div>
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-500 text-sm">Tidak ada data loss pada tanggal ini.</div>
                    @endif
                </div>

                <div class="mt-4 flex justify-end">
                    <button wire:click="closeLossModal" class="px-3 py-2 rounded-lg border border-gray-200 text-gray-700 text-xs hover:bg-gray-50">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
