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

class KontribusiHarianWilayahExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize
{
    public function __construct(
        public array $rows,
        public array $grandTotals = []
    ) {}

    // =========================
    // HEADINGS (2 ROWS)
    // =========================
    public function headings(): array
    {
        return [
            // Row 1 (group header)
            [
                'WILAYAH',
                'JENIS',
                'SELISIH %',
                '(+/-) RP',
                'KONTRIBUSI',
                'DISC MANUAL', '',         // F-G
                'RETUR', '',               // H-I
                'GAS', '',                 // J-K
                'TELUR', '',               // L-M
                'LOSS BAHAN',
                'KURANG SETORAN',
                'TOTAL KONTRIBUSI',
            ],
            // Row 2 (sub header)
            [
                '', '',
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

        // Aggregate data by wilayah (sum across all dates)
        $summaryByWilayah = [];
        foreach ($this->rows as $tgl => $byWilayah) {
            foreach ($byWilayah as $wilayahName => $list) {
                if (!isset($summaryByWilayah[$wilayahName])) {
                    $summaryByWilayah[$wilayahName] = [
                        'BY BULAN LALU' => [
                            'hrg' => 0,
                            'selisih_rp' => 0,
                            'kontribusi' => 0,
                            'disc_rp' => 0,
                            'retur_rp' => 0,
                            'gas_rp' => 0,
                            'telur_rp' => 0,
                            'loss_bahan' => 0,
                            'kurang_setoran' => 0,
                            'total_kontribusi' => 0,
                        ],
                        'BY TARGET' => [
                            'hrg' => 0,
                            'selisih_rp' => 0,
                            'kontribusi' => 0,
                            'disc_rp' => 0,
                            'retur_rp' => 0,
                            'gas_rp' => 0,
                            'telur_rp' => 0,
                            'loss_bahan' => 0,
                            'kurang_setoran' => 0,
                            'total_kontribusi' => 0,
                        ],
                    ];
                }
                
                foreach ($list as $r) {
                    $type = $r['type'] ?? 'BY BULAN LALU';
                    $summaryByWilayah[$wilayahName][$type]['hrg'] += $this->toInt($r['hrg'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['selisih_rp'] += $this->toInt($r['selisih_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['kontribusi'] += $this->toInt($r['kontribusi'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['disc_rp'] += $this->toInt($r['disc_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['retur_rp'] += $this->toInt($r['retur_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['gas_rp'] += $this->toInt($r['gas_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['telur_rp'] += $this->toInt($r['telur_rp'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['loss_bahan'] += $this->toInt($r['loss_bahan'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['kurang_setoran'] += $this->toInt($r['kurang_setoran'] ?? 0);
                    $summaryByWilayah[$wilayahName][$type]['total_kontribusi'] += $this->toInt($r['total_kontribusi'] ?? 0);
                }
            }
        }

        // Output summary rows (1 wilayah = 2 rows: BY BULAN LALU + BY TARGET)
        ksort($summaryByWilayah);
        foreach ($summaryByWilayah as $wilayahName => $data) {
            foreach (['BY BULAN LALU' => 'BY BULAN LALU', 'BY TARGET' => 'BY TARGET'] as $typeKey => $typeLabel) {
                $r = $data[$typeKey] ?? [];
                $hrg = (int) ($r['hrg'] ?? 0);

                $selPctDec = $this->pctSelisihDec($this->toInt($r['selisih_rp'] ?? 0), $hrg);
                $discPctDec = $this->pctDec($this->toInt($r['disc_rp'] ?? 0), $hrg);
                $retPctDec = $this->pctDec($this->toInt($r['retur_rp'] ?? 0), $hrg);
                $gasPctDec = $this->pctDec($this->toInt($r['gas_rp'] ?? 0), $hrg);
                $telPctDec = $this->pctDec($this->toInt($r['telur_rp'] ?? 0), $hrg);

                $out[] = [
                    $wilayahName,
                    $typeLabel,
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
                    $this->toInt($r['kurang_setoran'] ?? 0),
                    $this->toInt($r['total_kontribusi'] ?? 0),
                ];
            }
            $out[] = $this->emptyRow();
        }

        // GRAND TOTAL (2 row)
        if (!empty($this->grandTotals)) {
            $out[] = [
                'GRAND TOTAL (BY TARGET)',
                'BY TARGET',
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
                $this->toInt($this->grandTotals['target']['loss_bahan'] ?? 0),
                $this->toInt($this->grandTotals['target']['kurang_setoran'] ?? 0),
                $this->toInt($this->grandTotals['target']['total_kontribusi'] ?? 0),
            ];

            $out[] = [
                'GRAND TOTAL (BY BULAN LALU)',
                'BY BULAN LALU',
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
                $this->toInt($this->grandTotals['bl']['loss_bahan'] ?? 0),
                $this->toInt($this->grandTotals['bl']['kurang_setoran'] ?? 0),
                $this->toInt($this->grandTotals['bl']['total_kontribusi'] ?? 0),
            ];
        }

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

                $sheet->mergeCells('F1:G1'); // DISC MANUAL
                $sheet->mergeCells('H1:I1'); // RETUR
                $sheet->mergeCells('J1:K1'); // GAS
                $sheet->mergeCells('L1:M1'); // TELUR

                $sheet->mergeCells('N1:N2'); // LOSS
                $sheet->mergeCells('O1:O2'); // KURANG SETORAN
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
                // Percent columns: C, F, H, J, L
                foreach (['C','F','H','J','L'] as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0.00%');
                }

                // Rp columns: D, E, G, I, K, M, N, O, P
                foreach (['D','E','G','I','K','M','N','O','P'] as $col) {
                    $sheet->getStyle("{$col}3:{$col}{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // Alignments for data rows
                $sheet->getStyle("A3:C{$highestRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("D3:Q{$highestRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A3:Q{$highestRow}")
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
                    'O','P','Q', // Loss | Kurang Setoran | Total Kontribusi
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
