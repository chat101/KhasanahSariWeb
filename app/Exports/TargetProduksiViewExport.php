<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TargetProduksiViewExport implements FromView
{
    public function __construct(
        public string $tanggalProduksi,
        public array  $produk,
        public array  $produkList,
        public array  $metodeList,
        public array  $metodeSummary,
        public array  $jobSummary
    ) {}

    public function view(): View
    {
        return view('exports.target-produksi', [
            'tanggalProduksi' => $this->tanggalProduksi,
            'produk'          => $this->produk,
            'produkList'      => $this->produkList,
            'metodeList'      => $this->metodeList,
            'metodeSummary'   => $this->metodeSummary,
            'jobSummary'      => $this->jobSummary,
        ]);
    }
}
