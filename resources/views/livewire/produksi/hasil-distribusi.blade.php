<div id="hasil-distribusi-root" class="rounded-lg border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
    {{-- Toast sukses (dipicu dari Livewire $this->dispatch('notify', ...)) --}}
    {{-- Toast sukses --}}
    {{-- Toast (success/warning/error) --}}
    <div x-data="{ show: false, text: '', type: 'success' }"
        x-on:notify.window="
       text = $event.detail?.text || ($event.detail?.type==='success' ? 'Berhasil.' : 'Terjadi kesalahan');
       type = $event.detail?.type || 'success';
       show = true;
       // ⬇️ kalau error: pastikan draft dibersihkan (fallback)
       if (type === 'error') { window.clearDistribusiDraft(); }
       clearTimeout(window.__toastTO);
       window.__toastTO = setTimeout(() => show = false, $event.detail?.timeout || 4000);
     "
        x-show="show" x-transition
        class="fixed top-4 left-1/2 -translate-x-1/2 text-white text-xs px-3 py-2 rounded shadow z-50"
        :class="{
            'bg-emerald-600': type === 'success',
            'bg-amber-500': type === 'warning',
            'bg-rose-600': type === 'error'
        }"
        role="status" aria-live="polite">
        <span x-text="text"></span>
    </div>


    {{-- Tombol Kembali (Livewire 3 + fallback) --}}
    {{-- Tombol Kembali (fallback ke route kalau tidak ada history) --}}
    <div x-data class="p-3">
        <button type="button"
            class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:bg-emerald-600 dark:hover:bg-emerald-500"
            @click="
        if (history.length > 1 && document.referrer && new URL(document.referrer).origin === location.origin) {
          history.back();
        } else {
          if (window.Livewire?.navigate) {
            Livewire.navigate('{{ route('listperproduksi') }}');
          } else {
            window.location.href = '{{ route('listperproduksi') }}';
          }
        }
      ">
            ← Kembali
        </button>

        <noscript>
            <a href="{{ route('listperproduksi') }}"
                class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white">
                ← Kembali
            </a>
        </noscript>
    </div>



    {{-- Bar kontrol --}}
    <div class="px-4 pt-3 flex items-end justify-between gap-3">
        <div class="w-full sm:max-w-xs" x-data x-init="flatpickr($refs.tanggalInput, { defaultDate: '{{ $tanggalProduksi }}', dateFormat: 'Y-m-d' })">
            <label class="block text-sm font-semibold text-gray-700 dark:text-zinc-200 mb-1">Tanggal Produksi</label>
            <input type="text" x-ref="tanggalInput" wire:model="tanggalProduksi"
                class="w-full border rounded px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-200 dark:bg-zinc-800 dark:border-zinc-600"
                placeholder="Pilih tanggal" />
        </div>
        @if ($this->perintah_id)
            <div class="mt-3 text-xs px-3 py-2 rounded bg-yellow-100 text-yellow-800 shadow-sm "> Memproses Perintah ID:
                <span class="font-semibold">#{{ $this->perintah_id }}</span>
            </div>
        @endif
        <button wire:click="submit"
        wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow">
            Simpan
        </button>
        <button type="button" class="hidden"
            onclick="window.dispatchEvent(new CustomEvent('notify',{detail:{text:'Tes toast OK!'}}))">
            Test Toast
        </button>
    </div>

    {{-- GRID --}}
    <div id="grid-shell" class="mt-3 relative">

        {{-- HEADER (tetap di tempat, tidak ikut scroll Y) --}}
        <div id="grid-head" class="sticky top-0 z-20 bg-green-500 overflow-hidden" wire:ignore>
            <div id="grid-head-inner" class="relative will-change-transform">
                <table class="min-w-full text-[12px] text-left" id="grid-head-table">
                    <colgroup id="col-head">
                        <col style="width:60px" /><!-- No -->
                        <col class="col-id" /><!-- Id (disembunyikan, tapi tetap dihitung) -->
                        <col style="width:200px" /><!-- Nama -->
                        <col style="width:80px" /><!-- Target -->
                        <col style="width:110px" /><!-- PO Sistem -->
                        <col style="width:120px" /><!-- Pengalihan -->
                        <col style="width:120px" /><!-- Penyesuaian -->
                        <col style="width:100px" /><!-- Gojek -->
                        <col style="width:100px" /><!-- Complain -->
                        <col style="width:120px" /><!-- Penj Pabrik -->
                        <col style="width:110px" /><!-- Retur Produksi -->
                        <col style="width:130px" /><!-- Retur Jadi -->
                        <col style="width:100px" /><!-- Total Retur -->
                        <col style="width:90px" /><!-- SER -->
                        <col style="width:100px" /><!-- Lain-Lain -->
                        <col style="width:90px" /><!-- Sample -->
                        <col style="width:100px" /><!-- Real -->
                        <col style="width:120px" /><!-- Dist Sebelum Complain -->
                        <col style="width:110px" /><!-- Total -->
                    </colgroup>
                    <thead>
                        <tr class="text-gray-900">
                            <th class="th">No</th>
                            <th class="th th-id" aria-hidden="true"></th>
                            <th class="th text-left">Nama Barang</th>
                            <th class="th text-center">Target</th>
                            <th class="th text-center">PO Sistem</th>
                            <th class="th text-center">Pengalihan Produks</th>
                            <th class="th text-center">Penyesuaian PO</th>
                            <th class="th text-center">Gojek</th>
                            <th class="th text-center">Complain</th>
                            <th class="th text-center">Penjualan Pabrik</th>
                            <th class="th text-center">Retur Produksi</th>
                            <th class="th text-center">Retur Jadi (dekor)</th>
                            <th class="th text-center">Total Retur</th>
                            <th class="th text-center">SER</th>
                            <th class="th text-center">Lain-Lain</th>
                            <th class="th text-center">Sample</th>
                            <th class="th text-center">Real</th>
                            <th class="th text-left">Dist Sebelum Complain</th>
                            <th class="th text-center">Total</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        {{-- BODY (scroll X & Y) --}}
        <div id="grid-wrap" class="overflow-auto max-h-[70vh]" style="scrollbar-gutter:stable both-edges;">
            <table id="grid-body" data-ls-scope="hasil_distribusi-{{ $this->perintah_id }}"
                class="min-w-full text-[12px] text-left text-gray-800 dark:text-zinc-100">
                <colgroup id="col-body">
                    <col style="width:60px" />
                    <col class="col-id" />
                    <col style="width:200px" />
                    <col style="width:80px" />
                    <col style="width:110px" />
                    <col style="width:120px" />
                    <col style="width:120px" />
                    <col style="width:100px" />
                    <col style="width:100px" />
                    <col style="width:120px" />
                    <col style="width:110px" />
                    <col style="width:130px" />
                    <col style="width:100px" />
                    <col style="width:90px" />
                    <col style="width:100px" />
                    <col style="width:90px" />
                    <col style="width:100px" />
                    <col style="width:120px" />
                    <col style="width:110px" />
                </colgroup>
                <tbody>
                    @forelse($perintahproduksi as $index => $produk)
                        @php
                            $retur = $this->sumColumns($index, [6, 7]);
                            $positif = $this->sumColumns($index, [0, 1, 2, 3, 5, 8, 9, 10]);
                            $dist = $positif - $retur;
                            $total = $dist - $this->sumColumns($index, [4]);
                        @endphp
                        <tr class="{{ $loop->odd ? 'bg-[#0317fc]' : 'bg-black' }} text-white">
                            <td class="td">{{ $loop->iteration }}</td>
                            <td class="td td-id">{{ $produk->perintah_produksi_id }}</td>
                            <td class="td">{{ $produk->master_product_nama }}</td>
                            <td class="td text-center">{{ number_format($produk->sisa_target, 0, ',', '.') }}</td>

                            @for ($i = 0; $i <= 7; $i++)
                                <td class="td">
                                    <input type="number"
                                        wire:model.live.debounce.200ms="inputs.{{ $index }}.{{ $i }}"
                                        class="cell-input ls-sync" />
                                </td>
                            @endfor

                            <td class="td text-center">{{ number_format($retur, 0, ',', '.') }}</td>

                            @for ($i = 8; $i <= 11; $i++)
                                <td class="td">
                                    <input type="number"
                                        wire:model.live.debounce.200ms="inputs.{{ $index }}.{{ $i }}"
                                        class="cell-input ls-sync" />
                                </td>
                            @endfor

                            <td class="td text-center">{{ number_format($dist, 0, ',', '.') }}</td>
                            <td class="td text-center">{{ number_format($total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="td text-center text-gray-300 dark:text-gray-400" colspan="19">Tidak ada data
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ====== STYLE ====== --}}
    <style>
        /* Kunci layout & biar konten tidak memaksa melebar */
        #grid-body,
        #grid-head-table {
            table-layout: fixed;
            border-collapse: collapse;
            width: 100%;
        }

        #grid-body td,
        #grid-head th {
            box-sizing: border-box;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Header cell look */
        #grid-head th.th {
            padding: .5rem .5rem;
            border: 1px solid rgb(63 63 70 / .6);
            background: #22c55e;
        }

        /* Body cell look */
        #grid-body td.td {
            padding: .4rem .5rem;
            border: 1px solid rgb(63 63 70 / .6);
        }

        /* Input patuh kolom */
        .cell-input {
            display: block;
            width: 100%;
            max-width: 100%;
            min-width: 0 !important;
            padding: .25rem .4rem;
            text-align: center;
            border: 1px solid #3f3f46;
            border-radius: .25rem;
            background: #0b0b0b22;
        }

        /* Kolom Id: ada di layout tapi 0px */
        #col-head .col-id,
        #col-body .col-id {
            width: 0 !important;
        }

        .th-id,
        .td-id {
            width: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            visibility: hidden;
            overflow: hidden !important;
        }

        /* --- FIX: teks header menghilang saat di-transform --- */
        #grid-head-inner {
            /* sudah ada will-change, tambahkan: */
            transform: translateZ(0);
            /* promote ke compositing layer */
            backface-visibility: hidden;
            /* hindari glitch text */
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            overflow: visible;
            /* pastikan teks tidak terpotong */
        }

        /* Pastikan teks header selalu kelihatan */
        #grid-head th {
            position: relative;
            z-index: 2;
            /* di atas background hijau */
            color: #111827;
            /* gray-900 */
        }

        .dark #grid-head th {
            color: #0a0a0a;
            /* tetap gelap di dark mode */
        }

        /* Opsional: kalau masih terlihat “pudar”, coba font weight */
        #grid-head th .label,
        #grid-head th {
            font-weight: 600;
        }

        #grid-head-inner {
            transform: translateZ(0);
            backface-visibility: hidden;
        }
    </style>

    {{-- ====== SCRIPT ====== --}}
    <script>
        (() => {
            const wrap = document.getElementById('grid-wrap');
            const headInner = document.getElementById('grid-head-inner');
            const headTable = document.getElementById('grid-head-table');
            const colHead = document.getElementById('col-head');
            const bodyTable = document.getElementById('grid-body');

            let cachedX = 0;
            let scheduleId = 0;

            const raf = (fn) => requestAnimationFrame(fn);

            function normalizeInputs() {
                document.querySelectorAll('#grid-body td input').forEach(el => {
                    el.removeAttribute('size');
                    el.style.display = 'block';
                    el.style.width = '100%';
                    el.style.maxWidth = '100%';
                    el.style.minWidth = '0';
                    el.style.boxSizing = 'border-box';
                    el.style.overflow = 'hidden';
                });
            }

            function setHeadWidth() {
                const sw = bodyTable.scrollWidth;
                headTable.style.width = sw + 'px';
                headInner.style.width = sw + 'px';
            }

            function syncWidths() {
                const row0 = bodyTable.tBodies[0]?.rows[0];
                if (!row0) return;
                const tds = row0.cells;
                const ths = headTable.tHead.rows[0].cells;
                const n = Math.min(ths.length, tds.length);

                for (let i = 0; i < n; i++) {
                    const w = Math.round(tds[i].getBoundingClientRect().width);
                    ths[i].style.width = ths[i].style.minWidth = ths[i].style.maxWidth = w + 'px';
                    if (colHead?.children[i]) colHead.children[i].style.width = w + 'px';
                }
            } /* ←←← KURUNG TUTUP YANG HILANG TADI */

            // align header dengan body (anti drift saat zoom)
            let deltaLeft = 0;

            function measureDeltaLeft() {
                const wrapRect = wrap.getBoundingClientRect();
                const td0 = bodyTable.tBodies[0]?.rows[0]?.cells[0];
                if (!td0) {
                    deltaLeft = 0;
                    return;
                }
                const tdRect = td0.getBoundingClientRect();
                deltaLeft = Math.round(tdRect.left - wrapRect.left);
            }

            function nudgeHeader() {
                const x = Math.round(wrap.scrollLeft);
                headInner.style.transform = `translate3d(${deltaLeft - x}px,0,0)`;
            }

            function apply() {
                normalizeInputs();
                setHeadWidth();
                requestAnimationFrame(() => {
                    syncWidths();
                    measureDeltaLeft();
                    nudgeHeader();
                });
            }

            function scheduleApply() {
                if (scheduleId) cancelAnimationFrame(scheduleId);
                scheduleId = raf(() => {
                    scheduleId = 0;
                    apply();
                    setTimeout(apply, 120); // cadangan untuk zoom yang telat settle
                });
            }

            wrap.addEventListener('scroll', nudgeHeader, {
                passive: true
            });
            window.addEventListener('resize', scheduleApply);
            if (window.visualViewport) {
                visualViewport.addEventListener('resize', scheduleApply, {
                    passive: true
                });
                visualViewport.addEventListener('scroll', scheduleApply, {
                    passive: true
                });
            }

            const ro = new ResizeObserver(scheduleApply);
            ro.observe(wrap);
            ro.observe(bodyTable);

            function onMessageSent() {
                cachedX = wrap.scrollLeft;
            }

            function onMessageProcessed() {
                scheduleApply();
            }
            document.addEventListener('livewire:message-sent', onMessageSent);
            document.addEventListener('livewire:message-processed', onMessageProcessed);
            if (window.Livewire?.hook) {
                Livewire.hook('message.sent', onMessageSent);
                Livewire.hook('message.processed', onMessageProcessed);
            }

            document.addEventListener('DOMContentLoaded', scheduleApply);
            window.addEventListener('load', scheduleApply);
        })();
    </script>

    <script>
        (() => {
            const ROOT_SEL = '#grid-body';
            const getRoot = () => document.querySelector(ROOT_SEL);
            const getScope = (root = getRoot()) => (root?.dataset.lsScope || 'hasil_distribusi');

            let lsSkip = false; // blok penulisan ulang LS saat clear

            function getWireModelExpr(el) {
                for (const a of el.attributes)
                    if (a.name.startsWith('wire:model')) return el.getAttribute(a.name);
                return null;
            }

            function makeKey(scope, el, idx) {
                const expr = getWireModelExpr(el);
                return expr ? `${scope}::${expr}` : `${scope}::idx_${idx}`;
            }

            // bind (jangan nulis saat lsSkip true)
            function bindDraft() {
                const root = getRoot();
                if (!root) return;
                const scope = getScope(root);
                const inputs = root.querySelectorAll('input.ls-sync');

                inputs.forEach((input, idx) => {
                    if (input.dataset.lsBound === '1') return;
                    input.dataset.lsBound = '1';

                    const key = makeKey(scope, input, idx);
                    input.dataset.lsKey = key;

                    const saved = localStorage.getItem(key);
                    if (saved !== null && input.value !== saved) {
                        input.value = saved;
                        input.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                        input.dispatchEvent(new Event('change', {
                            bubbles: true
                        }));
                    }

                    input.addEventListener('input', () => {
                        if (lsSkip) return;
                        const v = input.value ?? '';
                        if (v === '') localStorage.removeItem(key);
                        else localStorage.setItem(key, v);
                    });
                });
            }

            // clear yang aman: hapus LS + kosongkan UI + refresh komponen
            window.clearDistribusiDraft = (sc = getScope()) => {
                const root = getRoot();
                if (!root) return;
                const prefix = sc + '::';

                lsSkip = true;
                try {
                    // hapus semua key scope
                    for (let i = localStorage.length - 1; i >= 0; i--) {
                        const k = localStorage.key(i);
                        if (k && k.startsWith(prefix)) localStorage.removeItem(k);
                    }
                    // kosongkan input & sinkron ke Livewire
                    root.querySelectorAll('input.ls-sync').forEach((input) => {
                        if (input.dataset.lsKey?.startsWith(prefix)) {
                            input.value = '0';
                            input.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            input.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }
                    });
                    // paksa refresh komponen (biar DOM bersih)
                    const compEl = root.closest('[wire\\:id]');
                    if (compEl && window.Livewire?.find) {
                        window.Livewire.find(compEl.getAttribute('wire:id')).call('$refresh');
                    }
                } finally {
                    setTimeout(() => {
                        lsSkip = false;
                    }, 0);
                }
            };

            // pasang listener di WINDOW **dan** DOCUMENT
            const onClear = (e) => {
                const sc = e.detail?.scope || getScope();
                window.clearDistribusiDraft(sc);
            };
            window.addEventListener('clear-draft', onClear);
            document.addEventListener('clear-draft', onClear);

            // inisialisasi
            const run = () => requestAnimationFrame(bindDraft);
            document.addEventListener('DOMContentLoaded', run);
            document.addEventListener('livewire:init', run);
            document.addEventListener('livewire:navigated', run);
            if (window.Livewire?.hook) Livewire.hook('morph.updated', run);
        })();
        window.addEventListener('clear-draft', e => console.log('clear-draft@window', e.detail));
        document.addEventListener('clear-draft', e => console.log('clear-draft@document', e.detail));
    </script>




</div>
