<?php

namespace App\Exports\produksi;


use Maatwebsite\Excel\Concerns\WithMultipleSheets;
class ProduksiMingguanExport implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(
        private array $brownisCakeData,
        private array $kukerData,
        private string $tanggalAwal,
        private string $tanggalAkhir
    ) {}

    public function sheets(): array
    {
        // Asumsikan BrownisCakeExport & KukerExport kamu sudah siap pakai
        return [
            // Sheet 1
            new BrownisCakeExport($this->brownisCakeData, $this->tanggalAwal, $this->tanggalAkhir),

            // Sheet 2
            new KukerExport($this->kukerData, $this->tanggalAwal, $this->tanggalAkhir),
        ];
    }
}
