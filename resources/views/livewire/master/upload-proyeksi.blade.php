<div class="space-y-4 text-black">
    <style>[x-cloak]{display:none!important}</style>

    {{-- CARD: UPLOAD (COMPACT ERP) --}}
    <div
        x-data="{ uploading:false, progress:0 }"
        x-on:livewire-upload-start="uploading=true; progress=0"
        x-on:livewire-upload-progress="progress=$event.detail.progress"
        x-on:livewire-upload-finish="uploading=false"
        x-on:livewire-upload-error="uploading=false"
        class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3"
    >

        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold flex items-center gap-2 text-gray-800">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                    MP
                </span>
                Upload Master Proyeksi Kontribusi
            </h2>

            <div wire:loading class="text-[11px] text-gray-500">
                Memproses...
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
            {{-- FILE --}}
            <div class="md:col-span-7">
                <label class="block mb-1 text-gray-500">File Excel</label>
                <input type="file"
                       wire:model="file"
                       accept=".xlsx,.xls"
                       class="w-full border rounded-lg px-2 py-1.5 bg-white">

                @error('file')
                    <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                @enderror

                @if($file)
                    <div class="text-[11px] text-gray-500 mt-1">
                        File: <span class="font-medium text-gray-700">{{ $file->getClientOriginalName() }}</span>
                    </div>
                @endif

                {{-- Progress upload --}}
                <div x-show="uploading" x-cloak class="mt-2 space-y-1">
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span>Mengupload...</span>
                        <span x-text="progress + '%'"></span>
                    </div>

                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-2 bg-indigo-600 transition-all" :style="`width:${progress}%`"></div>
                    </div>
                </div>
            </div>

            {{-- ACTION --}}
            <div class="md:col-span-5 flex items-end justify-end gap-2">
                <button type="button"
                        wire:click="import"
                        wire:loading.attr="disabled"
                        wire:target="import,file"
                        class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px]
                               disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="import">Import</span>
                    <span wire:loading wire:target="import" class="inline-flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Import...
                    </span>
                </button>
            </div>
        </div>

        {{-- Error per baris --}}
        @if(is_array($errorsImport) && count($errorsImport) > 0)
            <div class="text-xs bg-red-50 border border-red-200 rounded p-2">
                <div class="font-semibold text-red-700 mb-1">Ada data yang gagal diimport:</div>
                <ul class="list-disc ml-4 space-y-1 text-red-700">
                    @foreach($errorsImport as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Success --}}
        @if(session()->has('success'))
            <div class="text-xs bg-green-50 border border-green-200 rounded p-2 text-green-700">
                {{ session('success') }}
            </div>
        @endif
    </div>

    {{-- Integrated Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-1">
        <div class="flex items-center gap-2 w-full md:w-1/2">
            <div class="relative w-full">
                <input wire:model.live="search" class="py-1 pl-8 pr-8 border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-300 bg-white text-xs w-full" type="search" placeholder="Cari batch / toko..." />
                <span class="absolute left-2 top-1.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" /></svg>
                </span>
                @if($search)
                <button wire:click="$set('search','')" class="absolute right-2 top-1.5 text-gray-400 hover:text-gray-600" title="Clear">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 justify-end w-full md:w-auto">
            <button wire:click="import"
                class="flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white py-1 px-4 text-xs rounded-lg shadow transition">
                ⬆️ Import
            </button>
        </div>
    </div>

    {{-- CARD: TABEL BATCH TERAKHIR --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div class="text-sm font-semibold text-gray-700">
                Batch Terakhir: <span class="font-mono text-[12px]">{{ $lastBatchId ?? '-' }}</span>
            </div>

            {{-- SUMMARY --}}
            <div class="flex items-center gap-2 text-[11px]">
                <span class="px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                    Total Qty: <b>{{ number_format($sumQty ?? 0, 0, ',', '.') }}</b>
                </span>
                <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Total Rp: <b>{{ number_format($sumRupiah ?? 0, 0, ',', '.') }}</b>
                </span>
                <span class="px-2 py-1 rounded-full bg-gray-50 text-gray-600 border border-gray-200">
                    Baris: <b>{{ is_countable($latestRows) ? count($latestRows) : 0 }}</b>
                </span>
            </div>
        </div>

        @if($lastBatchId && $pivot && count($pivot) > 0)
            <div class="overflow-x-auto">
            <table class="min-w-max w-full text-xs text-gray-700">
                <thead class="text-[11px] uppercase text-gray-600">
                    <tr class="border-b">
                        <th rowspan="2" class="px-3 py-2 bg-gray-50 border-r text-left">No</th>
                        <th rowspan="2" class="px-3 py-2 bg-gray-50 border-r text-left">Toko</th>

                        @foreach($dates as $tgl)
                            <th colspan="2" class="px-3 py-2 text-center bg-amber-200 border-r border-amber-300">
                                {{ $tgl }}
                            </th>
                        @endforeach
                    </tr>
                    <tr class="border-b">
                        @foreach($dates as $tgl)
                            <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Qty</th>
                            <th class="px-3 py-2 text-center bg-amber-100 border-r border-amber-200">Rp</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($pivot as $toko => $rows)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-r">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2 border-r font-medium truncate">{{ $toko }}</td>

                            @foreach($dates as $tgl)
                                <td class="px-3 py-2 border-r text-right">{{ $rows[$tgl]['qty'] ?? '-' }}</td>
                                <td class="px-3 py-2 border-r text-right">{{ isset($rows[$tgl]) ? number_format($rows[$tgl]['rupiah'],0,',','.') : '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @else
            <div class="px-4 py-8 text-center text-sm text-gray-500">
                <div class="text-lg font-medium">Belum ada data proyeksi</div>
                <div class="text-xs mt-2">Silakan import file proyeksi untuk menambah data.</div>
            </div>
        @endif
    </div>
</div>
