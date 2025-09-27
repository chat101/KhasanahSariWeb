<div>
    <div>
      <div class="p-6 bg-white shadow rounded">

        {{-- ====== FLATPICKR (CDN) ====== --}}
        @once
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
          <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
          <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        @endonce

        {{-- Header + Datepicker --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
          <h2 class="text-xl font-bold text-gray-800">REPORT HASIL PRODUKSI</h2>

          <div x-data="{}" class="flex items-center gap-2">
            <label for="tgl" class="text-sm text-gray-600">Hari / Tanggal:</label>

            {{-- penting: wire:ignore supaya flatpickr tidak ter-unmount saat re-render --}}
            <div wire:ignore>
              <input
                x-ref="tgl"
                id="tgl"
                type="text"
                placeholder="Pilih tanggal"
                class="rounded border-gray-300 text-sm focus:ring-0 focus:border-gray-500 px-2 py-1 w-40 bg-white" />
            </div>

            <script>
              // init setelah setiap navigasi/render Livewire v3
              document.addEventListener('livewire:navigated', () => {
                const el = document.querySelector('#tgl');
                if (!el._flatpickr) { // cegah init ganda
                  flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    defaultDate: @js($this->tanggal ?? now()->toDateString()),
                    onChange: function (selectedDates, dateStr) {
                      @this.set('tanggal', dateStr);
                    }
                  });
                } else {
                  // kalau tanggal di server berubah, sync ke input
                  el._flatpickr.setDate(@js($this->tanggal ?? now()->toDateString()), true);
                }
              });
            </script>
          </div>
        </div>

        {{-- Teks tanggal terpilih --}}
        <p class="text-sm text-gray-600 mb-2">
          @php
            $tglTampil = $this->tanggal ? \Carbon\Carbon::parse($this->tanggal) : now();
          @endphp
          Tanggal : {{ $tglTampil->translatedFormat('l, d F Y') }}
        </p>

        {{-- DEBUG singkat (boleh hapus) --}}
        {{-- <div class="text-xs text-gray-500 mb-2">Tanggal: {{ $this->tanggal }} | Rows: {{ is_countable($produksi ?? []) ? count($produksi ?? []) : 0 }}</div> --}}

        <div class="overflow-x-auto">
          <table class="w-full border border-gray-300 text-xs">
            <thead>
              <tr class="bg-yellow-400 text-black text-center font-bold">
                <th rowspan="2" class="border border-gray-300 px-2 py-1">NO</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">ID</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">NAMA PRODUK</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">TARGET GILING</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">PATOKAN</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL GILING</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL REAL</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">SELISIH HASIL</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL DEKOR</th>
                <th class="border border-gray-300 px-2 py-1">REJECT PRODUKSI</th>
                <th rowspan="2" class="border border-gray-300 px-2 py-1">KETERANGAN</th>
              </tr>
            </thead>

            <tbody>
              @forelse (($produksi ?? []) as $i => $item)
                <tr class="{{ $loop->even ? 'bg-green-50' : 'bg-blue-50' }} text-black">
                  <td class="border border-gray-300 px-2 py-1 text-center">{{ $i + 1 }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-center">{{ $item['id'] ?? '' }}</td>
                  <td class="border border-gray-300 px-2 py-1">{{ $item['nama_produk'] ?? '-' }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['total_tong'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['patokan_produk'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['total_target'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['hasil_real'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['selisih_hasil'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['hasil_dekor'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($item['reject'] ?? 0) }}</td>
                  <td class="border border-gray-300 px-2 py-1">{{ $item['keterangan'] ?? '' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="11" class="text-center text-gray-500 py-2">Tidak ada data</td>
                </tr>
              @endforelse

              {{-- TOTAL MESIN --}}
              @php $total = $total ?? ['target' => 0]; @endphp
              <tr class="bg-green-300 font-bold">
                <td colspan="3" class="border border-gray-300 px-2 py-1 text-center">TOTAL MESIN</td>
                <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($total['target']) }}</td>
                <td colspan="7" class="border border-gray-300"></td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
