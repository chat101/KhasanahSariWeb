<?php

namespace App\Exports\produksi;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use App\Models\Produksi\MasterProduct;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProduksiBulanan implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(public string $periode) {}

    public function view(): View
    {
        [$year, $month] = explode('-', $this->periode);

        // sama seperti query di Livewire
        $produkCollection = MasterProduct::whereHas('detailPerintahProduksi.perintahProduksi', function ($q) use ($year, $month) {
                $q->whereYear('tanggal_perintah', $year)
                  ->whereMonth('tanggal_perintah', $month);
            })
            ->with([
                'detailPerintahProduksi.perintahProduksi',
                'produksiTambahan.perintahProduksi'
            ])
            ->orderBy('nama')
            ->get();

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

        $matrix = [];
        $no = 1;
        foreach ($produkCollection as $produk) {
            $harian = [];
            foreach ($produk->detailPerintahProduksi as $dpp) {
                $tgl = Carbon::parse($dpp->perintahProduksi->tanggal_perintah)->format('Y-m-d');
                $harian[$tgl] = ($harian[$tgl] ?? 0) + (float) $dpp->produksi_qty;
            }
            foreach ($produk->produksiTambahan as $pt) {
                $tgl = Carbon::parse($pt->perintahProduksi->tanggal_perintah)->format('Y-m-d');
                $harian[$tgl] = ($harian[$tgl] ?? 0) + (float) $pt->qty_tambahan;
            }
            $row = [
                'no' => $no++,
                'produk' => $produk->nama,
                'days' => []
            ];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = sprintf('%s-%02d', $this->periode, $d);
                $val = $harian[$date] ?? null;
                $row['days'][$d] = $val ?: null;
            }
            $matrix[] = $row;
        }

        return view('exports.laporan-produksi-bulanan', [
            'rows' => $matrix,
            'days' => range(1, $daysInMonth),
            'periode' => $this->periode
        ]);
    }
    public function styles(Worksheet $sheet)
    {
        $highestRow    = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $range = "A1:{$highestColumn}{$highestRow}";

        // Border + vertical center saja (tanpa horizontal!)
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        return [];
    }
     /** Merge judul + warna header */
  public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet         = $event->sheet->getDelegate();
            $highestColumn = $sheet->getHighestColumn();
            $highestRow    = $sheet->getHighestRow();

            // Merge judul & bulan
            $sheet->mergeCells("A1:{$highestColumn}1");
            $sheet->mergeCells("A2:{$highestColumn}2");

            // Judul
            $sheet->getStyle("A1")->applyFromArray([
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Bulan
            $sheet->getStyle("A2")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Header baris ke-3
            $headerRange = "A3:{$highestColumn}3";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE5E5E5'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Tinggi baris
            $sheet->getRowDimension(1)->setRowHeight(24);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getRowDimension(3)->setRowHeight(18);

            // ===== ALIGNMENT DATA =====
            // 1) Kolom tanggal (C..akhir) rata tengah
            $sheet->getStyle("C4:{$highestColumn}{$highestRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

            // 2) Kolom "No" (A) rata tengah
            $sheet->getStyle("A4:A{$highestRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // 3) Kolom "Produk" (B) rata kiri — LETAKKAN PALING TERAKHIR agar tidak ditimpa
            $sheet->getStyle("B4:B{$highestRow}")
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                ->setWrapText(true)
                ->setIndent(1);
        },
    ];
}


}
