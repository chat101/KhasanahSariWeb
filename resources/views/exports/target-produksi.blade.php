@php
    use Carbon\Carbon;

    // Reusable inline styles (Excel HTML reader-friendly)
    $tableStyle = 'border-collapse:collapse;width:100%;font-size:10px';
    $cellStyle = 'border:1px solid #000;padding:4px';
    $thStyle = 'border:1px solid #000;padding:4px;background:#eee;font-weight:bold;text-align:left';
    $tdRight = $cellStyle . ';text-align:right';
    $tdCenter = $cellStyle . ';text-align:center';
    // ==> Tambahkan style persen
    $tdPercent = $cellStyle . ";text-align:right;mso-number-format:'0,00%'";
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Target Produksi - {{ $tanggalProduksi }}</title>
</head>

<body>
    <h3>Target Produksi - {{ $tanggalProduksi }}</h3>

    {{-- Tabel PRODUK --}}
    <p style="font-weight:bold;margin:12px 0 6px 0">Produksi (Per Produk)</p>
    <table border="1" style="{{ $tableStyle }}">
        <thead>
            <tr>
                <th style="{{ $thStyle }}">PRODUK</th>
                <th style="{{ $thStyle }}">TONG</th>
                <th style="{{ $thStyle }}">PCS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produkList as $p)
                @php $dataProduksi = $produk[$p['id']] ?? null; @endphp
                <tr>
                    <td style="{{ $cellStyle }}">{{ $p['nama'] }}</td>
                    <td style="{{ $tdRight }}">
                        {{ isset($dataProduksi['total_produksi_qty']) ? number_format($dataProduksi['total_produksi_qty'], 0, ',', '.') : '-' }}
                    </td>
                    <td style="{{ $tdRight }}">
                        {{ isset($dataProduksi['total_target_produksi']) ? number_format($dataProduksi['total_target_produksi'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tabel METODE --}}
    <p style="font-weight:bold;margin:12px 0 6px 0">Target Produksi (Metode)</p>
    <table border="1" style="{{ $tableStyle }}">
        <thead>
            <tr>
                <th style="{{ $thStyle }}">METODE</th>
                <th style="{{ $thStyle }}">TONG</th>
                <th style="{{ $thStyle }}">PCS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($metodeList as $m)
                @php
                    $summary = collect($metodeSummary)->firstWhere('metode', $m) ?? [];
                    $qty = $summary['total_produksi_qty'] ?? 0;
                    $target = $summary['total_target_produksi'] ?? 0;
                @endphp
                <tr>
                    <td style="{{ $cellStyle }}">{{ $m }}</td>
                    <td style="{{ $tdRight }}">{{ number_format($qty, 0, ',', '.') }}</td>
                    <td style="{{ $tdRight }}">{{ number_format($target, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Tabel JOB SUMMARY --}}
    <p style="font-weight:bold;margin:12px 0 6px 0">Target Produksi (Kegiatan)</p>
    <table border="1" style="{{ $tableStyle }}">
        <thead>
            <tr>
                <th style="{{ $thStyle }}">KEGIATAN</th>
                <th style="{{ $thStyle }}">JML KARYAWAN</th>
                <th style="{{ $thStyle }}">TARGET PRODUKSI</th>
                <th style="{{ $thStyle }}">TARGET (Jam/Org)</th>
                <th style="{{ $thStyle }}">UNIT</th>
                <th style="{{ $thStyle }}">JAM (Mulai)</th>
                <th style="{{ $thStyle }}">JAM (Selesai, Est)</th>
                <th style="{{ $thStyle }}">PRODUKTIVITAS (Waktu)</th>
                <th style="{{ $thStyle }}">JAM (Selesai DB)</th>
                <th style="{{ $thStyle }}">%</th>
                <th style="{{ $thStyle }}">% real</th> {{-- <— TAMBAH --}}
                <th style="{{ $thStyle }}">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $lastGroup = null;
                $groupOrang = 0;
                $groupJobCount = 0;
                $groupTotalMenit = 0; // planned minutes = ∑(rasio*60)
                $groupJamMulai = null; // earliest start in group
                $groupMaxWaktuSelesai = null; // latest REAL finish in group
                $groupTotalRasio = 0.0; // ∑rasio (jam)
                $breakMinutes = 120; // potong istirahat 2 jam dari durasi aktual

                // Subtotal row printer
                $flushSubtotal = function () use (
                    &$groupOrang,
                    &$groupJobCount,
                    &$groupTotalMenit,
                    &$groupJamMulai,
                    &$groupMaxWaktuSelesai,
                    &$groupTotalRasio,
                    $cellStyle,
                    $tdCenter,
                    $tdRight,
                    $tdPercent,
                    $breakMinutes,
                ) {
                    if ($groupJobCount === 0) {
                        return '';
                    }

                    $rataOrang = $groupJobCount ? $groupOrang / max(1, $groupJobCount) : 0;
                    $subtotalSelesai = $groupJamMulai
                        ? $groupJamMulai->copy()->addMinutes((int) round($groupTotalMenit))
                        : null;

                    // % planned (pecahan 0..1)
                    $plannedFrac = $groupTotalMenit > 0 ? $groupTotalMenit / (8 * 60) : 0.0;

                    // % real
                    $hasAnyReal = !is_null($groupMaxWaktuSelesai) && $groupJamMulai;
                    $rawMinutes = $hasAnyReal ? $groupJamMulai->diffInMinutes($groupMaxWaktuSelesai) : 0;
                    $actualMins = $hasAnyReal ? max(0, $rawMinutes - $breakMinutes) : 0;
                    $workloadMin = (float) $groupTotalRasio * 60.0;
                    $realFrac = $hasAnyReal && $actualMins > 0 ? $workloadMin / $actualMins : 0.0;

                    $html = '<tr>';
                    $html .= '<td style="' . $cellStyle . ';font-weight:bold">SUBTOTAL GRUP</td>';
                    $html .= '<td style="' . $tdCenter . '">' . number_format($rataOrang, 0, ',', '.') . '</td>';
                    $html .= '<td style="' . $cellStyle . '"></td>'; // TARGET PRODUKSI
                    $html .= '<td style="' . $cellStyle . '"></td>'; // TARGET (Jam/Org)
                    $html .= '<td style="' . $cellStyle . '"></td>'; // UNIT
                    $html .=
                        '<td style="' .
                        $tdCenter .
                        '">' .
                        ($groupJamMulai ? $groupJamMulai->format('H:i') : '') .
                        '</td>';
                    $html .=
                        '<td style="' .
                        $tdCenter .
                        '">' .
                        ($subtotalSelesai ? $subtotalSelesai->format('H:i') : '') .
                        '</td>';
                    $html .=
                        '<td style="' .
                        $cellStyle .
                        '">' .
                        floor($groupTotalMenit / 60) .
                        ' jam ' .
                        (int) $groupTotalMenit % 60 .
                        ' menit</td>';
                    $html .=
                        '<td style="' .
                        $tdCenter .
                        '">' .
                        ($groupMaxWaktuSelesai ? $groupMaxWaktuSelesai->format('H:i') : '-') .
                        '</td>';

                    // Kolom % planned (pecahan)
                    $html .= '<td style="' . $tdPercent . '">' . sprintf('%.10F', $plannedFrac) . '</td>';

                    // Kolom % real (pecahan) — 0 jika belum ada jam real
                    $html .= '<td style="' . $tdPercent . '">' . sprintf('%.10F', $realFrac) . '</td>';

                    $html .= '<td style="' . $cellStyle . '"></td>'; // Keterangan
                    $html .= '</tr>';

                    // reset group accumulators
                    $groupOrang = 0;
                    $groupJobCount = 0;
                    $groupTotalMenit = 0;
                    $groupJamMulai = null;
                    $groupMaxWaktuSelesai = null;
                    $groupTotalRasio = 0.0;

                    return $html;
                };
            @endphp

            @foreach ($jobSummary as $job)
                @php
                    $group = $job['group_job'];

                    if (!is_null($lastGroup) && $lastGroup !== $group) {
                        echo $flushSubtotal();
                    }

                    $jmlOrang = (int) ($job['jml_orang'] ?? 0);
                    $produksi = (float) ($job['target_produksi'] ?? 0);
                    $target = (float) ($job['target'] ?? 0);
                    $unit = $job['unit'] ?? '';
                    $jamMulai = $job['jam_mulai'] ? Carbon::parse($job['jam_mulai']) : null;

                    // Rasio (jam) = produksi / (target * jml_orang)
                    $rasio = $target > 0 && $jmlOrang > 0 ? $produksi / ($target * $jmlOrang) : 0.0;
                    $totalMenit = $rasio * 60.0;
                    $jam = floor($totalMenit / 60);
                    $menit = (int) fmod($totalMenit, 60);

                    $jamSelesaiEst = $jamMulai ? $jamMulai->copy()->addMinutes($totalMenit) : null;
                    $waktuSelesaiDb = !empty($job['waktu_selesai']) ? Carbon::parse($job['waktu_selesai']) : null;

                    // Accumulate per group
                    $groupOrang += $jmlOrang;
                    $groupJobCount++;
                    $groupTotalMenit += $totalMenit; // for % planned
                    $groupTotalRasio += (float) $rasio; // for % real
                    $groupJamMulai = $groupJamMulai ?: $jamMulai;

                    if ($waktuSelesaiDb && (!$groupMaxWaktuSelesai || $waktuSelesaiDb->gt($groupMaxWaktuSelesai))) {
                        $groupMaxWaktuSelesai = $waktuSelesaiDb; // keep latest REAL finish
                    }

                    $jobDenganDesimal = ['PECAH TELUR', 'GILING'];
                    $produksiFormatted = in_array(strtoupper($job['nama_job']), $jobDenganDesimal)
                        ? number_format($produksi, 2, ',', '.')
                        : number_format((int) $produksi, 0, ',', '.');

                    $lastGroup = $group;
                @endphp

                <tr>
                    <td style="{{ $cellStyle }}">{{ $job['nama_job'] }}</td>
                    <td style="{{ $tdCenter }}">{{ $jmlOrang }}</td>
                    <td style="{{ $tdRight }}">{{ $produksiFormatted }}</td>
                    <td style="{{ $tdRight }}">{{ $target }}</td>
                    <td style="{{ $tdCenter }}">{{ $unit }}</td>
                    <td style="{{ $tdCenter }}">{{ $jamMulai ? $jamMulai->format('H:i') : '' }}</td>
                    <td style="{{ $tdCenter }}">{{ $jamSelesaiEst ? $jamSelesaiEst->format('H:i') : '' }}</td>
                    <td style="{{ $cellStyle }}">
                        {{ ($jam > 0 ? $jam . ' jam ' : '') . ($menit > 0 ? $menit . ' menit' : ($jam === 0 ? '0 menit' : '')) }}
                    </td>
                    <td style="{{ $tdCenter }}">{{ $waktuSelesaiDb ? $waktuSelesaiDb->format('H:i') : '' }}</td>

                    {{-- % per baris = porsi terhadap 8 jam (rasio/8) agar konsisten dengan subtotal planned --}}
                    <td style="{{ $tdPercent }}">{{ sprintf('%.10F', $rasio / 8) }}</td>

                    {{-- % real per baris dibiarkan kosong (hitungnya hanya di subtotal) --}}
                    <td style="{{ $tdPercent }}">{{ sprintf('%.10F', 0) }}</td>

                    <td style="{{ $cellStyle }}">{{ $job['keterangan'] ?? '' }}</td>
                </tr>
            @endforeach

            {!! $flushSubtotal() !!}
        </tbody>
    </table>

</body>

</html>
