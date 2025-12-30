<?php

namespace App\Livewire\Operasional;

use App\Models\MasterToko;
use App\Models\Operasional\KurangSetoran as KurangSetoranModel;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class KurangSetoran extends Component
{
    public $search = '';
    public string $tanggal;
    public $nominalByToko = []; // Format: [toko_id => nominal]
    public $keteranganByToko = []; // Format: [toko_id => keterangan]
    public $modal = false;
    protected function rules()
    {
        return [
            'tokoId' => 'required|exists:master_toko,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ];
    }

    public function updatingSearch()
    {
    }

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        $this->prefillFromDbForDate();
    }

    public function render()
    {
        $tokos = MasterToko::query()
            ->where('status', '1')
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where('nmtoko', 'like', $s)
                  ->orWhere('alamat', 'like', $s)
                  ->orWhereHas('area', fn($a) => $a->where('nama_area', 'like', $s));
            })
            ->with('area.wilayah')
            ->orderBy('nmtoko')
            ->get();

        // Calculate total nominal
        $totalNominal = array_sum(array_map(fn($v) => is_numeric($v) ? (int)$v : 0, $this->nominalByToko));

        // Existing saved per toko for selected date
        $selectedDate = $this->tanggal ?? now()->toDateString();
        $tokoIds = $tokos->pluck('id')->all();
        $existingRows = KurangSetoranModel::query()
            ->whereIn('toko_id', $tokoIds)
            ->whereDate('tanggal', $selectedDate)
            ->get()
            ->keyBy('toko_id');
        $existingByToko = [];
        foreach ($existingRows as $tid => $row) {
            $existingByToko[$tid] = [
                'nominal' => (int) ($row->nominal ?? 0),
                'keterangan' => (string) ($row->keterangan ?? ''),
                'updated_at' => optional($row->updated_at)->toDateTimeString(),
            ];
        }

        return view('livewire.operasional.kurang-setoran', [
            'tokos' => $tokos,
            'totalNominal' => $totalNominal,
            'existingByToko' => $existingByToko,
        ]);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->modal = true;
    }

    public function closeModal()
    {
        $this->modal = false;
    }

    public function resetForm()
    {
        $this->resetValidation();
    }

    public function saveNominal($tokoId)
    {
        $nominal = $this->nominalByToko[$tokoId] ?? null;

        if (!$nominal || !is_numeric($nominal)) {
            return;
        }

        // TODO: Save to database
        // KurangSetoran::updateOrCreate(
        //     ['toko_id' => $tokoId],
        //     ['nominal' => $nominal]
        // );

        // Optional: show success notification
        // session()->flash('message', 'Nominal kurang setoran berhasil disimpan');
    }

    public function saveKeterangan($tokoId)
    {
        $keterangan = $this->keteranganByToko[$tokoId] ?? null;

        // TODO: Save to database
        // KurangSetoran::updateOrCreate(
        //     ['toko_id' => $tokoId],
        //     ['keterangan' => $keterangan]
        // );
    }

    public function saveAll()
    {
        $this->validate([
            'tanggal' => 'required|date|before_or_equal:today',
        ]);
        try {
            // Gunakan transaction untuk atomicity
            DB::transaction(function () {
                $tanggal = Carbon::parse($this->tanggal)->toDateString();
                $savedCount = 0;

                foreach ($this->nominalByToko as $tokoId => $nominal) {
                    // Skip jika nominal dan keterangan kosong
                    $nominalInt = is_numeric($nominal) ? (int) str_replace('.', '', (string)$nominal) : 0;
                    $keterangan = $this->keteranganByToko[$tokoId] ?? null;

                    if ($nominalInt <= 0 && !$keterangan) {
                        continue;
                    }

                    // UpdateOrCreate untuk kurang setoran hari ini
                    KurangSetoranModel::updateOrCreate(
                        [
                            'toko_id'  => (int)$tokoId,
                            'tanggal'  => $tanggal,
                        ],
                        [
                            'nominal'     => $nominalInt,
                            'keterangan'  => $keterangan,
                        ]
                    );

                    $savedCount++;
                }

                // Flash message dengan jumlah data yang disimpan
                if ($savedCount > 0) {
                    session()->flash('message', "✅ {$savedCount} data kurang setoran berhasil disimpan untuk " . Carbon::parse($tanggal)->format('d/m/Y'));
                } else {
                    session()->flash('message', 'ℹ️ Tidak ada data kurang setoran yang perlu disimpan.');
                }
            });
        } catch (\Exception $e) {
            session()->flash('error', '❌ Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function updatedTanggal($value): void
    {
        // sanitize and prefill when date changes
        try {
            $this->tanggal = Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            // ignore parse failures, Livewire validation will catch
        }
        $this->prefillFromDbForDate();
    }

    private function prefillFromDbForDate(): void
    {
        $date = $this->tanggal ?? now()->toDateString();
        $rows = KurangSetoranModel::query()
            ->whereDate('tanggal', $date)
            ->get();

        // Reset then prefill
        $this->nominalByToko = [];
        $this->keteranganByToko = [];

        foreach ($rows as $r) {
            $tid = (int) ($r->toko_id ?? 0);
            if ($tid <= 0) continue;
            $nom = (int) ($r->nominal ?? 0);
            $ket = (string) ($r->keterangan ?? '');

            // preformat nominal with thousand separator for display
            $this->nominalByToko[$tid] = number_format($nom, 0, ',', '.');
            $this->keteranganByToko[$tid] = $ket;
        }
    }

    public function clearInputs(): void
    {
        $this->nominalByToko = [];
        $this->keteranganByToko = [];
        $this->resetValidation();
        session()->flash('message', 'Input untuk tanggal ini dibersihkan.');
    }

    public function store()
    {
        $this->validate();

        // TODO: Save to database (create KurangSetoran model & table if needed)
        // KurangSetoran::create([
        //     'toko_id' => $this->tokoId,
        //     'nominal' => $this->nominal,
        //     'keterangan' => $this->keterangan,
        //     'tanggal' => now(),
        // ]);

        session()->flash('message', 'Data kurang setoran berhasil disimpan.');
        $this->closeModal();
        $this->resetForm();
    }
}
