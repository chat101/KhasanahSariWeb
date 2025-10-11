<div>
    <div class="p-6 bg-white shadow rounded">

      {{-- Header + Datepicker --}}
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <h2 class="text-xl font-bold text-gray-800">REPORT HASIL PRODUKSI</h2>

        <div x-data="{}" class="flex items-center gap-2">
          <label for="tgl" class="text-sm text-gray-600">Hari / Tanggal:</label>
          <div wire:ignore>
            <input
              x-ref="tgl"
              id="tgl"
              type="text"
              placeholder="Pilih tanggal"
              class="rounded border-gray-300 text-sm focus:ring-0 focus:border-gray-500 px-2 py-1 w-40 bg-white" />
          </div>
          <script>
            document.addEventListener('livewire:navigated', () => {
              const el = document.querySelector('#tgl');
              if (!el._flatpickr) {
                flatpickr(el, {
                  dateFormat: 'Y-m-d',
                  defaultDate: @js($this->tanggal ?? now()->toDateString()),
                  onChange: function (selectedDates, dateStr) {
                    @this.set('tanggal', dateStr);
                  }
                });
              } else {
                el._flatpickr.setDate(@js($this->tanggal ?? now()->toDateString()), true);
              }
            });
          </script>
        </div>
      </div>

      {{-- Teks tanggal terpilih --}}
      <p class="text-sm text-gray-600 mb-2">
        @php $tglTampil = $this->tanggal ? \Carbon\Carbon::parse($this->tanggal) : now(); @endphp
        Tanggal : {{ $tglTampil->translatedFormat('l, d F Y') }}
      </p>

      <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 text-xs">
            <thead>
                <tr class="bg-yellow-400 text-black text-center font-bold">
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">NO</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">NO MESIN</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">NAMA MESIN</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">ID</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">NAMA PRODUK</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">TARGET GILING</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">PATOKAN</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL GILING</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL REAL</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">SELISIH HASIL</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">HASIL DEKOR</th>
                  <th colspan="2" class="border border-gray-300 px-2 py-1">REJECT PRODUKSI</th>
                  <th rowspan="2" class="border border-gray-300 px-2 py-1">KETERANGAN</th>
                </tr>
                <tr class="bg-sky-700 text-white">
                  <th class="border border-gray-300 px-2 py-1 text-center">REJECT / RETUR PRODUKSI</th>
                  <th class="border border-gray-300 px-2 py-1 text-center">KETERANGAN REJECT</th>
                </tr>
              </thead>


              <tbody>
                @php
                  $all    = collect($produksi ?? []);
                  $groups = $all->groupBy(fn($r) => $r['mesin_nama'] ?? 'Tanpa Mesin');

                  $noMesin = 1; // urutan mesin
                  $noProduk = 1; // urutan produk global
                @endphp

                @forelse($groups as $mesinNama => $rows)
                  @foreach($rows as $item)
                    <tr class="{{ $loop->even ? 'bg-green-50' : 'bg-blue-50' }} text-black">
                      {{-- NO PRODUK --}}
                      <td class="border border-gray-300 px-2 py-1 text-center">{{ $noProduk++ }}</td>

                      {{-- NO MESIN (rowspan hanya di baris pertama tiap grup) --}}
                      @if($loop->first)
                        <td class="border border-gray-300 px-2 py-1 text-center" rowspan="{{ $rows->count() + 1 }}">
                          {{ $noMesin }}
                        </td>
                        <td class="border border-gray-300 px-2 py-1 align-top font-semibold" rowspan="{{ $rows->count() + 1 }}">
                          {{ $mesinNama }}
                        </td>
                      @endif

                      {{-- ID PRODUK --}}
                      <td class="border border-gray-300 px-2 py-1 text-center">{{ $item['id'] ?? '' }}</td>

                      {{-- NAMA PRODUK --}}
                      <td class="border border-gray-300 px-2 py-1">{{ $item['nama_produk'] ?? '-' }}</td>

                      {{-- TARGET GILING --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['total_tong'] ?? 0)) }}</td>

                      {{-- PATOKAN --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['patokan_produk'] ?? 0)) }}</td>

                      {{-- HASIL GILING --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['total_target'] ?? 0)) }}</td>

                      {{-- HASIL REAL --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['hasil_real'] ?? 0)) }}</td>

                      {{-- SELISIH --}}
                      <td class="border border-gray-300 px-2 py-1 text-right
                      {{ ($item['selisih_hasil'] ?? 0) < 0 ? 'text-red-600 font-bold' : '' }}">
                      {{ number_format((int)($item['selisih_hasil'] ?? 0)) }}
                  </td>

                      {{-- HASIL DEKOR --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['hasil_dekor'] ?? 0)) }}</td>

                      {{-- REJECT --}}
                      <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format((int)($item['reject'] ?? 0)) }}</td>

                      {{-- KETERANGAN REJECT --}}
                      <td class="border border-gray-300 px-2 py-1">
                        <ul class="list-disc pl-4">
                            @forelse(($item['reject_detail'] ?? []) as $label => $jumlah)
                            @if($jumlah > 0)
                                <li>{{ $label }} = {{ $jumlah }}</li>
                            @endif
                        @empty
                            <li>-</li>
                        @endforelse

                        </ul>
                      </td>

                      {{-- KETERANGAN --}}
                      <td class="border border-gray-300 px-2 py-1">{{ $item['keterangan_reject'] ?? '' }}</td>
                    </tr>
                  @endforeach

                  {{-- SUBTOTAL Mesin --}}
                  <tr class="bg-green-200 font-bold text-black ">
                    <td colspan="5" class="border border-gray-300 px-2 py-1 text-center">
                      TOTAL MESIN {{ strtoupper($mesinNama) }}
                    </td>
                    <td class="border border-gray-300 px-2 py-1 text-right ">{{ number_format($rows->sum('total_tong')) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">—</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($rows->sum('total_target')) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($rows->sum('hasil_real')) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($rows->sum('selisih_hasil')) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($rows->sum('hasil_dekor')) }}</td>
                    <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($rows->sum('reject')) }}</td>

                  </tr>

                  @php $noMesin++; @endphp
                @empty
                  <tr>
                    <td colspan="14" class="text-center text-gray-500 py-2">Tidak ada data</td>
                  </tr>
                @endforelse
                      {{-- GRAND TOTAL --}}
                @php
                $grandTotalTong   = $all->sum('total_tong');
                $grandTotalTarget = $all->sum('total_target');
                $grandHasilReal   = $all->sum('hasil_real');
                $grandSelisih     = $all->sum('selisih_hasil');
                $grandHasilDekor  = $all->sum('hasil_dekor');
                $grandReject      = $all->sum('reject');
            @endphp

            <tr class="bg-yellow-400 text-black font-extrabold uppercase">
              <td colspan="5" class="border border-gray-400 px-2 py-2 text-center">
                GRAND TOTAL SELURUH MESIN
              </td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandTotalTong) }}</td>
              <td class="border border-gray-400 px-2 py-2 text-right">—</td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandTotalTarget) }}</td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandHasilReal) }}</td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandSelisih) }}</td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandHasilDekor) }}</td>
              <td class="border border-gray-400 px-2 py-2 text-right">{{ number_format($grandReject) }}</td>
              <td colspan="2" class="border border-gray-400 px-2 py-2 text-center">—</td>
            </tr>

              </tbody>


        </table>
      </div>

    </div>
  </div>
