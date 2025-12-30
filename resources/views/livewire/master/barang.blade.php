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

    {{-- Header + Add Button --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-2">
      <div class="flex-1">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-0 text-black">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">BR</span>
                    Master Barang
                </h2>
                <span class="text-[11px] text-gray-500">Kelola master barang</span>
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
            <input wire:model.live="search" class="py-1 pl-8 pr-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari nama barang..." />
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

    {{-- Product Table --}}
    <div class="bg-white rounded-xl shadow-lg ring-1 ring-gray-100 overflow-hidden">
      <div class="h-1 bg-gradient-to-r from-indigo-500 to-emerald-400"></div>
      <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-white">
          <tr class="text-left border-b">
            <th class="px-4 py-3 font-semibold text-gray-700">No</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Id</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Id Barang</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Nama</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Jenis</th>
            <th class="px-4 py-3 font-semibold text-gray-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($produks as $produk)
            <tr class="transform transition-all duration-150 hover:-translate-y-0.5 hover:shadow-sm">
              <td class="px-4 py-3">{{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}</td>
              <td class="px-4 py-3">{{ $produk->id }}</td>
              <td class="px-4 py-3">{{ $produk->barang_id }}</td>
              <td class="px-4 py-3 truncate">{{ $produk->nmbarang }}</td>
              <td class="px-4 py-3">{{ $produk->jenis }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button wire:click="edit({{ $produk->id }})" aria-label="Edit {{ $produk->id }}" title="Edit" class="p-2 rounded-md hover:bg-gray-50 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </button>
                  <div class="ml-auto">
                    <button wire:click="editHO({{ $produk->id }})" class="p-2 rounded-md hover:bg-gray-50 text-green-600" title="Tambah HO">+</button>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-6 py-10">
                <div class="text-center text-gray-500">
                  <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3v4M8 3v4"/></svg>
                  <div class="text-lg font-medium mt-3">Belum ada barang</div>
                  <div class="text-xs mt-1">Tambah barang untuk mulai mengelola data</div>
                  <div class="mt-4">
                    <button wire:click="openModal" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Tambah Barang</button>
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

      @if ($mode === 'editBahan')
      @if ($modal)
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-3">
              <div
                  class="bg-gradient-to-tl from-purple-600 to-purple-800 text-white rounded-md shadow w-full max-w-md p-4 space-y-3">
                  <h2 class="text-lg font-semibold text-center">
                      {{ $produkId ? 'Edit Data' : 'Tambah Data' }}
                  </h2>
                  <form wire:submit.prevent="store" class="space-y-3">
                      <div class="grid grid-cols-2 gap-2">
                          <div>
                              <label class="block mb-1">Kode Barang</label>
                              <input type="text" wire:model.defer="kode"
                                  class="w-full p-2 rounded text-xs border @error('kode') border-red-500 @enderror">
                              @error('kode')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                          <div>
                              <label class="block mb-1">Nama Barang</label>
                              <input type="text" wire:model.defer="nama"
                                  class="w-full p-2 rounded text-xs border @error('nama') border-red-500 @enderror">
                              @error('nama')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                          <div>
                              <label class="block mb-1">Jenis</label>
                              <select wire:model.defer="jenis"
                                  class="w-full p-2 rounded text-xs border bg-gradient-to-tl from-purple-600 to-purple-800 text-white @error('jenis') border-red-500 @enderror appearance-none focus:bg-purple-700">
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
                              <label class="block mb-1">Harga</label>
                              <input type="number" wire:model.defer="harga"
                                  class="w-full p-2 rounded text-xs border @error('harga') border-red-500 @enderror">
                              @error('harga')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                      </div>
                      <div class="grid grid-cols-2 gap-2">
                          <div>
                              <label class="block mb-1">Satuan 1</label>
                              <input type="number" wire:model.defer="sat1"
                                  class="w-full p-2 rounded text-xs border @error('sat1') border-red-500 @enderror">
                              @error('sat1')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                          <div>
                              <label class="block mb-1">Satuan 2</label>
                              <input type="number" wire:model.defer="sat2"
                                  class="w-full p-2 rounded text-xs border @error('sat2') border-red-500 @enderror">
                              @error('sat2')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                          <div class="col-span-2">
                              <label class="block mb-1">Keterangan</label>
                              <input type="text" wire:model.defer="keterangan"
                                  class="w-full p-2 rounded text-xs border @error('keterangan') border-red-500 @enderror">
                              @error('keterangan')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                      </div>
                      <div class="flex justify-between gap-2 pt-2">
                          <button type="button" wire:click="closeModal"
                              class="w-full py-1 bg-gray-600 hover:bg-gray-700 text-xs rounded">Batal</button>
                          <button type="submit"
                              class="w-full py-1 bg-blue-600 hover:bg-blue-700 text-xs rounded">Simpan</button>
                      </div>
                  </form>
              </div>
          </div>
      @endif
  @endif
  @if ($mode === 'editHO')
      @if ($modal)
          <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-3">
              <div
                  class="bg-gradient-to-tl from-purple-600 to-purple-800 text-white rounded-md shadow w-full max-w-md p-4 space-y-3">
                  <h2 class="text-lg font-semibold text-center">
                      Tambah ke HO
                  </h2>
                  <form wire:submit.prevent="storeHO" class="space-y-3">
                      <div class="grid grid-cols-2 gap-2 pt-2">
                          <div>
                              <label class="block mb-1">ID</label>
                              <input type="text" wire:model.defer="barang_id"
                                  class="w-full p-2 rounded text-xs border @error('barang_id') border-red-500 @enderror"
                                  readonly>
                              @error('barang_id')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>

                          <div>
                              <label class="block mb-1">ID Barang</label>
                              <input type="text" wire:model.defer="kode"
                                  class="w-full p-2 rounded text-xs border @error('kode') border-red-500 @enderror">
                              @error('kode')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                          <div>
                              <label class="block mb-1">Satuan</label>
                              <select wire:model.defer="satuan"
                                  class="w-full p-2 rounded text-xs border bg-gradient-to-tl from-purple-600 to-purple-800 text-white @error('jenis') border-red-500 @enderror appearance-none focus:bg-purple-700">
                                  <option value="">Pilih Satuan</option>
                                  <option value="Dus">Dus</option>
                                  <option value="Karung">Karung</option>
                                  <option value="Pack">Pack</option>
                                  <option value="Pail">Pail</option>
                                  <option value="Pcs">Pcs</option>
                                  <option value="Kilo">Kilo</option>
                                  <option value="Lusin">Lusin</option>
                                  <option value="Ikat">Ikat</option>
                                  <option value="Roll">Roll</option>
                                  <option value="Sak">Sak</option>
                                  <option value="Jerigen">Jerigen</option>
                              </select>
                              @error('satuan')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                          <div>
                              <label class="block mb-1">Gramasi</label>
                              <input type="text" wire:model.defer="gramasi"
                                  class="w-full p-2 rounded text-xs border @error('gramasi') border-red-500 @enderror">
                              @error('gramasi')
                                  <span class="text-red-400 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                      </div>
                      <div class="flex justify-between gap-2 pt-2">
                          <button type="button" wire:click="closeModal"
                              class="w-full py-1 bg-gray-600 hover:bg-gray-700 text-xs rounded">Batal</button>
                          <button type="submit"
                              class="w-full py-1 bg-blue-600 hover:bg-blue-700 text-xs rounded">Simpan</button>
                      </div>
                  </form>
              </div>
          </div>
      @endif
  @endif

</div>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      function confirmDelete(id) {
          Swal.fire({
              title: 'Yakin ingin menghapus?',
              text: "Data tidak bisa dikembalikan!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Ya, hapus!',
              cancelButtonText: 'Batal'
          }).then((result) => {
              if (result.isConfirmed) {
                  Livewire.dispatch('triggerDelete', {
                      id: id
                  });
              }
          });
      }

      Livewire.on('swal', ({
          icon = 'success',
          title = 'Berhasil!',
          text = ''
      }) => {
          Swal.fire({
              icon,
              title,
              text,
              toast: true,
              position: 'top',
              showConfirmButton: false,
              timer: 3000,
              timerProgressBar: true,
              customClass: {
                  popup: 'text-white bg-gray-800 shadow',
                  title: 'text-xs font-semibold',
              },
              didOpen: (toast) => {
                  toast.addEventListener('mouseenter', Swal.stopTimer);
                  toast.addEventListener('mouseleave', Swal.resumeTimer);
              }
          });
      });
  </script>
@endpush
