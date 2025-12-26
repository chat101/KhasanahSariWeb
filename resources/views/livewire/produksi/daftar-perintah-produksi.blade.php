<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">
    {{-- Header / Title + Search --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
            </svg>
            <h2 class="text-base font-semibold">Daftar Perintah Produksi</h2>
        </div>

        <div class="relative w-full max-w-xs">
            <input wire:model.live="search" type="search" placeholder="Cari perintah produksi…"
                class="form-control w-full pl-8 pr-3 py-1.5 rounded-md border border-gray-300 dark:border-zinc-600 text-sm bg-white dark:bg-zinc-800 focus:outline-none focus:ring focus:ring-indigo-200" />
            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
                </svg>
            </span>
        </div>
    </div>

    {{-- Notifikasi (tetap pakai session('message')) --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="mx-4 mt-3 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-2 text-xs">
            {{ session('message') }}
        </div>
    @endif

    {{-- Tabel --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left w-10 border border-gray-200 dark:border-zinc-700">No</th>
                    <th class="px-3 py-2 text-left w-16 border border-gray-200 dark:border-zinc-700">Tanggal Produksi</th>
                    <th class="px-3 py-2 text-left w-16 border border-gray-200 dark:border-zinc-700">ID Produksi</th>
                    <th class="px-3 py-2 text-left w-16 border border-gray-200 dark:border-zinc-700">Perekam</th>
                    <th class="px-3 py-2 text-left w-70 border border-gray-200 dark:border-zinc-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-indigo-700 dark:text-zinc-200">
                @forelse($perintahProduksi as $perintah)
                    <tr
                        class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
                        <td class="px-3 py-2 border border-gray-200 dark:border-zinc-700">
                            {{ ($perintahProduksi->currentPage() - 1) * $perintahProduksi->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-3 py-2 border border-gray-200 dark:border-zinc-700">
                            {{ $perintah->tanggal_perintah }}
                        </td>
                        <td class="px-3 py-2 border border-gray-200 dark:border-zinc-700">
                            {{ $perintah->no_perintah_produksi }}
                        </td>
                        <td class="px-3 py-2 border border-gray-200 dark:border-zinc-700">
                            {{ $perintah->user->name ?? '-' }}
                        </td>
                        <td class="px-3 py-2 border border-gray-200 dark:border-zinc-700">
                            <div class="flex items-center justify-between gap-2">
                                @if (Auth::user()->role === 'leaderproduksi')
                                    {{-- Tombol khusus untuk Leader Produksi --}}
                                    <a href="{{ route('hslglg', ['perintah_id' => $perintah->id]) }}"
                                        class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm shadow"
                                        aria-label="Hasil Giling">
                                        Hasil Giling
                                    </a>
                                @else
                                    {{-- Tombol umum untuk role selain Leader Produksi --}}
                                 @if (empty($perintah->status))

    {{-- Pengurangan Giling: hanya untuk anggun@gmail.com --}}
    @if (auth()->check() && auth()->user()->email === 'anggun@gmail.com')
        <button type="button" wire:click="openEditModal({{ $perintah->id }})"
            class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm shadow"
            aria-label="Tambah produksi">
            Pengurangan Giling
        </button>
    @endif

    {{-- Tombol lain tetap tampil seperti biasa --}}
    <a href="{{ route('hasdist', ['perintah_id' => $perintah->id]) }}"
        class="inline-flex items-center px-3 py-1.5 rounded bg-amber-400 hover:bg-amber-500 text-gray-900 text-xs font-medium shadow transition">
        Input Hasil
    </a>

@endif

                                    <button wire:click="confirmSelesai({{ $perintah->id }})" wire:loading.attr="disabled"
                                        class="inline-flex items-center px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium shadow transition">
                                        {{ $perintah->status ?? 0 ? 'Batal Selesai' : 'Selesai' }}
                                    </button>
                                @endif
                            </div>
                        </td>


                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-6 text-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-zinc-700"
                            colspan="5">
                            Tidak ada data
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-3 border-t border-gray-200 dark:border-zinc-700 text-xs text-gray-500">
        {{ $perintahProduksi->links() }}
    </div>

    {{-- Modal Konfirmasi (pakai variabel & method asli) --}}
    @if ($showConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" wire:click="$set('showConfirm', false)"></div>
            <div
                class="relative bg-white dark:bg-zinc-800 w-full max-w-sm rounded-lg shadow-lg border border-gray-200 dark:border-zinc-700">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 7h2v6h-2V7zm0 8h2v2h-2v-2z" />
                    </svg>
                    <h3 class="text-sm font-semibold">Ubah Status Produksi</h3>
                </div>

                <div class="px-4 py-3 text-sm">
                    Yakin ingin mengubah status produksi untuk perintah ini?
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-zinc-700 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showConfirm', false)"
                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-medium">
                        Batal
                    </button>
                    <button type="button" wire:click="selesaiProduksi" wire:loading.attr="disabled"
                        class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium">
                        Ya, Ubah
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if ($showEditModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            wire:click.self="$set('showEditModal', false)">
            <div class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-lg shadow-lg p-4 space-y-4 border border-gray-200 dark:border-zinc-700"
                wire:key="edit-modal-{{ $selectedPerintahId }}">
                <h3 class="text-lg font-semibold">Tambah Produksi (ID: {{ $selectedPerintahId }})</h3>

                {{-- Baris pilih produk & qty --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                    <div class="flex-1">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Pilih Produk</label>
                        <select wire:model="selectedProductId"
                            class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600">
                            <option value="">— pilih produk —</option>
                            @foreach ($mproducts as $p)
                                <option value="{{ $p['id'] }}">{{ $p['nama'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedProductId')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="w-full sm:w-40">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Jumlah</label>
                        <input type="number" step="0.01" wire:model="jumlahTambahan"
                            wire:keydown.enter="stageTambahan" x-ref="inputTambahan"
                            class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600"
                            placeholder="Qty" />
                        @error('jumlahTambahan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <button type="button" wire:click="stageTambahan"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow">
                            Tambah
                        </button>
                    </div>
                </div>

                {{-- Keterangan --}}
                <div>
                    <textarea wire:model="keteranganTambahan" rows="2"
                        class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                        placeholder="Masukkan keterangan (opsional)"></textarea>
                    @error('keteranganTambahan')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Draft Tambahan --}}
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <h4 class="text-sm font-semibold mb-2">Draft Tambahan</h4>

                    @if (!empty($stagedTambahan))
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Produk</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            Target Qty</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Keterangan</th>
                                        <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stagedTambahan as $i => $row)
                                        <tr
                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row['nama'] }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row['qty'], 2) }}</td>
                                            <td
                                                class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                                {{ number_format($row['target_qty'], 2) }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                {{ $row['keterangan'] ?: '-' }}</td>
                                            <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                                <button
                                                    class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded"
                                                    wire:click="removeStaged({{ $i }})">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-3">
                            <button type="button" wire:click="simpanPengurangan"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded shadow">
                                Simpan ke Database
                            </button>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada draft tambahan.</p>
                    @endif
                </div>

                {{-- Riwayat Pengurangan --}}
            {{-- Riwayat Tambahan --}}
            <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                <h4 class="text-sm font-semibold mb-2">Riwayat Tambahan</h4>

                @if (!empty($riwayatPengurangan))
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                            <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                <tr>
                                    <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">Tgl
                                    </th>
                                    <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                        Produk</th>
                                    <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                        Qty</th>
                                    <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                        Target Qty</th>
                                    <th class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-left">
                                        Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayatPengurangan as $row)
                                    <tr
                                        class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                        <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                            {{ $row->created_at->format('d-m-Y H:i') }}</td>
                                        <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                            {{ $row->product->nama ?? '-' }}</td>
                                        <td
                                            class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            {{ number_format($row->qty_pengurangan, 2) }}</td>
                                        <td
                                            class="px-2 py-1 border border-gray-200 dark:border-zinc-700 text-right">
                                            {{ number_format($row->target_qty_pengurangan, 2) }}</td>
                                        <td class="px-2 py-1 border border-gray-200 dark:border-zinc-700">
                                            {{ $row->keterangan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada riwayat tambahan.</p>
                @endif
            </div>

                {{-- Footer Modal --}}
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showEditModal', false)"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
{{-- Modal Tambahan --}}
