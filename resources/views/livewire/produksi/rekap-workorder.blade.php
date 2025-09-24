<div class="p-2 space-y-4 bg-gray-100 text-gray-800 text-sm rounded-lg shadow-lg">

    {{-- Notification --}}
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-4 left-1/2 transform -translate-x-1/2 w-64 bg-green-200 text-green-800 rounded-lg shadow-lg z-50">
        <div class="flex items-center justify-between px-3 py-2">
            <span class="text-xs font-medium">{{ session('message') }}</span>
            <button @click="show = false" class="text-green-800 hover:text-green-600 text-sm">✕</button>
        </div>
    </div>
    @endif

    <div class="w-full px-2 sm:px-4 py-6 bg-white border border-gray-300 rounded-xl shadow-md">

        <!-- Header: Tanggal dan Tombol -->
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-4">
            <div class="w-full sm:max-w-xs"
                 x-data
                 x-init="flatpickr($refs.tanggalInput, {
                     defaultDate: '{{ $tanggalProduksi }}',
                     dateFormat: 'Y-m-d',
                     onChange: (selectedDates, dateStr) => @this.set('tanggalProduksi', dateStr)
                 })">
                <label for="tanggalProduksi" class="block text-sm font-semibold text-gray-700 mb-1">
                    Tanggal Produksi:
                </label>
                <input
                    type="text"
                    x-ref="tanggalInput"
                    class="w-full border rounded px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-200"
                    placeholder="Pilih tanggal" />
            </div>


        </div>

        <!-- Scrollable Table -->
        <div class="overflow-x-auto">
            <table class="text-xs sm:text-sm w-full text-left text-gray-600  min-w-[600px]">
                <thead class="bg-blue-300 text-gray-800 sticky top-0 z-10">
                    <tr>
                        <th class="px-2 py-2">No</th>
                        <th class="px-1 sm:px-2 py-1 sm:py-2">Nama Barang</th>
                        <th class="px-2 py-2 text-center">Jumlah Tong</th>
                        <th class="px-2 py-2 text-center">Standart Tong</th>
                        <th class="px-2 py-2 text-right">Target Produksi</th>
                    </tr>
                </thead>
                <tbody  class="divide-y divide-gray-200 bg-white">
                    @forelse($produkList as $p)
                    @php
                    $dataProduksi = $produk[$p['id']] ?? null;
                @endphp
                    <tr>
                        <td class=>{{ $loop->iteration }}</td>
                        <td class="text-black-300">{{ $p['nama'] }}</td>
                        <td class=" text-center">
                            @if($dataProduksi && isset($dataProduksi['detail_perintah_produksi']))
                                @foreach($dataProduksi['detail_perintah_produksi'] as $detail)
                                    {{ $detail['produksi_qty'] }}<br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        {{-- <td class="text-black-300">{{$dataProduksi->patokan}}</td> --}}
                        <td class=" text-center">{{ isset($dataProduksi['patokan']) ? number_format($dataProduksi['patokan'], 0, ',', '.') : '-' }}</td>
                        <td class="px-2 py-2 text-right font-semibold">
                            @if($dataProduksi && isset($dataProduksi['detail_perintah_produksi']) && isset($dataProduksi['patokan']))
                                @foreach($dataProduksi['detail_perintah_produksi'] as $detail)
                                    {{ number_format($detail['produksi_qty'] * $dataProduksi['patokan'], 0, ',', '.') }}<br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-4">Tidak ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


</div>
