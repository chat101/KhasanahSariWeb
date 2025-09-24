<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Finance\MasterKontrakan as FinanceMasterKontrakan;
use App\Models\MasterToko;

class MasterKontrakan extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $tokoId    = null;
    // form state
    public ?int $editingId = null; // id kontrakan (null = create)
   // selalu wajib, agar bisa create untuk toko tertentu
    public $form = [
        'area' => '', 'jenis' => '', 'bank' => '',
        'nama_rekening' => '', 'no_rekening' => '',
        'nilai_sewa' => null, 'keterangan' => '',
    ];

    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'search' => ['except' => ''],
        'page'   => ['except' => 1],
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $s = trim($this->search);

        $tokos = MasterToko::with([
            'kontrakans' => function ($q) use ($s) {
                // Saat search: hanya muat kontrakan yang match
                $q->when($s !== '', function ($qq) use ($s) {
                    $qq->where(function ($w) use ($s) {
                        $like = "%{$s}%";
                        $w->where('area', 'LIKE', $like)
                          ->orWhere('jenis', 'LIKE', $like)
                          ->orWhere('bank', 'LIKE', $like)
                          ->orWhere('nama_rekening', 'LIKE', $like)
                          ->orWhere('no_rekening', 'LIKE', $like);
                    });
                })->orderBy('id');
            }
        ])
        ->when($s !== '', function ($q) use ($s) {
            $like = "%{$s}%";
            // Tampilkan toko yang namanya cocok ATAU punya kontrakan yang cocok
            $q->where(function ($w) use ($like) {
                $w->where('nmtoko', 'LIKE', $like)
                  ->orWhereHas('kontrakans', function ($rq) use ($like) {
                      $rq->where(function ($r) use ($like) {
                          $r->where('area', 'LIKE', $like)
                            ->orWhere('jenis', 'LIKE', $like)
                            ->orWhere('bank', 'LIKE', $like)
                            ->orWhere('nama_rekening', 'LIKE', $like)
                            ->orWhere('no_rekening', 'LIKE', $like);
                      });
                  });
            });
        })
        ->orderBy('id')
        ->paginate(150);

        $allTokos = MasterToko::orderBy('nmtoko')->get(['id','nmtoko']);

        return view('livewire.finance.master-kontrakan', compact('tokos', 'allTokos'));
    }




    /** Buka modal:
     *  - Edit kontrakan → beri $kontrakanId
     *  - Tambah kontrakan untuk toko tertentu → $kontrakanId=null, $tokoId diisi
     *  - Tambah umum (dari tombol header) → dua-duanya null, user pilih toko di modal
     */
    public function openEditKontrakan(?int $kontrakanId = null, ?int $tokoId = null)
    {
        $this->resetValidation();
        $this->editingId = $kontrakanId;

        if ($kontrakanId) {
            $k = FinanceMasterKontrakan::findOrFail($kontrakanId);
            $this->tokoId = $k->toko_id;
            $this->form = [
                'area' => $k->area ?? '',
                'jenis' => $k->jenis ?? '',
                'bank' => $k->bank ?? '',
                'nama_rekening' => $k->nama_rekening ?? '',
                'no_rekening' => $k->no_rekening ?? '',
                'nilai_sewa' => $k->nilai_sewa,
                'keterangan' => $k->keterangan ?? '',
            ];
        } else {
            $this->tokoId = $tokoId; // bisa null (user pilih di modal)
            $this->form = [
                'area' => '',
                'jenis' => '',
                'bank' => '',
                'nama_rekening' => '',
                'no_rekening' => '',
                'nilai_sewa' => null,
                'keterangan' => '',
            ];
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['editingId', 'tokoId']);
        $this->form = [
            'area' => '',
            'jenis' => '',
            'bank' => '',
            'nama_rekening' => '',
            'no_rekening' => '',
            'nilai_sewa' => null,
            'keterangan' => '',
        ];
    }


    public function saveKontrakan()
    {
        $this->validate([
            'tokoId' => 'required|exists:tokos,id',
            'form.area' => 'nullable|string',
            'form.jenis' => 'nullable|string',
            'form.bank' => 'nullable|string',
            'form.nama_rekening' => 'nullable|string',
            'form.no_rekening' => 'nullable|string',
            'form.nilai_sewa' => 'nullable|numeric',
            'form.keterangan' => 'nullable|string',
        ]);

        if ($this->editingId) {
            FinanceMasterKontrakan::whereKey($this->editingId)->update(array_merge($this->form, [
                'toko_id' => $this->tokoId,
            ]));
        } else {
            FinanceMasterKontrakan::create(array_merge($this->form, [
                'toko_id' => $this->tokoId,
            ]));
        }

        $this->showModal = false;
        $this->dispatch('notify', message: 'Kontrakan disimpan.');
    }

}
