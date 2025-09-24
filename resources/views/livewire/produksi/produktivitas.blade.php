                <!-- Baris Datepicker -->
                <div>
                    <div class="flex items-center mb-4 gap-2">
                        <div x-data x-init="flatpickr($refs.tanggalInput, { dateFormat: 'Y-m-d' })" class="flex items-center gap-2">
                            <label class="text-sm font-semibold text-gray-700">Tanggal Produksi:</label>
                            <input type="text" x-ref="tanggalInput"
                                class="px-3 py-1 text-sm border border-gray-300 rounded shadow-sm focus:outline-none focus:ring focus:border-blue-300"
                                wire:model.defer="tanggalProduksi" placeholder="Pilih tanggal">
                        </div>

                        <button wire:click="cariData"
                            class="px-4 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded shadow-sm transition">
                            Cari
                        </button>

                        <button wire:click="exportExcel"
                            class="px-3 py-1 text-sm bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded shadow-sm transition">
                            Export Excel
                        </button>
                        <!-- Tombol baru -->
                        <button wire:click="exportAnalisaExcel"
                            class="px-3 py-1 text-sm bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded shadow-sm transition">
                            Export Analisa
                        </button>
                    </div>
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- KIRI: Produk + Metode -->
                        <div class="w-full lg:max-w-[290px] space-y-4">
                            <!-- Target Produksi (Item) -->
                            <div class="bg-black text-white rounded p-4 shadow">
                                <h2 class="text-yellow-400 font-bold mb-2">Target Produksi (Item)</h2>
                                <table class="w-full text-xs border-separate border-spacing-y-1">
                                    <thead>
                                        <tr class="text-left border-b border-gray-600">
                                            <th>PRODUK</th>
                                            <th>TONG</th>
                                            <th>PCS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grandProd = 0;
                                            $grandTarget = 0;
                                        @endphp
                                        @foreach ($produkList as $p)
                                            @php
                                                $dataProduksi = $produk[$p['id']] ?? null;
                                                $prod = (int) ($dataProduksi['total_produksi_qty'] ?? 0);
                                                $targi = (int) ($dataProduksi['total_target_produksi'] ?? 0);
                                                $grandProd += $prod;
                                                $grandTarget += $targi;
                                            @endphp
                                            <tr>
                                                <td class="text-cyan-300">{{ $p['nama'] }}</td>
                                                <td>{{ isset($dataProduksi['total_produksi_qty']) ? number_format($dataProduksi['total_produksi_qty'], 0, ',', '.') : '-' }}
                                                </td>
                                                <td>{{ isset($dataProduksi['total_target_produksi']) ? number_format($dataProduksi['total_target_produksi'], 0, ',', '.') : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="font-semibold">
                                            <td class="pr-2">TOTAL</td>
                                            <td>{{ number_format($grandProd, 0, ',', '.') }}</td>
                                            <td>{{ number_format($grandTarget, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Target Produksi (Metode) -->
                            <div class="bg-black text-white rounded p-4 shadow">
                                <h2 class="text-yellow-400 font-bold mb-2">Target Produksi (Metode)</h2>
                                <table class="w-full text-xs border-separate border-spacing-y-1">
                                    <thead>
                                        <tr class="text-left border-b border-gray-600">
                                            <th>METODE</th>
                                            <th>TONG</th>
                                            <th>PCS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($metodeList as $m)
                                            @php
                                                $summary = collect($metodeSummary)->firstWhere('metode', $m);
                                                $qty = $summary['total_produksi_qty'] ?? 0;
                                                $target = $summary['total_target_produksi'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td class="text-cyan-300">{{ $m }}</td>
                                                <td>{{ number_format($qty, 0, ',', '.') }}</td>
                                                <td>{{ number_format($target, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="border-t border-gray-600 font-semibold">
                                            <td class="text-Center">Total</td>
                                            <td>{{ number_format($metodeTotalTong, 0, ',', '.') }}</td>
                                            <td>{{ number_format($metodeTotalPcs, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- KANAN: Target Produksi -->
                        <div class="w-full lg:w-full bg-black text-white rounded p-4 shadow overflow-auto">
                            <div class="flex justify-between text-sm text-yellow-200 mb-2">
                                <span>Target Produksi:</span>
                            </div>
                            <table class="w-full text-xs border-separate border-spacing-y-1">
                                <thead>
                                    <tr class="text-center border-b border-white">
                                        <th hidden>Group</th>
                                        <th class="text-white w-35">KEGIATAN</th>
                                        <th class="text-white w-25">JML KARYAWAN</th>
                                        <th class="text-white w-30">TARGET PRODUKSI</th>
                                        <th class="text-red-500 w-30">TARGET (Jam/Org)</th>
                                        <th class="text-red-500 w-15">UNIT</th>
                                        <th class="text-red-500 hidden">PRODUKTIVITAS</th>
                                        <th class="text-red-500 w-25">JAM (Mulai)</th>
                                        <th class="text-white w-25">JAM (Selesai)</th>
                                        <th class="text-red-500 w-40">PRODUKTIVITAS (Waktu)</th>
                                        <th class="text-red-500">SELESAI</th>
                                        <th class="text-red-500">%</th>
                                        <th class="text-red-500">% real</th>
                                        <th class="text-red-500">KETERANGAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tableRows as $row)
                                        @if ($row['row_type'] === 'item')
                                            <tr class="{{ $row['text_class'] }} text-center align-middle">
                                                <td hidden>{{ $row['group_job'] }}</td>
                                                <td class="text-left">{{ $row['nama_job'] }}</td>
                                                <td>{{ $row['jml_orang'] }}</td>
                                                <td>{{ $row['produksi_fmt'] }}</td>
                                                <td>{{ $row['target'] }}</td>
                                                <td>{{ $row['unit'] }}</td>
                                                <td>{{ $row['jam_mulai_fmt'] }}</td>
                                                <td>{{ $row['jam_selesai_plan_fmt'] }}</td>
                                                <td>{{ $row['waktu_planned_text'] }}</td>
                                                <td>{{ $row['selesai_real_fmt'] }}</td>
                                                <td></td>
                                                <td></td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="14" class="border-t border-white"></td>
                                            </tr>
                                            <tr class="font-bold text-white bg-black text-center">
                                                <td colspan="1"></td>
                                                <td>{{ number_format($row['rata_orang'], 0, ',', '.') }}</td>
                                                <td colspan="2"></td>
                                                <td></td>
                                                <td>{{ $row['jam_mulai_fmt'] }}</td>
                                                <td>{{ $row['jam_selesai_plan_fmt'] }}</td>
                                                <td>{{ $row['waktu_planned_text'] }}</td>
                                                <td>{{ $row['selesai_real_fmt'] }}</td>
                                                <td>{{ number_format($row['percent_planned'], 2, ',', '.') }} %</td>
                                                <td>{{ number_format($row['percent_real'], 2, ',', '.') }} %</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="14"></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
