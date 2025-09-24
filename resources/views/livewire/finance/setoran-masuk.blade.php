<div  x-data="setoranPersist(@js($tanggalSetoran))"
x-init="init()"
x-effect="switchDate($wire.tanggalSetoran)"
    class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">
    {{-- Header --}}
    <div
        class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z" />
            </svg>
            <h2 class="text-base font-semibold">Setoran Masuk Toko</h2>
        </div>

        {{-- Tanggal & Simpan --}}
        <div class="w-full sm:w-auto" x-data="{}" x-init="flatpickr($refs.tanggalInput, {
            dateFormat: 'Y-m-d',
            defaultDate: @js($tanggalSetoran),
            onChange: (d, s) => $wire.set('tanggalSetoran', s)
        })">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">Pilih Tanggal:</span>
                    <input type="text" x-ref="tanggalInput" value="{{ $tanggalSetoran }}"
                        class="w-40 sm:w-44 border rounded px-3 py-1.5 text-sm shadow-sm focus:ring focus:ring-indigo-200 dark:bg-zinc-800 dark:border-zinc-600"
                        placeholder="Pilih tanggal" />
                </div>
                <button type="button" wire:click="exportTxt"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-1.5 rounded shadow transition">
                    Export
                </button>
                <button type="button" wire:click="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-1.5 rounded shadow transition">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    {{-- Notification --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed top-4 left-1/2 -translate-x-1/2 w-72 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium">{{ session('message') }}</span>
                <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm"
                    aria-label="Tutup">✕</button>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">No</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Nama Toko</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Jumlah Setoran</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Total Setoran</th>
                    <th class="px-3 py-2 text-left border-b border-gray-200 dark:border-zinc-700">Keterangan</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 dark:text-zinc-200">
                @forelse ($tokos as $index => $toko)
                    <tr
                        class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
                        <td class="px-3 py-2 align-middle">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 align-middle">{{ $toko['nmtoko'] }}</td>

                        <td class="px-3 py-2 align-middle">
                            @once
                                <style>
                                    .input-glow[disabled] {
                                        border-color: #ef4444 !important;
                                        /* red-500 */
                                        box-shadow: 0 0 .55rem rgba(239, 68, 68, .9),
                                            inset 0 0 .2rem rgba(239, 68, 68, .45);
                                        background-color: rgba(239, 68, 68, .08);
                                        color: #ef4444;
                                    }

                                    .dark .input-glow[disabled] {
                                        border-color: #f87171 !important;
                                        /* red-400 */
                                        box-shadow: 0 0 .65rem rgba(248, 113, 113, .95),
                                            inset 0 0 .25rem rgba(248, 113, 113, .5);
                                        background-color: rgba(248, 113, 113, .12);
                                        color: #fecaca;
                                        /* red-200 */
                                    }
                                </style>
                            @endonce
                            <div class="flex items-center justify-center gap-2">
                                Rp
                                <input id="setoran-{{ $index }}" data-i="{{ $index }}" type="text"
                                    inputmode="numeric" pattern="^\d{1,3}(?:\.\d{3})*(?:,\d+)?$"
                                    wire:model.live="inputs.{{ $toko['id'] }}"
                                    class="w-24 border rounded text-right text-sm dark:bg-zinc-800 dark:border-zinc-600 input-glow"
                                    oninput="
                                  if (this.disabled) return;
                                  let v=this.value.replace(/\./g,'').replace(/[^0-9,]/g,'');
                                  const p=v.split(',');
                                  let i=p[0].replace(/^0+(?=\d)/,'');
                                  i=i.replace(/\B(?=(\d{3})+(?!\d))/g,'.');
                                  this.value = p.length>1 ? i+','+p[1].slice(0,2) : i;
                                "
                                    onkeydown="
                                  if (event.key==='Enter') {
                                    event.preventDefault();
                                    const curr = parseInt(this.dataset.i,10);

                                    const focusNext = () => {
                                      let j = curr + 1, next;
                                      while ((next = document.getElementById('setoran-'+j)) && next.disabled) j++;
                                      if (next) { next.focus(); next.select(); }
                                    };

                                    // Livewire v3: action mengembalikan Promise
                                    try {
                                      $wire.submit().then(() => setTimeout(focusNext, 10));
                                    } catch(e) {
                                      // fallback kalau promise tidak tersedia
                                      setTimeout(focusNext, 50);
                                    }
                                  }
                                "
                                    @disabled($hasUtama[$toko['id']] ?? false) />

                                <button type="button" wire:click="openTambahModal({{ $index }})"
                                    class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-sm shadow"
                                    aria-label="Tambah setoran">+
                                </button>
                            </div>
                        </td>
                        <td class="px-3 py-2 align-middle">
                            @php
                                $tot = (float) ($sumPerToko[$toko['id']] ?? 0);
                                $totFormatted =
                                    $tot == floor($tot)
                                        ? number_format($tot, 0, ',', '.')
                                        : rtrim(rtrim(number_format($tot, 2, ',', '.'), '0'), ',');
                            @endphp
                            @if ($tot > 0 || isset($sumPerToko[$toko['id']]))
                                <div class="flex justify-between w-32">
                                    <span>Rp</span>
                                    <span class="text-right">{{ $totFormatted }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-3 py-2 align-middle">
                            @php
                                $list = $keteranganListPerToko[$toko['id']] ?? [];
                                $list = array_values(array_filter($list, fn($v) => trim((string) $v) !== ''));
                            @endphp

                            @if (empty($list))
                                <input type="text" wire:model.defer="keteranganUtama.{{ $toko['id'] }}"
                                    class="w-full h-9 px-3 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                                    placeholder="Keterangan setoran utama (opsional)" />
                            @else
                                <ul
                                    class="space-y-1 text-[11px] text-gray-600 dark:text-gray-300 list-disc list-inside">
                                    @foreach ($list as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Tidak ada data
                        </td>
                    </tr>
                @endforelse

                @php
                    $grand = array_sum($sumPerToko ?? []);
                    $grandFormatted =
                        $grand == floor($grand)
                            ? number_format($grand, 0, ',', '.')
                            : rtrim(rtrim(number_format($grand, 2, ',', '.'), '0'), ',');
                @endphp

                <tr class="bg-gray-100 dark:bg-zinc-900 border-t border-gray-200 dark:border-zinc-700 font-semibold">
                    <td class="px-3 py-2"></td>
                    <td class="px-3 py-2 text-right">TOTAL</td>
                    <td class="px-3 py-2"></td>
                    <td class="px-3 py-2">
                        <div class="flex justify-between w-40">
                            <span>Rp</span>
                            <span class="text-right">{{ $grandFormatted }}</span>
                        </div>
                    </td>
                    <td class="px-3 py-2"></td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- Modal Tambahan --}}
    @if ($showTambahModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div
                class="bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-lg shadow-lg p-4 space-y-4 border border-gray-200 dark:border-zinc-700">
                <h3 class="text-lg font-semibold">
                    Tambah Setoran — {{ $selectedTokoName ?? '-' }}
                    <span class="text-xs text-gray-500 ml-2">({{ $tanggalSetoran }})</span>
                </h3>

                {{-- Baris input tambahan --}}
                <div class="flex items-end gap-3 flex-nowrap" x-data x-init="$nextTick(() => $refs.inputTambahan?.focus())">
                    <div class="w-40">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Jumlah</label>
                        <input type="text" inputmode="decimal" autocomplete="off" x-ref="inputTambahan"
                            wire:model.live="jumlahTambahan" wire:keydown.enter="stageTambahan"
                            class="w-full h-10 px-3 border rounded text-right dark:bg-zinc-800 dark:border-zinc-600"
                            placeholder="0"
                            oninput="
                          // izinkan minus, digit, titik, koma
                          let v = this.value.replace(/[^\d,.\-]/g, '');

                          // hanya satu minus & harus di depan
                          v = v.replace(/(?!^)-/g, '');

                          // buang titik lama
                          v = v.replace(/\./g, '');

                          // pecah desimal
                          const parts = v.split(',');
                          let intPart = parts[0] ?? '';
                          let sign = '';

                          if (intPart.startsWith('-')) {
                            sign = '-';
                            intPart = intPart.slice(1);
                          }

                          intPart = intPart.replace(/^0+(?=\d)/, ''); // hapus leading zero
                          intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // format ribuan

                          const dec = parts.length > 1 ? parts[1].replace(/[^\d]/g, '').slice(0, 2) : '';

                          this.value = sign + intPart + (dec ? ',' + dec : '');
                        " />
                        @error('jumlahTambahan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror

                    </div>

                    <div class="flex-1">
                        <label class="text-sm text-gray-600 dark:text-zinc-300">Keterangan</label>
                        <input type="text" wire:model="keteranganTambahan" wire:keydown.enter="stageTambahan"
                            class="w-full h-10 px-3 border rounded dark:bg-zinc-800 dark:border-zinc-600"
                            placeholder="Opsional" />
                        @error('keteranganTambahan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="self-end">
                        <button type="button" wire:click="stageTambahan"
                            class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded shadow">
                            Tambah
                        </button>
                    </div>
                </div>

                {{-- Draft (staging) tambahan untuk toko terpilih --}}
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <h4 class="text-sm font-semibold mb-2">Draft Tambahan</h4>
                    @php
                        $draft = collect($stagedTambahan)->where('tokos_id', $selectedTokoId)->values();
                    @endphp
                    @if ($draft->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-1 border">Qty</th>
                                        <th class="px-2 py-1 border">Keterangan</th>
                                        <th class="px-2 py-1 border text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($draft as $i => $row)
                                        <tr
                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                            <td class="px-2 py-1 border text-right">
                                                {{ number_format($row['jumlah_uang'], 2) }}</td>
                                            <td class="px-2 py-1 border">{{ $row['keterangan'] ?? '-' }}</td>
                                            <td class="px-2 py-1 border text-center">
                                                <button type="button"
                                                    wire:click="removeStaged({{ array_search($row, $stagedTambahan) }})"
                                                    class="px-2 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada draft.</p>
                    @endif
                </div>

                {{-- Riwayat tersimpan (DB) untuk toko terpilih --}}
                <div class="border-t border-gray-200 dark:border-zinc-700 pt-3">
                    <h4 class="text-sm font-semibold mb-2">Riwayat Tambahan</h4>
                    @if (!empty($riwayatTambahan))
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 dark:border-zinc-700">
                                <thead class="bg-gray-100 dark:bg-zinc-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-2 py-1 border">Tgl</th>
                                        <th class="px-2 py-1 border">Toko</th>
                                        <th class="px-2 py-1 border text-right">Qty</th>
                                        <th class="px-2 py-1 border">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($riwayatTambahan as $row)
                                        <tr
                                            class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900">
                                            <td class="px-2 py-1 border">{{ $row->created_at->format('d-m-Y H:i') }}
                                            </td>
                                            <td class="px-2 py-1 border">{{ $row->tokos->nmtoko ?? '-' }}</td>
                                            <td class="px-2 py-1 border text-right">
                                                {{ number_format($row->jumlah_uang, 2) }}</td>
                                            <td class="px-2 py-1 border">{{ $row->keterangan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada riwayat.</p>
                    @endif
                </div>

                {{-- Footer Modal --}}
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="saveTambahan"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                        Simpan Tambahan
                    </button>
                    <button type="button" wire:click="$set('showTambahModal', false)"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@once
<script>
document.addEventListener('alpine:init', () => {
  // Debounce util sederhana
  const debounce = (fn, wait = 300) => {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); };
  };

  Alpine.data('setoranPersist', (initialDate) => ({
    currDate: initialDate,

    key(d) {
      return `ks:setoran:inputs:${d}`;
    },

    // Restore dari localStorage -> isi langsung ke input DOM + dispatch 'input' agar Livewire sync
    load(d = this.currDate) {
      try {
        const raw = localStorage.getItem(this.key(d));
        if (!raw) return;
        const obj = JSON.parse(raw);

        for (const [tokosId, val] of Object.entries(obj)) {
          // cari input sesuai tokosId
          const sel = `input[wire\\:model\\.live="inputs.${tokosId}"]`;
          const el = document.querySelector(sel);
          if (!el || el.disabled) continue;

          // tulis ke DOM, lalu trigger reaktivitas Livewire
          el.value = val;
          el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        // jaga tampilan untuk semua input id="setoran-*"
        requestAnimationFrame(() => {
          document.querySelectorAll('input[id^="setoran-"]').forEach(el => {
            if (el.disabled) return;
            const m = (el.getAttribute('wire:model.live') || '').match(/inputs\.(\d+)/);
            if (!m) return;
            const v = obj[m[1]];
            if (v !== undefined) el.value = v;
          });
        });
      } catch (_) {}
    },

    saveDebounced: null,

    init() {
      // pertama kali masuk halaman → restore
      this.load(this.currDate);

      // simpan otomatis ke LS saat user mengetik
      this.saveDebounced = debounce(() => {
        const obj = {};
        document.querySelectorAll('input[id^="setoran-"]').forEach(el => {
          if (el.disabled) return;
          const val = (el.value || '').trim();
          if (!val) return;
          const m = (el.getAttribute('wire:model.live') || '').match(/inputs\.(\d+)/);
          if (m) obj[m[1]] = val;
        });
        localStorage.setItem(this.key(this.currDate), JSON.stringify(obj));
      }, 300);

      // dengarkan input pada kolom setoran
      document.addEventListener('input', (e) => {
        if (e.target && typeof e.target.id === 'string' && e.target.id.startsWith('setoran-')) {
          this.saveDebounced();
        }
      }, true);

      // setelah Livewire selesai update DOM (mis. ganti tanggal), restore lagi
      document.addEventListener('livewire:updated', () => {
        this.load(this.currDate);
      });

      // beri kesempatan setelah paint pertama (hindari ketimpa morph)
      setTimeout(() => this.load(this.currDate), 0);

      // hapus LS khusus tanggal aktif saat submit sukses (event dari server)
      window.addEventListener('setoran:clear-ls', (ev) => {
        const d = ev.detail?.date || this.currDate;
        localStorage.removeItem(this.key(d));
      });
    },

    // dipanggil otomatis via x-effect saat tanggalSetoran berubah
    switchDate(newDate) {
      if (!newDate || newDate === this.currDate) return;
      this.currDate = newDate;

      // kosongkan tampilan input dulu
      document.querySelectorAll('input[id^="setoran-"]').forEach(el => {
        if (!el.disabled) el.value = '';
      });

      // tunggu DOM baru dari Livewire, lalu restore dari LS tanggal baru
      setTimeout(() => this.load(newDate), 0);
    }
  }));
});
</script>
@endonce

