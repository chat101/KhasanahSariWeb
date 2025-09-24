<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z"/>
        </svg>
        <h2 class="text-base font-semibold">Daftar Perintah Produksi</h2>
      </div>

      {{-- Search Box --}}
      <div class="relative w-full sm:w-80">
        <input
          wire:model.live="search"
          type="search"
          placeholder="Cari perintah produksi..."
          class="w-full py-2 pl-10 pr-3 rounded-md border border-gray-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-gray-700 dark:text-zinc-200 placeholder-gray-400 text-sm focus:ring focus:ring-indigo-200 focus:outline-none"
        />
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
          </svg>
        </span>
      </div>
    </div>

    {{-- Notification --}}
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
           class="fixed top-4 left-1/2 -translate-x-1/2 w-11/12 sm:w-64 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
        <div class="flex items-center justify-between px-3 py-2">
          <span class="text-xs font-medium">{{ session('message') }}</span>
          <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">✕</button>
        </div>
      </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
          <tr>
            <th class="px-3 py-2 text-left w-14 border-b border-gray-200 dark:border-zinc-700">No</th>
            <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700 hidden">ID</th>
            <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Tanggal</th>
            <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">ID Produksi</th>
            <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700 hidden sm:table-cell">Perekam</th>
            <th class="px-3 py-2 text-center border-b border-gray-200 dark:border-zinc-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-gray-700 dark:text-zinc-200">
          @forelse($perintahProduksi as $perintah)
            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
              <td class="px-3 py-2 align-middle">
                {{ $loop->iteration }}
              </td>
              <td class="px-3 py-2 align-middle hidden">
                {{ $perintah->id }}
              </td>
              <td class="px-3 py-2 align-middle whitespace-nowrap">
                {{ $perintah->tanggal_perintah }}
              </td>
              <td class="px-3 py-2 align-middle">
                {{ $perintah->no_perintah_produksi }}
              </td>
              <td class="px-3 py-2 align-middle hidden sm:table-cell">
                {{ $perintah->user->name ?? '-' }}
              </td>
              <td class="px-3 py-2 align-middle text-center">
                <a
                  href="{{ route('selesaijob', ['perintah_id' => $perintah->id]) }}"
                  class="inline-flex items-center gap-1 bg-yellow-300 hover:bg-yellow-400 text-gray-800 text-xs px-2 py-1 rounded-md shadow-sm transition"
                >
                  ✎ Input Jam
                </a>
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

    {{-- Pagination (biarkan seperti semula) --}}
    <div class="px-4 py-3 text-xs text-gray-500">
      {{-- {{ $perintahProduksi->links() }} --}}
    </div>
  </div>
