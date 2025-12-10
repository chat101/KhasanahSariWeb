<div>
 {{-- resources/views/livewire/purchasing/piutang-index.blade.php --}}

<div class="space-y-4">

    {{-- TOTAL PIUTANG --}}
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                    Total Hutang
                </h2>
                <p class="text-2xl font-bold text-red-600 mt-1">
                    Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                </p>
            </div>

            <div class="w-64">
                <input type="text" wire:model.debounce.500ms="search"
                       class="w-full border rounded-lg px-3 py-2 text-sm"
                       placeholder="Cari tanggal / no transaksi...">
            </div>
        </div>
    </div>

    {{-- TABLE PIUTANG --}}
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-700 text-white dark:text-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">No</th>
                    <th class="px-3 py-2 text-left">Supplier</th>
                    <th class="px-3 py-2 text-right">Grand Total</th>
                    <th class="px-3 py-2 text-right">Total Bayar</th>
                    <th class="px-3 py-2 text-right">Sisa Hutang</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                @forelse($piutang as $row)
                    <tr>
                        <td class="px-3 py-2">{{ $row->tgl_input }}</td>
                        <td class="px-3 py-2">#{{ $row->id }}</td>
                        <td class="px-3 py-2">
                            {{ $row->gudang_masuk->supplier->nmsupp ?? '-' }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($row->grandtotal, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($row->total_bayar, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 text-right font-semibold">
                            {{ number_format($row->sisa_hutang, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if($row->sisa_hutang <= 0)
                                <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                                    Lunas
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs">
                                    Belum Lunas
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if($row->sisa_hutang > 0)
                                <button wire:click="openBayarModal({{ $row->id }})"
                                        class="px-3 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">
                                    Bayar
                                </button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                            Tidak ada data piutang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-2">
            {{ $piutang->links() }}
        </div>
    </div>

    {{-- MODAL BAYAR HUTANG --}}
    @if($showBayarModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg w-full max-w-lg p-4">
                <h3 class="text-lg font-semibold mb-3">
                    Bayar Hutang #{{ $nomorTransaksi }}
                </h3>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div>
                        <div class="text-gray-500">Tanggal Transaksi</div>
                        <div class="font-medium">{{ $tanggalTransaksi }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-500">Grand Total</div>
                        <div class="font-semibold">
                            Rp {{ number_format($grandtotal, 0, ',', '.') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-500">Total Bayar</div>
                        <div class="font-semibold text-green-600">
                            Rp {{ number_format($totalBayar, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-gray-500">Sisa Hutang</div>
                        <div class="font-semibold text-red-600">
                            Rp {{ number_format($sisaHutang, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div>
                        <label class="block text-gray-600 mb-1">Tanggal Bayar</label>
                        <input type="date" wire:model="tanggal_bayar"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                        @error('tanggal_bayar')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div
                    x-data="{
                        raw: @entangle('jumlah_bayar').live, // nilai mentah buat Livewire
                        get formatted() {
                            if (!this.raw) return '';
                            let n = this.raw.toString().replace(/\D/g, '');
                            return n ? new Intl.NumberFormat('id-ID').format(n) : '';
                        },
                        set formatted(val) {
                            let onlyNum = val.replace(/\D/g, '');
                            this.raw = onlyNum; // simpan ke Livewire tanpa titik
                        }
                    }"
                >
                    <label class="block text-gray-600 mb-1">Jumlah Bayar</label>

                    <input
                        type="text"
                        x-model="formatted"
                        inputmode="numeric"
                        class="w-full border rounded-lg px-3 py-2 text-sm text-right"
                    >

                    @error('jumlah_bayar')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>


                    <div>
                        <label class="block text-gray-600 mb-1">Bayar dari Kas</label>
                        <select wire:model="metode_bayar"
                                class="w-full border rounded-lg px-3 py-2 text-sm ">
                            <option class="text-black" value="Kas Kecil">Kas Kecil</option>
                            <option class="text-black" value="Kas Bank">Kas Bank</option>
                            {{-- tambahkan pilihan lain jika perlu --}}
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-600 mb-1">Keterangan</label>
                        <textarea wire:model="keterangan"
                                  class="w-full border rounded-lg px-3 py-2 text-sm"
                                  rows="2"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showBayarModal', false)"
                            class="px-3 py-1 rounded border text-sm">
                        Batal
                    </button>
                    <button wire:click="savePayment"
                            class="px-4 py-1 rounded bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                        Simpan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
