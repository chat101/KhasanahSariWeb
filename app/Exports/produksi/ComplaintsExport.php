<?php

namespace App\Exports\produksi;
use App\Models\Produksi\Complaint;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ComplaintsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(
        protected ?string $start = null,
        protected ?string $end   = null
    ) {}

    public function query()
    {
        $q = Complaint::query()->with('toko')->orderByDesc('id');

        $start = $this->start ?: null;
        $end   = $this->end   ?: null;

        if ($start && $end) {
            if ($start > $end) { [$start, $end] = [$end, $start]; }
            $q->whereBetween('tgl', [$start, $end]);
        } elseif ($start) {
            $q->whereDate('tgl', '>=', $start);
        } elseif ($end) {
            $q->whereDate('tgl', '<=', $end);
        }

        return $q;
    }

    public function headings(): array
    {
        return ['TGL', 'NAMA TOKO', 'COMPLAIN', 'KETERANGAN', 'KESALAHAN'];
    }

    public function map($row): array
    {
        return [
            optional($row->tgl)->format('d/m/Y'),
            optional($row->toko)->nmtoko,
            $row->complain,
            $row->keterangan,
            $row->kesalahan,
        ];
    }
}
