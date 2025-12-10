<div>
    <div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

            {{-- HEADER + INFO (compact) --}}
            <div
                class="px-3 py-2 border-b border-gray-200 dark:border-zinc-700
                        flex flex-col md:flex-row md:items-center md:justify-between gap-2">

                <div>
                    <h2 class="text-sm font-semibold flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24"
                             fill="currentColor">
                            <path
                                d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
                        </svg>
                        <span>Input Jurnal Umum</span>
                    </h2>
                    <p class="text-[11px] text-gray-500 dark:text-zinc-400 mt-0.5">
                        Gunakan form ini untuk mencatat transaksi manual (biaya, pendapatan, mutasi kas).
                    </p>
                </div>

                <div class="flex items-center gap-2 text-[11px] text-gray-500 dark:text-zinc-400">
                    <div class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Debet harus sama dengan Kredit</span>
                    </div>
                </div>
            </div>

            {{-- BODY FORM --}}
            <div class="p-3 space-y-3">

                {{-- HEADER FORM --}}
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 p-3 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                            <input type="date" wire:model="tanggal"
                                   class="w-full border rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-900">
                            @error('tanggal')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">No Bukti</label>
                            <input type="text" wire:model="no_bukti"
                                   class="w-full border rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-900">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                            <input type="text" wire:model="keterangan"
                                   class="w-full border rounded-lg px-3 py-2 text-sm bg-white dark:bg-zinc-900">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Jenis Transaksi</label>
                            <select wire:model="transaction_type_id"
                                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                <option value="">Pilih jenis...</option>
                                @foreach($transactionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->nama }}</option>
                                @endforeach
                            </select>
                            @error('transaction_type_id')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- input nominal --}}
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Nominal</label>
                            <input type="number" wire:model="nominal"
                                   class="w-full border rounded-lg px-3 py-2 text-sm text-right bg-white dark:bg-zinc-900">
                            @error('nominal')
                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- field dinamis tergantung jenis --}}
                        @if($currentType && $currentType->code === 'biaya')
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Akun Biaya</label>
                                <select wire:model="akun_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih akun biaya...</option>
                                    @foreach($coasBiaya as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('akun_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kas</label>
                                <select wire:model="kas_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih kas...</option>
                                    @foreach($coasKas as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kas_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif($currentType && $currentType->code === 'pendapatan')
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Akun Pendapatan</label>
                                <select wire:model="akun_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih akun pendapatan...</option>
                                    @foreach($coasPendapatan as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('akun_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kas</label>
                                <select wire:model="kas_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih kas...</option>
                                    @foreach($coasKas as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kas_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif($currentType && $currentType->code === 'mutasi_kas')
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kas Asal</label>
                                <select wire:model="kas_asal_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih kas asal...</option>
                                    @foreach($coasKas as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kas_asal_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Kas Tujuan</label>
                                <select wire:model="kas_tujuan_id"
                                        class="w-full border rounded-lg px-3 py-2 text-sm bg-white text-black dark:bg-zinc-900 dark:text-white">
                                    <option value="">Pilih kas tujuan...</option>
                                    @foreach($coasKas as $coa)
                                        <option value="{{ $coa->id }}">
                                            {{ $coa->kode }} - {{ $coa->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kas_tujuan_id')
                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TABEL DEBET/KREDIT (HASIL OTOMATIS, BISA DIEDIT) --}}
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 p-3 relative">

                    {{-- LOADING OVERLAY KHUSUS GENERATE LINES / PERUBAHAN FIELD UTAMA --}}
                    <div wire:loading.delay
                         wire:target="generateLines, transaction_type_id, akun_id, kas_id, kas_asal_id, kas_tujuan_id, nominal"
                         class="absolute inset-0 bg-white/80 dark:bg-black/40 backdrop-blur-sm flex items-center justify-center z-20 rounded-lg">
                        <div class="flex flex-col items-center gap-2 animate-fade-in">
                            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-30" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <span class="text-xs text-gray-700 dark:text-gray-200 font-medium">
                                Menyusun baris jurnal...
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold flex items-center gap-1.5">
                            <span>Detail Jurnal</span>

                            {{-- mini loading di judul --}}
                            <svg wire:loading
                                 wire:target="generateLines, transaction_type_id, akun_id, kas_id, kas_asal_id, kas_tujuan_id, nominal"
                                 class="h-3 w-3 text-indigo-500 animate-spin"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-30" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>

                            @if(count($lines) > 0)
                                @php
                                    $totalDebet  = collect($lines)->sum('debet');
                                    $totalKredit = collect($lines)->sum('kredit');
                                @endphp
                                <span class="text-[11px]
                                    @if($totalDebet == $totalKredit) text-emerald-600
                                    @else text-red-600 @endif">
                                    (D: {{ number_format($totalDebet,0,',','.') }} /
                                     K: {{ number_format($totalKredit,0,',','.') }})
                                </span>
                            @endif
                        </h3>
                        <button type="button"
                                wire:click="generateLines"
                                wire:loading.attr="disabled"
                                wire:target="generateLines"
                                class="px-3 py-1 rounded bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-xs flex items-center gap-1">
                            <span wire:loading.remove wire:target="generateLines">Generate Ulang</span>
                            <svg wire:loading wire:target="generateLines"
                                 class="h-3 w-3 animate-spin text-gray-600 dark:text-gray-200"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-30" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                        </button>
                    </div>

                    @error('lines')
                    <div class="text-xs text-red-600 mb-2">{{ $message }}</div>
                    @enderror

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left w-8">#</th>
                                    <th class="px-3 py-2 text-left">COA</th>
                                    <th class="px-3 py-2 text-right w-32">Debet</th>
                                    <th class="px-3 py-2 text-right w-32">Kredit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                                @forelse($lines as $index => $line)
                                    <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                        <td class="px-3 py-1 align-middle">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-3 py-1">
                                            <select wire:model="lines.{{ $index }}.coa_id"
                                                    class="w-full border rounded-lg px-2 py-1 text-xs bg-white text-black dark:bg-zinc-900 dark:text-white">
                                                <option value="">Pilih COA...</option>
                                                @foreach($allCoas as $coa)
                                                    <option value="{{ $coa->id }}">
                                                        {{ $coa->kode }} - {{ $coa->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("lines.$index.coa_id")
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="px-3 py-1 text-right">
                                            <input type="number" step="0.01"
                                                   wire:model="lines.{{ $index }}.debet"
                                                   class="w-full border rounded-lg px-2 py-1 text-xs text-right bg-white dark:bg-zinc-900">
                                        </td>
                                        <td class="px-3 py-1 text-right">
                                            <input type="number" step="0.01"
                                                   wire:model="lines.{{ $index }}.kredit"
                                                   class="w-full border rounded-lg px-2 py-1 text-xs text-right bg-white dark:bg-zinc-900">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-3 text-center text-gray-500 dark:text-zinc-400">
                                            Baris jurnal belum terbentuk. Pilih jenis transaksi, akun, kas, dan nominal.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button"
                                wire:click="save"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm flex items-center gap-2">
                            <span wire:loading.remove wire:target="save">
                                Simpan Jurnal
                            </span>
                            <svg wire:loading wire:target="save"
                                 class="animate-spin h-4 w-4 text-white" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-30" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4" />
                                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NOTIF SWAL --}}
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

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.25s ease-out both;
        }
    </style>
</div>
