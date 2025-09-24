<div>
    <style>
        body {
            background-color: #111827; /* mirip bg-gray-900 */
            color: #f3f4f6;            /* mirip text-gray-100 */
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #374151; /* mirip border-gray-700 */
        }
        th {
            background: #374151; /* mirip bg-gray-700 */
            color: #d1d5db;      /* mirip text-gray-300 */
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background: #1f2937; /* mirip bg-gray-800 */
        }
        tr:hover {
            background: #4b5563; /* mirip hover:bg-gray-700 */
        }

        .btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-blue {
            background: #2563eb;
            color: white;
        }
        .btn-blue:hover {
            background: #1d4ed8;
        }
        .btn-yellow {
            background: #facc15;
            color: #111;
        }
        .btn-yellow:hover {
            background: #eab308;
        }
    </style>
    <div x-show="activeTab === 'targetdivisi'">
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
                <input wire:model.live="search"
                    class="w-full pl-8 pr-3 py-2 bg-gray-800 border border-gray-700 text-xs text-gray-100 placeholder-gray-400 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 transition"
                    type="search" placeholder="Cari nama bahan..." />
                <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </div>
            <button wire:click="openModal"
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
                        <th class="px-3 py-2 hidden">ID</th>
                        <th class="px-3 py-2">Nama Bagian</th>
                        <th class="px-3 py-2">Personil</th>
                        <th class="px-3 py-2">Target</th>
                        <th class="px-3 py-2">Satuan</th>
                        <th class="px-3 py-2">Produk</th>
                        <th class="px-3 py-2 text-center">Gunakan Target Asli</th> {{-- NEW --}}
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-gray-700 transition">
                            <td class="px-2 py-2">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2 hidden">{{ $job->id }}</td>
                            <td class="px-3 py-2">{{ $job->nama_job }}</td>
                            <td class="px-3 py-2">{{ $job->jml_orang }}</td>
                            <td class="px-3 py-2">{{ $job->target }}</td>
                            <td class="px-3 py-2">{{ $job->unit }}</td>
                            <td>
                                <ul class="list-disc list-inside text-xs text-gray-200">
                                    @foreach ($job->produkToJob as $ptj)
                                        <li>{{ $ptj->product->nama ?? '-' }}</li>
                                    @endforeach
                                </ul>
                            </td>

                            <td class="px-3 py-2 text-center">
                                <div class="inline-flex items-center gap-2">
                                  <input type="checkbox"
                                         disabled
                                         @checked($job->use_target_as_output)
                                         class="h-4 w-4 rounded cursor-not-allowed appearance-none
                                                {{ $job->use_target_as_output
                                                    ? 'bg-blue-500 border-blue-600 checked:bg-blue-600'
                                                    : 'bg-red-500 border-red-600' }}">
                                  <span class="{{ $job->use_target_as_output ? 'text-blue-400' : 'text-red-400' }} text-[11px]">
                                    {{ $job->use_target_as_output ? 'Aktif' : 'Tidak' }}
                                  </span>
                                </div>
                              </td>



                            <td class="px-3 py-2 flex space-x-2">
                                <button
                                    class="bg-yellow-500 hover:bg-yellow-600 px-2 py-1 rounded text-xs text-gray-900"
                                    wire:click="openEdit({{ $job->id }})">✎ Edit</button>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pt-2 text-xs text-gray-400">
            {{-- {{ $produks->links() }} --}}
            {{-- <span>Pagination here</span> --}}
        </div>
    </div>
    {{-- MODAL TAMBAH BAGIAN --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            {{-- backdrop --}}
            <div class="absolute inset-0 bg-black/60" wire:click="closeModal"></div>

            {{-- dialog --}}
            <div
                class="relative w-[680px] max-w-[95vw] bg-gray-900 text-gray-100 rounded-xl shadow-2xl border border-gray-700">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
                    <h3 class="font-semibold text-sm">
                        {{ $editingId ? 'Edit Bagian / Divisi' : 'Tambah Bagian / Divisi' }}
                      </h3>
                    <button class="text-gray-300 hover:text-white" wire:click="closeModal">✕</button>
                </div>

                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block mb-1">Group <span class="text-red-400">*</span></label>
                        <select wire:model.defer="form.group_job"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                            <option value="">-- Pilih Group --</option>
                            <option value="LOYANG">LOYANG</option>
                            <option value="TELUR">TELUR</option>
                            <option value="GILING">GILING</option>
                            <option value="POPROK">POPROK</option>
                            <option value="DEKOR">DEKOR</option>
                            <option value="DUS">DUS</option>
                            <option value="OVEN">OVEN</option>
                            <option value="KUKUS">KUKUS</option>
                            <option value="BOLEN">BOLEN</option>
                            <option value="CUCI">CUCI</option>
                            <option value="ROKER">ROKER</option>
                        </select>
                        @error('form.group_job')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1">Nama Bagian <span class="text-red-400">*</span></label>
                        <input type="text" wire:model.defer="form.nama_job"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                        @error('form.nama_job')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1">Jumlah Personil</label>
                        <input type="number" min="0" wire:model.defer="form.jml_orang"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                        @error('form.jml_orang')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1">Target</label>
                        <input type="number" min="0" wire:model.defer="form.target"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                        @error('form.target')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1">Satuan Target</label>
                        <select wire:model.defer="form.unit"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                            <option value="">-- Pilih Satuan --</option>
                            <option value="Pcs">Pcs</option>
                            <option value="Tong Kecil">Tong Kecil</option>
                            <option value="Tong Besar">Tong Besar</option>
                        </select>
                        @error('form.unit')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-1">Jam Mulai</label>
                        <input type="time" wire:model.defer="form.jam_mulai"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                        @error('form.jam_mulai')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1">Deskripsi</label>
                        <input type="text" wire:model.defer="form.deskripsi"
                            class="w-full bg-gray-800 border border-gray-700 rounded px-3 py-2">
                        @error('form.deskripsi')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2 flex items-center gap-3">
                        <input id="use-target" type="checkbox" wire:model.defer="form.use_target_as_output"
                            class="h-4 w-4 rounded border-gray-400 text-blue-500 focus:ring-blue-500">
                        <label for="use-target" class="select-none">Gunakan “Target” sebagai kolom <em>TARGET
                                PRODUKSI</em> (bypass hitungan)</label>
                        @error('form.use_target_as_output')
                            <p class="text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1">Produk terkait (optional)</label>
                        <div class="bg-gray-800 border border-gray-700 rounded p-2 max-h-52 overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                @foreach ($allProducts as $p)
                                    <label class="flex items-center gap-2 py-1">
                                        <input type="checkbox" wire:model.defer="form.produk_ids"
                                            value="{{ $p->id }}"
                                            class="h-4 w-4 rounded border-gray-400 text-blue-500 focus:ring-blue-500">
                                        <span>{{ $p->nama }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('form.produk_ids')
                            <p class="text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-gray-700 flex items-center justify-end gap-2">
                    <button class="px-3 py-2 text-xs bg-gray-700 hover:bg-gray-600 rounded"
                        wire:click="closeModal">Batal</button>
                    <button class="px-3 py-2 text-xs bg-blue-600 hover:bg-blue-700 rounded" wire:click="saveJob"
                        wire:loading.attr="disabled">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
