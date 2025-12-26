<?php

namespace App\Exports\Operasional;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

class KontribusiHarianAreaExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize
{
    public function __construct(
        public array $rows,
        public array $grandTotals,
        public string $start,
        public string $end
    ) {}

    // =========================
    // HEADINGS (2 ROWS)
    // =========================
    public function headings(): array
    {
        return [
            // Row 1 (group header)
            [
                'OUTLET',
                'TANGGAL',
                'JENIS',
                'SELISIH %',
                '(+/-) RP',
                'KONTRIBUSI',
                'DISC MANUAL', '',         // G-H
                'RETUR', '',               // I-J
                'GAS', '',                 // K-L
                'TELUR', '',               // M-N
                'LOSS BAHAN',
                'TOTAL KONTRIBUSI',
            ],
            // Row 2 (sub header)
            [
                '', '', '',
                '', '', '',
                '%', 'RP',
                '%', 'RP',
                '%', 'RP',
                '%', 'RP',
                '', '',
            ],
        ];
    }

    // =========================
    // HELPERS
    // =========================
    private function toInt($v): int
    {
        if ($v === null) return 0;
        if (is_int($v)) return $v;
        if (is_float($v)) return (int) round($v);

        $s = (string) $v;
        $s = str_replace(['.', ' '], '', $s);
        $s = explode(',', $s)[0];

        return is_numeric($s) ? (int) $s : 0;
    }

