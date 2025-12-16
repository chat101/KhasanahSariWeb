<?php

namespace App\Livewire\Master;

use App\Models\MasterToko;
use Carbon\Carbon;
use Livewire\Component;

use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Operasional\MasterProyeksiKontribusi;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class UploadProyeksi extends Component
{
    use WithFileUploads;

    public $file;
    public array $errorsImport = [];
    public ?string $lastBatchId = null;
    public $latestRows = [];
    public array $pivot = [];
    public array $dates = [];
    public int $sumQty = 0;
    public int $sumRupiah = 0;

    protected $rules = [
        'file' => 'required|mimes:xlsx,xls|max:2048',
    ];
    public function mount()
    {
        $this->loadLastBatch();
        $this->sumQty = (int) collect($this->latestRows)->sum('qty');
        $this->sumRupiah = (int) collect($this->latestRows)->sum('rupiah');
    }
    public function loadLastBatch(): void
    {
        $this->pivot = [];
        $this->dates = [];
        $this->latestRows = [];

        // ambil batch terakhir
        $this->lastBatchId = MasterProyeksiKontribusi::query()
            ->whereNotNull('batch_id')
            ->latest('id')
            ->value('batch_id');

        if (!$this->lastBatchId) {
            return;
        }

        // 🔥 INI BAGIAN PENTING (SUM DI DATABASE)
        $this->latestRows = MasterProyeksiKontribusi::query()
            ->with('toko:id,nmtoko')
            ->selectRaw('toko_id, tanggal, SUM(qty) as qty, SUM(rupiah) as rupiah')
            ->where('batch_id', $this->lastBatchId)
            ->groupBy('toko_id', 'tanggal')
            ->orderBy('toko_id')
            ->orderBy('tanggal')
            ->get();

        // build pivot
        foreach ($this->latestRows as $r) {
            $tgl = \Carbon\Carbon::parse($r->tanggal)->format('d/m');
            $this->dates[$tgl] = $tgl;

            $toko = $r->toko->nmtoko ?? '-';

            $this->pivot[$toko][$tgl] = [
                'qty'     => (int) $r->qty,
                'rupiah' => (int) $r->rupiah,
            ];
        }

        ksort($this->dates);
        ksort($this->pivot);
    }


    public function import()
    {
        $this->validate();
        $this->errorsImport = [];
        $this->latestRows = [];

        $sheets = Excel::toArray([], $this->file);
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            $this->errorsImport[] = "File kosong / tidak ada data.";
            return;
        }

        // hapus header (baris pertama)
        array_shift($rows);

        $this->lastBatchId = 'BP-' . now()->format('YmdHis') . '-' . substr(md5((string) microtime()), 0, 6);
        $successCount = 0;

        foreach ($rows as $idx => $row) {

            $line = $idx + 2; // baris excel asli (header baris 1)

            // Karena kolom A kosong, data mulai dari kolom B:
            $namaToko = trim((string)($row[1] ?? '')); // B
            $jenis    = trim((string)($row[2] ?? '')); // C
            $tanggal  = $row[3] ?? null;               // D
            $qty      = $row[4] ?? 0;                  // E
            $rupiah   = $row[5] ?? 0;                  // F

            // skip baris kosong total
            if ($namaToko === '' && $jenis === '' && empty($tanggal) && empty($qty) && empty($rupiah)) {
                continue;
            }

            // skip header kalau keikut
            if (strtolower($namaToko) === 'toko') {
                continue;
            }

            // ✅ lebih aman: kalau nama toko kosong, anggap baris sampah/trailing → skip (bukan error)
            if ($namaTokoExcel === '') {
                $this->errorsImport[] = "Baris {$line}: Nama toko kosong (kolom toko wajib diisi).";
                continue;
            }

            if ($jenis === '') {
                $this->errorsImport[] = "Baris {$line}: Jenis kosong.";
                continue;
            }

            // cek toko
            $namaTokoExcel = trim((string)$namaToko);

            $toko = MasterToko::query()
                ->whereRaw('LOWER(TRIM(nmtoko)) = ?', [mb_strtolower($namaTokoExcel)])
                ->first();
                if (!$toko) {
                    $this->errorsImport[] = "Baris {$line}: Toko '{$namaTokoExcel}' tidak terdaftar di master toko (tokos.nmtoko).";
                    continue;
                }

            // parse tanggal
            try {
                if ($tanggal instanceof \DateTimeInterface) {
                    $tgl = Carbon::instance($tanggal);
                } elseif (is_numeric($tanggal)) {
                    $tgl = Carbon::instance(ExcelDate::excelToDateTimeObject($tanggal));
                } else {
                    $tgl = Carbon::parse($tanggal);
                }
            } catch (\Throwable $e) {
                $this->errorsImport[] = "Baris {$line}: Tanggal '{$tanggal}' tidak valid.";
                continue;
            }

            // normalisasi qty & rupiah
            $qtyInt = (int) round((float) $qty);
            $rupiahInt = (int) round((float) $rupiah);

            MasterProyeksiKontribusi::updateOrCreate(
                [
                    'toko_id' => $toko->id,
                    'tanggal' => $tgl->toDateString(),
                    'jenis'   => $jenis,
                ],
                [
                    'qty'           => $qtyInt,
                    'rupiah'        => $rupiahInt,
                    'periode_bulan' => (int) $tgl->month,
                    'periode_tahun' => (int) $tgl->year,
                    'batch_id'      => $this->lastBatchId,
                ]
            );

            $successCount++;
        }

        // ✅ LOAD DATA TERBARU walaupun ada sebagian error, asal ada yang berhasil
        if ($successCount > 0) {
            $this->latestRows = MasterProyeksiKontribusi::with('toko:id,nmtoko')
                ->where('batch_id', $this->lastBatchId)
                ->orderBy('toko_id')
                ->orderBy('tanggal')
                ->get();
        }
        // build pivot table
        $this->pivot = [];
        $this->dates = [];

        foreach ($this->latestRows as $r) {
            $tgl = Carbon::parse($r->tanggal)->format('d/m');

            $this->dates[$tgl] = $tgl;

            $toko = $r->toko->nmtoko;

            if (!isset($this->pivot[$toko])) {
                $this->pivot[$toko] = [];
            }

            $this->pivot[$toko][$tgl] = [
                'qty' => $r->qty,
                'rupiah' => $r->rupiah,
            ];
        }

        ksort($this->dates); // urut tanggal
        // ✅ Pesan status
        if ($successCount > 0 && count($this->errorsImport) === 0) {
            session()->flash('success', "Import sukses: {$successCount} baris.");
            $this->reset('file');
            return;
        }

        if ($successCount > 0 && count($this->errorsImport) > 0) {
            session()->flash('success', "Sebagian berhasil diimport: {$successCount} baris. (Ada baris yang gagal)");
            $this->reset('file');
            return;
        }
        $this->loadLastBatch();
        // kalau 0 berhasil, error list sudah tampil
    }


    public function render()
    {
        return view('livewire.master.upload-proyeksi');
    }
}
