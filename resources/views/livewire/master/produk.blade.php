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
                        <th class="px-2 py-2">Nama Barang</th>
                        <th class="px-2 py-2">Jenis</th>
                        <th class="px-2 py-2">Hpp</th>

                        <th class="px-2 py-2">Patokan Resep</th>
                        <th class="px-2 py-2">Metode</th>
                        <th class="px-2 py-2">Dekor</th>
                        <th class="px-2 py-2">Divisi</th>

                        <th class="px-2 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($produks as $produk)
                        <tr class="hover:bg-gray-100 transition">
                            <td class="px-2 py-2">
                                {{ ($produks->currentPage() - 1) * $produks->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-2 py-2">{{ $produk->id }}</td>
                            <td class="px-2 py-2">{{ $produk->produk_id }}</td>
                            <td class="px-2 py-2">{{ $produk->nama }}</td>
                            <td class="px-2 py-2">{{ $produk->jenis }}</td>
                            <td class="px-2 py-2"> Rp.{{ number_format($produk->hpp_produk, 2, ',', '.') }}</td>
                            <td class="px-2 py-2">{{ $produk->patokan }}</td>
                            <td class="px-2 py-2">{{ $produk->metode }}</td>
                            <td class="px-2 py-2">{{ $produk->dekor }}</td>
                            <td>
                                <ul class="list-disc list-inside text-xs">
                                    @forelse ($produk->jobs as $j)
                                        @if ($j->job)  {{-- skip if the related job is missing --}}
                                            <li>{{ $j->job->nama_job }}</li>
                                        @endif
                                    @empty
                                        <li class="italic text-gray-400">Belum ada job</li>
                                    @endforelse
                                </ul>
                            </td>
                            <td class="px-2 py-2 flex gap-1">
                                <button wire:click="edit({{ $produk->id }})"
                                    class="bg-yellow-300 hover:bg-yellow-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                    ✎
                                </button>
                                <div class="ml-auto">
                                    <button wire:click="editJob({{ $produk->id }})"
                                        class="bg-green-300 hover:bg-green-400 px-2 py-1 rounded-lg text-gray-800 text-xs shadow transition">
                                        + Bagian
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-2">Tidak ada data</td>
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
