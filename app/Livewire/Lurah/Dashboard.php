<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.lurah')]
#[Title('Dashboard Lurah')]
class Dashboard extends Component
{
    public function render()
    {
        $totalLansia = Lansia::count();

        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        $pendingApproval = BantuanGizi::where('status_penerima', 'penerima')
            ->whereNull('approved_at')
            ->count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderByDesc('total')
            ->first();

        $distribusiUsia = Lansia::get()->groupBy(function (Lansia $l) {
            $usia = $l->usia;
            if ($usia < 65) {
                return '60-64';
            } elseif ($usia < 70) {
                return '65-69';
            } elseif ($usia < 75) {
                return '70-74';
            } elseif ($usia < 80) {
                return '75-79';
            } else {
                return '80+';
            }
        })->map(fn ($g) => $g->count());

        $chartLabels = ['60-64', '65-69', '70-74', '75-79', '80+'];
        $chartData = collect($chartLabels)->map(fn ($label) => $distribusiUsia[$label] ?? 0)->values();

        $kondisiStats = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->keyBy('hasil_periksa');

        $distribusiPerRw = Lansia::select(
            'lansia.rw',
            DB::raw('count(distinct lansia.lansia_id) as total_lansia'),
            DB::raw('sum(case when bg.status_penerima = "penerima" then 1 else 0 end) as total_penerima'),
            DB::raw('sum(case when bg.status_penerima = "tidak_penerima" then 1 else 0 end) as tidak_penerima'),
            DB::raw('sum(case when bg.approved_at is not null then 1 else 0 end) as total_approved')
        )
            ->leftJoin('bantuan_gizi as bg', 'lansia.lansia_id', '=', 'bg.lansia_id')
            ->groupBy('lansia.rw')
            ->orderBy('lansia.rw')
            ->get();

        return view('livewire.lurah.dashboard', [
            'totalLansia' => $totalLansia,
            'totalPenerima' => $totalPenerima,
            'pendingApproval' => $pendingApproval,
            'rwTerbanyak' => $perRw,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'kondisiSehat' => $kondisiStats['baik']->total ?? 0,
            'kondisiSakit' => ($kondisiStats['sedang']->total ?? 0) + ($kondisiStats['buruk']->total ?? 0),
            'distribusiPerRw' => $distribusiPerRw,
        ]);
    }
}
