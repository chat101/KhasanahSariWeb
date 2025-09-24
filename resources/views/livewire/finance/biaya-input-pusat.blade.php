<div class="text-xs">
    {{-- Header --}}
    <div
        class="px-4 py-1 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 font-mono">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
            </svg>
            <h2 class="text-base font-semibold">INPUT BIAYA KELUAR DIPUSAT</h2>
        </div>

    </div>

    {{-- Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 left-1/2 -translate-x-1/2 w-72 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm"
                    aria-label="Tutup">✕</button>
            </div>
        </div>
    @endif

    <div
        class="bg-white dark:bg-zinc-800 w-full max-w-none rounded-lg shadow-lg p-4 space-y-4 border border-gray-200 dark:border-zinc-700 mt-1.5">
        <div>
            <div class="w-full sm:w-48">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Tanggal Input</label>
                <input x-ref="tgl" @keydown.enter.prevent="next($event.target)" type="date"
                    wire:model="tglPembayaran"
                    class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                    placeholder="Tanggal" />
                @error('tglPembayaran')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

        </div>
        {{-- Baris pilih produk & qty --}}
        <div x-data="{
            focusOrder: [],
            confirmMode: false,
            init() {
                this.focusOrder = [
                    $refs.toko,
                    $refs.kategori,
                    $refs.qty,
                    $refs.piutang,
                    $refs.keterangan,
                    $refs.tambah
                ];
            },
            next(currentEl) {
                const i = this.focusOrder.indexOf(currentEl);
                if (i === -1) return;
                const nextEl = this.focusOrder[Math.min(i + 1, this.focusOrder.length - 1)];
                if (!nextEl) return;
                nextEl.focus();
                if (typeof nextEl.select === 'function') nextEl.select();

                // kalau sudah sampai tombol → aktifkan confirmMode
                if (nextEl.tagName === 'BUTTON') {
                    this.confirmMode = true;
                    alert('Tekan ENTER lagi untuk menyimpan'); // munculkan alert
                }
            },
            handleSubmit() {
                if (this.confirmMode) {
                    this.confirmMode = false;
                    $wire.submit(); // kirim ke Livewire
                }
            }
        }" @keydown.enter.prevent="confirmMode ? handleSubmit() : next($event.target)"
            @focus-toko.window="$refs.toko.focus()" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
            <div class="w-full sm:w-48">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Pilih Toko</label>
                {{-- PILIH TOKO --}}
                <select x-ref="toko" @keydown.enter.prevent="next($event.target)" wire:model.live="selectedToko"
                    class="text-xs w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600
       @error('selectedToko') border-red-500 ring-1 ring-red-500 @enderror">
                    <option value="">— Toko —</option>
                    @foreach ($mtokos as $p)
                        <option value="{{ $p['id'] }}">{{ $p['nmtoko'] }}</option>
                    @endforeach
                </select>

            </div>

            <div class="w-full sm:w-28">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Kategori</label>
                {{-- PILIH KATEGORI --}}
                <select x-ref="kategori" @keydown.enter.prevent="next($event.target)" wire:model.live="selectedKategori"
                    class="text-xs w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600
       @error('selectedKategori') border-red-500 ring-1 ring-red-500 @enderror">
                    <option value="">— Kategori —</option>
                    <option value="GAS">GAS</option>
                    <option value="KONTRAKAN">KONTRAKAN</option>
                    <option value="TELUR">TELUR</option>
                </select>

            </div>
            {{-- SAAT KONTRAKAN: SELECT TAGIHAN --}}
            @if ($selectedKategori === 'KONTRAKAN')
                <div class="w-full sm:flex-1">
                    <label class="text-sm text-gray-600 dark:text-zinc-300">Tagihan Kontrakan</label>
                    <select wire:model.live="selectedKontrakanId"
                        class="text-xs w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600
                   @error('selectedKontrakanId') border-red-500 ring-1 ring-red-500 @enderror">
                        <option value="">— Pilih Tagihan —</option>
                        @forelse($kontrakanOptions as $opt)
                            <option value="{{ $opt['id'] }}">{{ $opt['label'] }}</option>
                        @empty
                            <option value="">(Tidak ada kontrakan untuk toko ini)</option>
                        @endforelse
                    </select>
                    @error('selectedKontrakanId')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            @endif
            <div class="w-full sm:w-15">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Qty</label>
                <input x-ref="qty" @keydown.enter.prevent="next($event.target)" type="text" wire:model.lazy="qty"
                    class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600
                      @error('qty') border-red-500 ring-1 ring-red-500 @enderror"
                    placeholder="Qty"
                    oninput="let v=this.value.replace(/[^\d,.\-]/g,'');v=v.replace(/(?!^)-/g,'');v=v.replace(/\./g,'');const parts=v.split(',');let intPart=parts[0]??'';let sign='';if(intPart.startsWith('-')){sign='-';intPart=intPart.slice(1);}intPart=intPart.replace(/^0+(?=\d)/,'');intPart=intPart.replace(/\B(?=(\d{3})+(?!\d))/g,'.');const dec=parts.length>1?parts[1].replace(/[^\d]/g,'').slice(0,2):'';this.value=sign+intPart+(dec?','+dec:'');" />
            </div>

            <div class="w-full sm:w-20">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Piutang</label>
                {{-- PIUTANG --}}
                @if ($selectedKategori === 'KONTRAKAN')
                    {{-- readonly & auto dari nilai_sewa kontrakan --}}
                    <input x-ref="piutang" @keydown.enter.prevent="next($event.target)"
                        type="text"
                        value="{{ $piutang !== null ? number_format($piutang, 0, ',', '.') : '' }}"
                        readonly
                        class="w-full px-3 py-2 border rounded text-right bg-gray-100 dark:bg-zinc-900 dark:border-zinc-600" />
                @else
                    {{-- kategori lain tetap editable --}}
                    <input x-ref="piutang"
         @keydown.enter.prevent="next($event.target)"
         type="text"
         x-on:input="
            let raw = $el.value.replace(/\./g,'').replace(/,/g,'.');
            $wire.piutang = raw; // kirim angka murni ke Livewire
            $el.value = new Intl.NumberFormat('id-ID').format(raw);
         "
         class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600
                @error('piutang') border-red-500 ring-1 ring-red-500 @enderror"
         placeholder="Piutang" />
                    @error('piutang')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                @endif

            </div>
            {{-- JUMLAH BAYAR (khusus KONTRAKAN) --}}
            @if ($selectedKategori === 'KONTRAKAN')
                <div class="w-full sm:w-40">
                    <label class="text-sm text-gray-600 dark:text-zinc-300">Jumlah Bayar</label>
                    <input @keydown.enter.prevent="next($event.target)" type="text" wire:model.lazy="jumlahBayar"
                        class="w-full px-3 py-2 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600
                  @error('jumlahBayar') border-red-500 ring-1 ring-red-500 @enderror"
                        placeholder="Jumlah Bayar"
                        oninput="let v=this.value.replace(/[^\d,.\-]/g,'');v=v.replace(/(?!^)-/g,'');v=v.replace(/\./g,'');const parts=v.split(',');let intPart=parts[0]??'';let sign='';if(intPart.startsWith('-')){sign='-';intPart=intPart.slice(1);}intPart=intPart.replace(/^0+(?=\d)/,'');intPart=intPart.replace(/\B(?=(\d{3})+(?!\d))/g,'.');const dec=parts.length>1?parts[1].replace(/[^\d]/g,'').slice(0,2):'';this.value=sign+intPart+(dec?','+dec:'');" />
                    @error('jumlahBayar')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror

                    {{-- @if ($piutang !== null && $jumlahBayar !== null && $jumlahBayar !== '')
                        @php
                            $jb = (float) str_replace([',', '.'], ['.', ''], str_replace('.', '', $jumlahBayar)); // tampilan saja—opsional
                            $sis = max(0, (float) $piutang - $jb);
                        @endphp
                        <p class="text-[11px] mt-1 text-gray-500">Sisa: {{ number_format($sis, 0, ',', '.') }}</p>
                    @endif --}}
                </div>
            @endif
            <div class="w-full sm:flex-1">
                <label class="text-sm text-gray-600 dark:text-zinc-300">Keterangan</label>
                <input x-ref="keterangan" @keydown.enter.prevent="next($event.target)" type="text"
                    wire:model="keterangan"
                    class="w-full px-3 py-2 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                    placeholder="Keterangan" />
                @error('keterangan')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
            </div>


            <div>
                <button x-ref="tambah" type="button" @click="$wire.submit()" {{-- klik tetap langsung simpan --}}
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow">
                    Tambah
                </button>
            </div>
        </div>

        {{-- Riwayat Biaya --}}
        <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
            <h4 class="text-sm font-semibold mb-2">Riwayat Biaya</h4>

            @if ($riwayatTambahan->isNotEmpty())
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full text-sm border border-gray-200 dark:border-zinc-700 text-[11px] font-mono">
                        <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="px-2 py-1 border">Tanggal</th>
                                <th class="px-2 py-1 border">Toko</th>
                                <th class="px-2 py-1 border">Kategori</th>
                                <th class="px-2 py-1 border text-right">Qty</th>
                                <th class="px-2 py-1 border text-right">Piutang</th>
                                <th class="px-2 py-1 border">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayatTambahan as $row)
                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                    <td class="px-2 py-1 border">
                                        {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                                    <td class="px-2 py-1 border">{{ $row->toko->nmtoko ?? '-' }}</td>
                                    <td class="px-2 py-1 border">{{ $row->kategori }}</td>
                                    <td class="px-2 py-1 border text-right">{{ number_format($row->qty, 0) }}</td>
                                    <td class="px-2 py-1 border text-right">
                                        {{ number_format($row->total_piutang, 0) }}</td>
                                    <td class="px-2 py-1 border">{{ $row->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada riwayat biaya.</p>
            @endif
        </div>
    </div>
</div>
