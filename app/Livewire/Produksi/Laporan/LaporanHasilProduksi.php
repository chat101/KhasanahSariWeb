<?php

namespace App\Livewire\Produksi\Laporan;

use Carbon\Carbon;
use Livewire\Component;
use App\Exports\produksi\ProduksiBulanan;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Produksi\MasterProduct;

class LaporanHasilProduksi extends Component
{
    /** @var string Format: Y-m (contoh: 2025-08) */
    public string $periode;

    /** Matrix data untuk tabel */
    public array $rows = [];

    public function mount(?string $periode = null): void
    {
        $this->periode = $periode ?: now()->format('Y-m');
        $this->buildRows();
    }

    public function updatedPeriode(): void
    {
        $this->buildRows();
    }

    #[Computed]
    public function days(): array
    {
        [$year, $month] = explode('-', $this->periode);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);

        // return [1,2,...,N]
        return range(1, $daysInMonth);
    }

    private function buildRows(): void
    {
        [$year, $month] = explode('-', $this->periode);

        $produkCollection = MasterProduct::whereHas('detailPerintahProduksi.perintahProduksi', function ($q) use ($year, $month) {
                $q->whereYear('tanggal_perintah', $year)
                  ->whereMonth('tanggal_perintah', $month);
            })
            ->with([
                'detailPerintahProduksi' => function ($query) use ($year, $month) {
                    $query->whereHas('perintahProduksi', function ($q) use ($year, $month) {
                            $q->whereYear('tanggal_perintah', $year)
                              ->whereMonth('tanggal_perintah', $month);
                        })
                        ->with('perintahProduksi');
                },
                'produksiTambahan' => function ($q) use ($year, $month) {
                    $q->whereHas('perintahProduksi', function ($sub) use ($year, $month) {
                        $sub->whereYear('tanggal_perintah', $year)
                            ->whereMonth('tanggal_perintah', $month);
                    })->with('perintahProduksi');
                },
            ])
            ->orderBy('nama')
            ->get();

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $matrix = [];
        $no = 1;

        foreach ($produkCollection as $produk) {
            // Kumpulkan qty per tanggal (gabungan utama+tambahan)
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
                'no'     => $no++,
                'produk' => $produk->nama,
                'days'   => [],
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = sprintf('%s-%02d', $this->periode, $d);
                // kosongkan sel jika 0 agar tampilan mirip template
                $val = (float) ($harian[$date] ?? 0);
                $row['days'][$d] = $val > 0 ? $val : null;
            }

            $matrix[] = $row;
        }

        $this->rows = $matrix;
        // dd($this->rows);
    }

    public function render()
    {
        return view('livewire.produksi.laporan.laporan-hasil-produksi');
    }
    public function export()
    {
        $filename = 'hasil-produksi-' . $this->periode . '.xlsx';
        return Excel::download(new ProduksiBulanan($this->periode), $filename);
    }
}
