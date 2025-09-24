<?php

namespace App\Livewire\Finance;

use App\Models\MasterToko;
use App\Models\Finance\Setoran_Masuk; // <-- sesuaikan jika nama model berbeda
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\finance\SetoranHarianExport;
use App\Exports\finance\SetoranHarianExportTxt;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Livewire\Component;

class SetoranMasuk extends Component
{
    /** Data utama */
    public array $inputs = []; // nilai setoran utama per toko id
    public array $tokos = []; // daftar toko untuk tabel
    public array $mtokos = []; // master toko (id, nmtoko)
    public string $tanggalSetoran; // tanggal aktif
    public ?string $search = null;

    /** Modal Tambahan (per baris) */
    public bool $showTambahModal = false;
    public ?int $selectedTokoId = null;
    public ?string $selectedTokoName = null;

    /** Form tambahan */
    public $jumlahTambahan;
    public ?string $keteranganTambahan = null;

    public array $keteranganUtama = []; // <-- baru
    public array $keteranganListPerToko = [];
    /** Staging (draft, belum commit DB) */
    public array $stagedTambahan = []; // tiap item: ['tokos_id'=>int,'nmtoko'=>string,'jumlah_uang'=>float,'keterangan'=>?string]
    public array $hasUtama = []; // apakah toko sudah ada setoran utama (untuk disable input utama)
    /** Riwayat tersimpan (dari DB) untuk toko terpilih */
    public array $riwayatTambahan = [];
    public array $sumPerToko = []; // total setoran per toko (utama + tambahan)

    public function mount(): void
    {
        $this->tanggalSetoran = now()->format('Y-m-d');
        $this->mtokos = MasterToko::select('id', 'nmtoko')->orderBy('nmtoko')->get()->toArray();

        $this->loadSetoran();
    }

    public function render()
    {
        return view('livewire.finance.setoran-masuk', [
            'tokos' => $this->tokos,
            'mtokos' => $this->mtokos,
        ]);
    }

    /** Dipanggil saat tanggal diganti oleh Alpine->Livewire */
    public function updatedTanggalSetoran($value): void
    {
        $this->loadSetoran();
        // jika modal terbuka, muat ulang riwayat sesuai toko terpilih
        if ($this->showTambahModal && $this->selectedTokoId) {
            $this->loadRiwayatTambahan($this->selectedTokoId);
        }
    }

