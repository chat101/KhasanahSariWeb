<?php

namespace App\Livewire\Operasional;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterToko;

class LaporanKontribusi extends Component
{
    public array $tokosUser = [];
    public string $tokosUserNames = '';
    public string $tab = 'target';

    public function mount()
    {
        $user = Auth::user();

       $tokos = MasterToko::query()
    ->forUser($user)
    ->orderBy('nmtoko')
    ->get(['id','nmtoko']);

$this->tokosUser = $tokos->map(fn($t) => ['id' => $t->id])->all();
$this->tokosUserNames = $tokos->pluck('nmtoko')->implode(', ');
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.operasional.laporan-kontribusi');
    }
}
