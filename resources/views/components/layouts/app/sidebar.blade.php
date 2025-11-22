<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    @livewireStyles

</head>

<body class="min-h-screen bg-dark white:bg-zinc-800  font-mono">
    <flux:sidebar sticky stashable
        class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 shadow-lg text-xs">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
        <flux:navlist variant="outline">
            <flux:navlist.group : class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                    {{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>
            {{-- Grup Master --}}
            @menugroup('MasterData')
            <flux:navlist.group expandable heading="Master Data" class="grid">
              @menuitem('masterdata', 'muser')
                <flux:navlist.item icon="home" :href="route('muser')" :current="request()->routeIs('muser')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                  {{ __('Master User') }}
                </flux:navlist.item>
              @endmenuitem

              @menuitem('masterdata', 'mtoko')
                <flux:navlist.item icon="home" :href="route('mtoko')" :current="request()->routeIs('mtoko')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                  {{ __('Master Toko') }}
                </flux:navlist.item>
              @endmenuitem

              @menuitem('masterdata', 'msupplier')
                <flux:navlist.item icon="home" :href="route('msupplier')" :current="request()->routeIs('msupplier')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                  {{ __('Master Supplier') }}
                </flux:navlist.item>
              @endmenuitem

              @menuitem('masterdata', 'mbarang')
                <flux:navlist.item icon="home" :href="route('mbarang')" :current="request()->routeIs('mbarang')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                  {{ __('Master Barang') }}
                </flux:navlist.item>
              @endmenuitem

              @menuitem('masterdata', 'mproduk')
                <flux:navlist.item icon="home" :href="route('mproduk')" :current="request()->routeIs('mproduk')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                  {{ __('Master Produk') }}
                </flux:navlist.item>
              @endmenuitem
              @menuitem('masterdata', 'slides.manage')
              <flux:navlist.item icon="home" :href="route('slides.manage')" :current="request()->routeIs('slides.manage')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                {{ __('Master Slide') }}
              </flux:navlist.item>
              @endmenuitem
              @menuitem('masterdata', 'mesintoproduk')
              <flux:navlist.item icon="home" :href="route('mesintoproduk')" :current="request()->routeIs('mesintoproduk')" wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                {{ __('Setting Produk Ke Mesin') }}
              </flux:navlist.item>
              @endmenuitem
            </flux:navlist.group>
          @endmenugroup
            {{-- Grup Gudang --}}
            @menugroup('Gudang')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="Gudang"
                    class="grid">
                    <flux:navlist.item icon="home" :href="route('brgmsk')" :current="request()->routeIs('brgmsk')"
                        wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Input Barang Masuk') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="home" :href="route('rekapbrgmsk')"
                        :current="request()->routeIs('rekapbrgmsk')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Rekap Hasil Input') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endmenugroup
            @menugroup('Purchasing')
                {{-- Grup Purchasing --}}
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="Purchasing"
                    class="grid">
                    <flux:navlist.item icon="home" :href="route('listsuppmasuk')"
                        :current="request()->routeIs('listsuppmasuk')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Supplier Masuk') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            @endmenugroup
            {{-- Grup Finance --}}
            @menugroup('FINANCE')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="FINANCE"
                    class="grid">
                    @menuitem('FINANCE', 'masterrekening')
                        <flux:navlist.item icon="home" :href="route('masterrekening')"
                            :current="request()->routeIs('masterrekening')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Master Rekening') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('FINANCE', 'uangmsk')
                        <flux:navlist.item icon="home" :href="route('uangmsk')" :current="request()->routeIs('uangmsk')"
                            wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Uang Setoran Toko') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('FINANCE', 'uangmskperiode')
                        <flux:navlist.item icon="home" :href="route('uangmskperiode')"
                            :current="request()->routeIs('uangmskperiode')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Uang Masuk PerPeriode') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('FINANCE', 'biayainputpusat')
                        <flux:navlist.item icon="home" :href="route('biayainputpusat')"
                            :current="request()->routeIs('biayainputpusat')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Piutang Supplier') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('FINANCE', 'bayarpiutang')
                        <flux:navlist.item icon="home" :href="route('bayarpiutang')"
                            :current="request()->routeIs('bayarpiutang')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Bayar Piutang') }}
                        </flux:navlist.item>
                    @endmenuitem

                </flux:navlist.group>
            @endmenugroup
            {{-- Grup Produksi --}}
            @menugroup('PRODUKSI')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="PRODUKSI"
                    class="grid">
                    @menuitem('PRODUKSI', 'perproduksi')
                        <flux:navlist.item icon="home" :href="route('perproduksi')"
                            :current="request()->routeIs('perproduksi')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Perintah Produksi') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'listperproduksi')
                        <flux:navlist.item icon="home" :href="route('listperproduksi')"
                            :current="request()->routeIs('listperproduksi')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Hasil Produksi') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'setjob')
                        <flux:navlist.item icon="home" :href="route('setjob')" :current="request()->routeIs('setjob')"
                            wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Setting Bagian') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'produktifitas')
                        <flux:navlist.item icon="home" :href="route('produktifitas')"
                            :current="request()->routeIs('produktifitas')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Produktifitas') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'selesaikanjob')
                        <flux:navlist.item icon="home" :href="route('selesaikanjob')"
                            :current="request()->routeIs('selesaikanjob')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Selesai Divisi') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'work-order')
                        <flux:navlist.item icon="home" :href="route('work-order')"
                            :current="request()->routeIs('work-order')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Work Order') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'hsldivisi')
                        <flux:navlist.item icon="home" :href="route('hsldivisi')"
                            :current="request()->routeIs('hsldivisi')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Hasil PerDivisi') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('PRODUKSI', 'opnamepenyesuaian')
                        <flux:navlist.item icon="home" :href="route('opnamepenyesuaian')"
                            :current="request()->routeIs('opnamepenyesuaian')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Penyesuaian Stok') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('PRODUKSI', 'komplen')
                        <flux:navlist.item icon="home" :href="route('komplen')" :current="request()->routeIs('komplen')"
                            wire:navigate class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Complain') }}
                        </flux:navlist.item>
                    @endmenuitem
                </flux:navlist.group>
            @endmenugroup

            @menugroup('TEKNISI')
            <flux:navlist.group expandable
                :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="TEKNISI"
                class="grid">
                @menuitem('TEKNISI', 'ticket.web.create')
                    <flux:navlist.item icon="home" :href="route('ticket.web.create')"
                        :current="request()->routeIs('ticket.web.create')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Tiket Teknisi') }}
                    </flux:navlist.item>
                @endmenuitem

            </flux:navlist.group>
        @endmenugroup
            {{-- Grup Laporan --}}
            @menugroup('LAPORAN')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="LAPORAN"
                    class="grid">
                    @menuitem('LAPORAN', 'lapbrgmsk')
                        <flux:navlist.item icon="home" :href="route('lapbrgmsk')"
                            :current="request()->routeIs('lapbrgmsk')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Laporan Barang Masuk') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('LAPORAN', 'lap-has-prod')
                        <flux:navlist.item icon="home" :href="route('lap-has-prod')"
                            :current="request()->routeIs('lap-has-prod')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Produksi Bulanan') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('LAPORAN', 'lap-prod-minggu')
                        <flux:navlist.item icon="home" :href="route('lap-prod-minggu')"
                            :current="request()->routeIs('lap-prod-minggu')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Produksi Mingguan') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('LAPORAN', 'lap-prod-hari')
                    <flux:navlist.item icon="home" :href="route('lap-prod-hari')"
                        :current="request()->routeIs('lap-prod-minggu')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Produksi Harian') }}
                    </flux:navlist.item>
                @endmenuitem
                    @menuitem('LAPORAN', 'ketepatanwkt')
                        <flux:navlist.item icon="home" :href="route('ketepatanwkt')"
                            :current="request()->routeIs('ketepatanwkt')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Ketepatan Waktu Selesai') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('LAPORAN', 'ketepatanwktbln')
                        <flux:navlist.item icon="home" :href="route('ketepatanwktbln')"
                            :current="request()->routeIs('ketepatanwktbln')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Ketepatan Waktu Bulanan') }}
                        </flux:navlist.item>
                    @endmenuitem
                </flux:navlist.group>
            @endmenugroup
        </flux:navlist>

        <flux:spacer />
        <!-- Desktop User Menu -->
        <flux:dropdown position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon-trailing="chevrons-up-down"
                class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md p-2" />

            <flux:menu class="w-[220px] bg-white dark:bg-zinc-800 shadow-lg rounded-md">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
        {{-- Footer (optional) --}}
        <footer class="text-center text-sm text-gray-500 py-4">
            © {{ date('Y') }} - CV. Khasanah Sari
        </footer>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden p-4 bg-white dark:bg-zinc-800 shadow-md">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down"
                class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md p-2" />

            <flux:menu class="bg-white dark:bg-zinc-800 shadow-lg rounded-md">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
    @stack('scripts')
</body>

</html>
