<div class="space-y-4">

    {{-- CARD LIST TRANSAKSI --}}
    <div
        class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-200/70 dark:border-zinc-800/80 overflow-hidden">

        {{-- HEADER --}}
        <div
            class="px-4 py-3 border-b border-gray-200 dark:border-zinc-800
                   flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            {{-- TITLE + INFO --}}
            <div class="flex items-start gap-3">
                <div
                    class="flex items-center justify-center w-9 h-9 rounded-full
                           bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 text-xs font-semibold">
                    BM
                </div>

                <div class="space-y-0.5">
                    <h2 class="text-sm md:text-base font-semibold text-gray-800 dark:text-zinc-100">
                        Daftar Transaksi Barang Masuk (Gudang)
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-zinc-400">
                        Transaksi Gudang Masuk yang siap diproses menjadi Purchasing.
                    </p>
                </div>
            </div>

            {{-- SEARCH + INFO JUMLAH --}}
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">

                <div class="text-[11px] text-gray-500 dark:text-zinc-400 order-2 sm:order-1">
                    Halaman
                    <span class="font-semibold text-gray-700 dark:text-zinc-200">
                        {{ $suppliers->currentPage() }}
                    </span>
                    · Total
                    <span class="font-semibold text-gray-700 dark:text-zinc-200">
                        {{ $suppliers->total() }}
                    </span>
                    transaksi.
                </div>

                <div class="relative w-full sm:w-72 order-1 sm:order-2">
                    <input type="text"
                           wire:model.live="search"
                           placeholder="Cari supplier, no faktur atau no PO..."
                           class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm rounded-lg
                                  border border-gray-300/80 dark:border-zinc-700
                                  bg-white dark:bg-zinc-900
                                  shadow-sm focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400
                                  text-gray-800 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500" />
                    <span
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 4a7 7 0 11-1 0 7 7 0 011 0zm6.293 9.707a9 9 0 11-1.414 1.414l-3-3" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        {{-- TOAST --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 class="fixed top-6 left-1/2 -translate-x-1/2 w-72 bg-emerald-500 text-white rounded-lg shadow-lg z-50 overflow-hidden">
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
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 18L18 6m0 12L6 6" />
                        </svg>
                    </button>
                </div>
                <div class="h-1 bg-white/30">
                    <div class="h-1 bg-white animate-toast-progress"></div>
                </div>
            </div>
        @endif

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs sm:text-sm">
                <thead class="bg-indigo-600 text-white">
                    <tr class="text-[11px] sm:text-xs uppercase tracking-wide">
                        <th class="px-3 py-2 text-left w-10">No</th>
                        <th class="px-3 py-2 text-left">Tanggal</th>
                        <th class="px-3 py-2 text-left">No PO</th>
                        <th class="px-3 py-2 text-left">No Faktur</th>
                        <th class="px-3 py-2 text-left">Nama Supplier</th>
                        <th class="px-3 py-2 text-center w-28">Status</th>
                        <th class="px-3 py-2 text-center w-28">Aksi</th>
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-gray-100 dark:divide-zinc-800 text-gray-700 dark:text-zinc-200">
                    @forelse($suppliers as $supplier)
                        <tr
                            class="odd:bg-white even:bg-gray-50/80
                                   dark:odd:bg-zinc-900 dark:even:bg-zinc-900/70
                                   hover:bg-indigo-50/80 dark:hover:bg-zinc-800/80
                                   transition-colors">

                            {{-- No --}}
                            <td class="px-3 py-2 align-middle text-[11px] sm:text-xs text-gray-500 dark:text-zinc-400">
                                {{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-3 py-2 align-middle whitespace-nowrap">
                                {{ optional($supplier->gudang_masuk)->tanggal
                                    ? \Carbon\Carbon::parse($supplier->gudang_masuk->tanggal)->format('d-m-Y')
                                    : '-' }}
                            </td>

                            {{-- No PO --}}
                            <td class="px-3 py-2 align-middle">
                                {{ $supplier->gudang_masuk->no_po ?? '-' }}
                            </td>

                            {{-- No Faktur --}}
                            <td class="px-3 py-2 align-middle">
                                <span class="font-medium text-gray-800 dark:text-zinc-100">
                                    {{ $supplier->gudang_masuk->no_faktur ?? '-' }}
                                </span>
                            </td>

                            {{-- Nama Supplier --}}
                            <td class="px-3 py-2 align-middle">
                                <div class="flex flex-col">
                                    <span class="text-[13px] font-semibold">
                                        {{ $supplier->gudang_masuk->supplier->nmsupp ?? '-' }}
                                    </span>
                                    <span class="text-[11px] text-gray-400 dark:text-zinc-500">
                                        {{ $supplier->gudang_masuk->supplier->telpsupp ?? '' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Status Gudang (di list ini harusnya semua belum diproses) --}}
                            <td class="px-3 py-2 align-middle text-center">
                                <span
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                           bg-amber-50 text-amber-700 border border-amber-200
                                           dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/60">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Belum diproses
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-2 align-middle text-center">
                                <button
                                    type="button"
                                    x-data
                                    x-on:click="openFinanceEdit({{ $supplier->id }})"
                                    class="inline-flex items-center gap-1
                                           px-3 py-1.5 rounded-md text-[11px] font-medium
                                           bg-indigo-600 hover:bg-indigo-700
                                           text-white shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5"
                                         viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M4 3a2 2 0 012-2h4.586A2 2 0 0112 1.586L15.414 5A2 2 0 0116 6.414V17a2 2 0 01-2 2H6a2 2 0 01-2-2V3z" />
                                    </svg>
                                    <span>Proses</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-zinc-400 text-xs">
                                Tidak ada transaksi gudang yang menunggu diproses.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div
            class="px-4 py-3 border-t border-gray-100 dark:border-zinc-800
                   flex items-center justify-between text-[11px] text-gray-500 dark:text-zinc-400">
            <div>
                Menampilkan
                <span class="font-semibold text-gray-700 dark:text-zinc-200">
                    {{ $suppliers->firstItem() ?? 0 }}–{{ $suppliers->lastItem() ?? 0 }}
                </span>
                dari
                <span class="font-semibold text-gray-700 dark:text-zinc-200">
                    {{ $suppliers->total() }}
                </span>
                transaksi.
            </div>
            <div class="text-xs">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL EDIT TRANSAKSI (PUNYAMU, TETAP) --}}
    @if ($showEditModal)
        {{-- blok modal yang sudah kamu punya, tidak diubah --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- Overlay --}}
            <div
                class="absolute inset-0 bg-black/40 backdrop-blur-sm {{ $isClosing ? 'animate-fadeOut' : 'animate-fadeIn' }}">
            </div>

            {{-- Dialog --}}
            <div
                class="relative bg-white dark:bg-zinc-800 w-full max-w-4xl rounded-lg shadow-lg
                       border border-gray-200 dark:border-zinc-700
                       overflow-y-auto max-h-[85vh] p-4
                       {{ $isClosing ? 'animate-zoomOut' : 'animate-zoomIn' }}">

                {{-- (isi modalmu persis seperti sebelumnya) --}}
                {{-- ... COPY dari kode modal yang sudah ada ... --}}
                {{-- aku tidak ulang di sini supaya singkat, tapi struktur & class boleh kamu pertahankan --}}
                @includeIf('livewire.purchasing._modal-edit-barang-masuk')
                {{-- kalau tidak pakai include, tinggal tempel isi modal lama di sini --}}
            </div>
        </div>
    @endif

</div>

@push('scripts')
    {{-- script delay-hide-modal & swal tetap sama --}}
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
                width: 360,
                padding: "1rem",
                background: document.documentElement.classList.contains('dark') ? '#27272a' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#e4e4e7' : '#374151',
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
                        "bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-medium hover:bg-indigo-700 transition",
                    cancelButton:
                        "bg-gray-200 dark:bg-zinc-700 text-gray-700 dark:text-zinc-300 px-3 py-1.5 rounded-md text-xs font-medium hover:bg-gray-300 dark:hover:bg-zinc-600 transition mx-2",
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
