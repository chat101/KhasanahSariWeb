<?php

namespace App\Exports\produksi;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BrownisCakeExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected array $rows;
    protected ?string $awal;
    protected ?string $akhir;

    public function __construct(array $rows, ?string $awal, ?string $akhir)
    {
        $this->rows = $rows;
        $this->awal = $awal;
        $this->akhir = $akhir;
    }

    public function headings(): array
    {
        return [
            ['Rekap Brownis & Cake', $this->awal ? "Periode: {$this->awal} s/d {$this->akhir}" : ''],
            [
                'No','Produk','Patokan','Produksi (Tong)','Target Produksi','Real','Pengalihan Produk',
                '+/- Target vs Real','%','Distribusi','Complain','+/- Real vs Distribusi',
                'Reject Produksi','Reject dari Dekor','Total Reject','RP','% Retur'
            ],
        ];
    }

    public function array(): array
    {
        $data = [];
        $runningNo = 1;

        foreach ($this->rows as $r) {
            $isSubtotal   = ($r['nama'] ?? '') === 'Subtotal NON-CAKE' || ($r['nama'] ?? '') === 'Subtotal CAKE';
            $isGrandTotal = ($r['nama'] ?? '') === 'GRAND TOTAL';

            $no = ($isSubtotal || $isGrandTotal) ? '' : $runningNo++;

            // Excel % idealnya 0..1 => bagi 100
            $pctTargetVsReal = isset($r['percent_target_vs_real']) ? ((float) $r['percent_target_vs_real']) / 100 : 0;
            $pctRetur        = isset($r['persenretur']) ? ((float) $r['persenretur']) / 100 : 0;

            $data[] = [
                $no,
                $r['nama'] ?? '',
                $r['patokan'] ?? '',
                (float) ($r['total_qty'] ?? 0),
                (float) ($r['total_target_produksi'] ?? 0),
                (float) ($r['real_total'] ?? 0),
                (float) ($r['po_pengalihan'] ?? 0),
                (float) ($r['target_vs_real'] ?? 0),
                $pctTargetVsReal,
                (float) ($r['dist'] ?? 0),
                (float) ($r['complain'] ?? 0),
                (float) ($r['realvsdist'] ?? 0),
                (float) ($r['returproduksi'] ?? 0),
                (float) ($r['returjadi'] ?? 0),
                (float) ($r['totalretur'] ?? 0),
                (float) ($r['hpp'] ?? 0),
                $pctRetur,
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Judul (A1) dan periode (B1)
        $sheet->mergeCells('A1:Q1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [
            2 => [ // row headings tabel
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']], // gray-700
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $startRow = 3; // data mulai row ke-3 (1 = judul, 2 = header)
                $lastRow  = $startRow + count($this->rows) - 1;

                if ($lastRow >= $startRow) {
                    // Border tipis seluruh tabel data
                    $sheet->getStyle("A2:Q{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563')); // gray-600

                    // Rata kanan untuk angka
                    $sheet->getStyle("D{$startRow}:Q{$lastRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Format angka ribuan
                    foreach (['D','E','F','G','J','K','L','M','N'] as $col) {
                        $sheet->getStyle("{$col}{$startRow}:{$col}{$lastRow}")
                              ->getNumberFormat()->setFormatCode('#,##0');
                    }
                    // RP (kolom O)
                    $sheet->getStyle("O{$startRow}:O{$lastRow}")
                          ->getNumberFormat()->setFormatCode('"Rp" #,##0.00_-');

                    // Persentase (I dan Q)
                    foreach (['I','Q'] as $col) {
                        $sheet->getStyle("{$col}{$startRow}:{$col}{$lastRow}")
                              ->getNumberFormat()->setFormatCode('0.0%');
                    }

                    // Style baris subtotal & grand total (berdasar kolom "Produk" = kolom B)
                    $rowIndex = $startRow;
                    foreach ($this->rows as $r) {
                        $name = $r['nama'] ?? '';
                        if ($name === 'GRAND TOTAL') {
                            $sheet->getStyle("A{$rowIndex}:Q{$rowIndex}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '065F46']], // emerald-800-ish
                                'fill' => [
                                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                                    'startColor' => ['rgb' => '34D39926'], // emerald/15
                                    'endColor'   => ['rgb' => '34D39926'],
                                ],
                            ]);
                        } elseif ($name === 'Subtotal NON-CAKE' || $name === 'Subtotal CAKE') {
                            $sheet->getStyle("A{$rowIndex}:Q{$rowIndex}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '92400E']], // amber-800-ish
                                'fill' => [
                                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                                    'startColor' => ['rgb' => '4fbedc'], // amber/15
                                    'endColor'   => ['rgb' => '4fbedc'],
                                ],
                            ]);
                        }
                        $rowIndex++;
                    }
                }
            },
        ];
    }
    public function title(): string
    {
        return 'Brownis&Cake'; // nama worksheet
    }
}
