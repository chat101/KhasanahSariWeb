{{-- resources/views/livewire/complaints/form-table.blade.php --}}
<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
        </svg>
        <h2 class="text-base font-semibold">Form & Tabel Complain</h2>
    </div>

    <div class="p-4 space-y-6">

        {{-- FORM INPUT --}}
        <form wire:submit.prevent="save" class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <!-- Wrapper frame/glow: full width, glow terlihat -->
            <div class="relative my-2 md:col-span-12 overflow-visible z-0">
                {{-- GLow di belakang kartu (tapi di atas background parent) --}}
                <div aria-hidden="true"
                    class="absolute inset-0 -m-2 rounded-2xl
                bg-gradient-to-r from-indigo-500/60 via-sky-500/60 to-emerald-500/60
                blur-xl saturate-150 opacity-80">
                </div>

                {{-- Kartu konten --}}
                <div
                    class="relative z-10 rounded-2xl border border-gray-200 dark:border-zinc-700
                bg-white/90 dark:bg-zinc-900/70 backdrop-blur
                p-3 md:p-4 space-y-4 ring-1 ring-black/5 dark:ring-white/5 shadow-2xl shadow-indigo-500/10">

                    {{-- ====== BARIS: Tanggal, Toko, Keterangan, Kesalahan ====== --}}
                    <div class="md:col-span-12">
                        <div class="flex flex-col md:flex-row gap-3">
                            <div class="min-w-0 md:basis-48">
                                <label for="tgl"
                                    class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">TANGGAL</label>
                                <input type="date" id="tgl" wire:model="tgl"
                                    class="h-11 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-indigo-200 focus:outline-none text-sm dark:bg-zinc-800 dark:text-zinc-100" />
                                @error('tgl')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex-1 min-w-0">
                                <label
                                    class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">{{ $label }}</label>
                                <select wire:model.live="tokos_id" @disabled($disabled)
                                    class="h-11 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-indigo-200 focus:outline-none text-sm bg-white dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">{{ $placeholder }}</option>
                                    @foreach ($options as $opt)
                                        <option value="{{ $opt['id'] }}">{{ $opt['text'] }}</option>
                                    @endforeach
                                </select>
                                @error('tokos_id')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex-1 min-w-0">
                                <label for="keterangan"
                                    class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">KETERANGAN</label>
                                <select id="keterangan" wire:model="keterangan"
                                    class="h-11 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-indigo-200 focus:outline-none text-sm bg-white dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">— pilih —</option>
                                    @foreach ($keteranganOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('keterangan')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex-1 min-w-0">
                                <label for="kesalahan"
                                    class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">KESALAHAN</label>
                                <select id="kesalahan" wire:model="kesalahan"
                                    class="h-11 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-indigo-200 focus:outline-none text-sm bg-white dark:bg-zinc-800 dark:text-zinc-100">
                                    <option value="">— pilih —</option>
                                    @foreach ($kesalahanOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @error('kesalahan')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ====== BARIS: Complain + Simpan ====== --}}
                    <div class="md:col-span-12">
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label for="complain"
                                    class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">COMPLAIN</label>
                                <input id="complain" wire:model.live="complain"
                                    placeholder="Ketik keterangan complain…"
                                    class="h-11 w-full px-3 py-2 rounded-md border border-gray-300 dark:border-zinc-600 focus:ring focus:ring-indigo-200 focus:outline-none text-sm dark:bg-zinc-800 dark:text-zinc-100" />
                                @error('complain')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                class="h-12 shrink-0 px-4 md:px-5 rounded-md bg-yellow-300 hover:bg-yellow-400 text-gray-900 font-semibold shadow text-sm transition">
                                SIMPAN
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </form>

        {{-- TOAST sederhana --}}
        <div x-data="{ open: false }" x-init="Livewire.on('saved', () => {
            open = true;
            setTimeout(() => open = false, 1800)
        })">
            <div x-show="open" x-transition
                class="rounded-md bg-emerald-500 text-white px-3 py-1.5 inline-block shadow text-xs">
                Complain tersimpan.
            </div>
        </div>
        {{-- BAR FILTER TANGGAL --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-end gap-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">Dari</label>
                    <input type="date" wire:model.live="dateStart"
                        class="h-9 px-2 rounded-md border border-gray-300 dark:border-zinc-600
                      focus:ring focus:ring-indigo-200 focus:outline-none text-sm
                      bg-white dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-zinc-300 mb-1">Sampai</label>
                    <input type="date" wire:model.live="dateEnd"
                        class="h-9 px-2 rounded-md border border-gray-300 dark:border-zinc-600
                      focus:ring focus:ring-indigo-200 focus:outline-none text-sm
                      bg-white dark:bg-zinc-800 dark:text-zinc-100">
                </div>

                <button type="button" wire:click="$set('dateStart', null); $set('dateEnd', null)"
                    class="h-9 px-3 rounded-md bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600
                     text-gray-800 dark:text-zinc-100 text-xs font-medium">
                    Reset
                </button>
                <button wire:click="exportXlsx"
                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-zinc-700">
                Excel (.xlsx)
              </button>
            </div>
        </div>

        {{-- TABEL DATA --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300">
                    <tr class="text-left">
                        <th class="px-3 py-2">TGL</th>
                        <th class="px-3 py-2">NAMA TOKO</th>
                        <th class="px-3 py-2">COMPLAIN</th>
                        <th class="px-3 py-2">KETERANGAN</th>
                        <th class="px-3 py-2">KESALAHAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-zinc-700 text-gray-700 dark:text-zinc-200">
                    @forelse($rows as $r)
                        <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                            <td class="px-3 py-2 whitespace-nowrap">{{ optional($r->tgl)->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">{{ $r->toko->nmtoko }}</td>
                            <td class="px-3 py-2">{{ $r->complain }}</td>
                            <td class="px-3 py-2">{{ $r->keterangan }}</td>
                            <td class="px-3 py-2">{{ $r->kesalahan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Belum ada
                                data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $rows->links() }}
        </div>

    </div>
</div>
