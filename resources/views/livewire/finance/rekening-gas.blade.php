<div>
    <div x-show="activeTab === 'gas'">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed top-4 left-1/2 transform -translate-x-1/2 w-72 bg-emerald-500 text-white rounded-lg shadow-xl z-50">
                <div class="flex items-center justify-between px-3 py-2">
                    <span class="text-xs font-medium">{{ session('message') }}</span>
                    <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">
                        ✕
                    </button>
                </div>
            </div>
        @endif
        {{-- Header & Search --}}
        <div class="flex items-center justify-between gap-2">
            <div class="relative w-1/2">
                <input wire:model.live.debounce.300ms="search"
                    class="w-full pl-8 pr-3 py-2 bg-gray-800 border border-gray-700 text-xs text-gray-100 placeholder-gray-400 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 transition"
                    type="search" placeholder="Cari nama toko / area / bank / norek..." />
                <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>
            {{-- Tambah umum (user pilih toko di modal) --}}
            <button wire:click="openEditKontrakan"
                class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 text-xs rounded-lg shadow transition">
                ➕ Tambah
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto bg-gray-800 rounded-lg shadow mt-1">
            <table class="min-w-full text-left text-xs text-gray-300">
                <thead class="bg-gray-700 text-gray-200 uppercase">
                    <tr>
                        <th class="px-3 py-2">No</th>
                        <th class="px-3 py-2">Toko</th>
                        <th class="px-3 py-2">Area</th>
                        <th class="px-3 py-2">Jenis</th>
                        <th class="px-3 py-2">Bank</th>
                        <th class="px-3 py-2">Nama Rekening</th>
                        <th class="px-3 py-2">Rekening</th>
                        <th class="px-3 py-2">Catatan</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-700">
                    @php $no = ($gas->currentPage()-1) * $gas->perPage(); @endphp

                    @forelse($gas as $t)
                        @forelse($t->gass as $k)
                            @php $no++; @endphp
                            <tr>
                                <td class="px-3 py-2">{{ $no }}</td>
                                <td class="px-3 py-2">{{ $t->nmtoko }}</td>
                                <td class="px-3 py-2">{{ $k->area }}</td>
                                <td class="px-3 py-2">{{ $k->jenis }}</td>
                                <td class="px-3 py-2">{{ $k->bank }}</td>
                                <td class="px-3 py-2">{{ $k->nama_rekening }}</td>
                                <td class="px-3 py-2">{{ $k->no_rekening }}</td>

                                <td class="px-3 py-2">{{ $k->keterangan }}</td>
                                <td class="px-3 py-2">
                                    <button class="px-3 py-2 text-xs bg-yellow-500 hover:bg-yellow-600 text-white rounded"
                                        wire:click="openEditKontrakan({{ $k->id }}, null)">✎ Edit</button>
                                </td>
                            </tr>
                        @empty
                            @php $no++; @endphp
                            <tr>
                                <td class="px-3 py-2">{{ $no }}</td>
                                <td class="px-3 py-2">{{ $t->nmtoko }}</td>
                                <td class="px-3 py-2 text-center text-gray-400" colspan="6">
                                    Tidak ada supplier telur untuk {{ $t->nmtoko }}
                                </td>
                                <td class="px-3 py-2">
                                    <button class="px-3 py-2 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded"
                                        wire:click="openEditKontrakan(null, {{ $t->id }})">+ Tambah</button>
                                </td>
                            </tr>
                        @endforelse
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-4 text-center text-gray-400">Tidak ada toko</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{-- {{ $tokos->links() }} --}}
        </div>

        {{-- ================== MODAL TAMBAH/EDIT KONTRAKAN ================== --}}
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center">
                {{-- backdrop --}}
                <div class="absolute inset-0 bg-black/60" wire:click="closeModal"></div>

                {{-- dialog --}}
                <div
                    class="relative w-[720px] max-w-[95vw] bg-gray-900 text-gray-100 rounded-xl shadow-2xl border border-gray-700">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                        <h3 class="font-semibold text-sm">
                            {{ $editingId ? 'Edit Kontrakan' : 'Tambah Kontrakan' }}
                        </h3>
                        <button class="text-gray-300 hover:text-white" wire:click="closeModal">✕</button>
                    </div>
                    @if ($errors->any())
                        <div class="px-4 pt-3 text-xs text-red-300">
                            {{ implode(', ', $errors->all()) }}
                        </div>
                    @endif
                    <form wire:submit.prevent="saveKontrakan">
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">

                            {{-- Pilih Toko (wajib). Saat edit tetap boleh ganti jika kebijakanmu mengizinkan; kalau tidak, tambahkan disabled. --}}
                            <div class="md:col-span-2">
                                <label class="block mb-1">Toko <span class="text-red-400">*</span></label>
                                <select wire:model="tokoId"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                    <option value="">-- Pilih Toko --</option>
                                    @foreach ($allTokos as $t)
                                        <option value="{{ $t->id }}">{{ $t->nmtoko }}</option>
                                    @endforeach
                                </select>
                                @error('tokoId')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">Area</label>
                                <input type="text" wire:model.defer="form.area"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.area')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">Jenis</label>
                                <input type="text" wire:model.defer="form.jenis"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.jenis')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">Bank</label>
                                <input type="text" wire:model.defer="form.bank"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.bank')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">Nama Rekening</label>
                                <input type="text" wire:model.defer="form.nama_rekening"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.nama_rekening')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">No. Rekening</label>
                                <input type="text" wire:model.defer="form.no_rekening"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.no_rekening')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block mb-1">Nilai Sewa</label>
                                <input type="number" min="0" wire:model.defer="form.nilai_sewa"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.nilai_sewa')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block mb-1">Catatan</label>
                                <input type="text" wire:model.defer="form.keterangan"
                                    class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                                @error('form.keterangan')
                                    <p class="text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="px-4 py-3 border-t border-gray-700 flex items-center justify-end gap-2">
                            <button type="button" class="px-3 py-2 text-xs bg-gray-700 hover:bg-gray-600 rounded"
                                wire:click="closeModal">Batal</button>
                            <button type="submit" class="px-3 py-2 text-xs bg-blue-600 hover:bg-blue-700 rounded"
                                wire:loading.attr="disabled">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
