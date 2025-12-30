<div>
    <div class="p-2 space-y-4 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">

        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show"
                 class="fixed top-4 left-1/2 -translate-x-1/2 w-72 bg-green-200 text-green-900 rounded-lg shadow-lg z-50">
                <div class="flex items-center justify-between px-3 py-2">
                    <span class="text-xs font-medium">{{ session('message') }}</span>
                    <button @click="show=false">✕</button>
                </div>
            </div>
        @endif

        {{-- Header + Add Button --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
          <div class="flex-1">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-0 text-black">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">WL</span>
                        Master Wilayah
                    </h2>
                    <span class="text-[11px] text-gray-500">Kelola data wilayah</span>
                </div>
            </div>
          </div>
          <div class="flex items-center gap-2 justify-end w-full md:w-auto">
            <button wire:click="openModal"
                    class="flex items-center gap-1 bg-blue-400 hover:bg-blue-500 text-white py-1 px-4 text-xs rounded-lg shadow transition">
                ➕ Tambah
            </button>
          </div>
        </div>

        {{-- Search Box - Directly Above Table --}}
        <div class="flex items-center gap-2 mb-2">
            <div class="relative w-full md:w-1/3">
                <input wire:model.live="search" class="py-1 pl-8 pr-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari wilayah..." />
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

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-lg ring-1 ring-gray-100 overflow-hidden">
          <div class="h-1 bg-gradient-to-r from-indigo-500 to-emerald-400"></div>
          <div class="overflow-x-auto">
          <table class="min-w-full text-xs">
                <thead class="bg-white text-gray-700 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold w-16">ID</th>
                        <th class="px-4 py-3 text-left font-semibold">Nama Wilayah</th>
                        <th class="px-4 py-3 text-left font-semibold w-24">Status</th>
                        <th class="px-4 py-3 text-left font-semibold w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($wilayahs as $w)
                        <tr class="transform transition-all duration-150 hover:-translate-y-0.5 hover:shadow-sm">
                            <td class="px-4 py-3">{{ ($wilayahs->currentPage()-1)*$wilayahs->perPage() + $loop->iteration }}</td>
                            <td class="px-4 py-3">{{ $w->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $w->nama_wilayah }}</td>
                            <td class="px-4 py-3">
                                @if((string)($w->status ?? '1')==='1')
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-medium">Aktif</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-gray-200 text-gray-700 text-[11px] font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 flex gap-2">
                                <button wire:click="edit({{ $w->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors text-xs font-medium border border-amber-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                                    Edit
                                </button>
                                <button wire:click="delete({{ $w->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-50 text-red-700 hover:bg-red-100 transition-colors text-xs font-medium border border-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <p class="text-sm text-gray-500 font-medium">Tidak ada data wilayah</p>
                                    <p class="text-xs text-gray-400 mt-1">Mulai tambahkan wilayah baru untuk melihat daftar di sini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2 text-xs text-gray-500">
            {{ $wilayahs->links() }}
        </div>

        @if($modal)
            <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-2">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold">{{ $editId ? 'Edit Wilayah' : 'Tambah Wilayah' }}</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>

                    <form wire:submit.prevent="store" class="space-y-3">
                        <div>
                            <label class="block mb-1 text-xs text-gray-500">Nama Wilayah</label>
                            <input wire:model.defer="nama_wilayah" type="text"
                                   class="w-full border rounded-lg px-3 py-2 text-xs @error('nama_wilayah') border-red-500 @enderror">
                            @error('nama_wilayah') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-xs text-gray-500">Status</label>
                            <select wire:model.defer="status" class="w-full border rounded-lg px-3 py-2 text-xs">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="button" wire:click="closeModal"
                                    class="w-1/2 py-2 bg-gray-200 hover:bg-gray-300 text-xs rounded-lg">Batal</button>
                            <button type="submit"
                                    class="w-1/2 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

</div>
