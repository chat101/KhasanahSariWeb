<div class="mx-auto max-w-7xl px-4 py-6" x-data="tabs()" x-init="init()">

    <h1 class="text-2xl font-bold mb-4">Opname & Penyesuaian Stok</h1>

    {{-- Tabs header --}}
    <div class="relative border-b border-gray-200">
        <nav class="flex gap-1" aria-label="Tabs">
            <button :class="tabClass('awal')" @click="select('awal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                </svg>
                Stok Awal (Opname Awal Hari)
            </button>

            <button :class="tabClass('penyesuaian')" @click="select('penyesuaian')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11 2a1 1 0 011 1v1.07a7.002 7.002 0 015.657 5.657H19a1 1 0 110 2h-1.07A7.002 7.002 0 0112 17.93V19a1 1 0 11-2 0v-1.07A7.002 7.002 0 014.343 9.727H3a1 1 0 110-2h1.07A7.002 7.002 0 0110 4.07V3a1 1 0 011-1z" />
                </svg>
                Penyesuaian Stok (Tengah/Akhir Hari)
            </button>
            <button :class="tabClass('catatan')" @click="select('catatan')">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11 2a1 1 0 011 1v1.07a7.002 7.002 0 015.657 5.657H19a1 1 0 110 2h-1.07A7.002 7.002 0 0112 17.93V19a1 1 0 11-2 0v-1.07A7.002 7.002 0 014.343 9.727H3a1 1 0 110-2h1.07A7.002 7.002 0 0110 4.07V3a1 1 0 011-1z" />
                </svg>
                Catatan Penyesuaian
            </button>
        </nav>

        {{-- active tab underline --}}
        <div class="absolute bottom-0 h-0.5 bg-blue-600 transition-all duration-300" :style="underlineStyle()"></div>
    </div>

    {{-- Tabs content --}}
    <div class="mt-6">
        <div x-show="active === 'awal'" x-cloak>
            @livewire('produksi.stok-awal-opname', key('tab-awal'))
        </div>

        <div x-show="active === 'penyesuaian'" x-cloak>
            @livewire('produksi.penyesuaian-stok')
        </div>
        <div x-show="active === 'catatan'" x-cloak>
            {{-- ====== Informasi Opname & Penyesuaian (Styled) ====== --}}
            <div class="mx-auto max-w-4xl space-y-8 text-zinc-800 dark:text-zinc-100">

                {{-- Section 1 --}}
                <section class="space-y-3">
                    <h2 class="text-xl font-bold">
                        1. Stok Awal (Opname Awal Hari)
                    </h2>

                    <ul class="list-disc pl-6 space-y-2">
                        <li>
                            Tujuan: memastikan sistem mulai hari itu dengan <span class="font-semibold">stok awal yang
                                benar</span>.
                        </li>
                        <li>
                            Disimpan di tabel
                            <code
                                class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-zinc-900 dark:text-zinc-100 text-sm">stok_awal_manual</code>.
                        </li>
                        <li>
                            Jadi kalau ada salah hitung kemarin, langsung bisa dikoreksi di awal hari
                            (tanpa ganggu transaksi hari berjalan).
                        </li>
                        <li>
                            Lebih “satu kali saja” → biasanya dikerjakan pagi hari.
                        </li>
                    </ul>
                </section>

                <hr class="border-zinc-200 dark:border-zinc-700">

                {{-- Section 2 --}}
                <section class="space-y-3">
                    <h2 class="text-xl font-bold">
                        2. Penyesuaian Stok (Opname Tengah/Akhir Hari)
                    </h2>

                    <ul class="list-disc pl-6 space-y-2">
                        <li>
                            Tujuan: menangkap selisih yang ketahuan <span class="font-semibold">saat hari sedang
                                berjalan</span>
                            (misal barang rusak, hilang, atau ditemukan selisih saat cek fisik).
                        </li>
                        <li>
                            Dicatat di tabel
                            <code
                                class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-zinc-900 dark:text-zinc-100 text-sm">penyesuaian_stok</code>
                            + di-update ke
                            <code
                                class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-zinc-900 dark:text-zinc-100 text-sm">stok_rekap_harian</code>.
                        </li>
                        <li>
                            Bisa dilakukan berkali-kali dalam sehari.
                        </li>
                        <li>
                            Ada kolom <em>alasan</em> supaya jelas audit trail: kenapa ada koreksi.
                        </li>
                    </ul>
                </section>
{{-- ====== Catatan & Saran (Styled) ====== --}}
<div class="mx-auto max-w-4xl text-zinc-800 dark:text-zinc-100">
    <h2 class="text-lg font-bold mb-3">Catatan &amp; saran</h2>

    <ul class="list-disc pl-6 space-y-2">
      <li>
        <span class="font-semibold">Alur harian (best practice):</span>
        <ul class="list-disc pl-6 mt-2 space-y-1">
          <li>
            Pagi: isi <span class="font-semibold">Stok Awal</span> → menulis
            <code class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-sm text-zinc-900 dark:text-zinc-100">stok_awal_manual(T)</code>.
          </li>
          <li>
            Operasional: transaksi jalan seperti biasa.
          </li>
          <li>
            Sore/malam (jika perlu): <span class="font-semibold">Penyesuaian</span> → menambah
            <code class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-sm text-zinc-900 dark:text-zinc-100">masuk_hari/keluar_hari</code>
            via
            <code class="px-2 py-0.5 rounded-md bg-zinc-200/80 dark:bg-zinc-700/80 text-sm text-zinc-900 dark:text-zinc-100">penyesuaian_stok</code>.
          </li>
        </ul>
      </li>
    </ul>
  </div>

            </div>

        </div>
    </div>
</div>

{{-- Alpine helpers --}}
<script>
    function tabs() {
        return {
            active: 'awal',
            // untuk animasi underline
            positions: {
                awal: {
                    left: '0%',
                    width: '33.33%'
                },
                penyesuaian: {
                    left: '33.33%',
                    width: '33.33%'
                },
                catatan: {
                    left: '66.66%',
                    width: '33.33%'
                },
            },
            init() {
                // restore dari localStorage (optional)
                const saved = localStorage.getItem('tab-stok');
                if (['awal', 'penyesuaian', 'rekap'].includes(saved)) this.active = saved;

                // kalau url punya hash
                if (location.hash === '#penyesuaian') this.active = 'penyesuaian';
                if (location.hash === '#awal') this.active = 'awal';
                if (location.hash === '#catatan') this.active = 'catatan';
            },
            select(key) {
                this.active = key;
                localStorage.setItem('tab-stok', key);
                history.replaceState(null, '', '#' + key);
            },
            tabClass(key) {
                return [
                    'relative px-4 py-2 text-sm md:text-base font-medium rounded-t-md transition-colors',
                    'flex items-center',
                    this.active === key ? 'text-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700'
                ].join(' ');
            },
            underlineStyle() {
                const pos = this.positions[this.active];
                return `left:${pos.left}; width:${pos.width};`;
            }
        }
    }
</script>
