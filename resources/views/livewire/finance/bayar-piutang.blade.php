<div>
    <div class="space-y-3">

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="text-xs text-gray-500 block mb-1">Filter Toko</label>
                <select wire:model.live="filterTokoId"
                    class="w-full px-3 py-2 text-sm border rounded dark:bg-zinc-800 dark:border-zinc-700">
                    <option value="">— Semua Toko —</option>
                    @foreach ($tokos as $t)
                        <option value="{{ $t->id }}">{{ $t->nmtoko }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1">
                <label class="text-xs text-gray-500 block mb-1">Cari (Kategori / Keterangan)</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="contoh: KONTRAKAN, GAS, TELUR, kata keterangan…"
                    class="w-full px-3 py-2 text-sm border rounded dark:bg-zinc-800 dark:border-zinc-700" />
            </div>
        </div>


        {{-- Tabel --}}
        <div class="overflow-x-auto text-[11px] font-sans">
            <table class="min-w-full border text-[11px] font-mono">
                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-1 py-0.5 border">Tanggal</th>
                        <th class="px-1 py-0.5 border">Toko</th>
                        <th class="px-1 py-0.5 border">Kategori</th>
                        <th class="px-1 py-0.5 border text-right">Qty</th>
                        <th class="px-1 py-0.5 border text-right">Total</th>
                        <th class="px-1 py-0.5 border text-right">Terbayar</th>
                        <th class="px-1 py-0.5 border text-right">Sisa</th>
                        <th class="px-1 py-0.5 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                            <td class="px-1 py-0.5 border">{{ $r->tanggal->format('d-m-Y') }}</td>
                            <td class="px-1 py-0.5 border">{{ $r->toko->nmtoko ?? '-' }}</td>
                            <td class="px-1 py-0.5 border">{{ $r->kategori }}</td>
                            <td class="px-1 py-0.5 border text-right">{{ number_format($r->qty) }}</td>
                            <td class="px-1 py-0.5 border text-right">{{ number_format($r->total_piutang, 0) }}</td>
                            <td class="px-1 py-0.5 border text-right">{{ number_format($r->total_bayar ?? 0, 0) }}</td>
                            <td class="px-1 py-0.5 border text-right">
                                <span class="{{ ($r->sisa ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                    {{ number_format($r->sisa ?? 0, 0) }}
                                </span>
                            </td>
                            <td class="px-1 py-0.5 border text-center">
                                <button wire:click="openPay({{ $r->id }})"
                                    class="px-1.5 py-0.5 text-[10px] rounded bg-indigo-600 hover:bg-indigo-700 text-white">
                                    Bayar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-1 py-2 text-center text-gray-500">
                                Tidak ada data.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>



        {{-- Modal Bayar (fix fokus pertama kali setelah refresh/pindah halaman) --}}
        <div x-data="{ open: @entangle('showPay') }" x-cloak
            wire:key="pay-modal-{{ $current?->id ?? 'none' }}-{{ $showPay ? 'open' : 'closed' }}"
            x-effect="
    if (open) {
        $nextTick(() => {
            // beri 1 micro-delay agar selesai transition/render
            setTimeout(() => { $refs.jumlah?.focus(); $refs.jumlah?.select?.(); }, 0);
        });
    }
">
            <div x-show="open" class="fixed inset-0 bg-black/40 z-40"></div>
            <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div
                    class="w-full max-w-2xl bg-white dark:bg-zinc-800 rounded-xl shadow-lg border dark:border-zinc-700">
                    <div class="px-4 py-3 border-b dark:border-zinc-700 flex items-center justify-between">
                        <h3 class="font-semibold text-sm">Pembayaran Piutang</h3>
                        <button class="text-gray-500 hover:text-gray-700" @click="open=false">✕</button>
                    </div>

                    @if ($current)
                        <div class="p-4 space-y-3">
                            {{-- Ringkas header --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                                <div>
                                    <div class="text-gray-500">Toko</div>
                                    <div class="font-medium">{{ $current->toko->nmtoko ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Kategori</div>
                                    <div class="font-medium">{{ $current->kategori }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Total</div>
                                    <div class="font-medium">{{ number_format($current->total_piutang, 0) }}</div>
                                </div>
                                <div>
                                    <div class="text-gray-500">Sisa</div>
                                    <div
                                        class="font-medium {{ ($current->sisa ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ number_format($current->sisa ?? 0, 0) }}
                                    </div>
                                </div>
                            </div>

                            {{-- Form bayar --}}
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div class="sm:col-span-2">
                                    <label class="text-xs text-gray-500">Tanggal Bayar</label>
                                    <input type="date" wire:model="tgl_bayar"
                                        class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-700 @error('tgl_bayar') border-red-500 ring-1 ring-red-500 @enderror">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs text-gray-500">Jumlah Bayar</label>
                                    <input x-ref="jumlah"
                                        @input="$el.value=$el.value.replace(/[^\d]/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.')"
                                        @keydown.enter.prevent="$refs.keterangan?.focus(); $refs.keterangan?.select?.()"
                                        type="text" wire:model="jumlah_bayar"
                                        class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-700 @error('jumlah_bayar') border-red-500 ring-1 ring-red-500 @enderror">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs text-gray-500">Metode (opsional)</label>
                                    <input type="text" wire:model="metode"
                                        class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-700">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="text-xs text-gray-500">Catatan (opsional)</label>
                                    <input x-ref="keterangan" @keydown.enter.prevent="$wire.savePayment()"
                                        type="text" wire:model="catatan"
                                        class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-700">
                                </div>
                            </div>

                            <div class="flex justify-end gap-2">
                                <button class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800"
                                    @click="open=false">Tutup</button>
                                <button wire:click="savePayment"
                                    class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-white">
                                    Simpan Pembayaran
                                </button>
                            </div>

                            {{-- Detail pembayaran --}}
                            <div class="pt-3 border-t dark:border-zinc-700">
                                <div class="text-sm font-semibold mb-2">Riwayat Pembayaran</div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-xs border border-gray-200 dark:border-zinc-700">
                                        <thead class="bg-gray-100 dark:bg-zinc-900">
                                            <tr>
                                                <th class="px-2 py-1 border">Tanggal</th>
                                                <th class="px-2 py-1 border text-right">Jumlah</th>
                                                <th class="px-2 py-1 border">Metode</th>
                                                <th class="px-2 py-1 border">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($detailPembayaran as $d)
                                                <tr
                                                    class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                                    <td class="px-2 py-1 border">{{ $d->tgl_bayar?->format('d-m-Y') }}
                                                    </td>
                                                    <td class="px-2 py-1 border text-right">
                                                        {{ number_format($d->jumlah_bayar, 0) }}</td>
                                                    <td class="px-2 py-1 border">{{ $d->metode ?? '-' }}</td>
                                                    <td class="px-2 py-1 border">{{ $d->catatan ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="px-2 py-2 text-center text-gray-500">
                                                        Belum ada pembayaran.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Modal Konfirmasi Over/Underpay --}}
        <div x-data="{ open: @entangle('showConfirm') }" x-cloak @keydown.enter.prevent="$wire.confirmOverpay(true)">
            <div x-show="open" class="fixed inset-0 bg-black/40 z-[60]"></div>
            <div x-show="open" x-transition class="fixed inset-0 z-[70] flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white dark:bg-zinc-800 rounded-xl shadow-lg border dark:border-zinc-700 p-4"
                    tabindex="0" x-init="$watch('open', v => { if (v) $nextTick(() => $el.focus()) })">
                    <h3 class="font-semibold text-sm mb-2">Konfirmasi Nominal</h3>

                    <div class="text-sm space-y-1">
                        <div><span class="text-gray-500">Sisa tagihan:</span>
                            <span class="font-medium">{{ number_format($sisaNow ?? 0, 0) }}</span>
                        </div>
                        <div><span class="text-gray-500">Nominal diinput:</span>
                            <span class="font-medium">{{ number_format($enteredNominal ?? 0, 0) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Selisih ({{ $selisihLabel ?? 'lebih/kurang' }}):</span>
                            <span class="font-medium {{ ($selisih ?? 0) > 0 ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format(abs($selisih ?? 0), 0) }}
                            </span>
                        </div>

                        {{-- Pesan adaptif --}}
                        <p class="mt-2">
                            @php $s = $selisih ?? 0; @endphp
                            {{ $s > 0
                                ? 'Nominal melebihi sisa. Lanjutkan simpan?'
                                : ($s < 0
                                    ? 'Nominal kurang dari sisa. Lanjutkan simpan?'
                                    : 'Nominal pas. Lanjutkan simpan?') }}
                        </p>
                    </div>

                    <div class="mt-3 flex justify-end gap-2">
                        <button class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-gray-800"
                            wire:click="confirmOverpay(false)">Batal</button>
                        <button class="px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white"
                            wire:click="confirmOverpay(true)">Ya, simpan</button>
                    </div>
                </div>
            </div>
        </div>


        {{-- Fokus otomatis ke jumlah saat modal buka --}}

    </div>
</div>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('focus-jumlah', () => {
            const el = document.querySelector('[x-ref="jumlah"]');
            if (el) {
                el.focus();
                el.select?.();
            }
        });
    });
</script>
