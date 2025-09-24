<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AnalisaArrayExport implements FromArray, WithEvents, WithColumnWidths
{
    public function __construct(
        public string $tanggal,
        public array  $produk,            // [product_id => ['total_produksi_qty'=>..., 'total_target_produksi'=>...], ...]
        public array  $produkList,        // [['id'=>..,'nama'=>..], ...]
        public array  $jobSummary,        // [['job_id'=>..,'nama_job'=>..,'group_job'=>..,'jml_orang'=>..,'target_produksi'=>..,'target'=>..], ...]
        public array  $productJobMap,     // [product_id => [job_id, job_id, ...]]
        public array  $tongBesarProduk = [],   // [product_id => float]
        public array  $patokanProduk   = [],   // [product_id => float]
        public int    $shiftMinutes    = 480 ,  // tersisa bila nanti dipakai, tidak digunakan di versi ini

    ) {}

    /** Range kolom persen untuk diformat setelah sheet dibuat */
    private array $percentRanges = [];
    private int $rasioStartRow = 0;   // NEW
    private int $rasioEndRow   = 0;   // NEW
    /** Normalisasi angka: "18.770" -> 18770, "40,4" -> 40.4, 18770 -> 18770.0 */
    private function num(mixed $v): float
    {
        if (is_string($v)) {
            $s = trim(str_replace(["\u{00A0}", ' '], '', $v)); // hapus NBSP & spasi
            // decimal comma: "1.234,56" atau "40,4"
            if (preg_match('/^\d{1,3}(\.\d{3})*,\d+$/', $s) || preg_match('/^\d+,\d+$/', $s)) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
                return (float)$s;
            }
            // ribuan koma (US): "1,234.56"
            if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $s)) {
                $s = str_replace(',', '', $s);
                return (float)$s;
            }
            // angka biasa / ribuan titik tanpa desimal
            $s = str_replace('.', '', $s);
            return (float)$s;
        }
        return (float)$v;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ["Analisa Produksi - {$this->tanggal}"];
        $rows[] = [];

        // Index job by id
        $jobById = [];
        foreach ($this->jobSummary as $j) {
            $jobById[$j['job_id']] = $j;
        }

        // ========== 0) Target per produk-per-job dengan pembagi khusus ==========
        $jobYangDibagiTongBesar = ['PECAH TELUR', 'GILING']; // uppercase
        $productJobTarget = [];   // [pid][job_id] => target_alokasi (float)
        $jobTotalTarget   = [];   // [job_id] => total target semua produk

        foreach ($this->produk as $pid => $pData) {
            $jobIds = $this->productJobMap[$pid] ?? [];
            if (!$jobIds) continue;

            $targetProdukTotal = $this->num($pData['total_target_produksi'] ?? 0);

            foreach ($jobIds as $jobId) {
                $jobRow  = $jobById[$jobId] ?? null;
                $namaJob = strtoupper($jobRow['nama_job'] ?? '');

                $target = $targetProdukTotal;
                if (in_array($namaJob, $jobYangDibagiTongBesar, true)) {
                    $tong = max($this->num($this->tongBesarProduk[$pid] ?? 1), 1);
                    $pat  = max($this->num($this->patokanProduk[$pid]   ?? 1), 1);
                    $target = $target / $tong / $pat;
                }

                $productJobTarget[$pid][$jobId] = ($productJobTarget[$pid][$jobId] ?? 0.0) + $target;
                $jobTotalTarget[$jobId]         = ($jobTotalTarget[$jobId]         ?? 0.0) + $target;
            }
        }

        // ========== 0b) Group-Unique headcount ==========
        // Tujuan: satu grup job yang punya banyak pekerjaan tapi headcount sama TIDAK dijumlahkan ganda.
        $jobsByGroup = [];  // [group => [job_id, ...]]
        $groupHeads  = [];  // [group => headcount unik (ambil max di grup)]
        foreach ($this->jobSummary as $j) {
            $g = $j['group_job'] ?? '-';
            $jobsByGroup[$g][] = $j['job_id'];
            $groupHeads[$g] = max((int)($groupHeads[$g] ?? 0), (int)($j['jml_orang'] ?? 0));
        }

        // Alokasi orang per produk berbasis kelompok (Group-Unique):
        $orangGroupUnique = []; // [pid] => float
        foreach ($jobsByGroup as $group => $jobIdsInGroup) {
            $H = (int)($groupHeads[$group] ?? 0);
            if ($H <= 0) continue;

            $TG  = 0.0;
            $TpG = []; // [pid] => float
            foreach ($this->produk as $pid => $_) {
                $Tp = 0.0;
                foreach ($jobIdsInGroup as $jid) {
                    $Tp += (float)($productJobTarget[$pid][$jid] ?? 0.0);
                }
                $TpG[$pid] = $Tp;
                $TG += $Tp;
            }
            if ($TG <= 0.0) continue;

            foreach ($TpG as $pid => $Tp) {
                $orangGroupUnique[$pid] = ($orangGroupUnique[$pid] ?? 0.0) + $H * ($Tp / $TG);
            }
        }

        // ========== 1) Produktivitas per Produk – Group-Unique ==========
        $rows[] = ['1) Produktivitas per Orang (Per Produk) – Group-Unique'];
        $rows[] = ['Produk', 'Orang (Group-Unique)', 'Total Produksi', 'Produktivitas / Orang'];

        foreach ($this->produkList as $p) {
            $pid       = $p['id'];
            $data      = $this->produk[$pid] ?? null;
            $produksi  = $this->num($data['total_produksi_qty'] ?? 0);
            $orangGU   = (float)($orangGroupUnique[$pid] ?? 0);

            $rows[] = [
                $p['nama'],
                $orangGU,
                $produksi,
                $orangGU > 0 ? $produksi / $orangGU : 0.0,
            ];
        }

        $rows[] = [];

        // ========== 2) Kontribusi Produk terhadap Total Produksi ==========
        $rows[] = ['2) Kontribusi Produk terhadap Total Produksi'];
        $rows[] = ['Produk', 'Produksi', '% Kontribusi'];

        $totalProduksiAll = 0.0;
        foreach ($this->produk as $pd) {
            $totalProduksiAll += $this->num($pd['total_produksi_qty'] ?? 0);
        }

        $percentStartRow = count($rows) + 1;
        foreach ($this->produkList as $p) {
            $produksi = $this->num($this->produk[$p['id']]['total_produksi_qty'] ?? 0);
            $fraction = $totalProduksiAll > 0 ? ($produksi / $totalProduksiAll) : 0.0; // 0..1
            $rows[] = [$p['nama'], $produksi, $fraction];
        }
        $percentEndRow = count($rows);
        $this->percentRanges[] = ['start' => "C{$percentStartRow}", 'end' => "C{$percentEndRow}"]; // kolom C = %

        $rows[] = [];

        // ========== 4) Rasio Orang vs Output (per Job) + Produktivitas/Orang (Group-Unique) ==========
      // ========== 4) Rasio Orang vs Output (per Job) + Produktivitas/Orang (Group-Unique) ==========
