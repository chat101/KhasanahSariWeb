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

class KukerExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithEvents, WithTitle
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
        return [['Rekap Kue Kering', $this->awal ? "Periode: {$this->awal} s/d {$this->akhir}" : ''], ['No', 'Produk', 'Patokan', 'Produksi (Tong)', 'Target Produksi', 'Hasil Produksi', 'Reject', 'Sample', '+- Target VS Produksi', 'Stok Awal', 'Complain', 'Distribusi', 'Stok Sistem', 'Stok Aktual', '+- Sistem VS Aktual']];
    }

    public function array(): array
    {
        $data = [];
        $runningNo = 1;

        foreach ($this->rows as $r) {
            $isTotal = !empty($r['is_total']);
            $isSubtotal = !empty($r['is_subtotal']);
            $isGrandTotal = !empty($r['is_grandtotal']);

            $no = $isTotal || $isSubtotal || $isGrandTotal ? '' : $runningNo++;

            $data[] = [$no, $r['nama'] ?? '', $r['patokan'] ?? '', (float) ($r['total_qty'] ?? 0), (float) ($r['total_target_produksi'] ?? 0), (float) ($r['real_total'] ?? 0), (float) ($r['totalretur'] ?? 0), (float) ($r['sample'] ?? 0), (float) ($r['targetvsrealroker'] ?? 0), (float) ($r['stok_awal_periode'] ?? 0), (float) ($r['complain'] ?? 0), (float) ($r['dist'] ?? 0), (float) ($r['stok_akhir_periode'] ?? 0), 'aktual', (float) ($r['realvssistemroker'] ?? 0)];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:O1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $start = 3;
                $last = $start + count($this->rows) - 1;

                if ($last >= $start) {
                    $sheet
                        ->getStyle("A2:O{$last}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4B5563'));

                    $sheet
                        ->getStyle("D{$start}:O{$last}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    foreach (['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'O'] as $col) {
                        $sheet
                            ->getStyle("{$col}{$start}:{$col}{$last}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0');
                    }

                    // Highlight baris total/subtotal/grand total
                    $i = $start;
                    foreach ($this->rows as $r) {
                        if (!empty($r['is_grandtotal'])) {
                            $sheet->getStyle("A{$i}:O{$i}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '065F46']],
                                'fill' => [
                                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                                    'startColor' => ['rgb' => '34D39926'],
                                    'endColor' => ['rgb' => '34D39926'],
                                ],
                            ]);
                        } elseif (!empty($r['is_subtotal'])) {
                            $sheet->getStyle("A{$i}:O{$i}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                                'fill' => [
                                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                                    'startColor' => ['rgb' => 'FBBF2426'],
                                    'endColor' => ['rgb' => 'FBBF2426'],
                                ],
                            ]);
                        } elseif (!empty($r['is_total'])) {
                            $sheet->getStyle("A{$i}:O{$i}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '0C4A6E']], // sky-900-ish
                                'fill' => [
                                    'fillType' => Fill::FILL_GRADIENT_LINEAR,
                                    'startColor' => ['rgb' => '38BDF826'],
                                    'endColor' => ['rgb' => '38BDF826'],
                                ],
                            ]);
                        }
                        $i++;
                    }
                }
            },
        ];
    }
    public function title(): string
    {
        return 'Kuker'; // nama worksheet
    }
}
