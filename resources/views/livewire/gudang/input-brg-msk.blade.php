<div class="bg-white shadow-md rounded-xl p-6 max-w-5xl mx-auto text-sm">
    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">📦 Form Barang Masuk</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-800 border border-green-300 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif


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
                <select wire:model="supplier"
                    class="w-full border border-gray-300 px-4 py-2 rounded-lg bg-blue-50 focus:ring focus:ring-blue-300 text-gray-900 shadow-sm">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($suppliers as $sup)
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
                <input type="text" wire:model.live="namabarang" x-ref="namabarang" placeholder="Nama Barang"
                    @class([
                        'px-4 py-2 rounded-lg focus:ring focus:ring-blue-300 text-gray-900 shadow-sm bg-blue-50',
                        'w-full border border-gray-300' => !$qtyError,
                        'w-full border-2 border-red-500' => $qtyError,
                    ])>
                @if (!empty($searchResults))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg">
                        @foreach ($searchResults as $item)
                            <li wire:click="selectBarang('{{ $item->id }}')"
                                class="px-3 py-2 hover:bg-blue-100 cursor-pointer text-sm text-black">
                                {{ $item->nmbarang }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <!-- Kolom Qty -->
            {{-- <input type="number" id="qtyInput" wire:model="qty" placeholder="Qty" @class([
                'px-2 py-1.5 rounded focus:ring focus:ring-blue-300 text-black bg-blue-50',
                'sm:w-24 md:w-32 border border-gray-300' => !$qtyError,
                'sm:w-24 md:w-32 border-2 border-red-500' => $qtyError,
            ])> --}}
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
                    x-on:blur="displayValue = formatInput(displayValue)" id="qtyInput" placeholder="Qty" type="text"
                    class="px-2 py-1.5 rounded focus:ring focus:ring-blue-300 text-black bg-blue-50 sm:w-24 md:w-32 border border-gray-300" />
            </div>

            <!-- Kolom Satuan -->
            <select wire:model.defer="satuan"
                class="border border-gray-300 px-2 py-1.5 rounded bg-blue-50 text-black sm:w-24 md:w-32">
                <option value=""></option>
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
                            <td class="border px-3 py-2">{{ $index + 1 }}</td> {{-- No urut --}}
                            <td class="border px-3 py-2">{{ $row['id'] }}</td>
                            <td class="border px-3 py-2">{{ $row['nmbarang'] }}</td>
                            {{-- <td class="border px-3 py-2">{{ $row['qty'] }}</td> --}}
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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Memuat data dari localStorage jika ada
        document.addEventListener('DOMContentLoaded', function() {
            const saved = localStorage.getItem('barangMasukRows');


            if (saved) {
                Livewire.dispatch('loadLocalRows', JSON.parse(saved));
            }
        });
        // Menyimpan data ke localStorage setiap kali ada perubahan
        Livewire.on('updateLocalStorage', (rows) => {
            localStorage.setItem('barangMasukRows', JSON.stringify(rows));
        });

        function loadRowsFromLocalStorage() {
            const saved = localStorage.getItem('barangMasukRows');
            if (saved) {
                Livewire.dispatch('loadLocalRows', JSON.parse(saved));
            }
        }

        // Saat DOM selesai dimuat (refresh hard)
        document.addEventListener('DOMContentLoaded', loadRowsFromLocalStorage);

        // Saat navigasi antar halaman Livewire (SPA behavior)
        window.addEventListener('livewire:navigated', loadRowsFromLocalStorage);


        // function formatRibuan(value) {
        //     value = value.replace(/\D/g, ''); // Hapus karakter selain angka
        //     return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Format ribuan
        // }
        // Menghapus data dari localStorage setelah data berhasil disimpan
        Livewire.on('clear-localstorage', () => {
            localStorage.removeItem('barangMasukRows');
            localStorage.removeItem('selectedSupplier');
            localStorage.removeItem('noFaktur');
            localStorage.removeItem('noPo');

        });

        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('fokus-ke-qty', function() {
                const qtyInput = document.getElementById('qtyInput');
                if (qtyInput) {
                    qtyInput.focus();
                }
            });
        });
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:error', ({
                title,
                text
            }) => {
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                });
            });
        });
        // Error handling dengan SweetAlert
        Livewire.on('swal:error', (title, text) => {
            setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: title || 'Terjadi Kesalahan',
                    text: text,
                    timer: 3000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-compact',
                        title: 'swal2-title-compact',
                        htmlContainer: 'swal2-text-compact'
                    }
                });
            }); // Delay 500ms
        });

        Livewire.on('swal:success', (title, text) => {
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: title || 'Berhasil!',
                    text: text,
                    timer: 2500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'swal2-compact',
                        title: 'swal2-title-compact',
                        htmlContainer: 'swal2-text-compact'
                    }
                });
            }, 100);
        });
    </script>
@endpush
