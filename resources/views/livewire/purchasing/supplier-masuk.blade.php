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
                        Transaksi gudang masuk yang siap diproses menjadi Purchasing.
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
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Cari supplier, no faktur atau no PO..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg text-xs sm:text-sm
                               border border-gray-300/80 dark:border-zinc-700
                               bg-white dark:bg-zinc-900
                               shadow-sm focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400
                               text-gray-800 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500"
                    />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500">
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
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 3000)"
                 x-show="show"
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

                            {{-- No (ikut pagination) --}}
                            <td class="px-3 py-2 align-middle text-[11px] sm:text-xs text-gray-500 dark:text-zinc-400">
                                {{ ($suppliers->currentPage() - 1) * $suppliers->perPage() + $loop->iteration }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-3 py-2 align-middle whitespace-nowrap">
                                {{ $supplier->tanggal ? \Carbon\Carbon::parse($supplier->tanggal)->format('d-m-Y') : '-' }}
                            </td>

                            {{-- No PO --}}
                            <td class="px-3 py-2 align-middle">
                                {{ $supplier->no_po ?? '-' }}
                            </td>

                            {{-- No Faktur --}}
                            <td class="px-3 py-2 align-middle">
                                <span class="font-medium text-gray-800 dark:text-zinc-100">
                                    {{ $supplier->no_faktur ?? '-' }}
                                </span>
                            </td>

                            {{-- Nama Supplier --}}
                            <td class="px-3 py-2 align-middle">
                                <div class="flex flex-col">
                                    <span class="text-[13px] font-semibold">
                                        {{ $supplier->supplier->nmsupp ?? '-' }}
                                    </span>
                                    @if (!empty($supplier->supplier->telpsupp))
                                        <span class="text-[11px] text-gray-400 dark:text-zinc-500">
                                            {{ $supplier->supplier->telpsupp }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status (opsional, pakai kolom status kalau ada) --}}
                            <td class="px-3 py-2 align-middle text-center">
                                @if (isset($supplier->status) && $supplier->status == 0)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                               bg-amber-50 text-amber-700 border border-amber-200
                                               dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-700/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Belum diproses
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium
                                               bg-emerald-50 text-emerald-700 border border-emerald-200
                                               dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-700/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Selesai
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-2 align-middle text-center">
                                <button
                                    wire:click="edit({{ $supplier->id }})"
                                    type="button"
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

    {{-- MODAL EDIT TRANSAKSI --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm {{ $isClosing ? 'animate-fadeOut' : 'animate-fadeIn' }}"></div>

            {{-- Dialog --}}
            <div
                class="relative bg-white dark:bg-zinc-800 w-full max-w-6xl rounded-xl shadow-lg
                       border border-gray-200 dark:border-zinc-700
                       overflow-y-auto max-h-[90vh] p-6
                       {{ $isClosing ? 'animate-zoomOut' : 'animate-zoomIn' }}">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-zinc-700 pb-3">
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
                                Update harga, diskon dan perhitungan total sesuai faktur supplier.
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-zinc-300">Tanggal</label>
                        <input type="date"
                               class="mt-1 w-full px-3 py-2 rounded border border-gray-300 dark:bg-zinc-800 dark:border-zinc-600"
                               wire:model.defer="tanggal" readonly>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-zinc-300">Nomor PO</label>
                        <input type="text"
                               class="mt-1 w-full px-3 py-2 rounded border border-gray-300 dark:bg-zinc-800 dark:border-zinc-600"
                               wire:model.defer="no_po">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-zinc-300">Nama Supplier</label>
                        <input type="text"
                               class="mt-1 w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600"
                               wire:model.defer="supplier" readonly>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 text-sm">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-zinc-300">No Transaksi</label>
                        <input type="text"
                               class="mt-1 w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600"
                               wire:model.defer="notrans" readonly>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-zinc-300">Nomor Faktur</label>
                        <input type="text"
                               class="mt-1 w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600"
                               wire:model.defer="no_faktur" readonly>
                    </div>
                </div>

                {{-- Table Barang --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs border border-gray-200 dark:border-zinc-700">
                        <thead class="bg-indigo-600 text-white">
                            <tr>
                                <th class="p-2 text-left w-10">No</th>
                                <th class="p-2 text-left">ID Barang</th>
                                <th class="p-2 text-left">Nama Barang</th>
                                <th class="p-2 text-right">Qty</th>
                                <th class="p-2 text-left">Satuan</th>
                                <th class="p-2 text-left">Harga</th>
                                <th class="p-2 text-left">Diskon</th>
                                <th class="p-2 text-left">PPn %</th>
                                <th class="p-2 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody wire:key="table-barang-masuk"
                               class="divide-y divide-gray-100 dark:divide-zinc-700">
                            @foreach($data as $index => $item)
                                <tr wire:key="baris-{{ $index }}"
                                    class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                    <td class="p-2">{{ $loop->iteration }}</td>
                                    <td class="p-2">{{ $item['barang']['id'] }}</td>
                                    <td class="p-2">{{ $item['barang']['nmbarang'] }}</td>
                                    <td class="p-2 text-right tabular-nums">
                                        {{ number_format($item['qty'], 3, ',', '.') }}
                                    </td>
                                    <td class="p-2">{{ $item['satuan'] }}</td>

                                    {{-- Harga (Alpine + entangle) --}}
                                    <td class="p-2">
                                        <div
                                            x-data="{
                                                rawValue: $wire.entangle('harga.{{ $index }}').live,
                                                displayValue: '',
                                                formatInput(value) {
                                                    value = value.replace(/[^0-9,]/g, '');
                                                    let [intPart, decPart] = value.split(',');
                                                    intPart = intPart.replace(/\./g, '');
                                                    let formatted = '';
                                                    for (let i = intPart.length - 1, j = 1; i >= 0; i--, j++) {
                                                        formatted = intPart[i] + formatted;
                                                        if (j % 3 === 0 && i !== 0) {
                                                            formatted = '.' + formatted;
                                                        }
                                                    }
                                                    if (decPart !== undefined) {
                                                        return formatted + ',' + decPart.substring(0, 2);
                                                    }
                                                    return formatted;
                                                },
                                                updateRawValue() {
                                                    const cleaned = this.displayValue.replace(/\./g, '').replace(',', '.');
                                                    const parsed  = parseFloat(cleaned);
                                                    this.rawValue = isNaN(parsed) ? null : parsed;
                                                },
                                                init() {
                                                    if (this.rawValue) {
                                                        let v = this.rawValue.toString().replace('.', ',');
                                                        this.displayValue = this.formatInput(v);
                                                    }
                                                    this.$watch('rawValue', (val) => {
                                                        if (val === null || val === '' || val === 0) {
                                                            this.displayValue = '';
                                                        } else {
                                                            let v = val.toString().replace('.', ',');
                                                            this.displayValue = this.formatInput(v);
                                                        }
                                                    });
                                                }
                                            }"
                                        >
                                            <input
                                                x-model="displayValue"
                                                x-on:input="
                                                    displayValue = formatInput(displayValue);
                                                    updateRawValue();
                                                "
                                                x-on:blur="displayValue = formatInput(displayValue)"
                                                placeholder="Harga"
                                                type="text"
                                                class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600
                                                       focus:ring focus:ring-blue-300 text-right sm:w-24 md:w-32
                                                       bg-blue-50 dark:bg-zinc-800 text-gray-800 dark:text-zinc-100"
                                            />
                                        </div>
                                    </td>

                                    {{-- Diskon (Alpine + entangle) --}}
                                    <td class="p-2">
                                        <div
                                            x-data="{
                                                rawValue: $wire.entangle('diskon.{{ $index }}').live,
                                                displayValue: '',
                                                formatInput(value) {
                                                    value = value.replace(/[^0-9,]/g, '');
                                                    let [intPart, decPart] = value.split(',');
                                                    intPart = intPart.replace(/\./g, '');
                                                    let formatted = '';
                                                    for (let i = intPart.length - 1, j = 1; i >= 0; i--, j++) {
                                                        formatted = intPart[i] + formatted;
                                                        if (j % 3 === 0 && i !== 0) {
                                                            formatted = '.' + formatted;
                                                        }
                                                    }
                                                    if (decPart !== undefined) {
                                                        return formatted + ',' + decPart.substring(0, 2);
                                                    }
                                                    return formatted;
                                                },
                                                updateRawValue() {
                                                    const cleaned = this.displayValue.replace(/\./g, '').replace(',', '.');
                                                    const parsed  = parseFloat(cleaned);
                                                    this.rawValue = isNaN(parsed) ? null : parsed;
                                                },
                                                init() {
                                                    if (this.rawValue) {
                                                        let v = this.rawValue.toString().replace('.', ',');
                                                        this.displayValue = this.formatInput(v);
                                                    }
                                                    this.$watch('rawValue', (val) => {
                                                        if (val === null || val === '' || val === 0) {
                                                            this.displayValue = '';
                                                        } else {
                                                            let v = val.toString().replace('.', ',');
                                                            this.displayValue = this.formatInput(v);
                                                        }
                                                    });
                                                }
                                            }"
                                        >
                                            <input
                                                x-model="displayValue"
                                                x-on:input="
                                                    displayValue = formatInput(displayValue);
                                                    updateRawValue();
                                                "
                                                x-on:blur="displayValue = formatInput(displayValue)"
                                                placeholder="Diskon"
                                                type="text"
                                                class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600
                                                       focus:ring focus:ring-blue-300 text-right sm:w-24 md:w-32
                                                       bg-blue-50 dark:bg-zinc-800 text-gray-800 dark:text-zinc-100"
                                            />
                                        </div>
                                    </td>

                                    {{-- PPN --}}
                                    <td class="p-2">
                                        <input type="number" wire:model.live="ppn.{{ $index }}"
                                               class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600
                                                      focus:ring focus:ring-blue-300 text-right sm:w-24 md:w-32
                                                      bg-blue-50 dark:bg-zinc-800 text-gray-800 dark:text-zinc-100" />
                                    </td>

                                    {{-- Total --}}
                                    <td class="p-2 text-right tabular-nums">
                                        {{ number_format($total[$index] ?? 0, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Grand Total --}}
                <div class="flex justify-end mt-4">
                    <div class="w-full max-w-sm">
                        <label class="text-xs font-semibold text-gray-700 dark:text-zinc-200">Grand Total</label>
                        <input
                            type="text"
                            class="mt-1 w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600 text-right font-bold text-lg"
                            value="Rp. {{ number_format($grandTotal, 2, ',', '.') }}"
                            readonly
                        >
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded text-sm">
                        Batal
                    </button>
                    <button type="button" wire:click="simpan"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
  <script>
      // animasi tutup modal: dengar event browser dari $this->dispatch('delay-hide-modal')
      window.addEventListener('delay-hide-modal', () => {
          setTimeout(() => {
              @this.set('showEditModal', false);
              @this.set('isClosing', false);
          }, 300); // sinkron dengan durasi animasi
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
@endpush
