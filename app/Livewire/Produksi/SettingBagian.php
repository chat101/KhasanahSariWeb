<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use App\Models\Produksi\MsJobs;
use Illuminate\Support\Facades\DB;
use App\Models\Produksi\JadwalDivisi;
use App\Models\Produksi\MasterProduct;

class SettingBagian extends Component
{
    public $jobs;
    public $msjobs_id;
    public $jadwals = [];

    public array $rows = [];
    public array $pilih = [];
    public array $produks = [];
    public array $matrixEdit = [];
    /** urutan hari untuk enum jadwal_divisi.hari */
    protected array $days = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];

    public function mount()
    {
        $this->jobs = MsJobs::with('produkToJob.product')->get();

        $this->jadwals = collect($this->days)
            ->map(fn($hari) => ['hari' => $hari, 'jumlah' => null])
            ->toArray();

        $this->produks = MasterProduct::query()
            ->orderBy('id')
            ->get(['id', 'nama', 'jenis', 'patokan', 'tong_produksi'])
            ->map(fn($p) => [
                'id' => (int) $p->id,
                'nama' => strtoupper($p->nama),
                'jenis' => $p->jenis ?? '-',
                'patokan' => (int) ($p->patokan ?? 0),
                'tong_produksi' => $p->tong_produksi,
            ])
            ->toArray();

        foreach ($this->produks as $r) {
            $id = (int) $r['id'];
            $tp = $r['tong_produksi'];
            $this->pilih[$id] = $this->pilih[$id] ?? [
                'besar' => $tp === 1 || $tp === '1',
                'kecil' => $tp === 0 || $tp === '0',
            ];
        }
        $this->loadMatrix(); // <-- panggil loader matriks
    }
 /** Muat nilai personil harian dari DB -> $matrixEdit */
 public function loadMatrix(): void
 {
     $jobs = MsJobs::orderBy('group_job')->orderBy('nama_job')->get(['id']);
     $byJob = JadwalDivisi::select('msjobs_id','hari',DB::raw('COALESCE(SUM(jumlah),0) as jumlah'))
         ->groupBy('msjobs_id','hari')->get()->groupBy('msjobs_id');

     $this->matrixEdit = [];
     foreach ($jobs as $job) {
         foreach ($this->days as $h) {
             $val = 0;
             if (isset($byJob[$job->id])) {
                 $rec = $byJob[$job->id]->firstWhere('hari', $h);
                 $val = (int) ($rec->jumlah ?? 0);
             }
             $this->matrixEdit[$job->id][$h] = $val;
         }
     }
 }

 /** Simpan satu baris (semua hari) untuk msjobs_id tertentu */
 public function saveRow(int $jobId): void
 {
     DB::transaction(function () use ($jobId) {
         foreach ($this->days as $h) {
             $val = (int) ($this->matrixEdit[$jobId][$h] ?? 0);
             JadwalDivisi::updateOrCreate(
                 ['msjobs_id' => $jobId, 'hari' => $h],
                 ['jumlah' => $val]
             );
         }
     });

     session()->flash('message', 'Jadwal divisi berhasil disimpan.');
     // opsional: refresh untuk pastikan sync dengan DB
     $this->loadMatrix();
 }
    /** Matriks untuk tabel ringkasan personil harian (semua divisi) */
    public function getMatrixProperty(): array
    {
        // preload seluruh jadwal (SUM per hari) lalu group by msjobs_id
        $jadwal = JadwalDivisi::select(
                'msjobs_id',
                'hari',
                DB::raw('COALESCE(SUM(jumlah),0) AS jumlah')
            )
            ->groupBy('msjobs_id','hari')
            ->get()
            ->groupBy('msjobs_id');

        $rows = [];
        $jobs = MsJobs::orderBy('group_job')->orderBy('nama_job')->get(['id','group_job','nama_job']);

        foreach ($jobs as $job) {
            $haris = [];
            $sum = 0; $count = 0;

            foreach ($this->days as $h) {
                // cari nilai untuk hari ini
                $val = 0;
                if ($jadwal->has($job->id)) {
                    $rec = $jadwal[$job->id]->firstWhere('hari', $h);
                    $val = (int) ($rec->jumlah ?? 0);
                }
                $haris[$h] = $val;

                if ($val > 0) { $sum += $val; $count++; }
            }

            $avg = $count ? round($sum / $count, 1) : 0.0;

            $rows[] = [
                'id'    => $job->id,
                'group' => $job->group_job,
                'nama'  => $job->nama_job,
                'hari'  => $haris,   // ['senin'=>x, ...]
                'avg'   => $avg,
            ];
        }

        return $rows;
    }

    public function render()
    {
        $jobs = MsJobs::orderBy('group_job')->orderBy('nama_job')->get(['id','group_job','nama_job']);
        return view('livewire.produksi.setting-bagian', [
            'divisis' => MsJobs::all(),
            'days'    => $this->days,
            'jobsForMatrix' => $jobs,   // <-- daftar baris
        ]);
    }

    public function updatedPilih($value, string $name): void
    {
        if (strpos($name, '.') === false) return;
        [$id, $kolom] = explode('.', $name, 2);
        $this->pilih[$id] = array_merge(['besar'=>false,'kecil'=>false], $this->pilih[$id] ?? []);
        if ($kolom === 'besar' && $value)  $this->pilih[$id]['kecil'] = false;
        if ($kolom === 'kecil' && $value)  $this->pilih[$id]['besar'] = false;
    }

    protected function rules()
    {
        return [
            'msjobs_id' => 'required|exists:msjobs,id',
            'jadwals.*.hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'jadwals.*.jumlah' => 'nullable|integer|min:0',
        ];
    }

    public function simpan()
    {
        $this->validate();
        foreach ($this->jadwals as $jadwal) {
            if ($jadwal['jumlah'] !== null) {
                JadwalDivisi::updateOrCreate(
                    ['msjobs_id' => $this->msjobs_id, 'hari' => $jadwal['hari']],
                    ['jumlah' => $jadwal['jumlah']]
                );
            }
        }
        session()->flash('message', 'Semua data berhasil disimpan');
    }

    public function simpanTong(): void
    {
        DB::transaction(function () {
            foreach ($this->produks as $r) {
                $id = (int) $r['id'];
                $cek = $this->pilih[$id] ?? [];
                $besar = (bool) ($cek['besar'] ?? false);
                $kecil = (bool) ($cek['kecil'] ?? false);
                $value = $besar ? 1 : ($kecil ? 0 : null);
                MasterProduct::whereKey($id)->update(['tong_produksi' => $value]);
            }
        });
        session()->flash('message', 'Pilihan ukuran disimpan.');
    }
}
