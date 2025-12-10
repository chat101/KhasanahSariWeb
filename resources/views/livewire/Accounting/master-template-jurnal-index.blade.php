<div>
    <div class="space-y-4">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Master Template Jurnal</h2>
                <p class="text-xs text-gray-500">
                    Pengaturan pola debit/kredit per jenis transaksi untuk jurnal otomatis.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <div>
                    <label class="block text-[11px] text-gray-500 mb-1">Jenis Transaksi</label>
                    <select wire:model.live="transaction_type_id"
                            class="border rounded-lg px-3 py-2 text-sm bg-white text-black">
                        @foreach($transactionTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->nama }} ({{ $type->code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="button"
                        wire:click="openCreateModal"
                        class="mt-5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                    + Tambah Baris Template
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left w-12">#</th>
                        <th class="px-3 py-2 text-left w-28">Side</th>
                        <th class="px-3 py-2 text-center w-24">Order</th>
                        <th class="px-3 py-2 text-left">Source Key</th>
                        <th class="px-3 py-2 text-right w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @forelse($templates as $index => $row)
                        <tr>
                            <td class="px-3 py-2">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs
                                    {{ $row->side === 'debit' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ strtoupper($row->side) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                {{ $row->order_no }}
                            </td>
                            <td class="px-3 py-2 font-mono text-xs">
                                {{ $row->source_key }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        wire:click="openEditModal({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs hover:bg-yellow-200">
                                    Edit
                                </button>
                                <button type="button"
                                        onclick="if(!confirm('Yakin hapus baris template ini?')) return false;"
                                        wire:click="delete({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs hover:bg-red-200">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                                Belum ada template untuk jenis transaksi ini.
                                Tambahkan baris baru dengan tombol <strong>+ Tambah Baris Template</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MODAL CREATE/EDIT --}}
        @if($showModal)
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40">
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg w-full max-w-md p-4">
                    <h3 class="text-base font-semibold mb-3">
                        {{ $editId ? 'Edit Template Jurnal' : 'Tambah Template Jurnal' }}
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Side (Debet/Kredit)</label>
                            <select wire:model="side"
                                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black">
                                <option value="debit">debit</option>
                                <option value="kredit">kredit</option>
                            </select>
                            @error('side')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-600 mb-1">Order No</label>
                            <input type="number" wire:model="order_no"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                            @error('order_no')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                            <p class="text-[11px] text-gray-400 mt-1">
                                Urutan baris jurnal. Contoh: baris 1 untuk debet, baris 2 untuk kredit.
                            </p>
                        </div>

                        <div>
                            <label class="block text-gray-600 mb-1">Source Key</label>

                            <select wire:model="source_key"
                            class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black">
                        <option value="">Pilih Source...</option>
                        @foreach($sourceOptions as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                            @error('source_key')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
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
     {{-- Stop trying to control. --}}
</div>
