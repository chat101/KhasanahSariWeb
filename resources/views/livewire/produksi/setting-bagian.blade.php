<div class="p-4 space-y-4 bg-gray-900 text-gray-100 text-sm rounded-lg shadow-lg">
    <div x-data="{ activeTab: 'targetdivisi' }" class="w-full">
        <!-- Tab Navigation -->
        <div class="flex space-x-2 border-b border-gray-300">
            <button @click="activeTab = 'targetdivisi'"
                :class="activeTab === 'targetdivisi'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Target Divisi
            </button>

            <button @click="activeTab = 'haripersonil'"
                :class="activeTab === 'haripersonil'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Personil Harian
            </button>

            <button @click="activeTab = 'settingTong'"
                :class="activeTab === 'settingTong'
                    ?
                    'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                    'text-gray-500 hover:text-blue-600'"
                class="px-4 py-2 text-sm">
                Setting Tong
            </button>
        </div>

        <!-- Tab Content -->
        <div class="mt-4 p-4 bg-black rounded-lg shadow border">
            @livewire('produksi.setting-sub-bagian') {{-- Include the Setting Sub Bagian Livewire component --}}


            <div x-show="activeTab === 'haripersonil'" x-cloak>
                {{-- <div class="space-y-4">
                    <div>
                        <label>Divisi</label>
                        <select wire:model="msjobs_id" class="form-select">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach ($divisis as $divisi)
                                <option class="text-red-500 text-xs" value="{{ $divisi->id }}">{{ $divisi->nama_job }}
                                </option>
                            @endforeach
                        </select>
                        @error('divisi_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($jadwals as $index => $jadwal)
                            <div class="flex items-center gap-4">
                                <label class="w-20 capitalize">{{ $jadwal['hari'] }}</label>
                                <input type="number" wire:model.lazy="jadwals.{{ $index }}.jumlah"
                                    placeholder="Jumlah orang" class="form-input w-full" />
                            </div>
                            @error("jadwals.$index.jumlah")
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        @endforeach
                    </div>

                    <button wire:click="simpan" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>

                    @if (session()->has('message'))
                        <div class="text-green-600 mt-4">{{ session('message') }}</div>
                    @endif
                </div> --}}
                {{-- === RINGKASAN PERSONIL HARIAN (MIRIP GAMBAR) === --}}
                {{-- === RINGKASAN PERSONIL HARIAN (EDIT LANGSUNG) === --}}
                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full text-xs border-separate border-spacing-y-0.5">
                        <thead>
                            <tr class="bg-slate-700 text-slate-100">
                                <th class="px-3 py-2 text-left">DIVISI</th>
                                @foreach ($days as $d)
                                    <th class="px-3 py-2 text-center capitalize">
                                        {{ $d === 'jumat' ? 'Jumat' : ucfirst($d) }}
                                    </th>
                                @endforeach
                                <th class="px-3 py-2 text-center">Avg</th>
                                <th class="px-3 py-2 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $palette = [
                                    'PERSIAPAN' => 'bg-rose-200/20',
                                    'TELUR' => 'bg-green-200/20',
                                    'GILING' => 'bg-indigo-200/20',
                                    'PROD' => 'bg-amber-200/20',
                                    'DEKOR' => 'bg-cyan-200/20',
                                    'PACKING' => 'bg-cyan-200/20',
                                    'LAINNYA' => 'bg-yellow-200/20',
                                ];
                            @endphp

                            @forelse ($jobsForMatrix as $job)
                                @php
                                    $bg = 'bg-slate-800/60';
                                    foreach ($palette as $key => $cls) {
                                        if (str_contains(strtoupper($job->group_job ?? ''), $key)) {
                                            $bg = $cls;
                                            break;
                                        }
                                    }

                                    // hitung avg hanya dari hari yang > 0
                                    $vals = array_map(fn($h) => (int) ($matrixEdit[$job->id][$h] ?? 0), $days);
                                    $nonZero = array_values(array_filter($vals, fn($v) => $v > 0));
                                    $avg = count($nonZero) ? array_sum($nonZero) / count($nonZero) : 0;
                                @endphp

                                <tr class="{{ $bg }} hover:bg-slate-700/50">
                                    <td class="px-3 py-2 text-slate-100 whitespace-nowrap font-medium">
                                        {{ strtoupper($job->nama_job) }}
                                    </td>

                                    @foreach ($days as $d)
                                        <td class="px-2 py-1 text-center">
                                            <input type="number" min="0"
                                                class="w-14 text-center rounded bg-slate-900 border border-slate-600 text-slate-100 py-1"
                                                wire:model.lazy="matrixEdit.{{ $job->id }}.{{ $d }}" />
                                        </td>
                                    @endforeach

                                    <td class="px-3 py-2 text-center font-semibold text-slate-100">
                                        {{ number_format($avg, 1, ',', '.') }}
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <button wire:click="saveRow({{ $job->id }})" wire:loading.attr="disabled"
                                            class="bg-emerald-500 hover:bg-emerald-600 text-slate-900 font-semibold px-3 py-1.5 rounded">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + count($days) }}" class="px-3 py-4 text-center text-slate-400">
                                        Belum ada data divisi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            <div x-show="activeTab === 'settingTong'" x-cloak>
                <h3 class="text-lg font-semibold mb-2 text-gray-100">Satuan Giling</h3>

                <div class="p-2 text-sm rounded-lg">

                    {{-- Notification --}}
                    @if (session()->has('message'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="fixed top-4 left-1/2 -translate-x-1/2 w-80 bg-emerald-200 text-emerald-900 rounded-lg shadow-lg z-50">
                            <div class="flex items-center justify-between px-3 py-2">
                                <span class="text-xs font-medium">{{ session('message') }}</span>
                                <button @click="show = false" class="text-emerald-900 hover:text-emerald-700 text-sm"
                                    aria-label="Tutup">✕</button>
                            </div>
                        </div>
                    @endif

                    <div
                        class="relative w-full rounded-2xl overflow-hidden shadow-2xl border border-slate-700/70 bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-slate-100">

                        {{-- Glow ring seperti di gambar --}}
                        <div aria-hidden="true"
                            class="pointer-events-none absolute inset-0 -z-10 rounded-2xl
                                    bg-gradient-to-r from-emerald-400/25 via-teal-400/20 to-cyan-400/25 blur-2xl">
                        </div>

                        {{-- Header card (judul & tombol) --}}
                        <div
                            class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-700/60 bg-slate-900/60">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_12px_rgba(16,185,129,0.8)]"></span>
                                <p class="font-semibold tracking-wide text-slate-100">Form &amp; Tabel — Satuan Giling
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if (session()->has('message'))
                                    <span class="text-emerald-400 text-xs">{{ session('message') }}</span>
                                @endif
                                <button wire:click="simpanTong" wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-2 rounded-md bg-yellow-400 px-3 py-1.5 text-[13px] font-semibold text-slate-900 shadow
                                           hover:bg-yellow-300 active:bg-yellow-400/90 transition">
                                    SIMPAN
                                </button>
                            </div>
                        </div>

                        {{-- Isi card --}}
                        <div class="px-4 py-4">

                            {{-- Tabel (gaya dark seperti list pada gambar) --}}
                            <div
                                class="rounded-xl border border-slate-700/70 overflow-hidden ring-1 ring-emerald-400/20">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-slate-800/80 text-slate-200">
                                            <th class="px-3 py-2 text-left w-14 font-semibold tracking-wide">NO</th>
                                            <th class="px-3 py-2 text-left font-semibold tracking-wide">NAMA BARANG
                                            </th>
                                            <th class="px-3 py-2 text-left w-40 font-semibold tracking-wide">JENIS</th>
                                            <th class="px-3 py-2 text-center w-32 font-semibold tracking-wide">SATUAN
                                                TONG</th>
                                            <th class="px-3 py-2 text-center w-28 font-semibold tracking-wide">BESAR
                                            </th>
                                            <th class="px-3 py-2 text-center w-28 font-semibold tracking-wide">KECIL
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-700/70">
                                        @forelse ($produks as $i => $r)
                                            <tr class="bg-slate-900 hover:bg-slate-800/70 transition-colors">
                                                <td class="px-3 py-2 text-slate-200">{{ $i + 1 }}</td>
                                                <td class="px-3 py-2 text-slate-100">{{ $r['nama'] }}</td>
                                                <td class="px-3 py-2 text-slate-300">{{ $r['jenis'] }}</td>
                                                <td class="px-3 py-2 text-center text-slate-200">{{ $r['patokan'] }}
                                                </td>

                                                {{-- Checkbox BESAR --}}
                                                <td class="px-3 py-2 text-center">
                                                    <input type="checkbox"
                                                        class="h-4 w-4 align-middle rounded border-slate-600 bg-slate-900 text-emerald-400
                                                               focus:ring-emerald-500 focus:ring-offset-0"
                                                        wire:model.live="pilih.{{ $r['id'] }}.besar">
                                                </td>

                                                {{-- Checkbox KECIL --}}
                                                <td class="px-3 py-2 text-center">
                                                    <input type="checkbox"
                                                        class="h-4 w-4 align-middle rounded border-slate-600 bg-slate-900 text-emerald-400
                                                               focus:ring-emerald-500 focus:ring-offset-0"
                                                        wire:model.live="pilih.{{ $r['id'] }}.kecil">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="text-center text-slate-400 py-6 bg-slate-900">
                                                    Tidak ada data.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
