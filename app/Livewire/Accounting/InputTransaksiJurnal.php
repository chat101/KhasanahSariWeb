<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\Accounting\Coa;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\JurnalDetail;
use App\Models\Accounting\JurnalHeader;
use App\Models\Accounting\JournalTransactionType;

class InputTransaksiJurnal extends Component
{
     // field header
     public $tanggal;
     public $no_bukti;
     public $keterangan;

     // jenis transaksi
     public $transaction_type_id = '';

     // input dinamis dari user (akan dipakai oleh template)
     public $akun_id = '';          // akun biaya/pendapatan
     public $kas_id = '';           // kas umum
     public $kas_asal_id = '';      // mutasi kas
     public $kas_tujuan_id = '';    // mutasi kas
     public $nominal;

     // data dropdown
     public $transactionTypes;
     public $coasKas;
     public $coasBiaya;
     public $coasPendapatan;
     public $allCoas;

     // baris jurnal yang di-generate otomatis (tapi bisa diedit)
     public $lines = []; // tiap elemen: ['coa_id' => ..., 'debet' => ..., 'kredit' => ...]
     public $listHeaders = [];

     public function mount()
     {
         $this->tanggal = now()->format('Y-m-d');

         // load jenis transaksi
         $this->transactionTypes = JournalTransactionType::orderBy('nama')->get();

         // load COA
         $this->coasKas        = Coa::where('is_kas', true)->orderBy('kode')->get();
         $this->coasBiaya      = Coa::where('tipe', 'biaya')->orderBy('kode')->get();
         $this->coasPendapatan = Coa::where('tipe', 'pendapatan')->orderBy('kode')->get();
         $this->allCoas        = Coa::orderBy('kode')->get();

         // list hasil input
         $this->loadHeaders(); // ⬅️
     }

     public function loadHeaders()
     {
         $this->listHeaders = JurnalHeader::withSum('details as total_debet', 'debet')
             ->withSum('details as total_kredit', 'kredit')
             ->orderBy('tanggal', 'desc')
             ->orderBy('id', 'desc')
             ->limit(20)
             ->get();
     }

     public function render()
     {
         $currentType = null;
         if ($this->transaction_type_id) {
             $currentType = $this->transactionTypes->firstWhere('id', $this->transaction_type_id);
         }

         return view('livewire.accounting.input-transaksi-jurnal', [
             'currentType' => $currentType,
         ]);
     }

     // setiap ada perubahan input utama, regenerate baris jurnal
     public function updated($name, $value)
     {
         if (in_array($name, [
             'transaction_type_id',
             'akun_id',
             'kas_id',
             'kas_asal_id',
             'kas_tujuan_id',
             'nominal',
         ])) {
             $this->generateLines();
         }
     }

     protected function rules()
     {
         return [
             'tanggal'             => ['required', 'date'],
             'transaction_type_id' => ['required', 'exists:journal_transaction_types,id'],
             'nominal'             => ['required', 'numeric', 'min:1'],
         ];
     }

