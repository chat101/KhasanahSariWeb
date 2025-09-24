<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25V6.75zm3 .75h12v9H6v-9z"/>
        </svg>
        <h2 class="text-base font-semibold">Supplier Masuk</h2>
      </div>

      {{-- Filters --}}
      <div class="w-full lg:w-auto flex flex-col md:flex-row items-stretch md:items-center gap-2">
        <div class="flex-1 md:flex-none">
          <input
            type="text"
            wire:model.live="search"
            placeholder="Cari Supplier…"
            class="w-full md:w-64 border rounded px-3 py-1.5 text-sm dark:bg-zinc-800 dark:border-zinc-600"
          >
        </div>

        <div class="flex items-center gap-2">
          <input
            type="date"
            wire:model="tanggalAwal"
            class="border rounded px-3 py-1.5 text-sm dark:bg-zinc-800 dark:border-zinc-600"
          >
          <span class="text-gray-500 dark:text-gray-400">s/d</span>
          <input
            type="date"
            wire:model="tanggalAkhir"
            class="border rounded px-3 py-1.5 text-sm dark:bg-zinc-800 dark:border-zinc-600"
          >
        </div>

        <button
          wire:click="exportExcel"
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-medium transition"
        >
          Export Excel
        </button>
      </div>
    </div>

    {{-- Toast Notif (opsional, tetap dipakai) --}}
    @if (session()->has('message'))
      <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed top-6 left-1/2 -translate-x-1/2 w-72 bg-green-600 text-white rounded-lg shadow-lg z-50 overflow-hidden"
      >
        <div class="flex items-start px-3 py-2 gap-2 text-xs">
          <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          <div class="flex-1">{{ session('message') }}</div>
          <button @click="show = false" class="hover:text-gray-200">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
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
        <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
          <tr>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left w-16">No</th>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left w-40">Tanggal</th>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left min-w-[220px]">Nama Supplier</th>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left">No PO</th>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left">No Faktur</th>
          </tr>
        </thead>

        <tbody class="text-indigo-700 dark:text-zinc-200">
          @forelse($listSuppMasuk as $suppmasuk)
            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5">
                {{ ($listSuppMasuk->currentPage() - 1) * $listSuppMasuk->perPage() + $loop->iteration }}
              </td>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5 tabular-nums">
                {{ $suppmasuk->tanggal }}
              </td>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5 font-medium text-white-900 dark:text-zinc-100">
                {{ $suppmasuk->supplier->nmsupp }}
              </td>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5">
                {{ $suppmasuk->no_po }}
              </td>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5">
                {{ $suppmasuk->no_faktur }}
              </td>
            </tr>
          @empty
            <tr>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="5">
                Tidak ada data.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-3">
      {{ $listSuppMasuk->links() }}
    </div>
  </div>
