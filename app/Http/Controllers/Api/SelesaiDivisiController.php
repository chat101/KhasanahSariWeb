<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

// Model sesuai struktur kamu
use App\Models\Produksi\MsJobs;
use App\Models\Produksi\InputSelesai;
use App\Models\Produksi\Perintah_Produksi;

class SelesaiDivisiController extends Controller
{
   /** Nama grup yang disatukan inputnya (samakan dengan Livewire) */
   private array $groupModeNames = ['POPROK','DEKOR'];

   /**
    * GET /api/selesai-divisi?perintah_id=...  (disarankan)
    *    atau  /api/selesai-divisi?tanggal=YYYY-MM-DD (ambil perintah pertama di tanggal itu)
    */
   public function index(Request $request)
   {
       $request->validate([
           'perintah_id' => ['nullable','integer','min:1'],
           'tanggal'     => ['nullable','date'],
       ]);

       // Tentukan perintah_id
       $perintahId = $request->integer('perintah_id');
       if (!$perintahId) {
           $tanggal = $request->query('tanggal');
           if ($tanggal) {
               $p = Perintah_Produksi::whereDate('tanggal_perintah', Carbon::parse($tanggal))->first();
           } else {
               // fallback: hari ini
               $p = Perintah_Produksi::whereDate('tanggal_perintah', Carbon::today())->first();
           }
           if (!$p) {
               return response()->json([
                   'ok' => false,
                   'message' => 'Perintah produksi tidak ditemukan untuk parameter yang diberikan.',
               ], 404);
           }
           $perintahId = $p->id;
       }

       $perintah = Perintah_Produksi::find($perintahId);
       if (!$perintah) {
           return response()->json(['ok' => false, 'message' => 'Perintah produksi tidak ditemukan.'], 404);
       }

       // Ambil semua jobs
       $jobs = MsJobs::select('id','nama_job','group_job')
           ->orderBy('group_job')->orderBy('nama_job')->get();

       // Build display rows (meniru Livewire)
       $byGroup = $jobs->groupBy(fn($j) => $j->group_job ?? '_NO_GROUP_');

       $displayRows = [];
       $groupKeyMap = [];
       $groupMembers = [];

       foreach ($byGroup as $groupName => $items) {
           $isGrouped = $groupName && in_array($groupName, $this->groupModeNames, true);

           if ($isGrouped) {
               $gkey = Str::slug($groupName, '_');
               $groupKeyMap[$gkey] = $groupName;
               $groupMembers[$gkey] = $items->pluck('id')->all();

               $displayRows[] = [
                   'type'        => 'group',
                   'group_name'  => $groupName,
                   'gkey'        => $gkey,
                   'member_ids'  => $groupMembers[$gkey],
               ];
           } else {
               foreach ($items as $j) {
                   $displayRows[] = [
                       'type' => 'job',
                       'id'   => $j->id,
                       'nama' => $j->nama_job,
                   ];
               }
           }
       }

       // Ambil data tersimpan untuk perintah ini
       $savedRows = InputSelesai::where('perintah_produksi_id', $perintahId)->get();

       // Status tersimpan per job
       $statusTersimpan = [];
       $jamSelesai = [];
       $keterangan = [];
       foreach ($savedRows as $r) {
           $statusTersimpan[$r->msjobs_id] = true;
           $jamSelesai[$r->msjobs_id]      = $r->waktu_selesai;
           $keterangan[$r->msjobs_id]      = $r->keterangan;
       }

       // Status & prefill grup
       $statusTersimpanGroup = [];
       $jamSelesaiGroup = [];
       $keteranganGroup = [];
       foreach ($groupMembers as $gkey => $members) {
           $subset = $savedRows->whereIn('msjobs_id', $members);
           $allSaved   = count($members) > 0 && $subset->count() === count($members);
           $statusTersimpanGroup[$gkey] = $allSaved;

           if ($subset->count() > 0) {
               $times = $subset->pluck('waktu_selesai')->unique()->values();
               $kets  = $subset->pluck('keterangan')->unique()->values();
               $jamSelesaiGroup[$gkey] = $times->count() === 1 ? $times->first() : '';
               $keteranganGroup[$gkey] = $kets->count()  === 1 ? $kets->first()  : '';
           } else {
               $jamSelesaiGroup[$gkey] = '';
               $keteranganGroup[$gkey] = '';
           }
       }

       return response()->json([
           'ok' => true,
           'perintah' => [
               'id'       => $perintah->id,
               'tanggal'  => (string) $perintah->tanggal_perintah,
               'status'   => (int) ($perintah->status ?? 0),
           ],
           'displayRows'          => $displayRows,
           'groupKeyMap'          => $groupKeyMap,
           'groupMembers'         => $groupMembers,
           'jamSelesai'           => $jamSelesai,
           'keterangan'           => $keterangan,
           'statusTersimpan'      => $statusTersimpan,
           'jamSelesaiGroup'      => $jamSelesaiGroup,
           'keteranganGroup'      => $keteranganGroup,
           'statusTersimpanGroup' => $statusTersimpanGroup,
       ]);
   }

