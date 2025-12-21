<div>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    <div class="space-y-4">

        {{-- HEADER --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                        TI
                    </span>
                    Master Trend & Inflasi
                </h2>
                <span class="text-[11px] text-gray-500">Kelola per bulan (Jan–Des) per tahun</span>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs">
                    <label class="text-gray-500">Tahun Aktif</label>
                    <select wire:model.live="tahunAktif" class="border rounded-lg px-3 py-1.5 text-gray-700 text-xs">
                        @forelse($listTahun as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @empty
                            <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" wire:click="openGenerateModal"
                        class="px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px]">
                        + Tahun Baru
                    </button>
                </div>
            </div>
        </div>

        {{-- FORM ADD/EDIT (tahun ikut tahunAktif) --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <div class="text-sm font-semibold">
                        {{ $editingId ? 'Edit Bulan' : 'Tambah Bulan' }}
                        <span class="text-xs text-gray-500 font-normal">— Tahun {{ $tahunAktif }}</span>
                    </div>
                    <div class="text-[11px] text-gray-500">Nilai minus otomatis tampil merah.</div>
                </div>

                @if ($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="px-3 py-1.5 rounded bg-gray-200 hover:bg-gray-300 text-[11px]">
                        Batal
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block mb-1 text-gray-500">Bulan</label>
                    <select wire:model.defer="bulan" class="w-full border rounded-lg px-2 py-1.5 text-gray-700">
                        @foreach (\App\Models\operasional\MasterTrendInflasi::BULAN_MAP as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('bulan')
                        <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-gray-500">Trend (%)</label>
                    <input type="number" step="0.01" wire:model.defer="trend"
                        class="w-full border rounded-lg px-2 py-1.5 text-gray-700">
                    @error('trend')
                        <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block mb-1 text-gray-500">Inflasi (%)</label>
                    <input type="number" step="0.01" wire:model.defer="inflasi"
                        class="w-full border rounded-lg px-2 py-1.5 text-gray-700">
                    @error('inflasi')
                        <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex items-end justify-end gap-2">
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                        class="relative px-3 py-1.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            Simpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>


        {{-- TABLE --}}
        <div class="relative bg-white rounded-lg shadow border border-gray-200 overflow-x-auto text-black">

            {{-- LOADING OVERLAY (opsional, enak dipakai) --}}
            <div wire:loading wire:target="save,delete,generateTahun,tahunAktif"
                class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-2 text-xs text-gray-700">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" fill="none" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    <span>Memuat data…</span>
                </div>
            </div>

            <div class="px-4 py-3 border-b border-gray-200">
                <div class="text-sm font-semibold">Data Tahun {{ $tahunAktif }}</div>
            </div>

            <table class="min-w-[800px] w-full text-xs text-left">
                <thead class="text-[11px] uppercase text-gray-600">
                    <tr class="border-b">
                        <th class="px-3 py-2 bg-gray-50 border-r">Bulan</th>
                        <th class="px-3 py-2 bg-gray-50 border-r text-right">Trend</th>
                        <th class="px-3 py-2 bg-gray-50 border-r text-right">Inflasi</th>
                        <th class="px-3 py-2 bg-gray-50 text-center w-40">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $r)
                        @php
                            $trend = (float) $r->trend;
                            $infl = (float) $r->inflasi;

                            // merah kalau minus, hijau kalau plus, netral kalau 0
                            $clsTrend =
                                $trend < 0
                                    ? 'text-rose-700 bg-rose-50'
                                    : ($trend > 0
                                        ? 'text-emerald-700 bg-emerald-50'
                                        : 'text-gray-600 bg-gray-50');

                            $clsInfl =
                                $infl < 0
                                    ? 'text-rose-700 bg-rose-50'
                                    : ($infl > 0
                                        ? 'text-emerald-700 bg-emerald-50'
                                        : 'text-gray-600 bg-gray-50');
                        @endphp

                        <tr wire:key="trend-inflasi-{{ $r->id }}" class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-r font-medium">{{ $r->nama_bulan }}</td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsTrend }}">
                                {{ $trend == 0 ? '-' : number_format($trend, 2, ',', '.') }}%
                            </td>

                            <td class="px-3 py-2 border-r text-right font-semibold {{ $clsInfl }}">
                                {{ $infl == 0 ? '-' : number_format($infl, 2, ',', '.') }}%
                            </td>

                            <td class="px-3 py-2 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" wire:click="startEdit({{ $r->id }})"
                                        class="px-2 py-1 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-[11px]">
                                        Edit
                                    </button>

                                    <button type="button" wire:click="delete({{ $r->id }})"
                                        class="px-2 py-1 rounded bg-rose-600 hover:bg-rose-700 text-white text-[11px]"
                                        onclick="return confirm('Hapus baris ini?')">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                Belum ada data. Klik <b>+ Tahun Baru</b> untuk generate 12 bulan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL: Generate Tahun Baru --}}
    @if ($openGenerate)
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/40" wire:click="closeGenerateModal"></div>

            <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-800">Generate Tahun Baru</div>
                        <div class="text-xs text-gray-500">Buat 12 bulan otomatis (Jan–Des)</div>
                    </div>
                    <button wire:click="closeGenerateModal" class="text-gray-500 hover:text-gray-800">
                        ✕
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 mt-4">
                    <div>
                        <label class="block text-[11px] text-gray-500 mb-1">Tahun</label>
                        <input type="number" min="2000" max="2100" wire:model.defer="tahunBaru"
                            class="w-full text-xs border rounded-lg px-3 py-2">
                        @error('tahunBaru')
                            <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] text-gray-500 mb-1">Default Inflasi (%)</label>
                        <input type="number" step="0.01" wire:model.defer="defaultInflasi"
                            class="w-full text-xs border rounded-lg px-3 py-2">
                        @error('defaultInflasi')
                            <div class="text-[11px] text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                        <div class="text-[11px] text-gray-500 mt-1">Trend akan dibuat 0 untuk semua bulan.</div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 mt-4">
                    <button wire:click="closeGenerateModal"
                        class="text-xs px-3 py-2 rounded-lg border hover:bg-gray-50">
                        Batal
                    </button>
                    <button wire:click="generateTahun"
                        class="text-xs px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                        Generate
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Toast hook (kalau belum pakai SweetAlert) --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (e) => alert(e.message));
        });
    </script>

</div>

{{-- The Master doesn't talk, he acts. --}}
</div>
