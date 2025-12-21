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

                <div
                x-data="{
                    raw: @entangle('nominal').live,
                    display: '',
                    format(v){
                        if(v === null || v === '' || Number(v) === 0) return '';
                        return v.toString()
                            .replace(/\D/g,'')
                            .replace(/\B(?=(\d{3})+(?!\d))/g,'.');
                    },
                    sync(){
                        this.display = this.format(this.raw);
                    }
                }"
                x-init="sync()"
                x-on:loss-saved.window="
                    raw = null;
                    display = '';
                "
            >
                <label class="block text-xs text-gray-500 mb-1">Nominal Loss (Rp)</label>

                <input
                    type="text"
                    x-model="display"
                    inputmode="numeric"
                    @input="
                        raw = display.replace(/\./g,'') === '' ? null : parseInt(display.replace(/\./g,''),10);
                        display = format(raw);
                    "
                    class="w-full border rounded-lg px-3 py-2 text-right"
                    placeholder="contoh: 1.250.000"
                />
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
                            <td class="px-3 py-2 text-right font-semibold">
                                {{ number_format($row->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $row->keterangan ?? '-' }}</td>
                            <td class="px-3 py-2 text-right">
                                <button wire:click="delete({{ $row->id }})" class="text-rose-600 hover:underline">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                Belum ada data loss di periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
