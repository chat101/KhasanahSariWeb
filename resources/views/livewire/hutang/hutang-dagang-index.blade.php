<div>
    <div class="space-y-4">

        {{-- HEADER + FILTER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                        AP
                    </span>
                    Hutang Dagang Supplier
                </h2>

                <span class="text-[11px] text-gray-500">
                    Menampilkan pembelian dari modul Gudang Masuk & Purchasing
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
                {{-- Periode --}}
                <div>
                    <label class="block mb-1 text-gray-500">Tanggal Input</label>
                    <div class="flex gap-1">
                        <input type="date" wire:model.live="tanggalAwal" class="w-1/2 border rounded-lg px-2 py-1">
                        <input type="date" wire:model.live="tanggalAkhir" class="w-1/2 border rounded-lg px-2 py-1">
                    </div>
                </div>

                {{-- Supplier --}}
                <div>
                    <label class="block mb-1 text-gray-500">Supplier</label>
                    <select wire:model.live="supplierId" class="w-full border rounded-lg px-2 py-1">
                        <option value="">Semua</option>
                        @foreach ($suppliers as $supp)
                            <option value="{{ $supp->id }}">{{ $supp->nmsupp }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Bayar --}}
                <div>
                    <label class="block mb-1 text-gray-500">Status</label>
                    <select wire:model.live="statusBayar" class="w-full border rounded-lg px-2 py-1">
                        <option value="">Semua</option>
                        <option value="0">Belum Lunas</option>
                        <option value="1">Lunas</option>
                    </select>
                </div>

                {{-- Search Faktur / Trans --}}
                <div>
                    <label class="block mb-1 text-gray-500">Cari Faktur / No Trans</label>
                    <input type="text" wire:model.live="search" class="w-full border rounded-lg px-2 py-1"
                        placeholder="cth: 1221212 / SUP25...">
                </div>

                {{-- Info total simple --}}
                <div class="flex items-end justify-end">
                    <div class="text-right text-[11px] text-gray-500">
                        Halaman: <span class="font-semibold">{{ $hutangs->count() }}</span> data
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">
            <button wire:click="openMultiPay"
                class="px-3 py-1.5 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-[11px]">
                Bayar Beberapa Faktur
            </button>

            <table class="min-w-full text-xs text-left">
                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500 border-b ">
                    <tr>
                        <th class="px-3 py-2">Tgl Input</th>
                        <th class="px-3 py-2">Tgl Faktur</th>
                        <th class="px-3 py-2">Jth Tempo</th>
                        <th class="px-3 py-2">Supplier</th>
                        <th class="px-3 py-2">No Trans</th>
                        <th class="px-3 py-2">No Faktur</th>
                        <th class="px-3 py-2 text-right">Grand Total</th>
                        <th class="px-3 py-2 text-center">Tempo</th>
                        <th class="px-3 py-2 text-center">Telat</th>
                        <th class="px-3 py-2 text-center">Status</th>
                        <th class="px-3 py-2 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($hutangs as $row)
                        @php
                            $gm = $row->gudang_masuk;
                            $supp = $gm?->supplier;

                            $tempoHari = $supp->tempo_hari ?? 0;
                            $tglFaktur = $gm ? \Carbon\Carbon::parse($gm->tanggal) : null;
                            $jatuhTempo = $tglFaktur ? $tglFaktur->copy()->addDays($tempoHari) : null;

                            $hariTelat = 0;
                            if ($jatuhTempo && $row->status_bayar == 0 && now()->gt($jatuhTempo)) {
                                $hariTelat = $jatuhTempo->diffInDays(now());
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-1.5">
                                {{ \Carbon\Carbon::parse($row->tgl_input)->format('d-m-Y') }}
                            </td>
                            <td class="px-3 py-1.5">
                                {{ $tglFaktur?->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5">
                                {{ $jatuhTempo?->format('d-m-Y') ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-[11px]">
                                        {{ $supp->nmsupp ?? '-' }}
                                    </span>
                                    <span class="text-[10px] text-gray-500">
                                        {{ $supp->telpsupp ?? '' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-3 py-1.5">
                                {{ $gm->notrans ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5">
                                {{ $gm->no_faktur ?? '-' }}
                            </td>
                            <td class="px-3 py-1.5 text-right font-semibold">
                                {{ number_format($row->grandtotal ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                {{ $tempoHari ? $tempoHari . ' hr' : 'Cash' }}
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                @if ($hariTelat > 0)
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px]">
                                        {{ $hariTelat }} hr
                                    </span>
                                @else
                                    <span class="text-[10px] text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-full text-[10px]
                                    {{ $row->status_bayar ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $row->status_bayar ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                @if (($row->sisa_hutang ?? $row->grandtotal - $row->total_bayar) > 0)
                                    <button wire:click="openSinglePay({{ $row->id }})"
                                        class="px-2 py-1 text-[11px] rounded bg-indigo-500 hover:bg-indigo-600 text-white">
                                        Bayar
                                    </button>
                                @else
                                    <span class="text-[10px] text-gray-400">Lunas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-3 text-center text-gray-500">
                                Tidak ada data hutang pada periode & filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="pt-2 text-xs text-gray-600">
            {{ $hutangs->links() }}
        </div>
    </div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
    {{-- MODAL BAYAR 1 FAKTUR --}}
    {{-- MODAL BAYAR 1 FAKTUR --}}
    <div x-data="{ open: @entangle('showSinglePay') }" x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/20">

        <div {{-- @click.outside="open = false" --}}
            class="w-full max-w-md bg-white rounded-lg shadow-lg border border-gray-200 p-4 text-sm text-gray-700">

            <h3 class="text-base font-semibold mb-3">Pembayaran Hutang - 1 Faktur</h3>

            <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <label class="block text-gray-500 mb-1">Tanggal Bayar</label>
                        <input type="date" wire:model="pay_tgl" class="w-full border rounded px-2 py-1 text-sm">
                        @error('pay_tgl')
                            <span class="text-red-500 text-[11px]">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Sisa Hutang Sebelum</label>
                        <div class="w-full border rounded px-2 py-1 bg-gray-50 text-right">
                            {{ number_format($pay_sisa_sebelum ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-gray-500 mb-1">Jumlah Bayar</label>
                    <div x-data="rupiahEntangled($wire.entangle('pay_jumlah').live)">
                        <input
                            type="text"
                            x-model="display"
                            @input="onInput($event)"
                            class="w-full border rounded px-2 py-1 text-right text-sm"
                            placeholder="0"
                        >
                    </div>
                    @error('pay_jumlah')
                        <span class="text-red-500 text-[11px]">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-gray-500 mb-1">Metode</label>
                        <select wire:model="pay_metode" class="w-full border rounded px-2 py-1 text-sm">
                            <option value="KAS">Kas</option>
                            <option value="BANK">Bank</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-500 mb-1">Keterangan</label>
                        <input type="text" wire:model="pay_keterangan"
                            class="w-full border rounded px-2 py-1 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="closeSinglePay" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-xs">
                    Batal
                </button>
                <button wire:click="saveSinglePay"
                    class="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-xs">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL BAYAR BEBERAPA FAKTUR --}}
    <div x-data="{ open: @entangle('showMultiPay') }" x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/20">

        <div {{-- @click.outside="open = false" --}}
            class="w-full max-w-4xl bg-white rounded-lg shadow-lg border border-gray-200 p-4 text-xs">

            <h3 class="text-sm font-semibold mb-3">Pembayaran Hutang - Beberapa Faktur</h3>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-3 text-black">
                <div>
                    <label class="block text-gray-500 mb-1">No Bukti</label>
                    <input type="text" wire:model="multi_no_bukti" class="w-full border rounded px-2 py-1 text-sm">
                </div>

                <div>
                    <label class="block text-gray-500 mb-1">Supplier</label>
                    <select wire:model="multi_supplier_id" wire:change="loadMultiItems"
                            class="w-full border rounded px-2 py-1 text-sm">
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->nmsupp }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-500 mb-1">Tanggal Bayar</label>
                    <input type="date" wire:model="multi_tgl" class="w-full border rounded px-2 py-1 text-sm">
                </div>

                <div>
                    <label class="block text-gray-500 mb-1">Metode</label>
                    <select wire:model="multi_metode" class="w-full border rounded px-2 py-1 text-sm">
                        <option value="KAS">Kas</option>
                        <option value="BANK">Bank</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-500 mb-1">Total Uang</label>
                    <div x-data="rupiahEntangled($wire.entangle('multi_total_uang').live)" class="w-full">
                        <input
                            type="text"
                            x-model="display"
                            @input="onInput($event)"
                            class="w-full border rounded px-2 py-1 text-right text-sm disabled:bg-gray-100 disabled:text-gray-400"
                            placeholder="0"
                            @disabled(!$multi_supplier_id)
                        >
                        @error('multi_total_uang')
                            <span class="text-red-500 text-[11px]">{{ $message }}</span>
                        @enderror

                        @if(!$multi_supplier_id)
                            <div class="text-[11px] text-amber-600 mt-1">
                                Pilih supplier dulu untuk input total uang.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-5">
                    <label class="block text-gray-500 mb-1">Keterangan</label>
                    <input type="text" wire:model="multi_keterangan" class="w-full border rounded px-2 py-1 text-sm">
                </div>

                <div class="md:col-span-5 flex items-end justify-end">
                    <div class="text-[11px] text-gray-500">
                        Total bayar per faktur:
                        <span class="font-semibold">
                            {{ number_format(collect($multi_items)->sum('bayar'), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>



            <div class="border rounded overflow-x-auto max-h-72">
                <table class="min-w-full text-[11px] text-gray-800">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-2 py-1">Tgl Input</th>
                            <th class="px-2 py-1">Jth Tempo</th> {{-- ⬅ tambah --}}
                            <th class="px-2 py-1">No Trans</th>
                            <th class="px-2 py-1">No Faktur</th>
                            <th class="px-2 py-1 text-right">Sisa Hutang</th>
                            <th class="px-2 py-1 text-right">Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($multi_items as $idx => $item)
                            <tr class="border-t">
                                <td class="px-2 py-1">
                                    {{ \Carbon\Carbon::parse($item['tgl_input'])->format('d-m-Y') }}
                                </td>
                                <td class="px-2 py-1">
                                    {{ \Carbon\Carbon::parse($item['jatuh_tempo'])->format('d-m-Y') }}
                                </td>
                                <td class="px-2 py-1">{{ $item['notrans'] }}</td>
                                <td class="px-2 py-1">{{ $item['no_faktur'] }}</td>
                                <td class="px-2 py-1 text-right">
                                    {{ number_format($item['sisa'], 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-1 text-right">
                                    <div
                                        x-data="rupiahEntangled($wire.entangle('multi_items.{{ $idx }}.bayar').live)"
                                        class="w-28  ml-auto"
                                    >
                                        <input
                                            type="text"
                                            x-model="display"
                                            @input="onInput($event)"
                                            class="w-full border rounded px-1 py-0.5 text-right text-xs "
                                            placeholder="0"
                                        >
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-400">
                                    Pilih supplier untuk melihat daftar faktur yang belum lunas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2 mt-3">
                <button @click="open = false" class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-[11px]">
                    Batal
                </button>
                <button wire:click="saveMultiPay"
                    class="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-[11px]">
                    Simpan Pembayaran
                </button>
            </div>
        </div>
    </div>
    <script>
//       document.addEventListener('alpine:init', () => {
//     Alpine.data('rupiahField', (wireField, initialValue = null) => ({
//         display: '',

//         init() {
//             if (initialValue !== null && initialValue !== '') {
//                 this.display = this.format(initialValue.toString());
//             }
//         },

//         onlyNumber(value) {
//             return (value || '').replace(/[^\d]/g, '');
//         },

//         format(value) {
//             const raw = this.onlyNumber(value);
//             if (!raw) return '';
//             return new Intl.NumberFormat('id-ID').format(parseInt(raw));
//         },

//         onInput(event) {
//             this.display = this.format(event.target.value);

//             const raw = this.onlyNumber(this.display);
//             const numeric = raw ? parseInt(raw) : null;

//             this.$wire.set(wireField, numeric);
//         },
//     }));
// });

        document.addEventListener('livewire:init', () => {
            Livewire.on('multi-total-over', (payload) => {
                alert(
                    'Jumlah uang yang diinput melebihi total hutang.\n' +
                    'Total hutang: ' + payload.totalHutang
                );
            });
        });
    </script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('notify-error', (payload) => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: payload.message,
            });
        });
    });
    </script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify-warning', (payload) => {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: payload.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
            });
        });
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('rupiahEntangled', (model) => ({
                    model,      // ini ter-entangle ke Livewire
                    display: '',

                    init() {
                        // tampilan awal
                        this.display = this.format(this.model);

                        // 🔥 kalau Livewire mengubah model (mis: dipotong), update display
                        this.$watch('model', (v) => {
                            this.display = this.format(v);
                        });
                    },

                    onlyNumber(v) {
                        return (v ?? '').toString().replace(/[^\d]/g, '');
                    },

                    toNumber(v) {
                        const raw = this.onlyNumber(v);
                        return raw ? parseInt(raw) : null;
                    },

                    format(v) {
                        const n = this.toNumber(v);
                        if (!n) return '';
                        return new Intl.NumberFormat('id-ID').format(n);
                    },

                    onInput(e) {
                        // user ketik -> rapikan display
                        this.display = this.format(e.target.value);

                        // kirim angka bersih ke Livewire
                        this.model = this.toNumber(this.display);
                    },
                }));
            });
            </script>
    <script>
        document.addEventListener('livewire:init', () => {
          Livewire.on('multi-total-over', (payload) => {
            alert('Jumlah uang melebihi total hutang.\nTotal hutang: ' + payload.totalHutang);
          });

          Livewire.on('pay-over', (payload) => {
            alert('Jumlah bayar melebihi sisa hutang.\nMaksimal: ' + payload.max);
          });
        });
        </script>


</div>
