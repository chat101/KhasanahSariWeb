<div class="space-y-4" wire:key="laporan-kontribusi-root">

    {{-- HEADER --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
        <div class="flex items-center justify-between gap-2">
            <h2 class="text-sm font-semibold flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">
                    LK
                </span>
                Laporan Kontribusi
            </h2>
            <span class="text-[11px] text-gray-500">Monitoring kontribusi</span>
        </div>

        {{-- TABS --}}
        <div class="flex flex-wrap items-center gap-2 text-[11px]">
            <button type="button"
                    wire:click="setTab('target')"
                    @class([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'target',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'target',
                    ])>
                Report by Target
            </button>

            <button type="button"
                    wire:click="setTab('bulanlalu')"
                    @class([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'bulanlalu',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'bulanlalu',
                    ])>
                By Bulan Lalu
            </button>

            <button type="button"
                    wire:click="setTab('daily')"
                    @class([
                        'px-3 py-1.5 rounded-lg border transition-colors',
                        'bg-indigo-600 text-white border-indigo-600' => $tab === 'daily',
                        'bg-gray-100 text-gray-700 hover:bg-gray-200 border-gray-200' => $tab !== 'daily',
                    ])>
                Detail Harian Area
            </button>
        </div>
    </div>

    {{-- PANEL --}}
    <div class="bg-white rounded-lg shadow border border-gray-200 p-3">
        @if ($tab === 'target')
            <livewire:operasional.kontribusi-target
                :tokos-user="$tokosUser"
                :key="'kontribusi-target'" />
        @elseif ($tab === 'bulanlalu')
            <livewire:operasional.kontribusi-bulan-lalu
                :tokos-user="$tokosUser"
                :key="'kontribusi-bulanlalu'" />
        @else
            <livewire:operasional.kontribusi-harian-area
                :tokos-user="$tokosUser"
                :key="'kontribusi-daily'" />
        @endif
    </div>

</div>