    /** Muat data tabel toko + nilai input awal (sesuaikan sesuai kebutuhan) */
    public function loadSetoran(): void
    {
        $this->tokos = MasterToko::select('id', 'nmtoko')->orderBy('nmtoko')->get()->toArray();

        foreach ($this->tokos as $t) {
            $tid = (int) $t['id'];

            // hitung total (utama + tambahan) pada tanggal aktif
            $sum = Setoran_Masuk::where('tokos_id', $tid)
            ->whereDate('tanggal_setoran', $this->tanggalSetoran)
            ->sum('jumlah_uang');
            $this->sumPerToko[$tid] = (float) $sum;

            // Ambil 1 baris utama (kalau ada) untuk prefill input & keteranganUtama
            $utama = Setoran_Masuk::where('tokos_id', $tid)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'utama')
                ->first(['jumlah_uang', 'keterangan']);
            // tandai apakah sudah ada utama (untuk disabled di input)
            $this->hasUtama[$tid] = (bool) $utama;
            // seed input HANYA jika belum pernah ada; kosongkan kalau belum ada utama
            if (!array_key_exists($tid, $this->inputs)) {
                $this->inputs[$tid] = '';
            }
            // Prefill input utama (jangan pakai SUM agar tidak dobel)
            $this->inputs[$tid] = $this->inputs[$tid] ?? ($utama?->jumlah_uang ?? null);
            $this->keteranganUtama[$tid] = $this->keteranganUtama[$tid] ?? ($utama?->keterangan ?? null);

            // JANGAN tampilkan nilai jika sudah ada 'utama' -> biarkan kosong (akan di-disable di Blade)
            if ($this->hasUtama[$tid]) {
                $this->inputs[$tid] = '';
            }

            // Ambil semua tambahan (bisa banyak)
            $tambahanRows = Setoran_Masuk::where('tokos_id', $tid)
                ->whereDate('tanggal_setoran', $this->tanggalSetoran)
                ->where('status', 'tambahan')
                ->orderBy('created_at')
                ->get(['jumlah_uang', 'keterangan', 'created_at']);

            // Susun list gabungan
            // Susun list gabungan (hanya yang ada isinya)
            $list = [];

            if ($utama && isset($utama->keterangan)) {
                $k = trim((string) $utama->keterangan);
                if ($k !== '') {
                    $list[] = $k;
                }
            }

            foreach ($tambahanRows as $r) {
                if (isset($r->keterangan)) {
                    $k = trim((string) $r->keterangan);
                    if ($k !== '') {
                        $list[] = $k;
                    }
                }
            }

            // bersihkan lagi (jaga-jaga), hilangkan duplikat, reindex
            $list = array_values(array_unique(array_filter($list, fn($v) => trim((string) $v) !== '')));

            // set hanya jika benar-benar ada isi; kalau tidak, buang key-nya
            if (!empty($list)) {
                $this->keteranganListPerToko[$tid] = $list;
            } else {
                unset($this->keteranganListPerToko[$tid]);
            }
        }
    }

    /** Klik tombol + pada baris index tertentu */
    public function openTambahModal(int $index): void
    {
        $row = $this->tokos[$index] ?? null;
        if (!$row) {
            return;
        }

        $this->selectedTokoId = (int) $row['id'];
        $this->selectedTokoName = $row['nmtoko'];
        $this->jumlahTambahan = null;
        $this->keteranganTambahan = null;

        $this->loadRiwayatTambahan($this->selectedTokoId);

        $this->showTambahModal = true;
    }

    /** Muat riwayat dari DB untuk toko terpilih pada tanggalSetoran */
    public function loadRiwayatTambahan(int $tokoId): void
    {
        // Sesuaikan query dengan skema tabel Anda
        $this->riwayatTambahan = Setoran_Masuk::with('tokos:id,nmtoko')->where('tokos_id', $tokoId)->whereDate('tanggal_setoran', $this->tanggalSetoran)->orderByDesc('created_at')->get()->all();
    }
    function parseRupiahToFloat($val): float {
        return (float) str_replace(',', '.', str_replace('.', '', $val));
    }
    /** Tambah baris draft ke staging (belum simpan DB) */
    public function stageTambahan(): void
    {
        $this->validate([
            'jumlahTambahan' => ['required'],
            'keteranganTambahan' => ['nullable', 'string', 'max:255'],
        ]);

        if (!$this->selectedTokoId) {
            $this->dispatch('swal:error', 'Toko belum dipilih.');
            return;
        }

        $this->stagedTambahan[] = [
            'tokos_id' => $this->selectedTokoId,
            'nmtoko' => $this->selectedTokoName,
            'jumlah_uang'  => $this->normalizeAmount($this->jumlahTambahan), // pakai helper
            'keterangan' => $this->keteranganTambahan ?: null,
        ];

        // reset field input
        $this->jumlahTambahan = null;
        $this->keteranganTambahan = null;
    }

    /** Hapus 1 baris draft dari staging */
    public function removeStaged(int $index): void
    {
        if (isset($this->stagedTambahan[$index])) {
            array_splice($this->stagedTambahan, $index, 1);
        }
    }

    /** Simpan semua draft untuk toko terpilih ke DB */
    public function saveTambahan(): void
    {
        if (!$this->selectedTokoId) {
            return;
        }

        $drafts = array_values(array_filter($this->stagedTambahan, fn($row) => (int) $row['tokos_id'] === (int) $this->selectedTokoId));

        if (empty($drafts)) {
            $this->dispatch('swal:error', 'Tidak ada draft untuk disimpan.');
            return;
        }

        DB::transaction(function () use ($drafts) {
            foreach ($drafts as $row) {
                Setoran_Masuk::create([
                    'tokos_id' => $row['tokos_id'],
                    'tanggal_setoran' => $this->tanggalSetoran,
                    'jumlah_uang' => $row['jumlah_uang'],
                    'keterangan' => $row['keterangan'],
                    'status' => 'tambahan', // contoh kolom tambahan
                    'user_id' => Auth::id(),
                    // tambahkan kolom lain jika perlu: 'created_by' => auth()->id(), ...
                ]);
            }
        });
        $this->loadSetoran(); // <--- refresh total per toko

        // Buang draft yang barusan disimpan
        $this->stagedTambahan = array_values(array_filter($this->stagedTambahan, fn($row) => (int) $row['tokos_id'] !== (int) $this->selectedTokoId));

        // reload riwayat
        $this->loadRiwayatTambahan($this->selectedTokoId);

        session()->flash('message', 'Tambahan setoran berhasil disimpan.');
    }
    /** Ubah "1.234,56" atau "1234.56" menjadi float 1234.56 */
  /** Ubah "1.234,56" atau "1234.56" atau "360.000" jadi float 1234.56 / 360000 */
