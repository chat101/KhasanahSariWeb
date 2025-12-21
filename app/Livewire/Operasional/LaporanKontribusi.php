<?php

namespace App\Livewire\Operasional;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\MasterToko;

class LaporanKontribusi extends Component
{
    public array $tokosUser = [];
    public string $tokosUserNames = '';

    public function mount()
    {
        $user = Auth::user();

        $tokos = MasterToko::query()
            ->forUser($user)
            ->orderBy('nmtoko')
            ->get(['id','nmtoko','api_name','api_id','produksi_sendiri']);

        $this->tokosUser = $tokos->toArray();
        $this->tokosUserNames = $tokos->pluck('nmtoko')->implode(', ');
    }

    public function render()
    {
        return view('livewire.operasional.laporan-kontribusi');
    }
}
