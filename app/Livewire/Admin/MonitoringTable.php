<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\Pendataan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Monitoring Operasional')]
class MonitoringTable extends Component
{
    public function render()
    {
        $totalInputHariIni = Pendataan::whereDate('created_at', today())->count();
        $totalPending = Pendataan::where('status_verifikasi', 'menunggu')->count();
        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        $logInput = Pendataan::with(['user', 'lansia'])
            ->latest()
            ->limit(20)
            ->get();

        $distribusiPerRw = Lansia::select(
            'lansia.rw',
            DB::raw('count(distinct lansia.lansia_id) as total_lansia'),
            DB::raw('sum(case when bg.status_penerima = "penerima" then 1 else 0 end) as total_penerima'),
            DB::raw('sum(case when bg.status_penerima = "tidak_penerima" then 1 else 0 end) as tidak_penerima')
        )
            ->leftJoin('bantuan_gizi as bg', 'lansia.lansia_id', '=', 'bg.lansia_id')
            ->groupBy('lansia.rw')
            ->orderBy('lansia.rw')
            ->get();

        return view('livewire.admin.monitoring-table', compact(
            'totalInputHariIni',
            'totalPending',
            'totalPenerima',
            'logInput',
            'distribusiPerRw'
        ));
    }
}
