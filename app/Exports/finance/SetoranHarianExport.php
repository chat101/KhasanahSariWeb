<?php

namespace App\Exports\finance;
use App\Models\Finance\Setoran_Masuk;
use App\Models\MasterToko;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SetoranHarianExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct(protected string $tanggalSetoran, protected ?string $search = null) {}

    /** Ambil data per toko sesuai tanggal + filter search */
    public function collection(): Collection
    {
        $tokos = MasterToko::select('id', 'nmtoko')
            ->when($this->search && trim($this->search) !== '', function ($q) {
                $q->where('nmtoko', 'like', '%' . trim($this->search) . '%');
            })
            ->orderBy('nmtoko')
            ->get();

        $rows = collect();
        $no = 1;
        $grandTotal = 0;

        foreach ($tokos as $toko) {
            $utama = Setoran_Masuk::where('tokos_id', $toko->id)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'utama')
                ->first(['jumlah_uang', 'keterangan']);

            $tambahan = Setoran_Masuk::where('tokos_id', $toko->id)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'tambahan')
                ->get(['jumlah_uang', 'keterangan']);

            $sumTambahan = (float) $tambahan->sum('jumlah_uang');
            $sumUtama = (float) ($utama->jumlah_uang ?? 0);
            $total = $sumUtama + $sumTambahan;
            $grandTotal += $total;

            // gabungan keterangan (list)
            $ketList = [];
            if ($utama) {
                $ketList[] = 'Utama: ' . $this->fmt($sumUtama) . ($utama->keterangan ? ' — ' . $utama->keterangan : '');
            }
            foreach ($tambahan as $r) {
                $ketList[] = 'Tambahan: ' . $this->fmt($r->jumlah_uang) . ($r->keterangan ? ' — ' . $r->keterangan : '');
            }

            $rows->push([
                'no' => $no++,
                'id' => $toko->id,
                'toko' => $toko->nmtoko,
                'utama' => $sumUtama,
                'tambahan' => $sumTambahan,
                'total' => $total,
                'keterangan' => implode("\n", $ketList),
            ]);
        }

        // tambahkan baris TOTAL
        $rows->push([
            'no' => '',
            'id' => '',
            'toko' => 'TOTAL',
            'utama' => '',
            'tambahan' => '',
            'total' => $grandTotal,
            'keterangan' => '',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'id','Nama Toko', 'Jumlah Utama', 'Jumlah Tambahan', 'Total Setoran', 'Keterangan (gabungan)'];
    }

    /** Mapping ke kolom Excel */
    public function map($row): array
    {
        return [$row['no'], $row['id'],$row['toko'], $this->asNumber($row['utama']), $this->asNumber($row['tambahan']), $this->asNumber($row['total']), $row['keterangan']];
    }

    /** Styling: header bold, wrap text di kolom keterangan */
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet
            ->getStyle('F2:F' . $sheet->getHighestRow())
            ->getAlignment()
            ->setWrapText(true);

        // Format angka rupiah sederhana (tanpa simbol)
        $sheet
            ->getStyle('C2:E' . $sheet->getHighestRow())
            ->getNumberFormat()
            ->setFormatCode('#,##0');

        // Lebarkan kolom keterangan dikit
        $sheet->getColumnDimension('F')->setWidth(60);
        // Tambahkan border ke semua sel yang terisi
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();
        $sheet
            ->getStyle("A1:{$lastCol}{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        // Ratakan teks: angka kanan, teks tengah/kiri
        $sheet
            ->getStyle("C2:E{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet
            ->getStyle("A1:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        return [];
    }

    /** helper tampil angka tanpa trailing nol */
    private function fmt($v): string
    {
        $n = (float) $v;
        if (floor($n) == $n) {
            return number_format($n, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    }

    private function asNumber($v)
    {
        if ($v === '' || $v === null) {
            return null;
        }
        return (float) $v;
    }
}