$rows[] = ['4) Rasio Orang vs Output (per Job)'];
$rows[] = ['Job', 'Orang', 'Produksi', 'Target (Jam/Org)', 'Rasio (Produksi/(Target*Orang))', 'Produktivitas / Orang'];

// simpan baris pertama data rasio (kolom E)
$this->rasioStartRow = count($rows) + 1;

foreach ($this->jobSummary as $job) {
    $orang       = (int)$this->num($job['jml_orang']       ?? 0);
    $produksi    =      $this->num($job['target_produksi'] ?? 0);
    $target      =      $this->num($job['target']          ?? 0);
    $rasio       = ($target > 0 && $orang > 0) ? ($produksi / ($target * $orang)) : 0.0; // 0..1

    $subGroup    = $job['group_job'] ?? '-';
    $headUnique  = (int)($groupHeads[$subGroup] ?? 0);
    $prodPerOrg  = $headUnique > 0 ? ($produksi / $headUnique) : 0.0;

    $rows[]      = [$job['nama_job'], $orang, $produksi, $target, $rasio, $prodPerOrg];
}

// simpan baris terakhir data rasio (kolom E)
$this->rasioEndRow = count($rows);

// tandai juga sebagai range persen (opsional, tetap biar konsisten)
$this->percentRanges[] = ['start' => "E{$this->rasioStartRow}", 'end' => "E{$this->rasioEndRow}"];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Basic styling
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $fullRange  = "A1:{$highestCol}{$highestRow}";

                // Border tipis + vertical middle
                $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
                      ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR);
                $sheet->getStyle($fullRange)->getAlignment()
                      ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // --- Format persen untuk range lain yang kamu tandai (mis. kontribusi produk)
                foreach ($this->percentRanges as $r) {
                    $sheet->getStyle("{$r['start']}:{$r['end']}")
                          ->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                }

                // ===============================
                // ENFORCER: Kolom "Rasio" (kolom E) di Analisa #4
                // ===============================
                // 1) Coerce semua E2..E{N} menjadi numeric (tanpa /100)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $addr = "E{$row}";
                    $val  = $sheet->getCell($addr)->getValue();
                    if ($val === null || $val === '') continue;

                    if (is_string($val)) {
                        // buang spasi/NBSP, buang tanda %, normalisasi pemisah
                        $s = trim(str_replace(["\xC2\xA0", ' '], '', $val));
                        $s = rtrim($s, '%');
                        $s = str_replace('.', '', $s);  // buang ribuan titik
                        $s = str_replace(',', '.', $s); // koma -> titik
                        if ($s === '' || !is_numeric($s)) continue;
                        $val = (float)$s;               // contoh: "10,4277" -> 10.4277
                    } else {
                        $val = (float)$val;
                    }

                    // Catatan penting:
                    // JANGAN bagi 100 di sini. Nilai 10.4277 akan ditampilkan 1042.77% oleh format persen.
                    $sheet->setCellValueExplicit(
                        $addr,
                        $val,
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
                    );
                }

                // 2) Terapkan format Percent (2 desimal) & rata kanan untuk SELURUH kolom E
                $sheet->getStyle("E1:E{$highestRow}")
                      ->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
                $sheet->getStyle("E1:E{$highestRow}")
                      ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                // --- Rata kanan angka B..F (termasuk kolom produktivitas/orang)
                $sheet->getStyle("B1:F{$highestRow}")
                      ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }



    public function columnWidths(): array
    {
        return [
            'A' => 36, 'B' => 20, 'C' => 18, 'D' => 20, 'E' => 28, 'F' => 22,
        ];
    }
}
