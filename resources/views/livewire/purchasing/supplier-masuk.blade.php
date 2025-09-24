<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z"/>
        </svg>
        <h2 class="text-base font-semibold">Daftar Transaksi Barang Masuk</h2>
      </div>

      {{-- Search --}}
      <div class="relative w-full sm:w-72">
        <input
          type="text"
          wire:model.live="search"
          placeholder="Cari Supplier..."
          class="w-full pl-10 pr-3 py-2 rounded-md border border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:outline-none text-sm dark:bg-zinc-800 dark:border-zinc-600"
        />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 4a7 7 0 11-1 0 7 7 0 011 0zm6.293 9.707a9 9 0 11-1.414 1.414l-3-3" />
          </svg>
        </span>
      </div>
    </div>

    {{-- Toast --}}
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
           class="fixed top-6 left-1/2 -translate-x-1/2 w-72 bg-green-500 text-white rounded-lg shadow-lg z-50 overflow-hidden">
        <div class="flex items-start px-3 py-2 gap-2">
          <div class="pt-0.5">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m1-4a9 9 0 11-6.219-8.562"/>
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
            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
              <td class="px-3 py-2 align-middle">{{ $loop->iteration }}</td>
              <td class="px-3 py-2 align-middle whitespace-nowrap">{{ $supplier->tanggal }}</td>
              <td class="px-3 py-2 align-middle">{{ $supplier->no_po }}</td>
              <td class="px-3 py-2 align-middle">{{ $supplier->no_faktur }}</td>
              <td class="px-3 py-2 align-middle">{{ $supplier->supplier->nmsupp }}</td>
              <td class="px-3 py-2 align-middle text-center">
                <button
                  wire:click="edit({{ $supplier->id }})"
                  class="inline-flex items-center gap-1 bg-amber-500 hover:bg-amber-600 text-white px-3 py-1 rounded text-xs shadow">
                  Proses
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
    @if($showEditModal)
      <div class="fixed inset-0 z-50 flex items-center justify-center">
        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm {{ $isClosing ? 'animate-fadeOut' : 'animate-fadeIn' }}"></div>

        {{-- Dialog --}}
        <div class="relative bg-white dark:bg-zinc-800 w-full max-w-6xl rounded-lg shadow-lg border border-gray-200 dark:border-zinc-700 overflow-y-auto max-h-[90vh] p-6 {{ $isClosing ? 'animate-zoomOut' : 'animate-zoomIn' }}">
          <h2 class="text-lg font-bold text-gray-800 dark:text-zinc-100 mb-4">Edit Transaksi Barang Masuk</h2>

          {{-- Header Form --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-200">Tanggal</label>
              <input type="date" class="w-full px-3 py-2 rounded border border-gray-300 dark:bg-zinc-800 dark:border-zinc-600" wire:model.defer="tanggal" readonly>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-200">Nomor PO</label>
              <input type="text" class="w-full px-3 py-2 rounded border border-gray-300 dark:bg-zinc-800 dark:border-zinc-600" wire:model.defer="no_po">
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-200">Nama Supplier</label>
              <input type="text" class="w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600" wire:model.defer="supplier" readonly>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-200">No Transaksi</label>
              <input type="text" class="w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600" wire:model.defer="notrans" readonly>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-2 00">Nomor Faktur</label>
              <input type="text" class="w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600" wire:model.defer="no_faktur" readonly>
            </div>
          </div>

          {{-- Table Barang --}}
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left border border-gray-200 dark:border-zinc-700">
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
              <tbody wire:key="table-barang-masuk" class="divide-y divide-gray-100 dark:divide-zinc-700">
                @foreach($data as $index => $item)
                  <tr wire:key="baris-{{ $index }}" class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                    <td class="p-2">{{ $loop->iteration }}</td>
                    <td class="p-2">{{ $item['barang']['id'] }}</td>
                    <td class="p-2">{{ $item['barang']['nmbarang'] }}</td>
                    <td class="p-2 text-right tabular-nums">
                      {{ number_format($item['qty'], 3, ',', '.') }}
                    </td>
                    <td class="p-2">{{ $item['satuan'] }}</td>

                    {{-- Harga dengan format (tetap pakai Alpine & entangle yang sudah ada) --}}
                    <td class="p-2">
                      <div x-data="{
                          rawValue: $wire.entangle('harga.' + {{ $index }}),
                          displayValue: '',
                          formatInput(value) {
                            value = value.replace(/[^0-9,]/g, '');
                            let [intPart, decPart] = value.split(',');
                            intPart = intPart.replace(/\./g, '');
                            let formatted = '';
                            for (let i = intPart.length - 1, j = 1; i >= 0; i--, j++) {
                              formatted = intPart[i] + formatted;
                              if (j % 3 === 0 && i !== 0) formatted = '.' + formatted;
                            }
                            if (decPart !== undefined) return formatted + ',' + decPart.substring(0, 2);
                            return formatted;
                          },
                          updateRawValue() {
                            const cleaned = this.displayValue.replace(/\./g, '').replace(',', '.');
                            const parsed = parseFloat(cleaned);
                            this.rawValue = isNaN(parsed) ? null : parsed;
                          },
                          init() {
                            if (this.rawValue) {
                              let val = this.rawValue.toString().replace('.', ',');
                              this.displayValue = this.formatInput(val);
                            }
                            this.$watch('rawValue', (val) => { if (!val) this.displayValue = '' });
                          }
                        }">
                        <input
                          x-model="displayValue"
                          x-on:input.debounce.300ms="displayValue = formatInput(displayValue); updateRawValue()"
                          x-on:blur="displayValue = formatInput(displayValue)"
                          placeholder="Harga" type="text"
                          wire:model.live="harga.{{ $index }}"
                          class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-blue-300 text-black sm:w-24 md:w-32 bg-blue-50 dark:bg-zinc-800"
                        />
                      </div>
                    </td>

                    <td class="p-2">
                      <input type="number" wire:model.live="diskon.{{ $index }}"
                             class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-blue-300 text-black sm:w-24 md:w-32 bg-blue-50 dark:bg-zinc-800" />
                    </td>
                    <td class="p-2">
                      <input type="number" wire:model.live="ppn.{{ $index }}"
                             class="px-2 py-1.5 rounded border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-blue-300 text-black sm:w-24 md:w-32 bg-blue-50 dark:bg-zinc-800" />
                    </td>

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
              <label class="text-sm font-semibold text-gray-700 dark:text-zinc-200">Grand Total</label>
              <input
                type="text"
                class="w-full px-3 py-2 rounded border border-gray-300 bg-gray-100 dark:bg-zinc-900/40 dark:border-zinc-600 text-right font-bold text-lg"
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
  @endpush