private function normalizeAmount(null|string|float $v): float
{
    if ($v === null || $v === '') {
        return 0.0;
    }

    $s = (string) $v;
    // Sisakan hanya digit, koma, titik, minus
    $s = preg_replace('/[^\d.,-]/', '', $s) ?? '';

    $comma = strrpos($s, ',');
    $dot   = strrpos($s, '.');

    if ($comma !== false && $dot !== false) {
        // Ada koma & titik
        if ($comma > $dot) {
            // Format ID: 1.234,56 -> buang titik (ribuan), koma jadi titik (desimal)
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // Format EN: 1,234.56 -> buang koma (ribuan), titik biarkan (desimal)
            $s = str_replace(',', '', $s);
        }
    } elseif ($comma !== false) {
        // Hanya koma -> anggap koma desimal, titik (jika ada) ribuan
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        // Tidak ada koma:
        // UI kamu mem-format ribuan pakai titik, jadi anggap SEMUA titik = pemisah ribuan
        // Contoh "360.000" -> "360000", "1.234.567" -> "1234567"
        $s = str_replace('.', '', $s);
        // Jika mau tetap mendukung desimal-EN "1234.56", hapus 2 baris di atas dan pakai deteksi pola.
    }

    // Rapikan trailing separator
    $s = rtrim($s, '.');

    // Tangani tanda minus (opsional)
    if (substr_count($s, '-') > 1) {
        $s = str_replace('-', '', $s); // buang minus berlebih
    }

    return (float) $s;
}

    /** Simpan form utama (nilai inputs per toko) */
    public function submit(): void
    {
        $this->validate([
            'inputs.*' => ['nullable', 'regex:/^\s*(?:\d{1,3}(?:\.\d{3})*(?:,\d*)?|\d+(?:\.\d*)?)\s*$/'],
            'keteranganUtama.*' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($this->inputs as $tokosId => $nilaiRaw) {
            $tokosId = (int) $tokosId;

            // jika sudah ada 'utama' dan input kosong (karena disabled), lewatkan saja
            if (($this->hasUtama[$tokosId] ?? false) && (is_null($nilaiRaw) || trim((string) $nilaiRaw) === '')) {
                continue;
            }

            $amount = $this->normalizeAmount($nilaiRaw);

            if ($amount <= 0) {
                Setoran_Masuk::where('tokos_id', $tokosId)->whereDate('tanggal_setoran', $this->tanggalSetoran)->where('status', 'utama')->delete();
                continue;
            }

            Setoran_Masuk::firstOrCreate(
                [
                    'tokos_id' => $tokosId,
                    'tanggal_setoran' => $this->tanggalSetoran,
                    'status' => 'utama',
                ],
                [
                    'jumlah_uang' => $amount,
                    'keterangan' => $this->keteranganUtama[$tokosId] ?? null,
                    'user_id' => Auth::id(),
                ],
            );
        }

        session()->flash('message', 'Setoran utama diproses.');
        $this->loadSetoran();
    }
    public function exportExcel()
    {
        $filename = 'Setoran_' . $this->tanggalSetoran . '.xlsx';
        return Excel::download(new SetoranHarianExport($this->tanggalSetoran, $this->search ?? null), $filename);
    }
    public function exportTxt()
{
    $filename = 'Setoran_' . $this->tanggalSetoran . '.txt';

    $response = \Maatwebsite\Excel\Facades\Excel::download(
        new SetoranHarianExportTxt($this->tanggalSetoran, $this->search ?? null),
        $filename,
        ExcelWriter::CSV // pakai CSV writer, tapi setting & ekstensi .txt
    );

    // Paksa MIME jadi text/plain agar jelas TXT
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    $response->headers->set('X-Content-Type-Options', 'nosniff');

    return $response;
}

}
