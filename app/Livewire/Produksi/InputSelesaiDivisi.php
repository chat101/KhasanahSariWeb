<?php

namespace App\Livewire\Produksi;

use App\Models\Produksi\MsJobs;
use Livewire\Component;
use App\Models\Produksi\InputSelesai;
use App\Models\Produksi\Perintah_Produksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // <— TAMBAH

class InputSelesaiDivisi extends Component
{
    public array $divisiList = []; // masih dipakai untuk referensi
    public array $displayRows = []; // <-- LIST UNTUK DITAMPILKAN (job atau group)
    public array $groupMembers = []; // key = gkey, value = [job_id,...]
    public array $groupKeyMap = []; // gkey => group_name

    public array $jamSelesai = []; // per jobId
    public array $keterangan = []; // per jobId
    public array $statusTersimpan = []; // per jobId

    public array $jamSelesaiGroup = []; // per gkey
    public array $keteranganGroup = []; // per gkey
    public array $statusTersimpanGroup = []; // per gkey (true jika semua subjob tersimpan)

    public string $tanggalProduksi;
    public $perintahProduksi;
    public $perintah_id;

    /** Grup yang ingin disatukan inputnya */
    protected array $groupModeNames = ['POPROK','DEKOR']; // <-- sesuaikan daftar grup di sini
    // protected array $groupModeNames = ['POPROK', 'GRUP_LAIN'];

    public function mount($perintah_id)
    {
        $this->perintah_id = $perintah_id;

        $perintah = Perintah_Produksi::find($perintah_id);
        $this->tanggalProduksi = $perintah ? $perintah->tanggal_perintah : now()->format('Y-m-d');

        // Ambil jobs: pastikan MsJobs punya kolom group_job
        $jobs = MsJobs::select('id', 'nama_job', 'group_job')->orderBy('group_job')->orderBy('nama_job')->get();

        // Simpan list asli bila perlu
        $this->divisiList = $jobs->map(fn($j) => ['id' => $j->id, 'nama_job' => $j->nama_job, 'group_job' => $j->group_job])->toArray();

        // Bangun displayRows: jika group_job termasuk groupModeNames => tampil 1 baris grup
        $byGroup = $jobs->groupBy(fn($j) => $j->group_job ?? '_NO_GROUP_');
        $this->displayRows = [];
        foreach ($byGroup as $groupName => $items) {
            $isGrouped = $groupName && in_array($groupName, $this->groupModeNames, true);

            if ($isGrouped) {
                $gkey = Str::slug($groupName, '_'); // kunci aman untuk Livewire
                $this->groupKeyMap[$gkey] = $groupName;
                $this->groupMembers[$gkey] = $items->pluck('id')->all();

                $this->displayRows[] = [
                    'type' => 'group',
                    'group_name' => $groupName,
                    'gkey' => $gkey,
                    'member_ids' => $this->groupMembers[$gkey],
                ];

                // init state grup
                $this->jamSelesaiGroup[$gkey] = $this->jamSelesaiGroup[$gkey] ?? '';
                $this->keteranganGroup[$gkey] = $this->keteranganGroup[$gkey] ?? '';
                $this->statusTersimpanGroup[$gkey] = $this->statusTersimpanGroup[$gkey] ?? false;

                // init tiap member job juga
                foreach ($this->groupMembers[$gkey] as $jid) {
                    $this->jamSelesai[$jid] = $this->jamSelesai[$jid] ?? '';
                    $this->keterangan[$jid] = $this->keterangan[$jid] ?? '';
                }
            } else {
                // tampilkan tiap job satu baris
                foreach ($items as $j) {
                    $this->displayRows[] = [
                        'type' => 'job',
                        'id' => $j->id,
                        'nama' => $j->nama_job,
                    ];
                    $this->jamSelesai[$j->id] = $this->jamSelesai[$j->id] ?? '';
                    $this->keterangan[$j->id] = $this->keterangan[$j->id] ?? '';
                }
            }
        }

        $this->loadJamSelesai();
    }

