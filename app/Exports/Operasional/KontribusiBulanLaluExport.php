<?php

namespace App\Exports\Operasional;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class KontribusiBulanLaluExport implements FromArray, WithHeadings, WithEvents, ShouldAutoSize
{
    public function __construct(
        public array $rows,
        public array $grandTotals,
        public string $periodStart,
        public string $periodEnd,
        public string $bulanLaluStart,
        public string $bulanLaluEnd
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
                'AREA',
                'PIC AREA',
                'OUTLET',
                'SELISIH %',
                '(+/-) RP',
                'KONTRIBUSI',
                'DISC MANUAL',
                '',
                'RETUR',
                '',
                'GAS',
                '',
                'TELUR',
                '',
                'LOSS BAHAN',
                'KURANG SETORAN',
                'TOTAL KONTRIBUSI',
            ],
            // Row 2 (sub header)
            ['', '', '', '', '', '', '', '%', 'RP', '%', 'RP', '%', 'RP', '%', 'RP', '', '', ''],
        ];
    }

    // =========================
    // DATA
    // =========================
    public function array(): array
    {
        $data = [];

        // 1) Group directly by area (skip wilayah grouping)
        $groupArea = collect($this->rows)
            ->groupBy(fn($r) => $r['area_label'] ?? '-');

        // 2) Create sortable area list with totals
        $areaList = [];
        foreach ($groupArea as $area => $rowsArea) {
            $rowsAreaArray = is_array($rowsArea) ? $rowsArea : $rowsArea->all();
            
            // Calculate total kontribusi for this area
            $totalArea = 0;
            foreach ($rowsAreaArray as $row) {
                $totalArea += (int)($row['total_kontribusi'] ?? 0);
            }
            
            $areaList[] = [
                'area' => $area,
                'rows' => $rowsAreaArray,
                'total' => $totalArea,
            ];
        }

        // 3) Sort areas by total (ascending - dari negatif terbesar ke positif)
        usort($areaList, function($a, $b) {
            return ((int)$a['total']) - ((int)$b['total']);
        });

        // 4) Process each area in sorted order
        foreach ($areaList as $areaData) {
            $area = $areaData['area'];
            $rowsArea = $areaData['rows'];

            // Sort toko within area by total_kontribusi (ascending)
            usort($rowsArea, function($a, $b) {
                return ((int)($a['total_kontribusi'] ?? 0)) - ((int)($b['total_kontribusi'] ?? 0));
            });

            // Add individual toko rows
            foreach ($rowsArea as $row) {
                $data[] = [
                    $row['wilayah_label'] ?? '-',
                    $row['area_label'] ?? '-',
                    $row['area_pic'] ?? '-',
                    $row['outlet'] ?? '-',
                    $this->toFloatPct($row['selisih_persen']),
                    (int)($row['selisih_rp'] ?? 0),
                    (int)($row['kontribusi_rp'] ?? 0),
                    $this->toFloatPct($row['sc_manual_persen']),
                    (int)($row['sc_manual_rp'] ?? 0),
                    $this->toFloatPct($row['retur_persen']),
                    (int)($row['retur_rp'] ?? 0),
                    $this->toFloatPct($row['gas_persen']),
                    (int)($row['gas_rp'] ?? 0),
                    $this->toFloatPct($row['telur_persen']),
                    (int)($row['telur_rp'] ?? 0),
                    (int)($row['loss_bahan'] ?? 0),
                    (int)($row['kurang_setoran'] ?? 0),
                    (int)($row['total_kontribusi'] ?? 0),
                ];
            }

            // Add area subtotal
            $sumCols = $this->sumAreaRows($rowsArea);
            $data[] = [
                '',
                'SUBTOTAL AREA: ' . $area,
                '',
                '',
                $this->toFloatPct($sumCols['selisih_persen']),
                (int)($sumCols['selisih_rp'] ?? 0),
                (int)($sumCols['kontribusi_rp'] ?? 0),
                $this->toFloatPct($sumCols['sc_manual_persen']),
                (int)($sumCols['sc_manual_rp'] ?? 0),
                $this->toFloatPct($sumCols['retur_persen']),
                (int)($sumCols['retur_rp'] ?? 0),
                $this->toFloatPct($sumCols['gas_persen']),
                (int)($sumCols['gas_rp'] ?? 0),
                $this->toFloatPct($sumCols['telur_persen']),
                (int)($sumCols['telur_rp'] ?? 0),
                (int)($sumCols['loss_bahan'] ?? 0),
                (int)($sumCols['kurang_setoran'] ?? 0),
                (int)($sumCols['total_kontribusi'] ?? 0),
            ];
        }

        // Add grand total row
        $data[] = [];
        $data[] = [
            'GRAND TOTAL',
            '',
            '',
            '',
            $this->toFloatPct($this->grandTotals['selisih_persen']),
            (int)($this->grandTotals['selisih_rp'] ?? 0),
            (int)($this->grandTotals['kontribusi_rp'] ?? 0),
            $this->toFloatPct($this->grandTotals['sc_manual_persen']),
            (int)($this->grandTotals['sc_manual_rp'] ?? 0),
            $this->toFloatPct($this->grandTotals['retur_persen']),
            (int)($this->grandTotals['retur_rp'] ?? 0),
            $this->toFloatPct($this->grandTotals['gas_persen']),
            (int)($this->grandTotals['gas_rp'] ?? 0),
            $this->toFloatPct($this->grandTotals['telur_persen']),
            (int)($this->grandTotals['telur_rp'] ?? 0),
            (int)($this->grandTotals['loss_bahan'] ?? 0),
            (int)($this->grandTotals['kurang_setoran'] ?? 0),
            (int)($this->grandTotals['total_kontribusi'] ?? 0),
        ];

        return $data;
    }

    // =========================
    // FORMATTING HELPERS
    // =========================
    private function toFloatPct($v): float
    {
        // output: -52.72 (decimal, bukan string dengan %)
        if ($v === null || $v === '' || $v === '-') {
            return 0.0;
        }
        if (is_int($v) || is_float($v)) {
            return (float) $v / 100.0; // convert dari percent ke decimal
        }

        $s = trim((string) $v);
        $s = str_replace('%', '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s / 100.0 : 0.0;
    }

    private function sumAreaRows($rowsArea): array
    {
        $rows = collect($rowsArea);

        $avgPct = function(string $key) use ($rows): ?float {
            $vals = $rows->map(function($r) use ($key) {
                $v = $r[$key] ?? null;
                if ($v === null || $v === '' || $v === '-') return null;
                if (is_int($v) || is_float($v)) return (float) $v;
                $s = trim((string) $v);
                $s = str_replace('%', '', $s);
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
                return is_numeric($s) ? (float) $s : null;
            })
                ->filter(fn($v) => !is_null($v))
                ->values();
            if ($vals->isEmpty()) return null;
            return round((float)$vals->avg(), 2);
        };

        return [
            'selisih_persen'   => $avgPct('selisih_persen'),
            'selisih_rp'       => (int) $rows->sum(fn($r) => (int)($r['selisih_rp'] ?? 0)),
            'kontribusi_rp'    => (int) $rows->sum(fn($r) => (int)($r['kontribusi_rp'] ?? 0)),
            'sc_manual_persen' => $avgPct('sc_manual_persen'),
            'sc_manual_rp'     => (int) $rows->sum(fn($r) => (int)($r['sc_manual_rp'] ?? 0)),
            'retur_persen'     => $avgPct('retur_persen'),
            'retur_rp'         => (int) $rows->sum(fn($r) => (int)($r['retur_rp'] ?? 0)),
            'gas_persen'       => $avgPct('gas_persen'),
            'gas_rp'           => (int) $rows->sum(fn($r) => (int)($r['gas_rp'] ?? 0)),
            'telur_persen'     => $avgPct('telur_persen'),
            'telur_rp'         => (int) $rows->sum(fn($r) => (int)($r['telur_rp'] ?? 0)),
            'loss_bahan'       => (int) $rows->sum(fn($r) => (int)($r['loss_bahan'] ?? 0)),
            'kurang_setoran'   => (int) $rows->sum(fn($r) => (int)($r['kurang_setoran'] ?? 0)),
            'total_kontribusi' => (int) $rows->sum(fn($r) => (int)($r['total_kontribusi'] ?? 0)),
        ];
    }

    // =========================
    // STYLING
    // =========================
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // ===== Header styling =====
                $sheet->getStyle('A1:' . $highestColumn . '2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'color' => ['rgb' => '366092'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Row height for header
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ===== Find subtotal area rows =====
                $subtotalRows = [];
                for ($row = 3; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('B' . $row)->getValue();
                    if ($cellValue && strpos((string)$cellValue, 'SUBTOTAL AREA:') === 0) {
                        $subtotalRows[] = $row;
                    }
                }

                // ===== Style subtotal area rows =====
                foreach ($subtotalRows as $row) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'F3E5F5']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    ]);
                }

                // ===== Find grand total row =====
                $grandTotalRow = null;
                for ($row = 3; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if ($cellValue === 'GRAND TOTAL') {
                        $grandTotalRow = $row;
                        break;
                    }
                }

                // ===== Border all table =====
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ===== Number formatting =====
                // Percentage columns: E, H, J, L, N (decimal to percentage)
                foreach (['E', 'H', 'J', 'L', 'N'] as $col) {
                    $sheet->getStyle($col . '3:' . $col . $highestRow)
                        ->getNumberFormat()
                        ->setFormatCode('0.00%');
                }

                // Currency columns: F, G, I, K, M, O, P, Q, R
                foreach (['F', 'G', 'I', 'K', 'M', 'O', 'P', 'Q', 'R'] as $col) {
                    $sheet->getStyle($col . '3:' . $col . $highestRow)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0');
                }

                // ===== Alignment =====
                // Left align: A-D (text columns)
                $sheet->getStyle('A3:D' . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Right align: E onwards (numbers)
                $sheet->getStyle('E3:' . $highestColumn . $highestRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Vertical center all
                $sheet->getStyle('A3:' . $highestColumn . $highestRow)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // ===== Conditional Formatting (color negative/positive) =====
                $numericCols = ['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];

                foreach ($numericCols as $col) {
                    // Data rows (excluding subtotal and grand total)
                    for ($row = 3; $row <= $highestRow; $row++) {
                        if (in_array($row, $subtotalRows)) continue; // skip subtotal rows
                        if ($grandTotalRow && $row === $grandTotalRow) continue; // skip grand total
                        if ($row === $grandTotalRow - 1) continue; // skip empty row before grand total

                        $cellValue = $sheet->getCell($col . $row)->getValue();
                        if ($cellValue === null || $cellValue === '') continue;

                        $numVal = (float) $cellValue;
                        if ($numVal < 0) {
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'font' => ['color' => ['rgb' => 'FF0000'], 'bold' => true],
                            ]);
                        } elseif ($numVal > 0) {
                            $sheet->getStyle($col . $row)->applyFromArray([
                                'font' => ['color' => ['rgb' => '008000'], 'bold' => true],
                            ]);
                        }
                    }
                }

                // ===== Grand total styling =====
                if ($grandTotalRow) {
                    $sheet->getStyle('A' . $grandTotalRow . ':' . $highestColumn . $grandTotalRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '366092']],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ===== Freeze panes =====
                $sheet->freezePane('A3');
            },
        ];
    }
}
