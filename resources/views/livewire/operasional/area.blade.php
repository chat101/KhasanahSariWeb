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

        <div class="flex items-center justify-between gap-2">
            <div></div>

            <button wire:click="openModal"
                    class="flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white py-1 px-4 text-xs rounded-lg shadow">
                ➕ Tambah
            </button>
        </div>

        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold">AR</div>
                    <div>
                        <h2 class="text-sm font-semibold">Master Area</h2>
                        <span class="text-[11px] text-gray-600">Kelola data area</span>
                    </div>
                </div>
            </div>

            <div class="mt-3 mb-2 flex gap-3">
                <div class="w-1/3">
                    <input wire:model.live="search" class="py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari area / wilayah..." />
                </div>

                <div class="w-1/4">
                    <select wire:model.live="filterWilayah" class="py-1 px-3 border border-gray-300 rounded-lg bg-white text-xs w-full">
                        <option value="">Semua Wilayah</option>
                        @foreach($wilayahs as $w)
                            <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white p-2 rounded-lg shadow">
            <table class="min-w-full text-left border border-gray-200 bg-gray-50 rounded-lg text-xs">
                <thead class="bg-blue-100 text-gray-900">
                    <tr>
                        <th class="px-2 py-2 w-12">No</th>
                        <th class="px-2 py-2 w-16">ID</th>
                        <th class="px-2 py-2">Wilayah</th>
                        <th class="px-2 py-2">Nama Area</th>
                        <th class="px-2 py-2 w-24">Status</th>
                        <th class="px-2 py-2 w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($areas as $a)
                        <tr class="hover:bg-gray-100">
                            <td class="px-2 py-2">{{ ($areas->currentPage()-1)*$areas->perPage() + $loop->iteration }}</td>
                            <td class="px-2 py-2">{{ $a->id }}</td>
                            <td class="px-2 py-2">{{ $a->wilayah?->nama_wilayah ?? '-' }}</td>
                            <td class="px-2 py-2 font-medium">{{ $a->nama_area }}</td>
                            <td class="px-2 py-2">
                                @if((string)($a->status ?? '1')==='1')
                                    <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px]">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 text-[11px]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 flex gap-1">
                                <button wire:click="edit({{ $a->id }})"
                                        class="bg-yellow-300 hover:bg-yellow-400 px-2 py-1 rounded-lg text-xs shadow">✎</button>
                                <button wire:click="delete({{ $a->id }})"
                                        class="bg-red-300 hover:bg-red-400 px-2 py-1 rounded-lg text-xs shadow">🗑</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-gray-500">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2 text-xs text-gray-500">
            {{ $areas->links() }}
        </div>

        @if($modal)
            <div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 p-2">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold">{{ $editId ? 'Edit Area' : 'Tambah Area' }}</h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
                    </div>

                    <form wire:submit.prevent="store" class="space-y-3">
                        <div>
                            <label class="block mb-1 text-xs text-gray-500">Wilayah</label>
                            <select wire:model.defer="wilayah_id"
                                    class="w-full border rounded-lg px-3 py-2 text-xs @error('wilayah_id') border-red-500 @enderror">
                                <option value="">Pilih Wilayah</option>
                                @foreach($wilayahs as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                                @endforeach
                            </select>
                            @error('wilayah_id') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block mb-1 text-xs text-gray-500">Nama Area</label>
                            <input wire:model.defer="nama_area" type="text"
                                   class="w-full border rounded-lg px-3 py-2 text-xs @error('nama_area') border-red-500 @enderror">
                            @error('nama_area') <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div> @enderror
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
      {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
</div>
