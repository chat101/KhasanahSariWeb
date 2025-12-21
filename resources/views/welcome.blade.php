<x-layouts.landing title="Khasanah Sari Bakery">
    <div class="min-h-screen">
        {{-- Top bar --}}
        <header class="sticky top-0 z-20 border-b border-white/10 bg-zinc-950/70 backdrop-blur">
            <div class="mx-auto max-w-6xl px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-9 w-9 rounded-xl bg-white/10 border border-white/10 grid place-items-center">
                        <span class="text-sm font-semibold">KS</span>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold">Khasanah Sari Bakery</div>
                        <div class="text-[11px] text-zinc-400">ERP & Operasional</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @auth

                        <a href="{{ route('dashboard') }}"
                           class="text-xs px-3 py-2 rounded-lg bg-white text-zinc-900 font-semibold hover:opacity-90">
                            Masuk Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-xs px-3 py-2 rounded-lg bg-white text-zinc-900 font-semibold hover:opacity-90">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        {{-- Hero --}}
        <main class="mx-auto max-w-6xl px-4 py-12 md:py-16">
            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="space-y-5">
                    <div class="inline-flex items-center gap-2 text-[11px] px-3 py-1.5 rounded-full border border-white/10 bg-white/5">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        <span class="text-zinc-300">Sistem internal • Aman • Cepat</span>
                    </div>

                  <h1 class="text-4xl md:text-5xl font-semibold tracking-tight leading-tight">
                        Kelola gudang, purchasing, kontribusi, dan laporan
                        <span class="text-zinc-400">lebih rapi & realtime.</span>
                    </h1>

               <p class="text-base md:text-lg text-zinc-400 leading-relaxed">
                        Dashboard operasional untuk monitoring target, retur, diskon, biaya, hingga analisis kontribusi per toko.
                        Dibangun untuk workflow Khasanah Sari Bakery.
                    </p>

                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="px-4 py-2.5 rounded-xl bg-white text-zinc-900 text-sm font-semibold hover:opacity-90">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="px-4 py-2.5 rounded-xl bg-white text-zinc-900 text-sm font-semibold hover:opacity-90">
                                Login
                            </a>

                            {{-- @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   class="px-4 py-2.5 rounded-xl border border-white/15 bg-white/5 text-sm font-semibold hover:bg-white/10">
                                    Buat Akun
                                </a>
                            @endif --}}
                        @endauth

                        <a href="#fitur"
                           class="px-4 py-2.5 rounded-xl border border-white/15 bg-white/5 text-sm font-semibold hover:bg-white/10">
                            Lihat Fitur
                        </a>
                    </div>

                    <div class="flex items-center gap-3 text-[11px] text-zinc-400 pt-3">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-5 w-5 rounded-lg bg-white/5 border border-white/10 grid place-items-center">✓</span>
                            Livewire v3
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-5 w-5 rounded-lg bg-white/5 border border-white/10 grid place-items-center">✓</span>
                            Tailwind
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-5 w-5 rounded-lg bg-white/5 border border-white/10 grid place-items-center">✓</span>
                            Laravel 12
                        </span>
                    </div>
                </div>

                {{-- Card kanan --}}
                <div class="relative">
                    <div class="absolute -inset-6 blur-2xl opacity-40 bg-gradient-to-tr from-indigo-500/20 via-emerald-500/10 to-amber-500/20"></div>

                    <div class="relative rounded-2xl border border-white/10 bg-white/5 p-5 shadow-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold">Ringkasan Hari Ini</div>
                                <div class="text-[11px] text-zinc-400">Contoh preview</div>
                            </div>
                            <div class="text-[11px] px-2 py-1 rounded-lg bg-emerald-500/15 text-emerald-200 border border-emerald-400/20">
                                Online
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div class="rounded-xl border border-white/10 bg-zinc-950/40 p-3">
                                <div class="text-zinc-400 text-[11px]">Penjualan Neto</div>
                                <div class="mt-1 text-lg font-semibold">Rp 0</div>
                                <div class="text-[11px] text-zinc-500">sample</div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-zinc-950/40 p-3">
                                <div class="text-zinc-400 text-[11px]">Biaya</div>
                                <div class="mt-1 text-lg font-semibold">Rp 0</div>
                                <div class="text-[11px] text-zinc-500">sample</div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-zinc-950/40 p-3">
                                <div class="text-zinc-400 text-[11px]">Retur</div>
                                <div class="mt-1 text-lg font-semibold">0</div>
                                <div class="text-[11px] text-zinc-500">sample</div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-zinc-950/40 p-3">
                                <div class="text-zinc-400 text-[11px]">Diskon Manual</div>
                                <div class="mt-1 text-lg font-semibold">0</div>
                                <div class="text-[11px] text-zinc-500">sample</div>
                            </div>
                        </div>

                        <div id="fitur" class="mt-4 rounded-xl border border-white/10 bg-zinc-950/40 p-3">
                            <div class="text-[11px] text-zinc-400">Highlight</div>
                            <ul class="mt-2 space-y-1.5 text-xs text-zinc-300">
                                <li>• Laporan kontribusi per hari & periode</li>
                                <li>• Mapping biaya: diskon manual by type, telur/gas by ket</li>
                                <li>• UI total merah/hijau seperti yang kamu mau</li>
                                <li>• Export & integrasi API operasional</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="mt-14 border-t border-white/10 pt-6 text-[11px] text-zinc-500">
                © {{ date('Y') }} Khasanah Sari Bakery • Internal System
            </footer>
        </main>
    </div>
</x-layouts.landing>
