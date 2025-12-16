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

    {{-- Search + Add --}}
    <div class="flex items-center justify-between gap-2">
        <div class="relative w-1/2">
            <input wire:model.live="search"
                   class="py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white text-gray-700 text-xs placeholder-gray-400 w-full"
                   type="search" placeholder="Cari toko / area / wilayah..." />
            <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
                </svg>
            </span>
        </div>

        <button wire:click="openModal"
                class="flex items-center gap-1 bg-blue-400 hover:bg-blue-500 text-white py-1 px-4 text-xs rounded-lg shadow transition">
            ➕ Tambah
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white p-2 rounded-lg shadow-lg">
        <table class="min-w-full text-left text-gray-700 border border-gray-200 bg-gray-50 rounded-lg text-xs">
            <thead class="bg-blue-300 text-gray-900">
                <tr>
                    <th class="px-2 py-2 w-12">No</th>
                    <th class="px-2 py-2 w-16">ID</th>
                    <th class="px-2 py-2">Wilayah</th>
                    <th class="px-2 py-2">Area</th>
                    <th class="px-2 py-2">Nama Toko</th>
                    <th class="px-2 py-2">Alamat</th>
                    <th class="px-2 py-2 w-20">Status</th>
                    <th class="px-2 py-2 w-20">Produksi Sendiri</th>

                    <th class="px-2 py-2 w-28">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($produks as $toko)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-2 py-2">
                            {{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-2 py-2">{{ $toko->id }}</td>
                        <td class="px-2 py-2">
                            {{ $toko->area?->wilayah?->nama_wilayah ?? '-' }}
                        </td>
                        <td class="px-2 py-2">
                            {{ $toko->area?->nama_area ?? '-' }}
                        </td>
                        <td class="px-2 py-2 font-medium text-gray-900">
                            {{ $toko->nmtoko }}
                        </td>
                        <td class="px-2 py-2">
                            {{ $toko->alamat ?? '-' }}
                        </td>
                        <td class="px-2 py-2">
                            @if((string)($toko->status ?? '1') === '1')
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px]">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 text-[11px]">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-2 py-2">
                            @if((string)($toko->produksi_sendiri ?? '1') === '1')
                                <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px]">Ya</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 text-[11px]">Tidak</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 flex gap-1">
                            <button wire:click="edit({{ $toko->id }})"
                                    class="bg-yellow-300 hover:bg-yellow-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                ✎
                            </button>

                            <button
                                x-data
                                @click="$dispatch('swal:confirm', { id: {{ $toko->id }} })"
                                class="bg-red-300 hover:bg-red-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                🗑
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-500 py-3">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
