<div class="p-2 space-y-4 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">

    {{-- Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
             x-transition
             class="fixed top-4 left-1/2 transform -translate-x-1/2 w-72 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">✕</button>
            </div>
        </div>
    @endif

    {{-- Integrated Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-1">
      <div class="flex items-center gap-2 w-full md:w-1/2">
        <div class="relative w-full">
          <input wire:model.live="search" class="py-1 pl-8 pr-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari toko / area / wilayah..." />
          <span class="absolute left-2 top-1.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" /></svg>
          </span>
          @if($search)
          <button wire:click="$set('search','')" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600" title="Clear">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-2 justify-end w-full md:w-auto">
        <button wire:click="openModal"
          class="flex items-center gap-1 bg-blue-400 hover:bg-blue-500 text-white py-1 px-4 text-xs rounded-lg shadow transition">
          ➕ Tambah
        </button>
      </div>
    </div>

    {{-- HEADER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">TO</span>
                Master Toko
            </h2>
            <span class="text-[11px] text-gray-500">Kelola master data toko</span>
        </div>

        <div class="mt-3 mb-2">
            <div class="w-1/3">
                <input wire:model.live="search" class="py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari toko / area / wilayah..." />
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-lg ring-1 ring-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-indigo-500 to-emerald-400"></div>
      <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white">
          <tr class="text-left border-b">
            <th class="px-4 py-3 font-semibold text-gray-700">No</th>
            <th class="px-4 py-3 font-semibold text-gray-700">ID</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Wilayah</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Area</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Nama Toko</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Alamat</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Status</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Produksi</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($produks as $toko)
            <tr class="transform transition-all duration-150 hover:-translate-y-0.5 hover:shadow-sm">
              <td class="px-4 py-3">{{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}</td>
              <td class="px-4 py-3">{{ $toko->id }}</td>
              <td class="px-4 py-3 truncate">{{ $toko->area?->wilayah?->nama_wilayah ?? '-' }}</td>
              <td class="px-4 py-3 truncate">{{ $toko->area?->nama_area ?? '-' }}</td>
              <td class="px-4 py-3 font-medium truncate">{{ $toko->nmtoko }}</td>
              <td class="px-4 py-3 truncate">{{ $toko->alamat ?? '-' }}</td>
              <td class="px-4 py-3">
                @if((string)($toko->status ?? '1') === '1')
                  <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-green-50 text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15 5 11.586a1 1 0 011.414-1.414L8.414 12.172l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-gray-50 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 9l3-3 1 1-3 3 3 3-1 1-3-3-3 3-1-1 3-3-3-3 1-1 3 3z"/></svg>
                    Nonaktif
                  </span>
                @endif
              </td>
              <td class="px-4 py-3">
                @if((string)($toko->produksi_sendiri ?? '1') === '1')
                  <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-green-50 text-green-700">Ya</span>
                @else
                  <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs bg-gray-50 text-gray-600">Tidak</span>
                @endif
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button wire:click="edit({{ $toko->id }})" aria-label="Edit {{ $toko->id }}" title="Edit" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-amber-700 bg-amber-100 hover:bg-amber-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" /></svg>
                    Edit
                  </button>

                  <button x-data @click="$dispatch('swal:confirm', { id: {{ $toko->id }} })" aria-label="Hapus {{ $toko->id }}" title="Hapus" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-red-700 bg-red-100 hover:bg-red-200 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" /></svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-10">
                <div class="text-center text-gray-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3v4M8 3v4"/></svg>
                  <div class="text-lg font-medium mt-3">Belum ada toko</div>
                  <div class="text-xs mt-1">Tambah toko untuk mulai mengelola data</div>
                  <div class="mt-4">
                    <button wire:click="openModal" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Tambah Toko</button>
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
      </div>
    </div> 

    {{-- Pagination --}}
    <div class="pt-2 text-xs text-gray-500">
        {{ $produks->links() }}
    </div>

    {{-- Modal --}}
    @if ($modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-2">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4 space-y-3">

                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold">
                        {{ $produkId ? 'Edit Toko' : 'Tambah Toko' }}
                    </h2>
                    <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <form wire:submit.prevent="store" class="space-y-3">
                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Wilayah</label>
                        <select wire:model.live="wilayah_id"
                                class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('wilayah_id') border-red-500 @enderror">
                            <option value="">Pilih Wilayah</option>
                            @foreach($wilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                        @error('wilayah_id') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Area</label>
                        <select wire:model.defer="area_id"
                                class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('area_id') border-red-500 @enderror"
                                @disabled(!$wilayah_id)>
                            <option value="">{{ $wilayah_id ? 'Pilih Area' : 'Pilih Wilayah dulu' }}</option>
                            @foreach($areas as $a)
                                <option value="{{ $a['id'] }}">{{ $a['nama_area'] }}</option>
                            @endforeach
                        </select>
                        @error('area_id') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Nama Toko</label>
                        <input type="text" wire:model.defer="nama"
                               class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('nama') border-red-500 @enderror">
                        @error('nama') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs text-gray-500">API ID</label>
                        <input type="text" wire:model.defer="apiid"
                               class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('apiid') border-red-500 @enderror">
                        @error('apiid') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Alamat</label>
                        <input type="text" wire:model.defer="alamat"
                               class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('alamat') border-red-500 @enderror">
                        @error('alamat') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Produksi Sendiri</label>
                        <select wire:model.defer="produksi_sendiri"
                                class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('produksi_sendiri') border-red-500 @enderror">
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                        @error('produksi_sendiri') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-xs text-gray-500">Status</label>
                        <select wire:model.defer="status"
                                class="w-full border rounded-lg px-3 py-2 text-xs bg-white @error('status') border-red-500 @enderror">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        @error('status') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" wire:click="closeModal"
                                class="w-1/2 py-2 bg-gray-200 hover:bg-gray-300 text-xs rounded-lg">
                            Batal
                        </button>
                        <button type="submit"
                                class="w-1/2 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-lg">
                            Simpan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    {{-- SweetAlert confirm bridge (contoh, sesuaikan punyamu) --}}
    <script>
        document.addEventListener('swal:confirm', (e) => {
            const id = e.detail.id;
            // kalau kamu sudah punya mekanisme swal confirm sendiri, pakai itu.
            // ini contoh minimal:
            if (confirm('Yakin hapus data ini?')) {
                @this.call('delete', id);
            }
        });
    </script>
</div>