   /**
    * POST /api/selesai-divisi/row
    * body: { perintah_id, job_id, waktu_selesai:"HH:MM", keterangan:"..." }
    */
   public function saveRow(Request $request)
   {
       $request->validate([
           'perintah_id'   => ['required','integer','min:1'],
           'job_id'        => ['required','integer','min:1'],
           'waktu_selesai' => ['required','date_format:H:i'],
           'keterangan'    => ['nullable','string'],
       ]);

       $perintahId = (int) $request->input('perintah_id');
       $jobId      = (int) $request->input('job_id');
       $jam        = $request->input('waktu_selesai');
       $ket        = $request->input('keterangan');

       InputSelesai::updateOrCreate(
           ['perintah_produksi_id' => $perintahId, 'msjobs_id' => $jobId],
           ['waktu_selesai' => $jam, 'keterangan' => $ket, 'users_id' => Auth::id()]
       );

       return response()->json(['ok' => true, 'message' => 'Tersimpan.']);
   }

   /**
    * POST /api/selesai-divisi/group
    * body: { perintah_id, gkey, waktu_selesai:"HH:MM", keterangan:"..." }
    */
   public function saveGroup(Request $request)
   {
       $request->validate([
           'perintah_id'   => ['required','integer','min:1'],
           'gkey'          => ['required','string'],
           'waktu_selesai' => ['required','date_format:H:i'],
           'keterangan'    => ['nullable','string'],
       ]);

       $perintahId = (int) $request->input('perintah_id');
       $gkey       = $request->input('gkey');
       $jam        = $request->input('waktu_selesai');
       $ket        = $request->input('keterangan');

       // bangun ulang mapping grup -> member
       $jobs = MsJobs::select('id','nama_job','group_job')->get();
       $byGroup = $jobs->groupBy(fn($j) => $j->group_job ?? '_NO_GROUP_');

       $foundMembers = [];
       foreach ($byGroup as $groupName => $items) {
           if ($groupName && in_array($groupName, $this->groupModeNames, true)) {
               $key = Str::slug($groupName, '_');
               if ($key === $gkey) {
                   $foundMembers = $items->pluck('id')->all();
                   break;
               }
           }
       }

       if (empty($foundMembers)) {
           return response()->json(['ok' => false, 'message' => 'Grup tidak ditemukan atau tidak punya anggota.'], 404);
       }

       foreach ($foundMembers as $jobId) {
           InputSelesai::updateOrCreate(
               ['perintah_produksi_id' => $perintahId, 'msjobs_id' => $jobId],
               ['waktu_selesai' => $jam, 'keterangan' => $ket, 'users_id' => Auth::id()]
           );
       }

       return response()->json(['ok' => true, 'message' => 'Jam selesai grup disimpan.', 'saved_count' => count($foundMembers)]);
   }
}
