<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\MasterToko;
use Livewire\WithPagination;

class RekeningTelur extends Component
{
    use WithPagination;
    public bool $showModal = false;
    public string $search = '';
    protected $paginationTheme = 'tailwind';
    protected $queryString = [
        'search' => ['except' => ''],
        'page'   => ['except' => 1],
    ];
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function render()
    {
        $s = trim($this->search);

        $telur = MasterToko::with([
            'eggs' => function ($q) use ($s) {
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
                  ->orWhereHas('eggs', function ($rq) use ($like) {
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

        return view('livewire.finance.rekening-telur', compact('telur', 'allTokos'));
    }

}
