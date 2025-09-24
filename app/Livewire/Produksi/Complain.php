<?php

namespace App\Livewire\Produksi;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MasterToko;
use App\Models\Produksi\Complaint;
use App\Exports\produksi\ComplaintsExport;
use Maatwebsite\Excel\Facades\Excel;

class Complain extends Component
{
    use WithPagination;

    // --- Form state
    public $tgl;              // 'Y-m-d'
    public $tokos_id = null;  // <-- simpan id toko di sini
    public $complain = '';
    public $keterangan = '';
    public $kesalahan  = '';

    // --- Opsi dropdown (statis)
    public array $keteranganOptions = ['tidak sesuai sj','patah / rusak','expired','salah item','tidak dikirim','salah turun','dus kosong','lainnya'];
    public array $kesalahanOptions  = ['DISTRIBUSI','PENGIRIM','PRODUKSI','TOKO','LAINNYA'];

    // --- UI helper utk dropdown toko
    public string $label = 'TOKO';
    public string $placeholder = '— pilih toko —';
    public bool   $disabled = false;
    public ?string $dateStart = null;
    public ?string $dateEnd   = null;
    // --- data toko utk <select>
    public array $options = [];
  // Reset page saat filter diubah
  public function updatingDateStart() { $this->resetPage(); }
  public function updatingDateEnd()   { $this->resetPage(); }

    public function mount(): void
    {
        // default tanggal = hari ini
        $this->tgl = now()->toDateString();

        // ambil data toko (id, text)
        $this->options = MasterToko::query()
            ->orderBy('nmtoko')
            ->get(['id','nmtoko'])
            ->map(fn($r) => ['id' => $r->id, 'text' => $r->nmtoko])
            ->all();
    }

    /** Aturan validasi (dinamis) */
    protected function rules(): array
    {
        $tokoTable = (new MasterToko)->getTable(); // pastikan pakai nama tabel sebenarnya
        return [
            'tgl'        => ['required','date'],
            'tokos_id'   => ['required','integer',"exists:{$tokoTable},id"],
            'complain'   => ['required','string','min:2'],
            'keterangan' => ['required','string'],
            'kesalahan'  => ['required','string'],
        ];
    }

    public function save(): void
    {
        $this->validate($this->rules());

        Complaint::create([
            'tokos_id'   => (int) $this->tokos_id,
            'tgl'        => $this->tgl,
            'complain'   => trim((string) $this->complain),
            'keterangan' => $this->keterangan,
            'kesalahan'  => $this->kesalahan,
        ]);

        // reset form (kecuali tanggal)
        $this->reset(['tokos_id','complain','keterangan','kesalahan']);
        $this->resetPage();

        $this->dispatch('saved');
        session()->flash('message', 'Complain tersimpan.');
    }

    public function render()
    {
        // 1) Mulai dengan query dasar
    $q = Complaint::query()
    ->with('toko')          // relasi toko untuk nama toko
    ->orderByDesc('id');    // urut terbaru

// 2) Terapkan filter tanggal (opsional)
$start = $this->dateStart ?: null;
$end   = $this->dateEnd   ?: null;

if ($start && $end) {
    if ($start > $end) { [$start, $end] = [$end, $start]; } // swap kalau kebalik
    $q->whereBetween('tgl', [$start, $end]);
} elseif ($start) {
    $q->whereDate('tgl', '>=', $start);
} elseif ($end) {
    $q->whereDate('tgl', '<=', $end);
}

// 3) Ambil hasil
$rows = $q->paginate(10);

        return view('livewire.produksi.complain', [
            'rows' => $rows,
        ]);
    }
    public function exportXlsx()
{
    $start = $this->dateStart ?: null;
    $end   = $this->dateEnd   ?: null;

    $labelRange = $start || $end
        ? sprintf('(%s-%s)', $start ?? 'all', $end ?? 'all')
        : '(all)';

    $filename = 'complain-export-'.$labelRange.'.xlsx';

    return Excel::download(new ComplaintsExport($start, $end), $filename);
}
}
