<div>
    <div class="space-y-4">

        {{-- HEADER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 text-black">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold">Input Loss Bahan</div>
                    <div class="text-xs text-gray-500">Catat loss per toko per tanggal (Rp)</div>
                </div>
            </div>

            {{-- FORM --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs mt-4">
                <div>
                    <label class="block mb-1 text-gray-500">Tanggal</label>
                    <input type="date" wire:model.live="tanggal" class="w-full border rounded-lg px-2 py-2">
                    @error('tanggal')
                        <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-gray-500">Toko</label>
                    <select wire:model.live="toko_id" class="w-full border rounded-lg px-2 py-2">
                        <option value="">- pilih toko aktif -</option>
                        @foreach ($this->tokos as $t)
                            <option value="{{ $t->id }}">{{ $t->nmtoko }}</option>
                        @endforeach
                    </select>
                    @error('toko_id')
                        <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- New: Nama Bahan - server-side search (show on focus, stay open while interacting) -->
                <div x-data="{ open: @entangle('openSearch'), localSearch: '' }" class="relative">
                    <label class="block mb-1 text-gray-600 font-medium">Nama Bahan</label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            wire:model.debounce.200ms="barangSearch"
                            wire:focus="$set('openSearch', true)"
                            wire:input="$set('openSearch', true)"
                            placeholder="Cari bahan..."
                            class="w-full border rounded-lg px-3 py-2 text-sm"
                            @focus="open = true"
                            @input="localSearch = $event.target.value; open = true"
                            autocomplete="off"
                        />

                        @if($this->barang_id)
                            <button type="button" wire:click="clearSelectedBarang" class="text-sm text-rose-600">Batal</button>
                        @endif
                    </div>

                    {{-- hasil pencarian (dropdown) --}}
                    @if($openSearch)
                        <ul class="absolute z-50 bg-white border rounded shadow mt-1 max-h-60 overflow-auto w-full text-sm" @click.away="open = false">
                            @foreach($this->searchBarangs as $s)
                                <li wire:key="barang-{{ $s->id }}"
                                    @mousedown.prevent="$wire.selectBarang({{ $s->id }})"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer flex justify-between items-center">
                                    <div class="truncate">{{ $s->nmbarang }}</div>
                                    <div class="text-gray-500 text-xs ml-3">{{ number_format($s->harga ?? 0,0,',','.') }}</div>
                                </li>
                            @endforeach
                            @if($this->searchBarangs->isEmpty())
                                <li class="px-3 py-2 text-gray-500">Tidak ada hasil</li>
                            @endif
                        </ul>
                    @endif

                    @error('barang_id')
                        <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
                    @enderror

                    {{-- selected barang summary --}}
                    {{-- @if($this->selectedBarang)
                        <div class="mt-2 flex items-center justify-between bg-gray-50 border border-gray-100 rounded px-3 py-2 text-sm">
                            <div>
                                <div class="font-medium">{{ $this->selectedBarang->nmbarang }}</div>
                                <div class="text-xs text-gray-500">Harga: Rp {{ number_format($this->selectedBarang->harga ?? 0,0,',','.') }} / unit</div>
                            </div>
                            <div class="text-right">
                                <button type="button" wire:click="clearSelectedBarang" class="text-xs text-rose-600">Ganti</button>
                            </div>
                        </div>
                    @endif --}}
                </div>

                <!-- Qty + stepper with client-side nominal calc -->
                <div>
                    <label class="block mb-1 text-gray-600 font-medium">Qty</label>
                    <div x-data="{
                        raw: @entangle('qty').live,
                        price: {{ $this->selectedBarang->harga ?? 0 }},
                        display: '',
                        format(v){
                            if(v === null || v === '' || Number(v) === 0) return '';
                            return v.toString().replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
                        },
                        sync(){
                            this.display = this.format(this.raw);
                        },
                        inc(){
                            this.raw = (this.raw || 0) + 1; this.sync(); this.updateNominal(); $wire.incrementQty();
                        },
                        dec(){
                            this.raw = Math.max(1, (this.raw || 1) - 1); this.sync(); this.updateNominal(); $wire.decrementQty();
                        },
                        updateNominal(){
                            const n = (this.raw || 0) * (this.price || 0);
                            // push to Livewire nominal immediately for UI sync
                            $wire.set('nominal', n);
                        }
                    }" x-init="sync()" x-effect="sync()">
                        <div class="flex items-center gap-2">
                            <button type="button" @click.prevent="dec()" class="px-2 py-1 bg-gray-100 rounded text-sm">-</button>
                            <input type="text" x-model="display" inputmode="numeric" @input="raw = display.replace(/\./g,'') === '' ? null : parseInt(display.replace(/\./g,''),10); display = format(raw); updateNominal(); $wire.set('qty', raw)"
                                class="w-full border rounded-lg px-3 py-2 text-right text-sm" placeholder="0" />
                            <button type="button" @click.prevent="inc()" class="px-2 py-1 bg-gray-100 rounded text-sm">+</button>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">Total unit × harga akan dihitung otomatis</div>
                    </div>
                    @error('qty')
                        <div class="text-[11px] text-rose-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div
                    x-data="{
                        raw: @entangle('nominal').live,
                        display: '',
                        format(v){
                            if(v === null || v === '' || Number(v) === 0) return '';
                            return v.toString().replace(/\D/g,'').replace(/\B(?=(\d{3})+(?!\d))/g,'.');
                        },
                        sync(){ this.display = this.format(this.raw); }
                    }"
                    x-init="sync()" x-effect="sync()"
                    x-on:loss-saved.window=" raw = null; display = ''; "
                >
                    <label class="block mb-1 text-gray-600 font-medium">Nominal Loss (Rp)</label>
                    <input type="text" x-model="display" inputmode="numeric" readonly class="w-full border rounded-lg px-3 py-2 text-right text-sm bg-gray-50" />
                </div>

                <div>
                    <label class="block mb-1 text-gray-500">Keterangan</label>
                    <input type="text" wire:model.live="keterangan" class="w-full border rounded-lg px-2 py-2"
                        placeholder="opsional">
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button wire:click="save"
                    class="px-3 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-xs">
                    Simpan
                </button>
            </div>
        </div>

        {{-- LIST --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">
            <div class="p-4 border-b text-xs grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="block mb-1 text-gray-500">Filter Periode</label>
                    <div class="flex gap-2 items-center">
                        <input type="date" wire:model.live="periodeAwal" class="w-full border rounded-lg px-2 py-2">
                        <span class="text-gray-400 text-[11px]">sd</span>
                        <input type="date" wire:model.live="periodeAkhir" class="w-full border rounded-lg px-2 py-2">
                    </div>
                </div>
            </div>

            <table class="min-w-[900px] w-full text-xs text-left">
                <thead class="text-[11px] uppercase text-gray-600">
                    <tr class="border-b bg-gray-50">
                        <th class="px-3 py-2">Tanggal</th>
                        <th class="px-3 py-2">Toko</th>
                        <th class="px-3 py-2">Barang</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2 text-right">Nominal</th>
                        <th class="px-3 py-2">Ket</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">{{ optional($row->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-medium">{{ $row->toko->nmtoko ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $row->barang->nmbarang ?? '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row->qty ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right font-semibold">{{ number_format($row->nominal, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $row->keterangan ?? '-' }}</td>
                            <td class="px-3 py-2 text-right">
                                <button wire:click="delete({{ $row->id }})" class="text-rose-600 hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                Belum ada data loss di periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
