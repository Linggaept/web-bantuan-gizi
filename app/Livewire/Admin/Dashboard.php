<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $totalLansia = Lansia::count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderByDesc('total')
            ->first();

        $kondisiStats = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->keyBy('hasil_periksa');

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

        $lansiaTerbaru = Lansia::with(['pemeriksaan' => fn ($q) => $q->latest('tanggal_periksa')->limit(1)])
            ->latest()
            ->limit(5)
            ->get();

        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        return view('livewire.admin.dashboard', [
            'totalLansia' => $totalLansia,
            'rwTerbanyak' => $perRw,
            'kondisiSehat' => $kondisiStats['baik']->total ?? 0,
            'kondisiSakit' => ($kondisiStats['sedang']->total ?? 0) + ($kondisiStats['buruk']->total ?? 0),
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'lansiaTerbaru' => $lansiaTerbaru,
            'totalPenerima' => $totalPenerima,
        ]);
    }
}
