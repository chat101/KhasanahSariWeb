<?php

namespace App\Livewire\Produksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;


    class PenyesuaianStok extends Component
    {
        public string $tanggal;            // 'YYYY-MM-DD'
        public array  $rows = [];          // [{id,nama,system,real,selisih,alasan}]
        public array  $counted = [];       // real dihitung user
        public array  $alasan  = [];

        public function mount(?string $tanggal = null)
        {
            $this->tanggal = $tanggal ?: now()->toDateString();
            $this->loadRows();
        }

        private function ensureRecapRow(string $tanggal, int $pid): void
        {
            // jika baris recap T belum ada -> create dengan stok_awal = last ending < T
            $exists = DB::table('stok_rekap_harian')
                ->where('mproducts_id', $pid)
                ->where('tanggal', $tanggal)
                ->exists();

            if (!$exists) {
                $lastEnding = (float) (DB::table('stok_rekap_harian')
                    ->where('mproducts_id', $pid)
                    ->where('tanggal', '<', $tanggal)
                    ->orderByDesc('tanggal')
                    ->selectRaw('COALESCE(stok_akhir, stok_awal + masuk_hari - keluar_hari) AS akhir')
                    ->value('akhir') ?? 0);

                DB::table('stok_rekap_harian')->insert([
                    'mproducts_id' => $pid,
                    'tanggal'      => $tanggal,
                    'stok_awal'    => $lastEnding,
                    'masuk_hari'   => 0,
                    'keluar_hari'  => 0,
                    // jika tidak generated:
                    'stok_akhir'   => $lastEnding,
                ]);
            }
        }

        private function currentSystemStock(string $tanggal, int $pid): float
        {
            // stok akhir berjalan (pakai baris T jika ada, fallback last ending < T)
            $row = DB::table('stok_rekap_harian')
                ->where('mproducts_id', $pid)
                ->where('tanggal', $tanggal)
                ->selectRaw('stok_awal, masuk_hari, keluar_hari, COALESCE(stok_akhir, stok_awal + masuk_hari - keluar_hari) AS akhir')
                ->first();

            if ($row) return (float) $row->akhir;

            return (float) (DB::table('stok_rekap_harian')
                ->where('mproducts_id', $pid)
                ->where('tanggal', '<', $tanggal)
                ->orderByDesc('tanggal')
                ->selectRaw('COALESCE(stok_akhir, stok_awal + masuk_hari - keluar_hari) AS akhir')
                ->value('akhir') ?? 0);
        }

        public function loadRows(): void
        {
            $produk = DB::table('mproducts')->select('id','nama')->orderBy('id')->get();
            $this->rows = [];
            foreach ($produk as $p) {
                $system = $this->currentSystemStock($this->tanggal, (int)$p->id);
                $this->rows[] = [
                    'id'     => (int)$p->id,
                    'nama'   => $p->nama,
                    'system' => (float)$system,
                ];
            }
        }

        public function updatedTanggal(): void
        {
            $this->loadRows();
            $this->counted = [];
            $this->alasan  = [];
        }

        public function save(): void
        {
            $this->validate([
                'tanggal'      => 'required|date',
                'counted.*'    => 'nullable|numeric|min:0',
                'alasan.*'     => 'nullable|string|max:255',
            ]);

            DB::transaction(function () {
                foreach ($this->rows as $r) {
                    $pid = (int)$r['id'];
                    if (!isset($this->counted[$pid]) || $this->counted[$pid] === '') continue;

                    $counted = (float)$this->counted[$pid];
                    $system  = $this->currentSystemStock($this->tanggal, $pid);
                    $diff    = $counted - $system; // +masuk, -keluar

                    // Jejak audit penyesuaian
                    DB::table('penyesuaian_stok')->insert([
                        'tanggal'      => $this->tanggal,
                        'mproducts_id' => $pid,
                        'qty_diff'     => $diff,
                        'alasan'       => $this->alasan[$pid] ?? null,
                        'user_id'      => Auth::id(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);

                    // Pastikan recap tanggal ada
                    $this->ensureRecapRow($this->tanggal, $pid);

                    // Terapkan ke rekap: diff>0 -> masuk; diff<0 -> keluar
                    DB::table('stok_rekap_harian')
                        ->where('mproducts_id', $pid)
                        ->where('tanggal', $this->tanggal)
                        ->update([
                            'masuk_hari'  => DB::raw('masuk_hari + '  . max($diff, 0)),
                            'keluar_hari' => DB::raw('keluar_hari + ' . max(-$diff, 0)),
                            // kalau tidak generated:
                            'stok_akhir'  => DB::raw('stok_awal + masuk_hari + ' . max($diff,0) . ' - (keluar_hari + ' . max(-$diff,0) . ')'),
                        ]);
                }
            });

            session()->flash('message', 'Penyesuaian stok tersimpan untuk '.$this->tanggal);
            $this->loadRows();
            $this->counted = [];
            $this->alasan  = [];
        }

        public function render()
        {
            return view('livewire.produksi.penyesuaian-stok');
        }
}
