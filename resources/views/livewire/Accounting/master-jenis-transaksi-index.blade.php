<div>
    <div class="space-y-4">

        {{-- HEADER + FILTER --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold">Master Jenis Transaksi</h2>
                <p class="text-xs text-gray-500">
                    Pengaturan jenis transaksi untuk journal otomatis (biaya, pendapatan, mutasi kas, dll).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.debounce.500ms="search"
                       placeholder="Cari code / nama..."
                       class="border rounded-lg px-3 py-2 text-sm w-48">
                <button type="button"
                        wire:click="openCreateModal"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                    + Tambah Jenis
                </button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left w-16">#</th>
                        <th class="px-3 py-2 text-left w-40">Code</th>
                        <th class="px-3 py-2 text-left">Nama</th>
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
                                {{ $row->code }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $row->nama }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                <button type="button"
                                        wire:click="openEditModal({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-xs hover:bg-yellow-200">
                                    Edit
                                </button>
                                <button type="button"
                                        onclick="if(!confirm('Yakin hapus jenis transaksi ini?')) return false;"
                                        wire:click="delete({{ $row->id }})"
                                        class="px-2 py-1 rounded bg-red-100 text-red-800 text-xs hover:bg-red-200">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                Belum ada data jenis transaksi.
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
                        {{ $editId ? 'Edit Jenis Transaksi' : 'Tambah Jenis Transaksi' }}
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block text-gray-600 mb-1">Code</label>
                            <input type="text" wire:model="code"
                                   placeholder="contoh: biaya, pendapatan, mutasi_kas"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                            @error('code')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-600 mb-1">Nama</label>
                            <input type="text" wire:model="nama"
                                   placeholder="contoh: Transaksi Biaya"
                                   class="w-full border rounded-lg px-3 py-2 text-sm">
                            @error('nama')
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
      {{-- The whole world belongs to you. --}}
</div>
<script>
    document.addEventListener('livewire:init', () => {
        window.addEventListener('notify', event => {
            const { type, message } = event.detail;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type === 'success' ? 'success' : 'error',
                    title: type === 'success' ? 'Berhasil' : 'Error',
                    text: message,
                    timer: 2000,
                    showConfirmButton: false,
                });
            } else {
                alert(message);
            }
        });
    });
    </script>
