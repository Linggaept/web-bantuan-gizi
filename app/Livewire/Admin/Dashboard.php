<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Services\PeriodeService;
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

        $kondisiStats = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(\Illuminate\Support\Facades\DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->keyBy('hasil_periksa');

        $lansiaTerbaru = Lansia::with(['pemeriksaan' => fn ($q) => $q->latest('tanggal_periksa')->limit(1)])
            ->latest()
            ->limit(5)
            ->get();

        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        $periodes = PeriodeService::listForYear(now()->year);
        $periodeLabels = collect($periodes)->pluck('label')->toArray();
        $periodeData = collect($periodes)->map(function ($p) {
            return \App\Models\PemeriksaanKesehatan::where('periode_bulan', $p['bulan'])
                ->where('periode_tahun', $p['tahun'])
                ->count();
        })->toArray();

        return view('livewire.admin.dashboard', [
            'totalLansia' => $totalLansia,
            'kondisiSehat' => $kondisiStats['sehat']->total ?? 0,
            'kondisiSakit' => $kondisiStats['sakit']->total ?? 0,
            'periodeLabels' => $periodeLabels,
            'periodeData' => $periodeData,
            'lansiaTerbaru' => $lansiaTerbaru,
            'totalPenerima' => $totalPenerima,
        ]);
    }
}
