<div class="p-3">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-100">Transaksi Bank</h2>

        <div class="flex gap-2">
            <a href="{{ route('transaksi.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs shadow">
                + Tambah
            </a>

            <button wire:click="$set('showTransferModal', true)"
                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-md text-xs shadow">
                Transfer Bank
            </button>
        </div>
    </div>
    {{-- FILTER --}}
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 mb-4 shadow">
        <div class="grid grid-cols-4 gap-2">

            <input type="text" wire:model.live="search"
                class="border border-gray-600 bg-gray-900 text-white px-2 py-1.5 rounded text-xs"
                placeholder="Cari...">

            <select wire:model.live="filterBank"
                class="border border-gray-600 bg-gray-900 text-white px-2 py-1.5 rounded text-xs">
                <option value="">Bank</option>
                @foreach ($banks as $b)
                    <option value="{{ $b->id }}">{{ $b->nama_bank }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterKategori"
                class="border border-gray-600 bg-gray-900 text-white px-2 py-1.5 rounded text-xs">
                <option value="">Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->nama_kategori }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterTipe"
                class="border border-gray-600 bg-gray-900 text-white px-2 py-1.5 rounded text-xs">
                <option value="">Tipe</option>
                <option value="debit">Debit</option>
                <option value="kredit">Kredit</option>
            </select>

        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session()->has('success'))
        <div class="bg-green-700 text-green-100 p-2 rounded text-xs mb-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-lg shadow border border-gray-700 bg-gray-900">

        <table class="w-full text-[11px] text-gray-300">

            <thead class="bg-gray-800 text-gray-200">
                <tr>
                    <th class="p-1.5 border border-gray-700">Tanggal</th>
                    <th class="p-1.5 border border-gray-700">Bank</th>
                    <th class="p-1.5 border border-gray-700">Kategori</th>
                    <th class="p-1.5 border border-gray-700">Tipe</th>
                    <th class="p-1.5 border border-gray-700">Jumlah</th>
                    <th class="p-1.5 border border-gray-700">Ket</th>
                    <th class="p-1.5 border border-gray-700">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($transaksi as $tx)
                    <tr class="hover:bg-gray-800 transition">
                        <td class="p-1.5 border border-gray-800">{{ $tx->tanggal }}</td>
                        <td class="p-1.5 border border-gray-800">{{ $tx->bank->nama_bank }}</td>
                        <td class="p-1.5 border border-gray-800">{{ $tx->category->nama_kategori ?? '-' }}</td>
                        <td class="p-1.5 border border-gray-800 capitalize">{{ $tx->tipe }}</td>
                        <td class="p-1.5 border border-gray-800">
                            Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="p-1.5 border border-gray-800 truncate max-w-[100px]">
                            {{ $tx->keterangan }}
                        </td>
                        <td class="p-1.5 border border-gray-800 text-center">
                            <a href="{{ route('transaksi.edit', $tx->id) }}"
                               class="text-blue-400 hover:text-blue-300 text-[11px]">Edit</a>
                            |
                            <button wire:click="delete({{ $tx->id }})"
                                onclick="return confirm('Hapus?')"
                                class="text-red-400 hover:text-red-300 text-[11px]">
                                Hapus
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-3 text-gray-400 text-xs">
                            Tidak ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>
{{-- MODAL TRANSFER --}}
@if($showTransferModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">

    <div class="bg-gray-800 border border-gray-700 rounded-md p-4 w-80 shadow-lg">

        <h3 class="text-sm font-semibold text-gray-100 mb-3">Transfer Antar Bank</h3>

        <form wire:submit.prevent="saveTransfer" class="space-y-2 text-xs">

            <div>
                <label class="text-gray-300 text-[11px]">Dari Bank</label>
                <select wire:model="bank_from"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded">
                    <option value="">Pilih Bank Sumber</option>
                    @foreach ($banks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bank }}</option>
                    @endforeach
                </select>
                @error('bank_from') <span class="text-red-400 text-xs">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="text-gray-300 text-[11px]">Ke Bank</label>
                <select wire:model="bank_to"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded">
                    <option value="">Pilih Bank Tujuan</option>
                    @foreach ($banks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bank }}</option>
                    @endforeach
                </select>
                @error('bank_to') <span class="text-red-400 text-xs">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="text-gray-300 text-[11px]">Jumlah</label>
                <input type="number" wire:model="jumlah"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded">
                @error('jumlah') <span class="text-red-400 text-xs">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="text-gray-300 text-[11px]">Tanggal</label>
                <input type="date" wire:model="tanggal"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded">
                @error('tanggal') <span class="text-red-400 text-xs">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="text-gray-300 text-[11px]">Keterangan</label>
                <textarea wire:model="keterangan"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded h-14"></textarea>
            </div>

            <div>
                <label class="text-gray-300 text-[11px]">Upload Bukti</label>
                <input type="file" wire:model="bukti"
                    class="w-full border border-gray-600 bg-gray-900 text-white p-1 rounded">
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <button type="button"
                    wire:click="$set('showTransferModal', false)"
                    class="px-2 py-1 rounded bg-gray-600 text-white text-[11px]">
                    Batal
                </button>

                <button class="px-2 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-[11px]">
                    Simpan
                </button>
            </div>

        </form>

    </div>
</div>
@endif
</div>
