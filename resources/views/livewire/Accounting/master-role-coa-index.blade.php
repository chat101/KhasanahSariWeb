<div>
    <div class="space-y-4">

        {{-- HEADER + FILTER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Master Role COA</h2>
                <p class="text-xs text-gray-500">
                    Atur default role untuk setiap akun COA, agar bisa dipakai di jurnal otomatis (source_key: role:...).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.debounce.500ms="search"
                       placeholder="Cari kode / nama / role..."
                       class="border rounded-lg px-3 py-2 text-sm w-64">
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left w-12">#</th>
                        <th class="px-3 py-2 text-left w-28">Kode</th>
                        <th class="px-3 py-2 text-left">Nama Akun</th>
                        <th class="px-3 py-2 text-center w-24">Tipe</th>
                        <th class="px-3 py-2 text-center w-32">Role</th>
                        <th class="px-3 py-2 text-right w-28">Aksi</th>
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
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">
                                    {{ $row->tipe }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($row->default_role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">
                                        {{ $row->default_role }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        wire:click="openEditModal({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs hover:bg-yellow-200">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                COA belum ada atau belum ditemukan untuk filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-3 py-2">
                {{ $items->links() }}
            </div>
        </div>

        {{-- MODAL EDIT ROLE --}}
        @if($showModal)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40">
                <div class="bg-white  dark:bg-zinc-800 rounded-lg shadow-lg w-full max-w-md p-4">
                    <h3 class="text-base font-semibold mb-3">
                        Atur Role untuk COA
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Kode Akun</label>
                            <div class="px-3 py-2 border rounded-lg bg-gray-50 text-xs font-mono text-black">
                                {{ $kode }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Nama Akun</label>
                            <div class="px-3 py-2 border rounded-lg bg-gray-50 text-xs text-black">
                                {{ $nama }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Tipe</label>
                            <div class="px-3 py-2 border rounded-lg bg-gray-50 text-xs text-black">
                                {{ $tipe }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Default Role</label>
                            <select wire:model="default_role"
                                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black">
                                @foreach($roleOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('default_role')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1">
                                Role ini dipakai di template jurnal dengan source_key <code>role:nama_role</code>,
                                misalnya: <code>role:piutang</code>, <code>role:hutang_dagang</code>, dll.
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
