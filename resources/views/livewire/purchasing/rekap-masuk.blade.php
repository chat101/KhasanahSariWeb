<div>
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

        {{-- Header --}}
        <div
            class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24"
                    fill="currentColor">
                    <path
                        d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
                </svg>
                <h2 class="text-base font-semibold">Daftar Transaksi Barang Masuk</h2>
            </div>

            {{-- Search --}}
            <div class="relative w-full sm:w-72">
                <input type="text" wire:model.live="search" placeholder="Cari Supplier..."
                    class="w-full pl-10 pr-3 py-2 rounded-md border border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:outline-none text-sm dark:bg-zinc-800 dark:border-zinc-600" />
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 4a7 7 0 11-1 0 7 7 0 011 0zm6.293 9.707a9 9 0 11-1.414 1.414l-3-3" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Toast --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed top-6 left-1/2 -translate-x-1/2 w-72 bg-green-500 text-white rounded-lg shadow-lg z-50 overflow-hidden">
                <div class="flex items-start px-3 py-2 gap-2">
                    <div class="pt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m1-4a9 9 0 11-6.219-8.562" />
                        </svg>
                    </div>
                    <div class="flex-1 text-xs">{{ session('message') }}</div>
                    <button @click="show = false" class="text-white/90 hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6m0 12L6 6" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 bg-white/30">
                    <div class="h-1 bg-white animate-toast-progress"></div>
                </div>
            </div>
        @endif

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-indigo-600 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-left w-12">No</th>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">No PO</th>
                        <th class="px-3 py-2 text-left">No Faktur</th>
                        <th class="px-3 py-2 text-left">Nama Supplier</th>
                        <th class="px-3 py-2 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-gray-700 dark:text-zinc-200">
                    @forelse($suppliers as $supplier)
                        <tr
                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">

                            {{-- No --}}
                            <td class="px-3 py-2 align-middle">{{ $loop->iteration }}</td>

                            {{-- Tanggal --}}
                            <td class="px-3 py-2 align-middle whitespace-nowrap">
                                {{ $supplier->gudang_masuk->tanggal ?? '-' }}
                            </td>

                            {{-- No PO --}}
                            <td class="px-3 py-2 align-middle">
                                {{ $supplier->gudang_masuk->no_po ?? '-' }}
                            </td>

                            {{-- No Faktur --}}
                            <td class="px-3 py-2 align-middle">
                                {{ $supplier->gudang_masuk->no_faktur ?? '-' }}
                            </td>

                            {{-- NAMA SUPPLIER (INI YANG DIMINTA) --}}
                            <td class="px-3 py-2 align-middle">
                                {{ $supplier->gudang_masuk->supplier->nmsupp ?? '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-2 align-middle text-center">
                                <button
                                type="button"
                                x-data
                                x-on:click="openFinanceEdit({{ $supplier->id }})"
                                class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs shadow"
                            >
                                Edit
                            </button>

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3">
            {{ $suppliers->links() }}
        </div>

        {{-- Modal Edit Transaksi --}}
    {{-- Modal Edit Transaksi --}}
@if ($showEditModal)
<div class="fixed inset-0 z-50 flex items-center justify-center">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm {{ $isClosing ? 'animate-fadeOut' : 'animate-fadeIn' }}"></div>

    {{-- Dialog --}}
    <div class="relative bg-white dark:bg-zinc-800 w-full max-w-4xl rounded-lg shadow-lg
                border border-gray-200 dark:border-zinc-700
                overflow-y-auto max-h-[85vh] p-4
                {{ $isClosing ? 'animate-zoomOut' : 'animate-zoomIn' }}">

                <div class="flex items-center justify-between mb-4
                border-b border-gray-200 dark:border-zinc-700 pb-3">

        <div class="flex items-center gap-2">
            <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900/40">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5 text-indigo-600 dark:text-indigo-300"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7h18M3 12h18M3 17h18" />
                </svg>
            </div>

            <div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-zinc-100">
                    Edit Transaksi Barang Masuk
                </h2>
                <p class="text-xs text-gray-500 dark:text-zinc-400">
                    Update data barang dan perhitungan transaksi masuk
                </p>
            </div>
        </div>

        <button wire:click="closeModal"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="w-5 h-5 text-gray-500 dark:text-gray-300"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

    </div>

        {{-- Header Form --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3 text-sm">
            <div>
                <label class="font-semibold">Tanggal</label>
                <input type="date" class="w-full px-2 py-1.5 rounded border dark:bg-zinc-800" wire:model.defer="tanggal" readonly>
            </div>
            <div>
                <label class="font-semibold">Nomor PO</label>
                <input type="text" class="w-full px-2 py-1.5 rounded border dark:bg-zinc-800" wire:model.defer="no_po">
            </div>
            <div>
                <label class="font-semibold">Nama Supplier</label>
                <select
                wire:model="supplier_id"
                class="w-full px-2 py-1.5 rounded border dark:bg-zinc-800 dark:border-zinc-600 text-sm">
                <option value="">-- Pilih Supplier --</option>
                @foreach($listSupplier as $sup)
                    <option value="{{ $sup->id }}">{{ $sup->nmsupp }}</option>
                @endforeach
            </select>

            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3 text-sm">
            <div>
                <label class="font-semibold">No Transaksi</label>
                <input type="text" class="w-full px-2 py-1.5 rounded border bg-gray-100 dark:bg-zinc-900/40" wire:model.defer="notrans" readonly>
            </div>
            <div>
                <label class="font-semibold">Nomor Faktur</label>
                <input type="text" class="w-full px-2 py-1.5 rounded border bg-gray-100 dark:bg-zinc-900/40" wire:model.defer="no_faktur" readonly>
            </div>
        </div>

        {{-- Table Barang --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs border border-gray-200 dark:border-zinc-700">
                <thead class="bg-indigo-600 text-white text-xs">
                    <tr>
                        <th class="px-2 py-1">No</th>
                        <th class="px-2 py-1">ID</th>
                        <th class="px-2 py-1">Nama Barang</th>
                        <th class="px-2 py-1 text-right">Qty</th>
                        <th class="px-2 py-1">Sat</th>
                        <th class="px-2 py-1">Harga</th>
                        <th class="px-2 py-1">Diskon</th>
                        <th class="px-2 py-1">PPN%</th>
                        <th class="px-2 py-1 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-gray-700 dark:text-zinc-200">

                    @foreach($data as $index => $item)
                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                        <td class="px-2 py-1">{{ $loop->iteration }}</td>
                        <td class="px-2 py-1">{{ $barang_id[$index] }}</td>
                        <td class="px-2 py-1">
                            <select
                                wire:model.live="barang_id.{{ $index }}"
                                class="px-2 py-1 w-40 rounded border dark:bg-zinc-800 dark:border-zinc-600 text-xs">

                                <option value="">-- pilih --</option>

                                @foreach($listBarang as $brg)
                                    <option value="{{ $brg->id }}">{{ $brg->nmbarang }}</option>
                                @endforeach

                            </select>
                        </td>


                        <td class="px-2 py-1 text-right">{{ number_format($item['qty'], 3, ',', '.') }}</td>

                        <td class="px-2 py-1">
                            <input type="text"
                                class="px-2 py-1 w-16 text-center rounded border bg-gray-100 dark:bg-zinc-900/40"
                                wire:model.defer="data.{{ $index }}.satuan"
                                readonly>
                        </td>

                        {{-- Harga --}}
                        <td class="px-2 py-1">
                            <input type="text"
                                x-data="moneyFormat()"
                                x-on:input="formatInput($event)"
                                wire:model.live="harga.{{ $index }}"
                                class="px-2 py-1 w-24 text-right rounded border bg-blue-50 dark:bg-zinc-800">
                        </td>

                        {{-- Diskon --}}
                        <td class="px-2 py-1">
                            <input type="text"
                                x-data="moneyFormat()"
                                x-on:input="formatInput($event)"
                                wire:model.live="diskon.{{ $index }}"
                                class="px-2 py-1 w-20 text-right rounded border bg-blue-50 dark:bg-zinc-800">
                        </td>

                        {{-- PPN --}}
                        <td class="px-2 py-1">
                            <input type="text"
                                x-data="moneyFormat()"
                                x-on:input="formatInput($event)"
                                wire:model.live="ppn.{{ $index }}"
                                class="px-2 py-1 w-16 text-right rounded border bg-blue-50 dark:bg-zinc-800">
                        </td>

                        {{-- Total --}}
                        <td class="px-2 py-1 text-right font-semibold">
                            {{ number_format($total[$index] ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        {{-- Grand Total --}}
        <div class="flex justify-end mt-3">
            <div class="w-full max-w-xs">
                <label class="text-sm font-semibold">Grand Total</label>
                <input type="text"
                       class="w-full px-3 py-1.5 rounded border bg-gray-100  text-gray-800 text-right font-bold text-lg"
                       value="Rp. {{ number_format($grandTotal, 2, ',', '.') }}" readonly>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-2 mt-4">
            <button type="button" wire:click="closeModal"
                class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded text-sm">
                Batal
            </button>
            <button type="button" wire:click="simpan"
                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">
                Simpan
            </button>
        </div>

    </div>
</div>
@endif

    </div>

    @push('scripts')
        <script>

            window.addEventListener('delay-hide-modal', () => {
                setTimeout(() => {
                    @this.set('showEditModal', false);
                    @this.set('isClosing', false);
                }, 300);
            });

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
                });
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
        <script>
            function moneyFormat() {
                return {
                    formatInput(e) {
                        let val = e.target.value.replace(/[^0-9,]/g, '');
                        let parts = val.split(',');
                        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                        e.target.value = parts.join(',');
                    }
                }
            }
            </script>
        <script>
            function openFinanceEdit(id) {
                Swal.fire({
                    width: 360, // lebih kecil & compact
                    padding: "1rem",
                    background: document.documentElement.classList.contains('dark')
                        ? '#27272a'   // dark zinc-800
                        : '#ffffff',  // light
                    color: document.documentElement.classList.contains('dark')
                        ? '#e4e4e7'   // zinc-200
                        : '#374151',  // gray-700

                    title: `
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/40
                                        text-indigo-600 dark:text-indigo-300 rounded-full mb-1">
                                <i class="fa-solid fa-user-shield text-xl"></i>
                            </div>
                            <span class="text-base font-semibold">Verifikasi Manager Finance</span>
                            <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
                                Masukkan kredensial untuk membuka transaksi
                            </p>
                        </div>
                    `,

                    html: `
                        <div class="flex flex-col gap-2 mt-3">
                            <input id="swal-email"
                                type="text"
                                class="swal2-input !py-1.5 !text-xs !rounded-md !border-gray-300
                                       dark:!bg-zinc-800 dark:!border-zinc-700 dark:!text-zinc-200"
                                placeholder="Email Manager Finance">

                            <input id="swal-password"
                                type="password"
                                class="swal2-input !py-1.5 !text-xs !rounded-md !border-gray-300
                                       dark:!bg-zinc-800 dark:!border-zinc-700 dark:!text-zinc-200"
                                placeholder="Password Manager Finance">
                        </div>
                    `,

                    focusConfirm: false,
                    showCancelButton: true,

                    confirmButtonText: `
                        <i class="fa-solid fa-lock-open mr-1"></i>
                        <span class="text-xs font-medium">Lanjutkan</span>
                    `,
                    cancelButtonText: `
                        <span class="text-xs font-medium">Batal</span>
                    `,

                    buttonsStyling: false,
                    customClass: {
                        popup: "rounded-lg shadow-md border border-gray-200 dark:border-zinc-700",
                        confirmButton:
                            "bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium " +
                            "hover:bg-indigo-700 transition",
                        cancelButton:
                            "bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 " +
                            "px-3 py-1.5 rounded-md text-xs font-medium hover:bg-gray-300 dark:hover:bg-zinc-600 transition mx-2",
                    },

                    preConfirm: () => {
                        const email = document.getElementById("swal-email").value;
                        const password = document.getElementById("swal-password").value;

                        if (!email || !password) {
                            Swal.showValidationMessage("Email & password wajib diisi");
                            return false;
                        }
                        return { email, password };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call("verifyFinanceManager", id, result.value.email, result.value.password);
                    }
                });
            }
            </script>

    @endpush
    {{-- The Master doesn't talk, he acts. --}}
</div>
