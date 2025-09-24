<div class="p-2 space-y-4 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">
    {{-- Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 left-1/2 transform -translate-x-1/2 w-64 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">
                    ✕
                </button>
            </div>
        </div>
    @endif

    {{-- Search Box --}}
    <div class="flex items-center justify-between gap-2">
        <div class="relative w-1/2">
            <input wire:model.live="search"
                class="form-control py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white text-gray-700 text-xs placeholder-gray-400 transition-all duration-300 ease-in-out w-full"
                type="search" placeholder="Cari nama bahan..." />
            <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 4a7 7 0 111-0 7 7 0 01-1 0zm6.293 9.707a9 9 0 111.414-1.414 6 6 0 01-.708-.707l-3-3a9 9 0 011.414 1.414z" />
                </svg>
            </span>
        </div>
        <button wire:click="openModal"
            class="flex items-center gap-1 bg-blue-400 hover:bg-blue-500 text-white py-1 px-4 text-xs rounded-lg shadow transition">
            ➕ Tambah
        </button>
    </div>

    {{-- Product Table --}}
    <div class="overflow-x-auto bg-white p-2 rounded-lg shadow-lg">
        <table class="min-w-full text-left text-gray-600 border border-gray-300 bg-gray-50 rounded-lg text-xs">
            <thead class="bg-blue-300 text-gray-800">
                <tr>
                    <th class="px-2 py-2">No</th>
                    <th class="px-2 py-2">Id</th>
                    <th class="px-2 py-2">Id Barang</th>
                    <th class="px-2 py-2">Nama</th>
                    <th class="px-2 py-2">Jenis</th>
                    <th class="px-2 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                {{-- @forelse($produks as $produk)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-2 py-2">
                            {{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-2 py-2">{{ $produk->id }}</td>
                        <td class="px-2 py-2">{{ $produk->barang_id }}</td>
                        <td class="px-2 py-2">{{ $produk->nmbarang }}</td>
                        <td class="px-2 py-2">{{ $produk->jenis }}</td>
                        <td class="px-2 py-2 flex gap-1">
                            <button wire:click="edit({{ $produk->id }})"
                                class="bg-yellow-300 hover:bg-yellow-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                ✎
                            </button>
                            <button wire:click="editHO({{ $produk->id }})"
                                class="bg-green-300 hover:bg-green-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                + HO
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-2">Tidak ada data</td>
                    </tr>
                @endforelse --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pt-2 text-xs text-gray-500">
        {{ $produks->links() }}
    </div>

    {{-- Modal --}}
    {{-- @if ($mode === 'editBahan' && $modal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-2">
            <div
                class="bg-gradient-to-tl from-blue-200 to-blue-300 text-gray-800 rounded-lg shadow-lg w-full max-w-sm p-4 space-y-2">
                <h2 class="text-sm font-semibold text-center">
                    {{ $produkId ? 'Edit Data' : 'Tambah Data' }}
                </h2>
                <form wire:submit.prevent="store" class="space-y-2">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-xs">Kode Barang</label>
                            <input type="text" wire:model.defer="kode"
                                class="w-full p-1 rounded-lg text-xs border @error('kode') border-red-500 @enderror">
                            @error('kode')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-xs">Nama Barang</label>
                            <input type="text" wire:model.defer="nama"
                                class="w-full p-1 rounded-lg text-xs border @error('nama') border-red-500 @enderror">
                            @error('nama')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block mb-1 text-xs">Jenis</label>
                            <select wire:model.defer="jenis"
                                class="w-full p-1 rounded-lg text-xs border bg-gradient-to-tl from-blue-200 to-blue-300 text-gray-800 @error('jenis') border-red-500 @enderror appearance-none focus:bg-blue-300">
                                <option value="">Pilih Jenis</option>
                                <option value="bahan baku">Bahan Baku</option>
                                <option value="bahan jadi">Bahan Jadi</option>
                                <option value="barang jadi">Barang Jadi</option>
                                <option value="paketan">Paketan</option>
                            </select>
                            @error('jenis')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm">Harga</label>
                            <input type="number" wire:model.defer="harga"
                                class="w-full p-2 rounded-lg text-sm border @error('harga') border-red-500 @enderror">
                            @error('harga')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm">Satuan 1</label>
                            <input type="number" wire:model.defer="sat1"
                                class="w-full p-2 rounded-lg text-sm border @error('sat1') border-red-500 @enderror">
                            @error('sat1')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block mb-1 text-sm">Satuan 2</label>
                            <input type="number" wire:model.defer="sat2"
                                class="w-full p-2 rounded-lg text-sm border @error('sat2') border-red-500 @enderror">
                            @error('sat2')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block mb-1 text-sm">Keterangan</label>
                            <input type="text" wire:model.defer="keterangan"
                                class="w-full p-2 rounded-lg text-sm border @error('keterangan') border-red-500 @enderror">
                            @error('keterangan')
                                <span class="text-red-400 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-between gap-m pt-4">
                        <button type="button" wire:click="closeModal"
                            class="w-full py-2 bg-gray-300 hover:bg-gray-400 text-sm rounded-lg shadow transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="w-full py-2 bg-blue-400 hover:bg-blue-500 text-sm rounded-lg shadow transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif --}}
</div>
