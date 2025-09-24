<div class="p-4 space-y-4 bg-blue-100 text-gray-800 text-sm rounded-2xl shadow-sm">

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-6 left-1/2 transform -translate-x-1/2 w-64 bg-green-500 text-white rounded-lg shadow-lg z-50 overflow-hidden">
            <div class="flex items-start px-3 py-2 space-x-2">
                <div class="pt-1">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m1-4a9 9 0 11-6.219-8.562" />
                    </svg>
                </div>
                <div class="flex-1 text-xs">
                    {{ session('message') }}
                </div>
                <button @click="show = false" class="text-white hover:text-gray-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="h-1 bg-white/30">
                <div class="h-1 bg-white animate-toast-progress"></div>
            </div>
        </div>
    @endif

    {{-- Header Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-2">
        {{-- Search Box --}}
        <div class="w-full md:w-1/3">
            <input type="text" wire:model.live="search" placeholder="Cari Supplier..."
                class="w-full px-3 py-1 rounded-md border border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:outline-none text-sm">
        </div>

        {{-- Date Range Filter --}}
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input type="date" wire:model="tanggalAwal"
                class="px-3 py-1 rounded-md border border-gray-300 shadow-sm text-sm focus:ring focus:ring-indigo-200 focus:outline-none">
            <span class="text-gray-500">s/d</span>
            <input type="date" wire:model="tanggalAkhir"
                class="px-3 py-1 rounded-md border border-gray-300 shadow-sm text-sm focus:ring focus:ring-indigo-200 focus:outline-none">

            {{-- Tombol Export --}}
            {{-- <button wire:click="exportExcel"
                class="px-4 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition">
                Export Excel
            </button> --}}
        </div>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-sm">
        <table class="min-w-full text-xs text-left text-gray-700 border border-gray-300 bg-white rounded-lg">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider">No</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider">Tanggal</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider">Nama Supplier</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider">No PO</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider">No Faktur</th>
                    <th class="px-3 py-2 font-semibold uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($gudangMasuks as $gudangMasuk)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-3 py-2">
                            {{ ($gudangMasuks->currentPage() - 1) * $gudangMasuks->perPage() + $loop->iteration }}</td>
                        <td class="px-3 py-2">{{ $gudangMasuk->tanggal }}</td>
                        <td class="px-3 py-2">{{ $gudangMasuk->supplier->nmsupp }}</td>
                        <td class="px-3 py-2">{{ $gudangMasuk->no_po }}</td>
                        <td class="px-3 py-2">{{ $gudangMasuk->no_faktur }}</td>
                        <td class="px-3 py-2 flex justify-between items-center text-xs">
                            <button wire:click="edit({{ $gudangMasuk->id }})"
                                class="bg-yellow-400 hover:bg-yellow-500 px-2 py-1 rounded-lg text-white text-xs">✎</button>
                            <button wire:click="delete({{ $gudangMasuk->id }})"
                                class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded-lg text-white text-xs ml-auto">🗑</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-3">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pt-3">
        {{ $gudangMasuks->links() }}
    </div>

    <div x-data="{ showModal: @entangle('modal') }" x-show="showModal"
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
        <div @click.outside="showModal = false"
            class="w-full max-w-4xl p-5 bg-white rounded-lg shadow-lg border border-gray-300 text-sm">

            <h2 class="text-gray-800 text-lg font-semibold mb-4"
                x-text="'Form ' + (@this.produkId ? 'Edit' ) + ' Barang Masuk'"></h2>

            {{-- Form Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kiri --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-800 font-medium mb-2">📅 Tanggal</label>
                        <input type="date" wire:model="tanggal"
                            class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50" />
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 font-medium mb-2">🧾 No Transaksi</label>
                        <input type="text" wire:model="notrans"
                            class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50"
                            readonly />
                        @error('notrans')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 font-medium mb-2">👤 Operator</label>
                        <input type="text" wire:model="userName"
                            class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50"
                            readonly />
                        @error('userName')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Kanan --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-800 font-medium mb-2">🧾 No. PO</label>
                        <input type="text" wire:model="no_po"
                            class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50" />
                        @error('no_po')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 font-medium mb-2">🧾 No. Faktur</label>
                        <input type="text" wire:model="no_faktur"
                            class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50" />
                        @error('no_faktur')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-gray-800 font-medium mb-2">🏢 Supplier</label>
                        <select wire:model="supplier_id"
                        class="w-full border border-gray-300 px-4 py-2 rounded-lg bg-blue-50 focus:ring focus:ring-blue-300 text-gray-900 shadow-sm">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach ($suppid as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->nmsupp }}</option>
                    @endforeach
                    </select>
                        @error('supplier')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <hr class="my-8 border-gray-300">

            {{-- Tambah Barang --}}
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">➕ Tambah Barang</h3>
                <div class="flex flex-wrap gap-4 items-center">
                    <input type="text" wire:model="barangid" placeholder="id" class="hidden">
                    <!-- Kolom ID Barang -->
                    <input type="text" wire:model="idbarang" placeholder="ID Barang"
                        class="border border-gray-300 px-4 py-2 rounded-lg bg-blue-50 text-gray-900 shadow-sm sm:w-28 md:w-36"
                        readonly>
                    <div class="relative flex-1">
                        <input type="text" wire:model.live="namabarang" x-ref="namabarang"
                            placeholder="Nama Barang" @class([
                                'px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50',
                                // 'w-full border border-gray-300' => !$qtyError,
                                // 'w-full border-2 border-red-500' => $qtyError,
                            ])>
                        @if (!empty($searchResults))
                            <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg">
                                {{-- @foreach ($searchResults as $item)
                                    <li wire:click="selectBarang('{{ $item->id }}')"
                                        class="px-3 py-2 hover:bg-blue-100 cursor-pointer text-sm text-black">
                                        {{ $item->nmbarang }}
                                    </li>
                                @endforeach --}}
                            </ul>
                        @endif
                    </div>

                    <div x-data="{
                        rawValue: @entangle('qty').live,
                        displayValue: '',

                        formatInput(value) {
                            // Hanya angka dan koma
                            value = value.replace(/[^0-9,]/g, '');

                            // Pisahkan bagian desimal
                            let [intPart, decPart] = value.split(',');

                            // Hapus titik ribuan lama
                            intPart = intPart.replace(/\./g, '');

                            // Format titik ribuan
                            let formatted = '';
                            for (let i = intPart.length - 1, j = 1; i >= 0; i--, j++) {
                                formatted = intPart[i] + formatted;
                                if (j % 3 === 0 && i !== 0) {
                                    formatted = '.' + formatted;
                                }
                            }

                            if (decPart !== undefined) {
                                return formatted + ',' + decPart.substring(0, 3); // max 3 desimal
                            }
                            return formatted;
                        },

                        updateRawValue() {
                            const cleaned = this.displayValue.replace(/\./g, '').replace(',', '.');
                            const parsed = parseFloat(cleaned);
                            // Hindari kirim NaN ke Livewire
                            if (!isNaN(parsed)) {
                                this.rawValue = parsed;
                            } else {
                                this.rawValue = null;
                            }
                        },

                        init() {
                            if (this.rawValue) {
                                let val = this.rawValue.toString().replace('.', ',');
                                this.displayValue = this.formatInput(val);
                            }
                            // Watch rawValue dari Livewire, reset displayValue jika kosong
                            this.$watch('rawValue', (val) => {
                                if (!val) this.displayValue = '';
                            });
                        }
                    }">
                        <input x-model="displayValue"
                            x-on:input.debounce.300ms="displayValue = formatInput(displayValue); updateRawValue()"
                            x-on:blur="displayValue = formatInput(displayValue)" id="qtyInput" placeholder="Qty"
                            type="text"
                            class="px-2 py-1.5 rounded focus:ring focus:ring-blue-300 text-black bg-blue-50 sm:w-24 md:w-32 border border-gray-300" />
                    </div>

                    <!-- Kolom Satuan -->
                    <select wire:model.defer="satuan"
                        class="border border-gray-300 px-2 py-1.5 rounded bg-blue-50 text-black sm:w-24 md:w-32">
                        <option value="" disabled selected>satuan</option>
                        <option value="Dus">Dus</option>
                        <option value="Karung">Karung</option>
                        <option value="Pack">Pack</option>
                        <option value="Pail">Pail</option>
                        <option value="Pcs">Pcs</option>
                        <option value="Kilo">Kilo</option>
                        <option value="Lusin">Lusin</option>
                        <option value="Ikat">Ikat</option>
                        <option value="Roll">Roll</option>
                        <option value="Sak">Sak</option>
                        <option value="Jerigen">Jerigen</option>
                    </select>
                    @error('satuan')
                        <span class="text-red-400 text-xs">{{ $message }}</span>
                    @enderror

                    <!-- Kolom Gramasi -->
                    <input type="text" wire:model="gramasi" placeholder="Gramasi"
                        class="border border-gray-300 px-2 py-1.5 rounded bg-blue-50 text-black sm:w-24 md:w-32">

                    <!-- Tombol Tambah -->
                    <button type="button" wire:click="addRow"
                        class="bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700 transition sm:w-full md:w-auto">
                        ➕ Tambah
                    </button>
                </div>
            </div>

            {{-- Tabel List Barang --}}
            @if (!empty($rows))
                <div class="mt-6 border-t pt-4 text-black">
                    <h3 class="text-base font-semibold text-gray-700 mb-2">📋 List Barang</h3>
                    <table class="min-w-full border text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border px-3 py-2">No</th>
                                <th class="border px-3 py-2 ">ID</th>
                                <th class="border px-3 py-2">Nama Barang</th>
                                <th class="border px-3 py-2">Qty</th>
                                <th class="border px-3 py-2">Satuan</th>
                                <th class="border px-3 py-2">Gramasi</th>
                                <th class="border px-3 py-2">#</th>
                            </tr>
                        </thead>
                        <tbody>
                           @foreach ($rows as $index => $row)
<tr>
    <td class="border px-3 py-2">{{ $index + 1 }}</td>
    <td class="border px-3 py-2">{{ $row['id'] }}</td>
    <td class="border px-3 py-2">{{ $row['nmbarang'] }}</td>
    <td class="border px-3 py-2">{{ number_format($row['qty'], 3, ',', '.') }}</td>
    <td class="border px-3 py-2">{{ $row['satuan'] }}</td>
    <td class="border px-3 py-2">{{ $row['gramasi'] }}</td>
    <td class="border px-3 py-2 text-center">
        <button type="button" wire:click="removeRow({{ $index }})"
            class="text-red-600 hover:underline">🗑</button>
    </td>
</tr>
@endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Tombol Simpan --}}
            <form wire:submit.prevent="save" class="space-y-8">
                <!-- Konten Form -->
                {{-- Tombol Simpan --}}
                <div class="text-right pt-6 border-t">
                    <button type="submit"
                        class="bg-green-600 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-green-700 transition">
                        💾 Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
