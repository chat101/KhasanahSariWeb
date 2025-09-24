<?php

namespace App\Exports\finance;


use App\Models\MasterToko;
use Illuminate\Support\Collection;
use App\Livewire\Finance\Setoran_Masuk;
use App\Models\Finance\Setoran_Masuk as FinanceSetoran_Masuk;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class SetoranHarianExportTxt implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    public function __construct(
        protected string $tanggalSetoran,
        protected ?string $search = null
    ) {}

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
        $grandTotal = 0.0;

        foreach ($tokos as $toko) {
            $utama = FinanceSetoran_Masuk::where('tokos_id', $toko->id)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'utama')
                ->first(['jumlah_uang', 'keterangan']);

            $tambahan = FinanceSetoran_Masuk::where('tokos_id', $toko->id)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'tambahan')
                ->get(['jumlah_uang', 'keterangan']);

            $sumUtama    = (float) ($utama->jumlah_uang ?? 0);
            $sumTambahan = (float) $tambahan->sum('jumlah_uang');
            $total       = $sumUtama + $sumTambahan;
            $grandTotal += $total;

            // Gabung keterangan jadi SATU BARIS (hindari newline di CSV/TXT)
        // Gabung keterangan (tetap bentuk lama)
$ketList = [];
if ($utama) {
    $ketList[] = 'Utama: ' . $this->fmt($sumUtama) . ($utama->keterangan ? ' — ' . $utama->keterangan : '');
}
foreach ($tambahan as $r) {
    $ketList[] = 'Tambahan: ' . $this->fmt((float)$r->jumlah_uang) . ($r->keterangan ? ' — ' . $r->keterangan : '');
}
$keterangan = implode(' | ', array_values(array_filter($ketList, fn($v) => trim((string)$v) !== '')));

// **PENTING**: potong setelah tanda " | " → hilangkan semua "Tambahan: …"
if ($keterangan !== '') {
    $keterangan = explode(' | ', $keterangan)[0];
}


            $rows->push([
                'no'         => $no++,
                'id'         => $toko->id,
                'toko'       => $toko->nmtoko,
                'utama'      => $sumUtama,
                'tambahan'   => $sumTambahan,
                'total'      => $total,
                'keterangan' => $keterangan,
            ]);
        }

        // Baris TOTAL
        $rows->push([
            'no'         => '',
            'id'         => '',
            'toko'       => 'TOTAL',
            'utama'      => '',
            'tambahan'   => '',
            'total'      => $grandTotal,
            'keterangan' => '',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['No', 'id', 'Nama Toko', 'Jumlah Utama', 'Jumlah Tambahan', 'Total Setoran', 'Keterangan (gabungan)'];
    }

    public function map($row): array
    {
        return [
            $row['no'],
            $row['id'],
            $row['toko'],
            $this->n($row['utama']),
            $this->n($row['tambahan']),
            $this->n($row['total']),
            $row['keterangan'],
        ];
    }

    /** Setting CSV untuk TXT (TAB-delimited) */
    public function getCsvSettings(): array
    {
        return [
            'delimiter'              => ",",     // TAB biar rapi di text editor/Excel
            'enclosure'              => '"',      // biarkan default agar aman jika ada karakter khusus
            'line_ending'            => "\r\n",   // Windows friendly
            'use_bom'                => true,     // BOM untuk Excel
            'escape_character'       => '\\',
            'include_separator_line' => false,
            'excel_compatibility'    => false,
            'input_encoding'         => 'UTF-8',
        ];
    }

    /** Format angka ID: 1.234 atau 1.234,56 tanpa simbol Rp */
    private function fmt(float $v): string
    {
        if (floor($v) == $v) {
            return number_format($v, 0, ',', '.');
        }
        return rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
    }

    /** Map angka jadi string terformat; kosongkan jika ''/null */
    private function n($v): string
    {
        if ($v === '' || $v === null) return '';
        return $this->fmt((float)$v);
    }
}
