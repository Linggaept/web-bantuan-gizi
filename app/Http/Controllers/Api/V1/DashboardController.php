<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalLansia = Lansia::count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderBy('rw')
            ->get()
            ->map(fn ($row) => ['rw' => $row->rw, 'total' => $row->total]);

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
        })->map(fn ($group) => $group->count());

        $kondisiKesehatan = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->map(fn ($row) => ['kondisi' => $row->hasil_periksa, 'total' => $row->total]);

        $totalPenerimaBantuan = BantuanGizi::where('status_penerima', 'penerima')->count();

        return response()->json([
            'data' => [
                'total_lansia' => $totalLansia,
                'per_rw' => $perRw,
                'distribusi_usia' => $distribusiUsia,
                'kondisi_kesehatan' => $kondisiKesehatan,
                'total_penerima_bantuan' => $totalPenerimaBantuan,
            ],
        ]);
    }
}
