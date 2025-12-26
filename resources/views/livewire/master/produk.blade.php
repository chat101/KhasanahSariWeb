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
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">PR</span>
                    Master Produk
                </h2>
                <span class="text-[11px] text-gray-500">Kelola master produk</span>
            </div>

            <div class="mt-3 mb-2">
                <div class="w-1/3">
                    <input wire:model.live="search" class="form-control py-1 pl-8 pr-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari nama bahan..." />
                </div>
            </div>
        </div>

        {{-- Product Table --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
              <tr class="text-left">
                <th class="px-4 py-3 font-semibold text-gray-700">No</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Id</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Id Barang</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Nama Barang</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Jenis</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Hpp</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Patokan</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Metode</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Dekor</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Divisi</th>
                <th class="px-4 py-3 font-semibold text-gray-700">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @forelse($produks as $produk)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3">{{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}</td>
                  <td class="px-4 py-3">{{ $produk->id }}</td>
                  <td class="px-4 py-3">{{ $produk->produk_id }}</td>
                  <td class="px-4 py-3 truncate">{{ $produk->nama }}</td>
                  <td class="px-4 py-3">{{ $produk->jenis }}</td>
                  <td class="px-4 py-3">Rp.{{ number_format($produk->hpp_produk, 2, ',', '.') }}</td>
                  <td class="px-4 py-3">{{ $produk->patokan }}</td>
                  <td class="px-4 py-3">{{ $produk->metode }}</td>
                  <td class="px-4 py-3">{{ $produk->dekor }}</td>
                  <td class="px-4 py-3">
                    <ul class="list-disc list-inside text-xs">
                      @forelse ($produk->jobs as $j)
                        @if ($j->job)
                          <li>{{ $j->job->nama_job }}</li>
                        @endif
                      @empty
                        <li class="italic text-gray-400">Belum ada job</li>
                      @endforelse
                    </ul>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <button wire:click="edit({{ $produk->id }})" aria-label="Edit {{ $produk->id }}" title="Edit" class="p-2 rounded-md hover:bg-gray-50 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </button>
                      <div class="ml-auto">
                        <button wire:click="editJob({{ $produk->id }})" class="p-2 rounded-md hover:bg-gray-50 text-green-600" title="Tambah Bagian">+</button>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center py-6 text-gray-500">Tidak ada data</td>
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
                                    <label class="block mb-1">Kode Produk</label>
                                    <input type="text" wire:model.defer="kodeproduk"
                                        class="w-full p-2 rounded text-xs border @error('kodeproduk') border-red-500 @enderror">
                                    @error('kodeproduk')
                                        <span class="text-red-400 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-1">Nama Produk</label>
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
                                        <option value="Brownis">Brownis</option>
                                        <option value="Bolu">Bolu</option>
                                        <option value="Bolu Bulat">Bolu Bulat</option>
                                        <option value="Bolu Gulung">Bolu Gulung</option>
                                        <option value="Cake">Cake</option>
                                        <option value="Desert">Desert</option>

                                        <option value="Roker">Roker</option>
                                        <option value="Pastri">Pastri</option>
                                    </select>
                                    @error('jenis')
                                        <span class="text-red-400 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-1">Hpp</label>
                                    <input type="number"
                                    wire:model.defer="hpp"
                                    step="0.01" min="0"
                                    class="w-full p-2 rounded text-xs border @error('hpp') border-red-500 @enderror">
                             @error('hpp')
                                 <span class="text-red-400 text-xs">{{ $message }}</span>
                             @enderror
                                </div>
                                <div>
                                    <label class="block mb-1">Patokan</label>
                                    <input type="number" wire:model.defer="patokan"
                                        class="w-full p-2 rounded text-xs border @error('patokan') border-red-500 @enderror">
                                    @error('patokan')
                                        <span class="text-red-400 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block mb-1">Metode</label>
                                    <select wire:model.defer="metode"
                                        class="w-full p-2 rounded text-xs border bg-gradient-to-tl from-purple-600 to-purple-800 text-white @error('metode') border-red-500 @enderror appearance-none focus:bg-purple-700">
                                        <option value="">Pilih Metode</option>
                                        <option value="Kukus">Kukus</option>
                                        <option value="Oven">Oven</option>
                                        <option value="Roker">Roker</option>
                                        <option value="WIP">WIP</option>
                                    </select>
                                    @error('metode')
                                        <span class="text-red-400 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-1">Dekor</label>
                                    <select wire:model.defer="dekor"
                                        class="w-full p-2 rounded text-xs border bg-gradient-to-tl from-purple-600 to-purple-800 text-white @error('dekor') border-red-500 @enderror appearance-none focus:bg-purple-700">
                                        <option value="">Pilih Dekor</option>
                                        <option value="Coklat">Coklat</option>
                                        <option value="Keju">Keju</option>
                                        <option value="Bolu">Bolu</option>
                                    </select>
                                    @error('dekor')
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
        @if ($mode === 'editJob')
            @if ($modal)
                <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-3">
                    <div
                        class="bg-gradient-to-tl from-purple-600 to-purple-800 text-white rounded-md shadow w-full max-w-md p-4 space-y-3">
                        <h2 class="text-lg font-semibold text-center">
                            Tambah ke Produk
                        </h2>
                        @if (session()->has('message'))
                            <div class="bg-red-100 text-red-800 px-4 py-2 mb-3 rounded text-sm">
                                {{ session('message') }}
                            </div>
                        @endif
                        <form wire:submit.prevent="storeJob" class="space-y-3">
                            {{-- Daftar Job Produk --}}
                            <div class="space-y-2">

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block mb-1">Kode Produk</label>
                                        <input type="text" wire:model.defer="kodeproduk"
                                            class="w-full p-2 rounded text-xs border @error('kodeproduk') border-red-500 @enderror"
                                            readonly>
                                        @error('kodeproduk')
                                            <span class="text-red-400 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block mb-1">Nama Produk</label>
                                        <input type="text" wire:model.defer="nama"
                                            class="w-full p-2 rounded text-xs border @error('nama') border-red-500 @enderror"
                                            readonly>
                                        @error('nama')
                                            <span class="text-red-400 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                @foreach ($jobs as $index => $job)
                                    <div class="flex items-center space-x-2">
                                        <select wire:model="jobs.{{ $index }}.job_id"
                                            class="border border-gray-300 rounded px-3 py-2 w-full text-sm">
                                            <option value="">-- Pilih Job --</option>
                                            @foreach ($msjobs as $job)
                                                <option class="text-red-500 text-xs" value="{{ $job['id'] }}">
                                                    {{ $job['nama_job'] }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="removeJob({{ $index }})"
                                            class="px-2 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">✕</button>
                                    </div>
                                    @error("jobs.$index.nama_job")
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                @endforeach

                                <button type="button" wire:click="addJob"
                                    class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">+ Tambah
                                    Job</button>
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