    private function toFloatPct($v): float
    {
        // output: -52.72 (bukan -0.5272)
        if ($v === null) return 0.0;
        if (is_int($v) || is_float($v)) return (float) $v;

        $s = trim((string) $v);
        $s = str_replace('%', '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function pctDec($num, $den): float
    {
        $den = (float) $den;
        if ($den == 0.0) return 0.0;
        return ((float) $num / $den); // decimal percent
    }

    private function pctSelisihDec($selisihRp, $hrg): float
    {
        // Selisih% = selisih / baseline ; baseline = hrg - selisih
        $hrg = (float) $hrg;
        $sel = (float) $selisihRp;
        $baseline = $hrg - $sel;
        if ($baseline == 0.0) return 0.0;
        return ($sel / $baseline);
    }

    private function emptyRow(): array
    {
        return array_fill(0, 16, '');
    }

    // =========================
    // DATA
    // =========================
    public function array(): array
    {
        $out = [];

        foreach ($this->rows as $outlet => $byTanggal) {

            // TOTAL TOKO (biar sama dengan Blade)
            $totalToko = [
                'target' => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
                'bl'     => ['hrg'=>0,'selisih_rp'=>0,'kontribusi'=>0,'disc'=>0,'retur'=>0,'gas'=>0,'telur'=>0,'loss'=>0,'total'=>0],
            ];

            foreach ($byTanggal as $tgl => $list) {
                foreach ($list as $r) {
                    $jenis  = strtoupper(trim((string)($r['type'] ?? '')));
                    $bucket = $jenis === 'BY TARGET' ? 'target' : 'bl';

                    $totalToko[$bucket]['hrg']        += $this->toInt($r['hrg'] ?? 0);
                    $totalToko[$bucket]['selisih_rp'] += $this->toInt($r['selisih_rp'] ?? 0);
                    $totalToko[$bucket]['kontribusi'] += $this->toInt($r['kontribusi'] ?? 0);
                    $totalToko[$bucket]['disc']       += $this->toInt($r['disc_rp'] ?? 0);
                    $totalToko[$bucket]['retur']      += $this->toInt($r['retur_rp'] ?? 0);
                    $totalToko[$bucket]['gas']        += $this->toInt($r['gas_rp'] ?? 0);
                    $totalToko[$bucket]['telur']      += $this->toInt($r['telur_rp'] ?? 0);
                    $totalToko[$bucket]['loss']       += $this->toInt($r['loss_bahan'] ?? 0);
                    $totalToko[$bucket]['total']      += $this->toInt($r['total_kontribusi'] ?? 0);
                }
            }

            // DETAIL
            foreach ($byTanggal as $tgl => $list) {
                foreach ($list as $r) {
                    $hrg = $this->toInt($r['hrg'] ?? 0);

                    // Selisih%: pakai payload jika ada, kalau tidak hitung baseline
                    $selPct = $this->toFloatPct($r['selisih_persen'] ?? null);
                    $selPctDec = ($selPct != 0.0) ? ($selPct / 100.0) : $this->pctSelisihDec($this->toInt($r['selisih_rp'] ?? 0), $hrg);

                    // % lain: pakai payload jika ada, kalau tidak hitung rp/hrg
                    $discPct = $this->toFloatPct($r['disc_pct'] ?? null);
                    $retPct  = $this->toFloatPct($r['retur_pct'] ?? null);
                    $gasPct  = $this->toFloatPct($r['gas_pct'] ?? null);
                    $telPct  = $this->toFloatPct($r['telur_pct'] ?? null);

                    $discPctDec = ($discPct != 0.0) ? ($discPct / 100.0) : $this->pctDec($this->toInt($r['disc_rp'] ?? 0), $hrg);
                    $retPctDec  = ($retPct  != 0.0) ? ($retPct  / 100.0) : $this->pctDec($this->toInt($r['retur_rp'] ?? 0), $hrg);
                    $gasPctDec  = ($gasPct  != 0.0) ? ($gasPct  / 100.0) : $this->pctDec($this->toInt($r['gas_rp'] ?? 0), $hrg);
                    $telPctDec  = ($telPct  != 0.0) ? ($telPct  / 100.0) : $this->pctDec($this->toInt($r['telur_rp'] ?? 0), $hrg);

                    $out[] = [
                        $outlet,
                        (string) $tgl,
                        (string) ($r['type'] ?? ''),
                        $selPctDec,
                        $this->toInt($r['selisih_rp'] ?? 0),
                        $this->toInt($r['kontribusi'] ?? 0),
                        $discPctDec,
                        $this->toInt($r['disc_rp'] ?? 0),
                        $retPctDec,
                        $this->toInt($r['retur_rp'] ?? 0),
                        $gasPctDec,
                        $this->toInt($r['gas_rp'] ?? 0),
                        $telPctDec,
                        $this->toInt($r['telur_rp'] ?? 0),
                        $this->toInt($r['loss_bahan'] ?? 0),
                        $this->toInt($r['total_kontribusi'] ?? 0),
                    ];
                }
            }

            // TOTAL TOKO (2 row)
            foreach (['target' => 'BY TARGET', 'bl' => 'BY BULAN LALU'] as $bucket => $label) {
                $hrg = (int) $totalToko[$bucket]['hrg'];

                $out[] = [
                    $outlet,
                    '',
                    'TOTAL TOKO ' . $label,
                    $this->pctSelisihDec($totalToko[$bucket]['selisih_rp'], $hrg),
                    (int) $totalToko[$bucket]['selisih_rp'],
                    (int) $totalToko[$bucket]['kontribusi'],
                    $this->pctDec($totalToko[$bucket]['disc'], $hrg),
                    (int) $totalToko[$bucket]['disc'],
                    $this->pctDec($totalToko[$bucket]['retur'], $hrg),
                    (int) $totalToko[$bucket]['retur'],
                    $this->pctDec($totalToko[$bucket]['gas'], $hrg),
                    (int) $totalToko[$bucket]['gas'],
                    $this->pctDec($totalToko[$bucket]['telur'], $hrg),
                    (int) $totalToko[$bucket]['telur'],
                    (int) $totalToko[$bucket]['loss'],
                    (int) $totalToko[$bucket]['total'],
                ];
            }

            $out[] = $this->emptyRow();
        }

        // GRAND TOTAL (2 row)
        $out[] = [
            'GRAND TOTAL (BY TARGET)', '', '',
            $this->pctSelisihDec($this->toInt($this->grandTotals['target']['selisih_rp'] ?? 0), $this->toInt($this->grandTotals['target']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['target']['selisih_rp'] ?? 0),
            $this->toInt($this->grandTotals['target']['kontribusi'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['target']['disc'] ?? 0), $this->toInt($this->grandTotals['target']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['target']['disc'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['target']['retur'] ?? 0), $this->toInt($this->grandTotals['target']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['target']['retur'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['target']['gas'] ?? 0), $this->toInt($this->grandTotals['target']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['target']['gas'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['target']['telur'] ?? 0), $this->toInt($this->grandTotals['target']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['target']['telur'] ?? 0),
            $this->toInt($this->grandTotals['target']['loss'] ?? 0),
            $this->toInt($this->grandTotals['target']['total'] ?? 0),
        ];

        $out[] = [
            'GRAND TOTAL (BY BULAN LALU)', '', '',
            $this->pctSelisihDec($this->toInt($this->grandTotals['bl']['selisih_rp'] ?? 0), $this->toInt($this->grandTotals['bl']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['bl']['selisih_rp'] ?? 0),
            $this->toInt($this->grandTotals['bl']['kontribusi'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['bl']['disc'] ?? 0), $this->toInt($this->grandTotals['bl']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['bl']['disc'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['bl']['retur'] ?? 0), $this->toInt($this->grandTotals['bl']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['bl']['retur'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['bl']['gas'] ?? 0), $this->toInt($this->grandTotals['bl']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['bl']['gas'] ?? 0),
            $this->pctDec($this->toInt($this->grandTotals['bl']['telur'] ?? 0), $this->toInt($this->grandTotals['bl']['hrg'] ?? 0)),
            $this->toInt($this->grandTotals['bl']['telur'] ?? 0),
            $this->toInt($this->grandTotals['bl']['loss'] ?? 0),
            $this->toInt($this->grandTotals['bl']['total'] ?? 0),
        ];

        return $out;
    }

    // =========================
    // STYLING + MERGE HEADER
    // =========================
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate(); /** @var Worksheet $sheet */

                $highestRow = $sheet->getHighestRow();

                // Merge header cells (Row 1 & 2)
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:D2');
                $sheet->mergeCells('E1:E2');
                $sheet->mergeCells('F1:F2');

                $sheet->mergeCells('G1:H1'); // DISC MANUAL
                $sheet->mergeCells('I1:J1'); // RETUR
                $sheet->mergeCells('K1:L1'); // GAS
                $sheet->mergeCells('M1:N1'); // TELUR

                $sheet->mergeCells('O1:O2'); // LOSS
                $sheet->mergeCells('P1:P2'); // TOTAL

                // Freeze header (data start row 3)
                $sheet->freezePane('A3');

                // Header style
                $sheet->getStyle('A1:P2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => 'F3F4F6'], // gray-100
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Border all table
                $sheet->getStyle("A1:P{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Number formats
                // Percent columns: D, G, I, K, M
                foreach (['D','G','I','K','M'] as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0.00%');
                }

                // Rp columns: E,F,H,J,L,N,O,P
                foreach (['E','F','H','J','L','N','O','P'] as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Alignments for data rows
                $sheet->getStyle("A3:C{$highestRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("D3:P{$highestRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A3:P{$highestRow}")
                    ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Optional: set row height for header
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(18);

// =========================
// CONDITIONAL FORMATTING
// =========================

$highestRow = $sheet->getHighestRow();

// Kolom % dan Rp yang ingin diberi warna
$numericCols = [
    'D','E','F', // Selisih % | Selisih Rp | Kontribusi
    'G','H',     // Disc % | Disc Rp
    'I','J',     // Retur % | Retur Rp
    'K','L',     // Gas % | Gas Rp
    'M','N',     // Telur % | Telur Rp
    'O','P',     // Loss | Total Kontribusi
];

foreach ($numericCols as $col) {

    $range = "{$col}3:{$col}{$highestRow}";

    // NEGATIVE (MERAH)
    $neg = new Conditional();
    $neg->setConditionType(Conditional::CONDITION_CELLIS);
    $neg->setOperatorType(Conditional::OPERATOR_LESSTHAN);
    $neg->addCondition('0');
    $neg->getStyle()->getFont()
        ->setColor(new Color(Color::COLOR_RED))
        ->setBold(true);

    // POSITIVE (HIJAU)
    $pos = new Conditional();
    $pos->setConditionType(Conditional::CONDITION_CELLIS);
    $pos->setOperatorType(Conditional::OPERATOR_GREATERTHAN);
    $pos->addCondition('0');
    $pos->getStyle()->getFont()
        ->setColor(new Color(Color::COLOR_DARKGREEN))
        ->setBold(true);

    // Apply
    $sheet->getStyle($range)->setConditionalStyles([$neg, $pos]);
}


            },

        ];

    }


}
