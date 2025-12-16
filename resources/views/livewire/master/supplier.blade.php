<div class="p-2 space-y-4 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">

    {{-- Notifikasi --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-6 left-1/2 transform -translate-x-1/2 w-72 bg-green-500 text-white rounded-lg shadow z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span>{{ session('message') }}</span>
                <button @click="show = false" class="text-white hover:text-gray-200">
                    ✕
                </button>
            </div>
            <div class="h-1 bg-white/30">
                <div class="h-1 bg-white animate-toast-progress"></div>
            </div>
        </div>
    @endif

    {{-- Tombol Tambah --}}
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


    {{-- Tabel Produk --}}
    <div class="overflow-x-auto bg-gray-100 p-3 rounded-lg shadow">
        <table class="min-w-full text-left text-gray-800 border border-gray-300 bg-white rounded-lg">
            <thead class="bg-blue-500 text-white text-xs">
                <tr>
                    <th class="px-3 py-2">No</th>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Telepon</th>
                    <th class="px-3 py-2">Alamat</th>
                    <th class="px-3 py-2">Tempo</th>
                    <th class="px-3 py-2">Limit</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-300">
                @forelse($produks as $produk)
                    <tr class="hover:bg-gray-200">
                        <td class="px-3 py-2 text-xs">
                            {{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-3 py-2 text-xs">{{ $produk->nmsupp }}</td>
                        <td class="px-3 py-2 text-xs">{{ $produk->telpsupp }}</td>
                        <td class="px-3 py-2 text-xs">{{ $produk->suppalamat }}</td>
                        <td class="px-3 py-2 text-xs">
                            {{ $produk->tempo_hari ? 'Net ' . $produk->tempo_hari . ' hari' : 'Cash' }}
                        </td>
                        <td class="px-3 py-2 text-xs">
                            {{ $produk->max_hutang ? number_format($produk->max_hutang, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-3 py-2 text-xs">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px]
                            {{ $produk->is_aktif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $produk->is_aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 flex gap-1 text-xs">
                            <button wire:click="edit({{ $produk->id }})"
                                class="bg-yellow-400 hover:bg-yellow-500 px-2 py-1 rounded-lg text-white text-xs">✎</button>
                            <button wire:click="delete({{ $produk->id }})"
                                class="bg-red-500 hover:bg-red-600 px-2 py-1 rounded-lg text-white text-xs">🗑</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-3">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="pt-2 text-xs text-gray-600">
        {{ $produks->links() }}
    </div>
    {{-- Modal Tambah/Edit Supplier --}}
    <div x-data="{ showModal: @entangle('modal') }" x-show="showModal"
    class="fixed inset-0 flex items-center justify-center z-50 backdrop-blur-sm bg-white/30">


    <div
     {{-- @click.outside="showModal = false" --}}
        class="w-full max-w-2xl p-6 bg-white rounded-lg shadow-lg border border-gray-300 text-sm">

        <h2 class="text-gray-800 text-lg font-semibold mb-4"
            x-text="'Form ' + (@this.produkId ? 'Edit' : 'Tambah') + ' Supplier'"></h2>

            {{-- Form --}}
            <div class="space-y-3">
                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-gray-600 mb-1">Nama Supplier</label>
                    <input type="text" id="nama" wire:model.defer="nama"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="">
                    @error('nama')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nama 2 --}}
                <div>
                    <label for="nama2" class="block text-gray-600 mb-1">Telp Supplier</label>
                    <input type="text" id="telp" wire:model.defer="telp"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="">
                    @error('telp')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label for="alamat" class="block text-gray-600 mb-1">Alamat</label>
                    <textarea id="alamat" wire:model.defer="alamat" rows="3"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="Alamat lengkap supplier..."></textarea>
                    @error('alamat')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            {{-- Jatuh Tempo (Hari) --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="tempo_hari" class="block text-gray-600 mb-1">Jatuh Tempo (hari)</label>
                    <input type="number" id="tempo_hari" min="0" wire:model.defer="tempo_hari"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm">
                    @error('tempo_hari')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div x-data="{
                    display: '{{ $max_hutang ? number_format($max_hutang, 0, ',', '.') : '' }}',
                    updateValue(e) {
                        let raw = e.target.value.replace(/\./g, '');
                        this.display = new Intl.NumberFormat('id-ID').format(raw);
                        @this.set('max_hutang', raw);
                    }
                }"
            >
                <label for="max_hutang" class="block text-gray-600 mb-1">Limit Hutang (Rp)</label>

                <input type="text"
                    x-model="display"
                    @input="updateValue($event)"
                    class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800
                           rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                    placeholder="0">

                @error('max_hutang')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            </div>

            {{-- Contact & Email --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label for="contact_person" class="block text-gray-600 mb-1">Contact Person</label>
                    <input type="text" id="contact_person" wire:model.defer="contact_person"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm">
                    @error('contact_person')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-gray-600 mb-1">Email</label>
                    <input type="email" id="email" wire:model.defer="email"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm">
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Status --}}
            <div class="flex items-center gap-2 mt-1">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model.defer="is_aktif"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="ml-2 text-gray-700 text-sm">Supplier aktif</span>
                </label>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex justify-end gap-2 mt-5">
                <button wire:click="closeModal"
                    class="px-3 py-1 text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg text-sm">Batal</button>
                <button wire:click="store"
                    class="px-3 py-1 text-white bg-blue-500 hover:bg-blue-600 rounded-lg text-sm">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
