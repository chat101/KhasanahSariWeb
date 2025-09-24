<div class="bg-white dark:bg-zinc-800 rounded-lg shadow border border-gray-200 dark:border-zinc-700">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
        <path d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z"/>
      </svg>
      <h2 class="text-base font-semibold">Laporan Ketepatan Waktu Selesai (Planned vs Actual)</h2>
    </div>

    @php
      $rows = collect($lapKetepatan ?? []);
      $grouped = $rows->groupBy('tanggal')  ->sortKeysDesc();          // urut tanggal ASC
    @endphp

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-zinc-900 text-gray-600 dark:text-gray-300 sticky top-0 z-10">
          <tr>
            <th class="px-3 py-2 text-left w-28">Tanggal</th>
            {{-- <th class="px-3 py-2 text-left">Perintah/WO</th> --}}
            <th class="px-3 py-2 text-left">Grup Job</th>
            <th class="px-3 py-2 text-right">Planned Selesai</th>
            <th class="px-3 py-2 text-right">Actual Selesai</th>
            <th class="px-3 py-2 text-right">Selisih (menit)</th>
            <th class="px-3 py-2 text-center">Status</th>
            <th class="px-3 py-2 text-left">Keterangan</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
          @forelse ($grouped as $tgl => $items)
            {{-- Header tebal per tanggal --}}
            <tr>
              <td colspan="7" class="bg-gray-100 dark:bg-zinc-900/60 font-semibold px-3 py-2 border-t-2 border-gray-300 dark:border-zinc-600">
                <div class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6.75 3a.75.75 0 01.75.75V5h9V3.75a.75.75 0 011.5 0V5h.75A2.25 2.25 0 0121 7.25v11A2.25 2.25 0 0118.75 20.5H5.25A2.25 2.25 0 013 18.25v-11A2.25 2.25 0 015.25 5H6V3.75A.75.75 0 016.75 3zM4.5 9v9.25c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V9H4.5z"/>
                  </svg>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ optional(\Carbon\Carbon::make($tgl))->format('d-m-Y') ?? $tgl }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">• {{ $items->count() }} grup</span>
                </div>
              </td>
            </tr>

            @foreach ($items as $r)
              @php
                $planned = $r['planned'] ?? null;
                $actual  = $r['actual'] ?? null;
                $diff    = $r['selisih_menit'] ?? null;
                $status  = $r['status'] ?? null;

                $badgeClass = match ($status) {
                  'Terlambat'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                  'Lebih Cepat' => 'bg-green-500 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                  'Tepat Waktu' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                  default       => 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-zinc-200',
                };
              @endphp

              <tr class="bg-white/60 dark:bg-zinc-800/60 hover:bg-indigo-50/60 dark:hover:bg-zinc-700/50 transition">
                <td class="px-3 py-2 align-top">
                  {{ optional(\Carbon\Carbon::make($tgl))->format('d-m-Y') ?? $tgl }}
                </td>

                {{-- <td class="px-3 py-2 align-top">—</td> --}}

                <td class="px-3 py-2 align-top">
                  <div class="font-medium text-white-900 dark:text-zinc-100">
                    {{ $r['kategori_job'] ?? '-' }}
                  </div>
                </td>

                <td class="px-3 py-2 text-right align-top tabular-nums">
                  {{ $planned ?? '-' }}
                </td>

                <td class="px-3 py-2 text-right align-top tabular-nums">
                  {{ $actual ?? '-' }}
                </td>

                <td class="px-3 py-2 text-right align-top tabular-nums">
                  {{ $diff !== null ? number_format($diff) : '-' }}
                </td>

                <td class="px-3 py-2 text-center align-top">
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                    {{ $status ?? '-' }}
                  </span>
                </td>

                <td class="px-3 py-2 align-top">
                  {{ $r['keterangan'] ?? '-' }}
                </td>
              </tr>
            @endforeach
          @empty
            <tr>
              <td colspan="7" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                Belum ada data.
              </td>
            </tr>
          @endforelse
        </tbody>

        {{-- (Opsional) Footer ringkasan global --}}
        {{-- <tfoot class="bg-gray-50 dark:bg-zinc-900 text-sm">
          <tr>
            <td class="px-3 py-2 font-medium" colspan="4">Ringkasan</td>
            <td class="px-3 py-2 text-right font-semibold">{{ number_format($summary['avg_diff_minutes'] ?? 0, 1) }}</td>
            <td class="px-3 py-2 text-center" colspan="2">
              OTP: {{ number_format($summary['otp_percent'] ?? 0, 1) }}%
            </td>
          </tr>
        </tfoot> --}}
      </table>
    </div>
  </div>
