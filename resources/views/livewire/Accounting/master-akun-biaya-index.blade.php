<div>
    <div class="space-y-4">

        {{-- HEADER + FILTER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Master Akun Biaya</h2>
                <p class="text-xs text-gray-500">
                    Pengaturan akun-akun biaya (COA tipe biaya) untuk jurnal dan laporan laba rugi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.debounce.500ms="search"
                       placeholder="Cari kode / nama..."
                       class="border rounded-lg px-3 py-2 text-sm w-56">
                <button type="button"
                        wire:click="openCreateModal"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                    + Tambah Akun Biaya
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left w-16">#</th>
                        <th class="px-3 py-2 text-left w-32">Kode</th>
                        <th class="px-3 py-2 text-left">Nama Akun</th>
                        <th class="px-3 py-2 text-center w-24">Saldo Normal</th>
                        <th class="px-3 py-2 text-right w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($items as $index => $row)
                        <tr>
                            <td class="px-3 py-2">
                                {{ $items->firstItem() + $index }}
                            </td>
                            <td class="px-3 py-2 font-mono text-xs">
                                {{ $row->kode }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $row->nama }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                    {{ $row->normal_balance === 'D' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $row->normal_balance ?? '-' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        wire:click="openEditModal({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs hover:bg-yellow-200">
                                    Edit
                                </button>
                                <button type="button"
                                        onclick="if(!confirm('Yakin hapus akun biaya ini?')) return false;"
                                        wire:click="delete({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs hover:bg-red-200">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                Belum ada akun biaya yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-3 py-2">
                {{ $items->links() }}
            </div>
        </div>

        {{-- MODAL CREATE/EDIT --}}
        @if($showModal)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg w-full max-w-md p-4">
                    <h3 class="text-base font-semibold mb-3">
                        {{ $editId ? 'Edit Akun Biaya' : 'Tambah Akun Biaya' }}
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Kode</label>
                            <input type="text" wire:model="kode"
                                   placeholder="contoh: 511.01"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                            @error('kode')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Nama Akun</label>
                            <input type="text" wire:model="nama"
                                   placeholder="contoh: Biaya Listrik"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                            @error('nama')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Saldo Normal</label>
                            <select wire:model="normal_balance"
                                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black">
                                <option value="">- Pilih -</option>
                                <option value="D">Debet (D)</option>
                                <option value="K">Kredit (K)</option>
                            </select>
                            @error('normal_balance')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1">
                                Umumnya akun biaya memiliki saldo normal di sisi <strong>Debet (D)</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button"
                                wire:click="$set('showModal', false)"
                                class="px-3 py-1 rounded border text-sm">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="save"
                                class="px-4 py-1 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
     {{-- If your happiness depends on money, you will never be happy with yourself. --}}
</div>
