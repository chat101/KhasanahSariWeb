<?php
    namespace App\Livewire\Produksi;


    use App\Models\Produksi\Master_produkToJob;
    use App\Models\Produksi\MasterProduct;
    use App\Models\Produksi\MsJobs;
    use Livewire\Component;
    use Livewire\WithPagination;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Auth;



    class Produk extends Component
    {

        use WithPagination;

        protected $paginationTheme = 'tailwind'; // untuk style pagination yang lebih cantik
        public $produkId,$kodeproduk, $nama, $jenis, $patokan, $metode, $dekor,$hpp;
        public $search = ''; // Make sure this is initialized
        public $mode;

        public array $jobs = [];
        public array $msjobs = []; // untuk opsi dropdown
        public $modal = false;

        protected $listeners = ['triggerDelete' => 'delete'];

        // Perbaiki rules validasi
        protected $rules = [
            'nama' => 'required|string|max:255',
            'kodeproduk' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'patokan' => 'required|numeric',
            'hpp' => 'required|numeric',

            'metode' => 'required|string|max:255',

        ];

        // Hapus confirmDelete, karena sudah ada listener triggerDelete
        public function delete($id)
        {
            MasterProduct::findOrFail($id)->delete();
            session()->flash('message', 'Data berhasil dihapus!');
        }
        public function mount()
        {
            // Default satu baris kosong
                // Default: 1 baris job kosong
                $this->jobs = [['job_id' => '']];

        // Ambil semua nama_job dari database
        $this->msjobs = MsJobs::select('id', 'nama_job')->get()->toArray();
        }

        public function render()
        {
            // Log::info('Search keyword: ' . $this->search);
            $produks = MasterProduct::query();

            if ($this->search) {
                $produks = $produks->where(function ($query) {
                    $query->where('nama', 'like', '%' . $this->search . '%')->orWhere('produk_id', 'like', '%' . $this->search . '%');
                });
            }

            $produks = $produks->with('jobs.job')->orderBy('id', 'asc')->latest()->paginate(50);

            return view('livewire.master.produk', [
                'produks' => $produks,
            ]);

        }

        public function openModal()
        {
            $this->mode = 'editBahan';
            $this->resetInputFields();
            $this->modal = true;
        }

        public function closeModal()
        {
            $this->modal = false;
        }

        public function resetInputFields()
        {
            $this->nama = '';
            $this->kodeproduk = '';
            $this->jenis = '';
            $this->hpp = '';
            $this->patokan = '';
            $this->metode = '';
            $this->dekor = '';
            $this->produkId = null;
        }

        public function store()
        {
            $this->validate();

            MasterProduct::updateOrCreate(
                ['id' => $this->produkId],

                [
                    'produk_id' => $this->kodeproduk,
                    'nama' => $this->nama,
                    'jenis' => $this->jenis,
                    'hpp_produk' => $this->hpp,
                    'patokan' => $this->patokan,
                    'metode' => $this->metode,
                    'dekor' =>$this->dekor,

                ],
            );

            session()->flash('message', $this->produkId ? 'Produk berhasil diupdate.' : 'Produk berhasil ditambahkan.');

            $this->closeModal();
            $this->resetInputFields();
            // Reset pagination ke halaman pertama setelah menyimpan
            $this->resetPage();
        }

        public function edit($id)
        {
            $this->mode = 'editBahan';
            $produk = MasterProduct::findOrFail($id);
            $this->produkId = $id;
            $this->kodeproduk = $produk->produk_id;
            $this->nama = $produk->nama; // Pastikan sesuai dengan kolom di database
            $this->jenis = $produk->jenis; // Pastikan sesuai dengan kolom di database
            $this->hpp = $produk->hpp_produk; // Pastikan sesuai dengan kolom di database
            $this->patokan = $produk->patokan; // Pastikan sesuai dengan kolom di database
            $this->metode = $produk->metode; // Pastikan sesuai dengan kolom di database
            $this->dekor = $produk->dekor; // Pastikan sesuai dengan kolom di database
            $this->modal = true;
        }

        public function storeJob()
        {
            // dd($this->jobs);
            $duplikat = [];

            foreach ($this->jobs as $job) {
                if (empty($job['job_id'])) {
                    continue;
                }

                $exists = Master_produkToJob::where('msproducts_id', $this->produkId)
                    ->where('msjobs_id', $job['job_id'])
                    ->exists();

                if ($exists) {
                    // Kumpulkan job_id yang duplikat
                    $duplikat[] = $job['job_id'];
                    continue;
                }

                // Simpan jika tidak duplikat
                Master_produkToJob::create([
                    'msproducts_id' => $this->produkId,
                    'msjobs_id' => $job['job_id'],
                ]);
            }

            if (count($duplikat)) {
                // Ambil nama job dari ID duplikat untuk ditampilkan
                $namaJobs = MsJobs::whereIn('id', $duplikat)->pluck('nama_job')->toArray();
                session()->flash('message', 'Job berikut sudah pernah ditambahkan: ' . implode(', ', $namaJobs));
            } else {
                session()->flash('message', $this->produkId ? 'Produk berhasil diupdate.' : 'Produk berhasil ditambahkan.');
                $this->jobs = [['job_id' => '', 'nama_job' => '']]; // Reset input job
                $this->closeModal();
            }

        }
        public function addJob()
        {
            $this->jobs[] = ['msjobs_id' => ''];
        }
        public function editJob($id)
        {
            $this->mode = 'editJob';
            $this->produkId = $id;
            $produk = MasterProduct::findOrFail($id);
            $this->kodeproduk = $produk->produk_id?? null;
            $this->nama = $produk->nama?? null; // Pastikan sesuai dengan kolom di database
            $this->modal = true;
        }

            public function removeJob($index)
        {
            unset($this->jobs[$index]);
            $this->jobs = array_values($this->jobs); // Reset index array agar tidak lompat
        }

        public function updatingSearch()
        {
            $this->resetPage();
        }
    }