    public function loadJamSelesai()
    {
        $rows = InputSelesai::where('perintah_produksi_id', $this->perintah_id)->get();

        // job-level
        foreach ($rows as $item) {
            $this->jamSelesai[$item->msjobs_id] = $item->waktu_selesai;
            $this->keterangan[$item->msjobs_id] = $item->keterangan;
            $this->statusTersimpan[$item->msjobs_id] = true;
        }

        // group-level status + prefill (jika semua child jam sama)
        foreach ($this->groupMembers as $gkey => $memberIds) {
            $subset = $rows->whereIn('msjobs_id', $memberIds);
            $allSaved = count($memberIds) > 0 && $subset->count() === count($memberIds);
            $this->statusTersimpanGroup[$gkey] = $allSaved;

            if ($subset->count() > 0) {
                $times = $subset->pluck('waktu_selesai')->unique()->values();
                $kets = $subset->pluck('keterangan')->unique()->values();

                // kalau semua sama → prefill
                $this->jamSelesaiGroup[$gkey] = $times->count() === 1 ? $times->first() : $this->jamSelesaiGroup[$gkey] ?? '';
                $this->keteranganGroup[$gkey] = $kets->count() === 1 ? $kets->first() : $this->keteranganGroup[$gkey] ?? '';
            }
        }
    }

    public function render()
    {
        // refresh status tersimpan per job (biar realtime)
        $tersimpanIds = InputSelesai::where('perintah_produksi_id', $this->perintah_id)->pluck('msjobs_id')->toArray();
        $this->statusTersimpan = array_fill_keys($tersimpanIds, true);

        // refresh status grup
        foreach ($this->groupMembers as $gkey => $ids) {
            $countSaved = count(array_intersect($ids, $tersimpanIds));
            $this->statusTersimpanGroup[$gkey] = $countSaved === count($ids) && count($ids) > 0;
        }

        return view('livewire.produksi.input-selesai', [
            'displayRows' => $this->displayRows,
            'divisiList' => $this->divisiList,
            'perintahProduksi' => $this->perintahProduksi,
        ]);
    }

    public function simpanPerRow($jobId)
    {
        $jam = $this->jamSelesai[$jobId] ?? null;
        $keterangan = $this->keterangan[$jobId] ?? null;
        $namaJob = MsJobs::find($jobId)->nama_job ?? "Job ID: $jobId";

        if (!$jam) {
            session()->flash('message', "Jam belum diisi untuk Bagian: $namaJob");
            return;
        }

        // gunakan upsert agar bisa update jika sudah ada
        InputSelesai::updateOrCreate(['perintah_produksi_id' => $this->perintah_id, 'msjobs_id' => $jobId], ['waktu_selesai' => $jam, 'keterangan' => $keterangan, 'users_id' => Auth::id()]);

        $this->statusTersimpan[$jobId] = true;
        session()->flash('message', "Jam selesai untuk Bagian $namaJob berhasil disimpan.");
        $this->loadJamSelesai();
    }

    /** Simpan jam selesai untuk satu GROUP (diterapkan ke semua anggotanya) */
    public function simpanGrup(string $gkey)
    {
        $groupName = $this->groupKeyMap[$gkey] ?? $gkey;
        $memberIds = $this->groupMembers[$gkey] ?? [];

        $jam = $this->jamSelesaiGroup[$gkey] ?? null;
        $ket = $this->keteranganGroup[$gkey] ?? null;

        if (!$jam) {
            session()->flash('message', "Jam belum diisi untuk Grup: $groupName");
            return;
        }
        if (empty($memberIds)) {
            session()->flash('message', "Grup $groupName tidak memiliki sub-divisi.");
            return;
        }

        foreach ($memberIds as $jobId) {
            InputSelesai::updateOrCreate(['perintah_produksi_id' => $this->perintah_id, 'msjobs_id' => $jobId], ['waktu_selesai' => $jam, 'keterangan' => $ket, 'users_id' => Auth::id()]);
            $this->statusTersimpan[$jobId] = true;
        }

        $this->statusTersimpanGroup[$gkey] = true;
        session()->flash('message', "Jam selesai Grup $groupName disimpan ke " . count($memberIds) . ' sub-divisi.');
        $this->loadJamSelesai();
    }
}
