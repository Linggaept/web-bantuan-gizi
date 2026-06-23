<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.lurah')]
#[Title('Dashboard Lurah')]
class Dashboard extends Component
{
    public function render()
    {
        $periode = \App\Services\PeriodeService::current();

        $bantuanAktif = \App\Models\BantuanGizi::with('lansia')
            ->where('periode_bulan', $periode['bulan'])
            ->where('periode_tahun', $periode['tahun'])
            ->where('status_penerima', 'penerima')
            ->get();

        $totalPenerima = $bantuanAktif->count();
        $lansiaPenerima = $bantuanAktif->map(fn ($b) => $b->lansia)->filter();
        $periodeLabel = \App\Services\PeriodeService::label($periode['bulan'], $periode['tahun']);

        return view('livewire.lurah.dashboard', compact('totalPenerima', 'lansiaPenerima', 'periodeLabel'));
    }
}
