<?php

namespace App\Exports;

use App\Models\Gudang_Masuk;
use App\Models\GudangMasuk;
use App\Models\Purchasing;
use App\Models\Purchasing_Details;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GudangMasukExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $tanggalAwal, $tanggalAkhir;

    public function __construct($awal, $akhir)
    {
        $this->tanggalAwal = $awal;
        $this->tanggalAkhir = $akhir;
    }

    public function collection()
    {

        return Purchasing_Details::with('purchasing.gudangmasuk.details.barang', 'purchasing.gudangmasuk.supplier')
        ->whereHas('purchasing.gudangmasuk', function ($query) {
            $query->whereBetween('tanggal', [$this->tanggalAwal, $this->tanggalAkhir]);
        })
        ->get()
        ->map(function ($detailPurchasing) {
            $purchasing = $detailPurchasing->purchasing;
            $gudang = $purchasing->gudangMasuk ?? null;
            $supplier = $gudang?->supplier;

            // cari detail gudang yang sesuai dengan barang pada purchasing detail
            $detailGudang = $gudang?->details
                ->where('barang_id', $detailPurchasing->barang_id)
                ->first();

            $diskon = $detailPurchasing->diskon ;
            $ttlbersih = $detailPurchasing->total;
            $ppn = $detailPurchasing->ppn / 100 ;
            $gramasi = $detailGudang->gramasi ?? 0 ;
            $qty = $detailGudang->qty ?? 0;
            $ttlblmdisc = $qty * $detailPurchasing->harga;
            $ttlsetelahdic = ($ttlblmdisc - $diskon);
            $ppnsetelahdic = ($ttlblmdisc - $diskon) * $ppn;
            $pembelian = $ttlsetelahdic + $ppnsetelahdic;
            $totalgram = $gramasi * $qty;

            return [
                $gudang->tanggal ?? '-',  //TANGGAL
                $detailGudang->barang->barang_id ?? '-', //ID BARANG
                $detailGudang->barang->nmbarang ?? '-', //NAMA BARANG
                $gudang->supplier->supplier_id ?? '-', //ID SUPPLIER
                $supplier->nmsupp ?? '-', //SUPPLIER
                $gudang->no_faktur ?? '-', //VALID KODE
                $detailGudang->barang->nmbarang ?? '-', //URAIAN
                $detailPurchasing->qty ?? 0, //QTY
                $detailPurchasing->satuan ?? '-', //UNIT
                $detailPurchasing->harga ?? 0, //HARGA
                $ttlblmdisc ?? 0, //TOTAL
                $ppnsetelahdic, //PPN (af Disc )
                $ttlsetelahdic, //DPP
                $detailPurchasing->diskon ?? 0, //DISKON
                $pembelian, //PEMBELIAN
                $gudang->no_po ?? '-', //KETERANGAN
                $gramasi, //gramasi
                $totalgram, //ttlgramasi
            ];
        });
}

    public function headings(): array
    {
        return [
            'TANGGAL',
            'ID BARANG',
            'NAMA BARANG',
            'ID SUPPLIER',
            'SUPPLIER',
            'VALID KODE',
            'URAIAN',
            'QTY',
            'UNIT',
            'HARGA',
            'TOTAL',
            'PPN (af Disc )',
            'DPP',
            'DISKON',
            'PEMBELIAN',
            'KETERANGAN',
            'gramasi',
            'ttlgramasi',
        ];
    }

    public function styles($sheet)
    {
        // Mengatur font header
        $sheet->getStyle('A1:R1')->getFont()->setBold(False)->setSize(9)->setName('Aptos Narrow');

        // Mengatur font untuk seluruh data
        $sheet->getStyle('A2:R' . $sheet->getHighestRow())
            ->getFont()
            ->setSize(9)
            ->setName('Aptos Narrow');

        // Auto resize untuk setiap kolom (A, B, C, D)
        foreach (range('A', 'R') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Menambahkan border pada tabel
        $sheet->getStyle('A1:R' . $sheet->getHighestRow())->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Menambahkan rata tengah untuk kolom tanggal dan no PO, no Faktur
        $sheet->getStyle('A1:A' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C1:R' . $sheet->getHighestRow())->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [
            'A1:R1' => [
                'font' => [
                    'bold' => false,
                    'size' => 9, // ukuran font header
                    'name' => 'Aptos Narrow', // font untuk header
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Gudang Masuk Report';
    }
}
