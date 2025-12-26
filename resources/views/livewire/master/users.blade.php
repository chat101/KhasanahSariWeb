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

    {{-- Integrated Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-1">
        <div class="flex items-center gap-2 w-full md:w-1/2">
            <div class="relative w-full">
                <input wire:model.live="search" class="py-1 pl-8 pr-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari nama bahan..." />
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
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">US</span>
                Master Users
            </h2>
            <span class="text-[11px] text-gray-500">Kelola pengguna & role</span>
        </div>

        <div class="mt-3 mb-2">
            <div class="w-1/3">
                <input wire:model.live="search" class="form-control py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari nama bahan..." />
            </div>
        </div>
    </div>

    {{-- Tabel Produk --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left">
            <th class="px-4 py-3 font-semibold text-gray-700">No</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Nama</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Email</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Role</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Wilayah</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Area</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($users as $user)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
              <td class="px-4 py-3 truncate">{{ $user->name }}</td>
              <td class="px-4 py-3 truncate">{{ $user->email }}</td>
              <td class="px-4 py-3">{{ $user->role }}</td>
              <td class="px-4 py-3">{{ $user->wilayah_nama ?? '-' }}</td>
              <td class="px-4 py-3">{{ $user->area?->nama_area ?? '-' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button wire:click="edit({{ $user->id }})" aria-label="Edit {{ $user->id }}" title="Edit" class="p-2 rounded-md hover:bg-gray-50 text-gray-600"> 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>

                  <button onclick="confirmDelete({{ $user->id }})" aria-label="Hapus {{ $user->id }}" title="Hapus" class="p-2 rounded-md hover:bg-red-50 text-red-600"> 
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center py-6 text-gray-500">Tidak ada data</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="pt-2 text-xs text-gray-600">
        {{ $users->links() }}
    </div>
    {{-- Modal Tambah/Edit Supplier --}}
    <div x-data="{ showModal: @entangle('modal') }" x-show="showModal"
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
        <div @click.outside="showModal = false"
            class="w-full max-w-md p-5 bg-white rounded-lg shadow-lg border border-gray-300 text-sm">

            <h2 class="text-gray-800 text-lg font-semibold mb-4"
                x-text="'Form ' + (@this.userId ? 'Edit' : 'Tambah') + ' User'"></h2>

            {{-- Form --}}
            <div class="space-y-3">
                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-gray-600 mb-1">Nama</label>
                    <input type="text" id="nama" wire:model.defer="nama"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="">
                    @error('nama')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Nama 2 --}}
                <div>
                    <label for="email" class="block text-gray-600 mb-1">Email</label>
                    <input type="email" id="email" wire:model.defer="email"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="Masukkan email">
                    @error('email')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="block text-gray-600 mb-1">Role</label>
                    <select id="role" wire:model.live.change="role"
                    class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm">
                        <option value="">Pilih Role</option>
                        <option value="admin">Admin</option>
                        <option value="gudang">Gudang</option>
                        <option value="cream">Cream</option>
                        <option value="premixtoko">Premix Toko</option>
                        <option value="premixpabrik">Premix Pabrik</option>
                        <option value="finance">Finance</option>
                        <option value="kasir">Kasir</option>
                        <option value="wilayah">Wilayah</option>
                        <option value="area">Area</option>

                    </select>
                    @error('role')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
                @if (in_array(strtolower($role), ['kasir','personil','personil_toko']))
                <div>
                    <label class="block text-gray-600 mb-1">Toko</label>
                    <select wire:model.defer="toko_id"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg text-sm">
                        <option value="">- Pilih Toko -</option>
                        @foreach ($tokos as $t)
                            <option value="{{ $t->id }}">{{ $t->nmtoko }} ({{ $t->area?->nama_area ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('toko_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            @endif
                @if (strtolower($role) === 'wilayah')
                    <div>
                        <label class="block text-gray-600 mb-1">Wilayah</label>
                        <select wire:model.defer="wilayah_id"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg text-sm">
                            <option value="">- Pilih Wilayah -</option>
                            @foreach ($wilayahs as $w)
                                <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                            @endforeach
                        </select>
                        @error('wilayah_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
                @if (strtolower($role) === 'area')
                    <div>
                        <label class="block text-gray-600 mb-1">Area</label>
                        <select wire:model.defer="area_id"
                            class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg text-sm">
                            <option value="">- Pilih Area -</option>
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}">
                                    {{ $a->nama_area }} ({{ $a->wilayah?->nama_wilayah ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('area_id')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                @endif
            </div>
            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-gray-600 mb-1">Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="password" wire:model.defer="password"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.837-.68 1.613-1.192 2.3M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 .274-.837.68-1.613 1.192-2.3M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <div x-data="{ showPassword: false }">
                <label for="konfirmasi_password" class="block text-gray-600 mb-1">Konfirmasi Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="konfirmasi_password"
                        wire:model.defer="konfirmasi_password"
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:outline-none focus:ring focus:ring-blue-400 text-sm"
                        placeholder="">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-.274.837-.68 1.613-1.192 2.3M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7 .274-.837.68-1.613 1.192-2.3M15 12a3 3 0 11-6 0 3 3 0 016 0zm-6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
                @error('konfirmasipassword')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
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
<script>
    function confirmDelete(userId) {
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data user akan dihapus secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim event ke Livewire 3 (tanpa Alpine)
                window.Livewire.dispatch('delete-user', {
                    id: userId
                });
            }
        });
    }

    // Tampilkan notifikasi sukses setelah penghapusan
    window.addEventListener('user-deleted', () => {
        Swal.fire('Terhapus!', 'User berhasil dihapus.', 'success');
    });
</script>
