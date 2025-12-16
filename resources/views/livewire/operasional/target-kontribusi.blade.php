<div>
    <div class="space-y-4 text-black">

        {{-- Notifikasi --}}
        @if (session()->has('message'))
            <div x-data="{show:true}" x-init="setTimeout(()=>show=false,2500)" x-show="show"
                 class="fixed top-6 left-1/2 -translate-x-1/2 z-50 bg-emerald-500 text-white px-4 py-2 rounded-lg shadow text-[12px]">
                {{ session('message') }}
            </div>
        @endif

        {{-- HEADER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold flex items-center gap-2">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                        MT
                    </span>
                    Master Target Kontribusi
                </h2>
                <span class="text-[11px] text-gray-500">Set target (%) atau Rupiah untuk perhitungan laporan</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-xs">
                <div class="md:col-span-2">
                    <label class="block mb-1 text-gray-500">Cari (kode / nama)</label>
                    <input type="text" wire:model.live="search"
                           class="w-full border rounded-lg px-2 py-1"
                           placeholder="cth: GAS / RETUR / Diskon...">
                </div>

                <div class="md:col-span-3 flex items-end justify-end gap-2">
                    <button wire:click="openCreate"
                            class="px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px]">
                        + Tambah Target
                    </button>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
            <table class="min-w-full text-xs text-left">
                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500 border-b">
                    <tr>
                        <th class="px-3 py-2">Kode</th>
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2 text-center">Tipe</th>
                        <th class="px-3 py-2 text-right">Nilai</th>
                        <th class="px-3 py-2 text-center">Aktif</th>
                        <th class="px-3 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium">{{ $r->kode }}</td>
                            <td class="px-3 py-2">{{ $r->nama }}</td>

                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-[10px]
                                    {{ $r->tipe === 'PERSEN' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-amber-50 text-amber-800 border border-amber-100' }}">
                                    {{ $r->tipe }}
                                </span>
                            </td>

                            <td class="px-3 py-2 text-right font-semibold">
                                @if($r->tipe === 'PERSEN')
                                    {{ number_format($r->nilai, 2, ',', '.') }}%
                                @else
                                    {{ number_format($r->nilai, 0, ',', '.') }}
                                @endif
                            </td>

                            <td class="px-3 py-2 text-center">
                                <button wire:click="toggleAktif({{ $r->id }})"
                                        class="inline-flex px-2 py-0.5 rounded-full text-[10px]
                                        {{ $r->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $r->aktif ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>

                            <td class="px-3 py-2 text-center space-x-1">
                                <button wire:click="openEdit({{ $r->id }})"
                                        class="px-2 py-1 rounded bg-indigo-500 hover:bg-indigo-600 text-white text-[11px]">
                                    Edit
                                </button>
                                <button
                                    onclick="if(confirm('Hapus target ini?')) @this.delete({{ $r->id }})"
                                    class="px-2 py-1 rounded bg-red-500 hover:bg-red-600 text-white text-[11px]">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                Belum ada data target.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MODAL --}}
        <div x-data="{ open: @entangle('showModal') }" x-show="open"
             class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-sm bg-black/20">

            <div class="w-full max-w-lg bg-white rounded-lg shadow-lg border border-gray-200 p-4 text-sm text-gray-700"
                 @click.outside="open=false">

                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold">
                        {{ $editId ? 'Edit Target' : 'Tambah Target' }}
                    </h3>
                    <button @click="open=false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block text-gray-500 mb-1">Kode</label>
                        <input type="text" wire:model="kode"
                               class="w-full border rounded-lg px-2 py-1"
                               placeholder="DISC_MANUAL">
                        @error('kode') <div class="text-red-600 text-[11px] mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 mb-1">Nama</label>
                        <input type="text" wire:model="nama"
                               class="w-full border rounded-lg px-2 py-1"
                               placeholder="Diskon Manual">
                        @error('nama') <div class="text-red-600 text-[11px] mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 mb-1">Tipe</label>
                        <select wire:model.live="tipe" class="w-full border rounded-lg px-2 py-1">
                            <option value="PERSEN">PERSEN</option>
                            <option value="RUPIAH">RUPIAH</option>
                        </select>
                        @error('tipe') <div class="text-red-600 text-[11px] mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-500 mb-1">
                            Nilai {{ $tipe === 'PERSEN' ? '(%)' : '(Rp)' }}
                        </label>

                        <input type="number" step="0.01" wire:model="nilai"
                               class="w-full border rounded-lg px-2 py-1 text-right"
                               placeholder="{{ $tipe === 'PERSEN' ? '1.00' : '250000' }}">

                        @error('nilai') <div class="text-red-600 text-[11px] mt-1">{{ $message }}</div> @enderror

                        @if($tipe === 'PERSEN')
                            <div class="text-[11px] text-gray-500 mt-1">Contoh: 1.00 berarti 1%</div>
                        @else
                            <div class="text-[11px] text-gray-500 mt-1">Contoh: 250000 berarti Rp 250.000</div>
                        @endif
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2 text-xs">
                            <input type="checkbox" wire:model="aktif" class="rounded">
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="closeModal"
                            class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300 text-xs">
                        Batal
                    </button>
                    <button wire:click="save"
                            class="px-3 py-1 rounded bg-emerald-500 hover:bg-emerald-600 text-white text-xs">
                        Simpan
                    </button>
                </div>
            </div>
        </div>

    </div>
    {{-- In work, do what you enjoy. --}}
</div>
