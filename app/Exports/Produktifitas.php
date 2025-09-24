<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Produktifitas implements FromView, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(
        public string $tanggalProduksi,
        public array $produk, // hasil map->keyBy('id')->toArray() di cariData()
        public array $produkList, // list master produk untuk urutan + nama
        public array $metodeList, // list metode untuk urutan
        public array $metodeSummary, // ringkasan metode
        public array $jobSummary,
    ) {
        // ringkasan job (sudah diperkaya di cariData)
    }

    public function view(): View
    {
        // Jangan format angka di sini—biarkan Excel yang format.
        // Jika perlu subtotal per grup, kita hitung di blade export juga.
        $tanggal = Carbon::parse($this->tanggalProduksi)->format('Y-m-d');

        return view('Livewire.produksi.exports.produktifitas-harian', [
            'tanggal' => $tanggal,
            'produk' => $this->produk,
            'produkList' => $this->produkList,
            'metodeList' => $this->metodeList,
            'metodeSummary' => $this->metodeSummary,
            'jobSummary' => $this->jobSummary,
        ]);
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Cari baris header tiap tabel berdasarkan isi kolom A
                $rowProduk = $this->findRowByFirstCell($sheet, 'PRODUK'); // header tabel 1
                $rowMetode = $this->findRowByFirstCell($sheet, 'METODE'); // header tabel 2
                $rowGroup = $this->findRowByFirstCell($sheet, 'GROUP'); // header tabel 3

                if (!$rowProduk || !$rowMetode || !$rowGroup) {
                    return; // aman kalau ada yang tak ketemu
                }

                // Hitung baris akhir per tabel (skip 1 baris kosong antar tabel -> -2)
                $endProduk = max($rowProduk, $rowMetode - 2);
                $endMetode = max($rowMetode, $rowGroup - 2);
                $endGroup = $sheet->getHighestRow();

                // Kolom terakhir per tabel (sesuaikan bila struktur berubah)
                $lastColTbl1 = 'C'; // PRODUK | TONG | PCS
                $lastColTbl2 = 'C'; // METODE | TONG | PCS
                $lastColTbl3 = 'K'; // GROUP..KETERANGAN (11 kolom)

                // Style border tipis
                $borderThin = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ];

                // 1) Terapkan border per tabel
                $sheet->getStyle("A{$rowProduk}:{$lastColTbl1}{$endProduk}")->applyFromArray($borderThin);
                $sheet->getStyle("A{$rowMetode}:{$lastColTbl2}{$endMetode}")->applyFromArray($borderThin);
                $sheet->getStyle("A{$rowGroup}:{$lastColTbl3}{$endGroup}")->applyFromArray($borderThin);

                // 2) Hapus border di baris kosong antar tabel (jika ada)
                $gap1 = $rowMetode - 1; // baris kosong sebelum header METODE
                $gap2 = $rowGroup - 1; // baris kosong sebelum header GROUP
                foreach ([$gap1, $gap2] as $gapRow) {
                    if ($gapRow > 0) {
                        $sheet->getStyle("A{$gapRow}:{$lastColTbl3}{$gapRow}")->applyFromArray([
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_NONE],
                            ],
                        ]);
                    }
                }

                // 3) Bold baris header tiap tabel
                $sheet
                    ->getStyle("A{$rowProduk}:{$lastColTbl1}{$rowProduk}")
                    ->getFont()
                    ->setBold(true);
                $sheet
                    ->getStyle("A{$rowMetode}:{$lastColTbl2}{$rowMetode}")
                    ->getFont()
                    ->setBold(true);
                $sheet
                    ->getStyle("A{$rowGroup}:{$lastColTbl3}{$rowGroup}")
                    ->getFont()
                    ->setBold(true);

                // 4) Autosize kolom yang dipakai
                foreach (range('A', $lastColTbl3) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    /**
     * Cari baris pertama yang kolom A-nya sama persis (case-insensitive) dengan $text.
     */
    private function findRowByFirstCell(Worksheet $sheet, string $text): ?int
    {
        $max = $sheet->getHighestRow();
        $target = mb_strtoupper(trim($text));

        for ($r = 1; $r <= $max; $r++) {
            $val = $sheet->getCell("A{$r}")->getValue();
            if (is_string($val) && mb_strtoupper(trim($val)) === $target) {
                return $r;
            }
        }
        return null;
    }
}
