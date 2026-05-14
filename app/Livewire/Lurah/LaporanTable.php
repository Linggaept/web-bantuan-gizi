<?php

namespace App\Livewire\Lurah;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.lurah')]
#[Title('Laporan')]
class LaporanTable extends Component
{
    public function render()
    {
        return view('livewire.lurah.laporan-table');
    }
}
