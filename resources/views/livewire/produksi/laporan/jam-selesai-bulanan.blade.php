<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z"/>
        </svg>
        <h2 class="text-base font-semibold">Rekap Ketepatan Waktu Bulanan (Planned vs Actual)</h2>
      </div>

      <div class="space-x-2 text-sm">
        <label class="font-medium">Pilih Bulan:</label>
        <input type="month" wire:model.live="periode" class="border rounded px-2 py-1 text-sm dark:bg-zinc-800 dark:border-zinc-600">
        <button
          wire:click="export"
          class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-xs font-medium transition"
        >
          Export
        </button>
      </div>
    </div>

    {{-- Table --}}
    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
          <tr>
            <th class="border border-gray-200 dark:border-zinc-700 px-2 py-2 text-left w-16">No</th>
            <th class="border border-gray-200 dark:border-zinc-700 px-3 py-2 text-left min-w-[180px]">Grup Job</th>
            @foreach($this->days as $d)
              <th class="border border-gray-200 dark:border-zinc-700 px-2 py-2 text-center w-12">{{ $d }}</th>
            @endforeach
          </tr>
        </thead>

        <tbody class="text-indigo-700 dark:text-zinc-200">
          @forelse($rows as $row)
            <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-zinc-800 dark:even:bg-zinc-900 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
              <td class="border border-gray-200 dark:border-zinc-700 px-2 py-1.5">{{ $row['no'] }}</td>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-1.5 font-medium text-white-900 dark:text-zinc-100">
                {{ $row['produk'] }}
              </td>

              @foreach($this->days as $d)
                @php
                  $val = $row['days'][$d] ?? null; // selisih (menit), boleh negatif/positif
                  // pewarnaan ringan: merah=terlambat, hijau=lebih cepat, biru=tepat=0, abu=kosong
                  $cellBase = 'border border-gray-200 dark:border-zinc-700 px-2 py-1.5 text-right tabular-nums';
                  $cls =
                    $val === null ? '' :
                    ($val > 0 ? ' bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300' :
                    ($val < 0 ? ' bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' :
                                 ' bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-300'));
                @endphp
                <td class="{{ $cellBase }}{{ $cls }}">
                  {{ $val !== null ? number_format($val) : '' }}
                </td>
              @endforeach
            </tr>
          @empty
            <tr>
              <td class="border border-gray-200 dark:border-zinc-700 px-3 py-6 text-center text-gray-500 dark:text-gray-400" colspan="{{ 2 + count($this->days) }}">
                Tidak ada data pada bulan ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
