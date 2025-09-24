<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

    {{-- Header --}}
    <div
        class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
            </svg>
            <h2 class="text-base font-semibold">Perintah Produksi — Input Utama & Tambahan</h2>
        </div>

        {{-- Tanggal & Simpan --}}
        <div class="w-full sm:w-auto" x-data="{}" x-init="flatpickr($refs.tanggalInput, {
            dateFormat: 'Y-m-d',
            defaultDate: '{{ $tanggalProduksi }}',
            onChange: (d, s) => $wire.set('tanggalProduksi', s)
        })">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">Pilih Tanggal:</span>
                    <input type="text" x-ref="tanggalInput" value="{{ $tanggalProduksi }}"
                        class="w-40 sm:w-44 border rounded px-3 py-1.5 text-sm shadow-sm focus:ring focus:ring-indigo-200 dark:bg-zinc-800 dark:border-zinc-600"
                        placeholder="Pilih tanggal" />
                </div>
                <button type="button" wire:click="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-1.5 rounded shadow transition">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 left-1/2 -translate-x-1/2 w-72 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm"
                    aria-label="Tutup">✕</button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left w-12 border-b border-gray-200 dark:border-zinc-700">No</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Nama Barang</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700 hidden sm:table-cell">
                        Jenis</th>
                    <th class="px-3 py-2 text-right border-b border-gray-200 dark:border-zinc-700">Satuan Tong</th>
                    <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Produksi (tong)</th>
                    <th class="px-3 py-2 text-right border-b border-gray-200 dark:border-zinc-700">Target Produksi</th>
                </tr>
            </thead>
            <tbody x-data="{ focusIndex: null }" class="text-gray-700 dark:text-zinc-200">
                @php
                    $sumProduksiTong = 0.0; // total kolom "Produksi (tong)"
                    $sumTargetProduksi = 0.0; // total kolom "Target Produksi"
                @endphp
                @forelse ($produks as $index => $produk)
                    @php
                        $patokan = (float) ($produk['patokan'] ?? 0);
                        $qtyTong = (float) ($inputs[$produk['id']] ?? 0); // nilai input user
                        $rowTarget = $patokan * $qtyTong; // target per-baris

                        $sumProduksiTong += $qtyTong; // akumulasi total
                        $sumTargetProduksi += $rowTarget;
                    @endphp

                    <tr
                        class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
                        <td class="px-3 py-2 align-middle">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 align-middle">{{ $produk['nama'] }}</td>
                        <td class="px-3 py-2 align-middle hidden sm:table-cell">{{ $produk['jenis'] }}</td>
                        <td class="px-3 py-2 align-middle text-right tabular-nums">{{ $produk['patokan'] }}</td>
                        <td class="px-3 py-2 align-middle">
                            <div class="flex items-center justify-center gap-2">
                                <input type="text" inputmode="numeric" pattern="[0-9]*[.,]?[0-9]*"
                                    wire:model.live="inputs.{{ $produk['id'] }}"
                                    class="w-20 border rounded text-right text-sm dark:bg-zinc-800 dark:border-zinc-600"
                                    {{ $readonly[$produk['id']] ?? false ? 'readonly' : '' }}
                                    oninput="this.value=this.value.replace(/[^0-9.,]/g,'')"
                                    x-ref="input{{ $index }}"
                                    x-on:keydown.enter.prevent="
                      const next={{ $index + 1 }};
                      if ($refs['input' + next]) $refs['input' + next].focus();
                    " />
                                <button type="button" wire:click="openTambahModal({{ $index }})"
                                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm shadow"
                                    aria-label="Tambah produksi">
                                    +
                                </button>
                            </div>
                        </td>
                        <td class="px-3 py-2 align-middle text-right font-semibold tabular-nums">
                            {{ (float) ($produk['patokan'] ?? 0) * (float) ($inputs[$produk['id']] ?? 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                            Tidak ada data
                        </td>
                    </tr>
                @endforelse
                {{-- TOTAL ROW --}}
                <tr class="bg-gray-100 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 font-semibold">
                    <td class="px-3 py-2"></td>
                    <td class="px-3 py-2">TOTAL</td>
                    <td class="px-3 py-2 hidden sm:table-cell"></td>
                    <td class="px-3 py-2 text-right"></td>
                    <td class="px-3 py-2 text-center tabular-nums">
                        {{ rtrim(rtrim(number_format($sumProduksiTong, 2, '.', ''), '0'), '.') }}
                    </td>
                    <td class="px-3 py-2 text-right tabular-nums">
                        {{ rtrim(rtrim(number_format($sumTargetProduksi, 2, '.', ''), '0'), '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Modal Tambahan --}}
    @if ($showTambahModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div
                class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-lg shadow-lg p-4 space-y-4 border border-gray-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold">Tambah Produksi</h3>

                {{-- Baris pilih produk & qty --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Pilih Produk</label>
                        <select wire:model="selectedProductId"
                            class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600">
                            <option value="">— pilih produk —</option>
                            @foreach ($mproducts as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedProductId')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="w-full sm:w-40">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Jumlah</label>
                        <input type="number" step="0.01" wire:model="jumlahTambahan"
                            wire:keydown.enter="stageTambahan" x-ref="inputTambahan"
                            class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600"
                            placeholder="Qty" />
                        @error('jumlahTambahan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <button type="button" wire:click="stageTambahan"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow">
                            Tambah
                        </button>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div>
                    <textarea wire:model="keteranganTambahan" rows="2"
                        class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                        placeholder="Masukkan keterangan (opsional)"></textarea>
                    @error('keteranganTambahan')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Draft Tambahan --}}
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <h4 class="text-sm font-semibold mb-2">Draft Tambahan</h4>

                    @if (!empty($stagedTambahan))
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Produk</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Target Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Keterangan</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stagedTambahan as $i => $row)
                                        <tr
                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row['nama'] }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row['qty'], 2) }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row['target_qty'], 2) }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row['keterangan'] ?: '-' }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                <button
                                                    class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
                                                    wire:click="removeStaged({{ $i }})">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-3">
                            <button type="button" wire:click="simpanTambahan"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow">
                                Simpan ke Database
                            </button>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada draft tambahan.</p>
                    @endif
                </div>

                {{-- Riwayat Tambahan --}}
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <h4 class="text-sm font-semibold mb-2">Riwayat Tambahan</h4>

                    @if (!empty($riwayatTambahan))
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">Tgl
                                        </th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Produk</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Target Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($riwayatTambahan as $row)
                                        <tr
                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row->created_at->format('d-m-Y H:i') }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row->product->nama ?? '-' }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row->qty_tambahan, 2) }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row->target_qty_tambahan, 2) }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row->keterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada riwayat tambahan.</p>
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showTambahModal', false)"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
