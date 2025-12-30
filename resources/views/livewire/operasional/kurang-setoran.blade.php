<div class="p-1 space-y-2 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">

    {{-- Success Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
             x-transition
             class="fixed top-4 left-1/2 transform -translate-x-1/2 w-72 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-1">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">✕</button>
            </div>
        </div>
    @endif

    {{-- Error Notification --}}
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
             x-transition
             class="fixed top-4 left-1/2 transform -translate-x-1/2 w-72 bg-red-200 text-red-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-1">
                <span class="text-xs font-medium">{{ session('error') }}</span>
                <button @click="show = false" class="text-red-800 hover:text-red-600 text-sm">✕</button>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-2">
        <div class="flex items-center justify-between gap-1">
            <h2 class="text-xs font-semibold flex items-center gap-1">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-red-100 text-red-600 text-[10px] font-bold">KS</span>
                Kurang Setoran
            </h2>
            <span class="text-[10px] text-gray-400">Input nominal toko</span>
        </div>
    </div>

    {{-- Search + Date --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div class="relative w-full sm:w-72">
        <input wire:model.live="search"
             class="py-1 pl-7 pr-6 border border-gray-300 rounded text-xs w-full focus:ring-1 focus:ring-blue-300 focus:border-blue-400"
             type="search" placeholder="Cari toko..." />
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-2 top-1.5 h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" /></svg>
        @if($search)
        <button wire:click="$set('search','')" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
        @endif
      </div>
      <div class="flex items-center gap-2 text-xs text-gray-700">
        <span class="font-semibold">Tanggal</span>
        <input wire:model.live="tanggal"
             type="date"
             max="{{ \Carbon\Carbon::now()->toDateString() }}"
             class="border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-300 focus:border-blue-400">
      </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
      <div class="h-0.5 bg-gradient-to-r from-indigo-500 to-emerald-400"></div>
      <div class="overflow-x-auto">
      <table class="min-w-full text-xs">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-2 py-1.5 font-semibold text-gray-700 text-left">No</th>
            <th class="px-2 py-1.5 font-semibold text-gray-700 text-left">Nama Toko</th>
            <th class="px-2 py-1.5 font-semibold text-gray-700 text-left">Nominal</th>
            <th class="px-2 py-1.5 font-semibold text-gray-700 text-left">Ket</th>
            <th class="px-2 py-1.5 font-semibold text-gray-700 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($tokos as $toko)
            <tr class="hover:bg-gray-50">
              <td class="px-2 py-1 text-gray-600">{{ $loop->iteration }}</td>
              <td class="px-2 py-1 font-medium truncate text-gray-900">{{ $toko->nmtoko }}</td>
              <td class="px-2 py-1">
                <input wire:model.live="nominalByToko.{{ $toko->id }}"
                       @keydown.enter="$wire.saveNominal({{ $toko->id }}); document.getElementById('input-{{ $loop->index + 1 }}')?.focus()"
                       id="input-{{ $loop->index }}"
                       type="text"
                       inputmode="numeric"
                       class="w-28 border border-gray-300 rounded px-1.5 py-0.5 text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400 focus:outline-none"
                       placeholder="Rp 0"
                       x-data="{ formatInput(e) { let val = e.target.value.replace(/\D/g, ''); e.target.value = val ? new Intl.NumberFormat('id-ID').format(val) : ''; } }"
                       @input="formatInput($event)">
              </td>
              <td class="px-2 py-1">
                <input wire:model.live="keteranganByToko.{{ $toko->id }}"
                       @keydown.enter="$wire.saveKeterangan({{ $toko->id }})"
                       type="text"
                       class="w-48 border border-gray-300 rounded px-1.5 py-0.5 text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400 focus:outline-none"
                       placeholder="Keterangan">
              </td>
              <td class="px-2 py-1">
                <div class="flex items-center justify-center gap-1">
                  <button @click="alert('Edit feature coming soon')" aria-label="Edit" title="Edit"
                          class="inline-flex items-center justify-center h-5 w-5 rounded bg-amber-100 hover:bg-amber-200 text-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                  </button>

                  <button @click="alert('Delete feature coming soon')" aria-label="Hapus" title="Hapus"
                          class="inline-flex items-center justify-center h-5 w-5 rounded bg-red-100 hover:bg-red-200 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" /></svg>
                  </button>

                  @if(isset($existingByToko[$toko->id]))
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-semibold">
                      Tersimpan
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-6">
                <div class="text-center text-gray-400 text-xs">
                  Belum ada data kurang setoran. Mulai input nominal di atas.
                </div>
              </td>
            </tr>
          @endforelse
          
          {{-- TOTAL ROW --}}
          <tr class="bg-gray-100 font-semibold border-t-2 border-gray-300">
            <td colspan="2" class="px-2 py-1.5 text-gray-700">TOTAL</td>
            <td class="px-2 py-1.5">
              <span class="text-red-600">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
            </td>
            <td colspan="2" class="px-2 py-1.5"></td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    {{-- Save Button --}}
    <div class="flex justify-end mt-2 gap-2">
      <button wire:click="clearInputs"
              class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-medium rounded-lg border border-gray-300">
        Clear
      </button>
      <button wire:click="saveAll"
              wire:loading.attr="disabled"
              wire:target="saveAll"
              class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-xs font-semibold rounded-lg shadow transition">
        <span wire:loading.remove wire:target="saveAll">💾 Simpan Semua</span>
        <span wire:loading wire:target="saveAll">Menyimpan...</span>
      </button>
    </div>

</div>
