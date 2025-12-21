<div>
    <div>
        <style>[x-cloak]{display:none!important}</style>

        <div data-kontribusi-tabs
             x-data="{ tab:'target', h:0, syncH(){ this.$nextTick(()=> this.h = this.$refs.panel?.scrollHeight || 0) } }"
             x-init="syncH()"
             class="space-y-4">

            {{-- HEADER --}}
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4 space-y-3 text-black">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold flex items-center gap-2">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xs">LK</span>
                        Laporan Kontribusi
                    </h2>
                    <span class="text-[11px] text-gray-500">Monitoring kontribusi</span>
                </div>

                <div class="flex items-center gap-2 text-[11px]">
                    <button type="button" @click="tab='target'; syncH()"
                            :class="tab==='target' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg border border-gray-200">
                        Report by Target
                    </button>

                    <button type="button" @click="tab='bulanlalu'; syncH()"
                            :class="tab==='bulanlalu' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg border border-gray-200">
                        By Bulan Lalu
                    </button>
                </div>
            </div>

            {{-- WRAPPER --}}
            <div class="relative">
                <div x-ref="panel">

                    {{-- TAB 1 --}}
                    <div x-cloak x-show="tab==='target'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200 absolute inset-0"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2">
                        <livewire:operasional.kontribusi-target :tokos-user="$tokosUser" />
                    </div>

                    {{-- TAB 2 --}}
                    <div x-cloak x-show="tab==='bulanlalu'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200 absolute inset-0"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2">
                        <livewire:operasional.kontribusi-bulan-lalu :tokos-user="$tokosUser" />
                    </div>

                </div>
            </div>

        </div>

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('message.processed', () => {
                    const root = document.querySelector('[data-kontribusi-tabs]');
                    if (!root) return;
                    const x = Alpine.$data(root);
                    if (x && typeof x.syncH === 'function') x.syncH();
                });
            });
        </script>
    </div>
     {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
</div>
