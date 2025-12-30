<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    @livewireStyles

</head>

<body class="min-h-screen bg-dark white:bg-zinc-800 font-sans">
    <flux:sidebar sticky stashable
        class="border-r border-zinc-200 bg-gradient-to-b from-zinc-50 to-white dark:border-zinc-700 dark:from-zinc-900 dark:to-zinc-950 shadow-2xl text-sm">
        
        <!-- Logo/Brand Section -->
        <div class="px-4 py-5 mb-2 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 dark:from-indigo-900 dark:via-purple-900 dark:to-pink-900 shadow-lg">
            <div class="flex items-center gap-3 text-white">
                <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-base font-bold tracking-tight leading-tight">Khasanah Sari</h1>
                    <p class="text-xs text-white/80 mt-0.5">ERP System</p>
                </div>
            </div>
        </div>
        
        <flux:sidebar.toggle class="lg:hidden mb-4" icon="x-mark" />
        
        <flux:navlist variant="outline" class="px-3">
            <flux:navlist.group class="grid gap-1 mb-4">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate class="hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-md transition-all duration-300 rounded-lg font-semibold">
                    {{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>
            {{-- Grup Master --}}
            @menugroup('MasterData')
                <flux:navlist.group expandable heading="📊 MASTER DATA" class="grid gap-1 mb-3">
                    @menuitem('masterdata', 'muser')
                        <flux:navlist.item icon="user-circle" :href="route('muser')" :current="request()->routeIs('muser')"
                            wire:navigate class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master User') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('masterdata', 'mtoko')
                        <flux:navlist.item icon="building-storefront" :href="route('mtoko')" :current="request()->routeIs('mtoko')"
                            wire:navigate class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Toko') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'master.wilayah')
                        <flux:navlist.item icon="map" :href="route('master.wilayah')"
                            :current="request()->routeIs('master.wilayah')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Wilayah') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'master.area')
                        <flux:navlist.item icon="map-pin" :href="route('master.area')"
                            :current="request()->routeIs('master.area')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Area') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'msupplier')
                        <flux:navlist.item icon="truck" :href="route('msupplier')"
                            :current="request()->routeIs('msupplier')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Supplier') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('masterdata', 'mbarang')
                        <flux:navlist.item icon="cube" :href="route('mbarang')" :current="request()->routeIs('mbarang')"
                            wire:navigate class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Barang') }}
                        </flux:navlist.item>
                    @endmenuitem

                    @menuitem('masterdata', 'mproduk')
                        <flux:navlist.item icon="shopping-bag" :href="route('mproduk')" :current="request()->routeIs('mproduk')"
                            wire:navigate class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Produk') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'slides.manage')
                        <flux:navlist.item icon="photo" :href="route('slides.manage')"
                            :current="request()->routeIs('slides.manage')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Master Slide') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'mesintoproduk')
                        <flux:navlist.item icon="cog" :href="route('mesintoproduk')"
                            :current="request()->routeIs('mesintoproduk')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Setting Produk Ke Mesin') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'master-target-kontribusi')
                        <flux:navlist.item icon="chart-bar" :href="route('master-target-kontribusi')"
                            :current="request()->routeIs('master-target-kontribusi')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('Target Kontribusi') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'upload-proyeksi')
                        <flux:navlist.item icon="arrow-up-tray" :href="route('upload-proyeksi')"
                            :current="request()->routeIs('master-target-kontribusi')" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                            {{ __('UPLOAD PROYEKSI') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('masterdata', 'master.trend-inflasi')
                    <flux:navlist.item icon="chart-bar" :href="route('master.trend-inflasi')"
                        :current="request()->routeIs('master.trend-inflasi')" wire:navigate
                        class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 hover:shadow-md transition-all duration-300 rounded-lg">
                        {{ __('Master Trend Inflasi') }}
                    </flux:navlist.item>
                    @endmenuitem
                </flux:navlist.group>
            @endmenugroup
            {{-- Grup Gudang --}}
            @menugroup('Gudang')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="📦 GUDANG"
                    class="grid gap-1 mb-3">
                    <flux:navlist.item icon="inbox" :href="route('brgmsk')" :current="request()->routeIs('brgmsk')"
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
            @menugroup('Operasional')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="⚙️ OPERASIONAL"
                    class="grid gap-1 mb-3">
                    @menuitem('operasional', 'loss-bahan')
                    <flux:navlist.item icon="home" :href="route('loss-bahan')"
                    :current="request()->routeIs('loss-bahan')" wire:navigate
                    class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                    {{ __('Input Loss Bahan') }}
                </flux:navlist.item>
                @endmenuitem
                @menuitem('operasional', 'sisa-sales')
                    <flux:navlist.item icon="home" :href="route('sisa-sales')"
                        :current="request()->routeIs('sisa-sales')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Laporan Kontribusi') }}
                    </flux:navlist.item>
                @endmenuitem
                @menuitem('operasional', 'kontribusi-harian-toko')
                    <flux:navlist.item icon="home" :href="route('kontribusi-harian-toko')"
                    :current="request()->routeIs('kontribusi-harian-toko')" wire:navigate
                    class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                    {{ __('KONTRIBUSI HARIAN TOKO') }}
                </flux:navlist.item>
                @endmenuitem
                @menuitem('operasional', 'kurang-setoran')
                    <flux:navlist.item icon="home" :href="route('kurang-setoran')"
                    :current="request()->routeIs('kurang-setoran')" wire:navigate
                    class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                    {{ __('Kurang Setoran') }}
                </flux:navlist.item>
                @endmenuitem
                </flux:navlist.group>
            @endmenugroup
            @menugroup('Purchasing')
                {{-- Grup Purchasing --}}
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="🛒 PURCHASING"
                    class="grid gap-1 mb-3">
                    <flux:navlist.item icon="home" :href="route('listsuppmasuk')"
                        :current="request()->routeIs('listsuppmasuk')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Supplier Masuk') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="home" :href="route('rekapinputsuppmasuk')"
                        :current="request()->routeIs('rekapinputsuppmasuk')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Rekap Input Masuk') }}
                    </flux:navlist.item>
                    {{-- <flux:navlist.item icon="home" :href="route('hutangsupp')"
                        :current="request()->routeIs('hutangsupp')" wire:navigate
                        class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                        {{ __('Hutang Supplier') }}
                    </flux:navlist.item> --}}
                </flux:navlist.group>
            @endmenugroup

            {{-- Grup Accoounting --}}
            @menugroup('ACCOUNTING')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="💰 ACCOUNTING"
                    class="grid gap-1 mb-3">
                    @menuitem('ACCOUNTING', 'bank.index')
                        <flux:navlist.item icon="home" :href="route('bank.index')"
                            :current="request()->routeIs('bank.index')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER BANK') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.master-jenis-transaksi')
                        <flux:navlist.item icon="home" :href="route('keuangan.master-jenis-transaksi')"
                            :current="request()->routeIs('keuangan.master-jenis-transaksi')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER JENIS TRANSAKSI') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.master-akun-biaya')
                        <flux:navlist.item icon="home" :href="route('keuangan.master-akun-biaya')"
                            :current="request()->routeIs('keuangan.master-akun-biaya')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER AKUN BIAYA') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.master-kas')
                        <flux:navlist.item icon="home" :href="route('keuangan.master-kas')"
                            :current="request()->routeIs('keuangan.master-kas')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER KAS') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.master-template-jurnal')
                        <flux:navlist.item icon="home" :href="route('keuangan.master-template-jurnal')"
                            :current="request()->routeIs('keuangan.master-template-jurnal')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER TEMPLATE JURNAL') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.master-role-coa')
                        <flux:navlist.item icon="home" :href="route('keuangan.master-role-coa')"
                            :current="request()->routeIs('keuangan.master-role-coa')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MASTER ROLE COA') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'transaksi.index')
                        <flux:navlist.item icon="home" :href="route('transaksi.index')"
                            :current="request()->routeIs('transaksi.index')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('BUKU BANK') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'monitor-biaya')
                        <flux:navlist.item icon="home" :href="route('monitor-biaya')"
                            :current="request()->routeIs('monitor-biaya')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('MONITOR BIAYA') }}
                        </flux:navlist.item>
                    @endmenuitem
                    @menuitem('ACCOUNTING', 'keuangan.jurnal.input')
                        <flux:navlist.item icon="home" :href="route('keuangan.jurnal.input')"
                            :current="request()->routeIs('keuangan.jurnal.input')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('Jurnal Input') }}
                        </flux:navlist.item>
                    @endmenuitem
                </flux:navlist.group>
            @endmenugroup


            {{-- Grup Finance --}}
            @menugroup('FINANCE')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="💵 FINANCE"
                    class="grid gap-1 mb-3">
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
                    @menuitem('FINANCE', 'hutang.dagang.index')
                        <flux:navlist.item icon="home" :href="route('hutang.dagang.index')"
                            :current="request()->routeIs('hutang.dagang.index')" wire:navigate
                            class="hover:bg-zinc-200 dark:hover:bg-zinc-700 transition rounded-md">
                            {{ __('HUTANG DAGANG') }}
                        </flux:navlist.item>
                    @endmenuitem

                </flux:navlist.group>
            @endmenugroup
            {{-- Grup Produksi --}}
            @menugroup('PRODUKSI')
                <flux:navlist.group expandable
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="🏭 PRODUKSI"
                    class="grid gap-1 mb-3">
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
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="🔧 TEKNISI"
                    class="grid gap-1 mb-3">
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
                    :expanded="request()->routeIs('dashboard') || request()->routeIs('dashboard')" heading="📋 LAPORAN"
                    class="grid gap-1 mb-3">
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
        <div class="px-3 pb-3">
            <flux:dropdown position="bottom" align="start">
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                    class="w-full hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-950 dark:hover:to-purple-950 hover:shadow-lg transition-all duration-300 rounded-xl p-3 border border-zinc-200 dark:border-zinc-700" />

                <flux:menu class="w-[240px] bg-white dark:bg-zinc-800 shadow-2xl rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <flux:menu.radio.group>
                        <div class="p-3 text-sm font-normal border-b border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-center gap-3 text-start">
                                <span class="relative flex h-10 w-10 shrink-0 overflow-hidden rounded-xl shadow-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-sm">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-bold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate
                            class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 dark:hover:from-blue-950 dark:hover:to-cyan-950 transition-all duration-300 mx-2 my-1 rounded-lg">
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full p-2">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full hover:bg-red-50 dark:hover:bg-red-950 hover:text-red-600 dark:hover:text-red-400 transition-all duration-300 rounded-lg">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </div>
        
        {{-- Footer (optional) --}}
        <footer class="text-center text-xs text-zinc-400 dark:text-zinc-600 py-3 px-4 border-t border-zinc-200 dark:border-zinc-700">
            <div class="flex items-center justify-center gap-1.5">
                <span>©</span>
                <span>{{ date('Y') }}</span>
                <span>•</span>
                <span class="font-semibold">CV. Khasanah Sari</span>
            </div>
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

    <main class="w-full">
        {{ $slot }}
    </main>
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    @fluxScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
    @stack('scripts')
</body>

</html>