     public function save()
     {
         $this->validate();

         // 🔹 Ambil jenis transaksi + templates untuk lihat source_key apa saja yang dipakai
         $type = JournalTransactionType::with('templates')
             ->find($this->transaction_type_id);

         $sourceKeys = $type ? $type->templates->pluck('source_key')->all() : [];

         // 🔹 Validasi DINAMIS berdasarkan source_key yang dipakai template

         // butuh akun dari form?
         if (in_array('input_akun', $sourceKeys) && !$this->akun_id) {
             $this->addError('akun_id', 'Akun wajib dipilih.');
         }

         // butuh kas umum?
         if (in_array('input_kas', $sourceKeys) && !$this->kas_id) {
             $this->addError('kas_id', 'Kas wajib dipilih.');
         }

         // butuh kas asal mutasi?
         if (in_array('input_kas_asal', $sourceKeys) && !$this->kas_asal_id) {
             $this->addError('kas_asal_id', 'Kas asal wajib dipilih.');
         }

         // butuh kas tujuan mutasi?
         if (in_array('input_kas_tujuan', $sourceKeys) && !$this->kas_tujuan_id) {
             $this->addError('kas_tujuan_id', 'Kas tujuan wajib dipilih.');
         }

         if ($this->getErrorBag()->isNotEmpty()) {
             return;
         }

         // 🔹 Fallback: kalau lines masih kosong, coba generate dulu
         if (empty($this->lines)) {
             $this->generateLines();
         }

         // baris jurnal harus ada
         if (empty($this->lines)) {
             $this->addError('lines', 'Baris jurnal belum terbentuk.');
             return;
         }

         // validasi baris jurnal: harus ada akun & balance
         $totalDebet  = 0;
         $totalKredit = 0;

         foreach ($this->lines as $idx => $line) {
             $coaId  = $line['coa_id'] ?? null;
             $debet  = (float)($line['debet'] ?? 0);
             $kredit = (float)($line['kredit'] ?? 0);

             if (!$coaId) {
                 $this->addError("lines.$idx.coa_id", 'COA wajib dipilih.');
             }

             $totalDebet  += $debet;
             $totalKredit += $kredit;
         }

         if ($this->getErrorBag()->isNotEmpty()) {
             return;
         }

         if (round($totalDebet, 2) !== round($totalKredit, 2)) {
             $this->addError('lines', 'Total debet dan kredit harus sama.');
             return;
         }

         DB::beginTransaction();
         try {
             $header = JurnalHeader::create([
                 'no_bukti'   => $this->no_bukti,
                 'tanggal'    => $this->tanggal,
                 'keterangan' => $this->keterangan,
                 // kalau nanti kamu tambah kolom di tabel:
                 // 'transaction_type_id' => $this->transaction_type_id,
             ]);

             foreach ($this->lines as $line) {
                 JurnalDetail::create([
                     'jurnal_header_id' => $header->id,
                     'coa_id'           => $line['coa_id'],
                     'debet'            => $line['debet'] ?: 0,
                     'kredit'           => $line['kredit'] ?: 0,
                 ]);
             }

             DB::commit();

             // reset input
             $this->reset([
                 'no_bukti', 'keterangan',
                 'akun_id', 'kas_id', 'kas_asal_id', 'kas_tujuan_id',
                 'nominal', 'lines',
             ]);
             $this->tanggal = now()->toDateString();

             // ⬅️ refresh list hasil input
             $this->loadHeaders();

             $this->dispatch('notify', type: 'success', message: 'Jurnal berhasil disimpan.');
         } catch (\Throwable $e) {
             DB::rollBack();
             $this->dispatch('notify', type: 'error', message: 'Terjadi kesalahan saat menyimpan jurnal.');
         }
     }

     /**
      * Bangun baris jurnal (debet/kredit) berdasarkan template dan input user
      */
      public function generateLines()
      {
          $this->lines = [];

          if (!$this->transaction_type_id || !$this->nominal || $this->nominal <= 0) {
              return;
          }

          $type = JournalTransactionType::with('templates')
              ->find($this->transaction_type_id);

          if (!$type || $type->templates->isEmpty()) {
              return;
          }

          $amount    = (float) $this->nominal;
          $templates = $type->templates->sortBy('order_no');

          $lines = [];

          foreach ($templates as $tpl) {
              $coaId = $this->resolveCoaIdFromSourceKey($tpl->source_key);

              $lines[] = [
                  'coa_id'     => $coaId ?: null,
                  'debet'      => $tpl->side === 'debit'  ? $amount : 0,
                  'kredit'     => $tpl->side === 'kredit' ? $amount : 0,
                  'source_key' => $tpl->source_key,   // ⬅️ penting
              ];
          }

          $this->lines = $lines;
      }


     /**
      * Terjemahkan source_key dari template menjadi coa_id
      */
     protected function resolveCoaIdFromSourceKey(string $sourceKey): ?int
     {
         // contoh: role:hutang_dagang
         if (str_starts_with($sourceKey, 'role:')) {
             $role = substr($sourceKey, strlen('role:'));
             $coa = Coa::where('default_role', $role)->first();
             return $coa?->id;
         }

         return match ($sourceKey) {
             'input_akun'       => $this->akun_id ?: null,
             'input_kas'        => $this->kas_id ?: null,
             'input_kas_asal'   => $this->kas_asal_id ?: null,
             'input_kas_tujuan' => $this->kas_tujuan_id ?: null,
             default            => null,
         };
     }
     /**
 * Pilih list COA yang ditampilkan di dropdown, tergantung source_key baris itu.
 */
public function getCoaOptionsForSourceKey(?string $sourceKey)
{
    if (!$sourceKey) {
        return $this->allCoas;
    }

    // kalau dari role:... → default kita batasi ke COA dengan default_role itu
    if (str_starts_with($sourceKey, 'role:')) {
        $role = substr($sourceKey, strlen('role:'));
        return Coa::where('default_role', $role)
            ->orderBy('kode')
            ->get();
    }

    // sumber dari input kas / kas_asal / kas_tujuan → hanya akun kas/bank
    if (in_array($sourceKey, ['input_kas', 'input_kas_asal', 'input_kas_tujuan'])) {
        return $this->coasKas;
    }

    // sumber dari input_akun → biasanya biaya & pendapatan saja
    if ($sourceKey === 'input_akun') {
        return $this->coasBiaya->merge($this->coasPendapatan);
    }

    // fallback: semua COA
    return $this->allCoas;
}

}
