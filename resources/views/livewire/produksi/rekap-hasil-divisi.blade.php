<div>
    <div x-data="{ activeTab: 'rekaphasil' }" class="w-full">

        <div class="px-1 py-1 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24"
                    fill="currentColor">
                    <path
                        d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
                </svg>
                <h2 class="text-base font-semibold">Hasil Giling</h2>
            </div>

            <div class="relative w-full max-w-xs">
                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Notifikasi (tetap pakai session('message')) --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                class="mx-4 mt-3 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-2 text-xs">
                {{ session('message') }}
            </div>
        @endif


        {{-- Bar kontrol --}}
        <div class="px-4 pt-3 flex items-end justify-between gap-3">
            <div class="w-full sm:max-w-xs" x-data x-init="flatpickr($refs.tanggalInput, {
                dateFormat: 'Y-m-d',
                defaultDate: '{{ $tanggalProduksi }}',
                onChange(selected, dateStr) { $wire.set('tanggalProduksi', dateStr) }
            })">
                <label class="block text-sm font-semibold text-gray-700 dark:text-zinc-200 mb-1">
                    Tanggal Produksi
                </label>

                <input type="text" x-ref="tanggalInput" wire:ignore
                    class="w-full border rounded px-3 py-2 text-sm shadow-sm
                       focus:ring focus:ring-blue-200 dark:bg-zinc-800 dark:border-zinc-600"
                    placeholder="Pilih tanggal" />
            </div>

            @if ($this->perintah_id)
                <div class="mt-3 text-xs px-3 py-2 rounded bg-yellow-100 text-yellow-800 shadow-sm "> Memproses
                    Perintah ID:
                    <span class="font-semibold">#{{ $this->perintah_id }}</span>
                </div>
            @endif


        </div>
        <div id="grid-wrap" class="overflow-auto max-h-[70vh]" style="scrollbar-gutter:stable both-edges;">
            <table id="grid-body" class="min-w-full mt-1.5 text-[12px] text-left text-gray-800 dark:text-zinc-100">
                <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-center w-12 border-b border-gray-200 dark:border-zinc-700">No</th>
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Nama</th>
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Tong
                        </th>
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Target
                        </th>
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Giling</th>
                        {{-- <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Counter</th> --}}
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Poprok</th>
                        <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Dekor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($perintahproduksi as $index => $produk)
                        <tr>
                            <td class="td">{{ $loop->iteration }}</td>
                            <td class="td">{{ $produk->master_product_nama }}</td>

                            {{-- Produksi (tong) bersih --}}
                            <td class="td text-center">
                                {{ number_format($produk->qty_total, 0, ',', '.') }}
                            </td>

                            {{-- Target bersih --}}
                            <td class="td text-center">
                                {{ number_format($produk->sisa_target, 0, ',', '.') }}
                            </td>

                            {{-- Realisasi per divisi --}}
                            <td class="td text-center">
                                {{ number_format($produk->qty_giling, 0, ',', '.') }}
                            </td>
                            {{-- <td class="td text-center">
                                {{ number_format($produk->qty_counter, 0, ',', '.') }}
                            </td> --}}
                            <td class="td text-center">
                                {{ number_format($produk->qty_poprok, 0, ',', '.') }}
                            </td>
                            <td class="td text-center">
                                {{ number_format($produk->qty_dekor, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="td text-center text-gray-300 dark:text-gray-400" colspan="8">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if (count($perintahproduksi) > 0)
                @php
                $totalQtyTotal   = collect($perintahproduksi)->sum('qty_total');
                $totalSisaTarget = collect($perintahproduksi)->sum('sisa_target');
                $totalGiling     = collect($perintahproduksi)->sum('qty_giling');
                $totalCounter    = collect($perintahproduksi)->sum('qty_counter');
                $totalPoprok     = collect($perintahproduksi)->sum('qty_poprok');
                $totalDekor      = collect($perintahproduksi)->sum('qty_dekor');
            @endphp

            <tfoot>
              <tr class="bg-gray-100 dark:bg-zinc-800 font-semibold">
                <td colspan="2" class="px-3 py-2 text-right border-t border-gray-200 dark:border-zinc-700">Total</td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalQtyTotal, 0, ',', '.') }}
                </td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalSisaTarget, 0, ',', '.') }}
                </td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalGiling, 0, ',', '.') }}
                </td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalCounter, 0, ',', '.') }}
                </td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalPoprok, 0, ',', '.') }}
                </td>
                <td class="text-center border-t border-gray-200 dark:border-zinc-700">
                  {{ number_format($totalDekor, 0, ',', '.') }}
                </td>
              </tr>
            </tfoot>
                @endif
            </table>

        </div>


        {{-- ====== STYLE ====== --}}
        <style>
            /* Kunci layout & biar konten tidak memaksa melebar */
            #grid-body,
            #grid-head-table {
                table-layout: fixed;
                border-collapse: collapse;
                width: 100%;
            }

            #grid-body td,
            #grid-head th {
                box-sizing: border-box;
                min-width: 0;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Header cell look */
            #grid-head th.th {
                padding: .5rem .5rem;
                border: 1px solid rgb(63 63 70 / .6);
                background: #22c55e;
            }

            /* Body cell look */
            #grid-body td.td {
                padding: .4rem .5rem;
                border: 1px solid rgb(63 63 70 / .6);
            }

            /* Input patuh kolom */
            .cell-input {
                display: block;
                width: 100%;
                max-width: 100%;
                min-width: 0 !important;
                padding: .25rem .4rem;
                text-align: center;
                border: 1px solid #3f3f46;
                border-radius: .25rem;
                background: #0b0b0b22;
            }

            /* Kolom Id: ada di layout tapi 0px */
            #col-head .col-id,
            #col-body .col-id {
                width: 0 !important;
            }

            .th-id,
            .td-id {
                width: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                visibility: hidden;
                overflow: hidden !important;
            }

            /* --- FIX: teks header menghilang saat di-transform --- */
            #grid-head-inner {
                /* sudah ada will-change, tambahkan: */
                transform: translateZ(0);
                /* promote ke compositing layer */
                backface-visibility: hidden;
                /* hindari glitch text */
                -webkit-font-smoothing: antialiased;
                text-rendering: optimizeLegibility;
                overflow: visible;
                /* pastikan teks tidak terpotong */
            }

            /* Pastikan teks header selalu kelihatan */
            #grid-head th {
                position: relative;
                z-index: 2;
                /* di atas background hijau */
                color: #111827;
                /* gray-900 */
            }

            .dark #grid-head th {
                color: #0a0a0a;
                /* tetap gelap di dark mode */
            }

            /* Opsional: kalau masih terlihat “pudar”, coba font weight */
            #grid-head th .label,
            #grid-head th {
                font-weight: 600;
            }

            #grid-head-inner {
                transform: translateZ(0);
                backface-visibility: hidden;
            }
        </style>
    </div>

</div>

