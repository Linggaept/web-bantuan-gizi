<?php

namespace App\Services;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\Pendataan;

class RankingService
{
    /**
     * @var array<string, int>
     */
    private array $healthScoreMap = [
        'buruk' => 10,
        'sedang' => 6,
        'baik' => 3,
    ];

    /**
     * @return array{total_diproses: int, total_penerima: int}
     */
    public function rank(int $periodeBulan, int $periodeTahun, int $kuota): array
    {
        $lansiaIds = Pendataan::where('status_verifikasi', 'terverifikasi')
            ->pluck('lansia_id');

        $ranked = Lansia::whereIn('lansia_id', $lansiaIds)
            ->get()
            ->map(function (Lansia $lansia) use ($periodeBulan, $periodeTahun) {
                $latestPeriksa = PemeriksaanKesehatan::where('lansia_id', $lansia->lansia_id)
                    ->orderByDesc('tanggal_periksa')
                    ->orderByDesc('pemeriksaan_id')
                    ->first();

                $hasilPeriksa = $latestPeriksa?->hasil_periksa ?? 'sedang';
                $healthScore = $this->healthScoreMap[$hasilPeriksa] ?? 6;

                $usia = $lansia->usia;
                $skor = ($usia / 100 * 0.6) + ($healthScore / 10 * 0.4);

                return [
                    'lansia_id' => $lansia->lansia_id,
                    'skor_ranking' => round($skor, 4),
                    'periode_bulan' => $periodeBulan,
                    'periode_tahun' => $periodeTahun,
                ];
            })
            ->sortByDesc('skor_ranking')
            ->values();

        $penerima = $ranked->take($kuota)->pluck('lansia_id');

        foreach ($ranked as $item) {
            BantuanGizi::updateOrCreate(
                [
                    'lansia_id' => $item['lansia_id'],
                    'periode_bulan' => $periodeBulan,
                    'periode_tahun' => $periodeTahun,
                ],
                [
                    'skor_ranking' => $item['skor_ranking'],
                    'status_penerima' => $penerima->contains($item['lansia_id']) ? 'penerima' : 'tidak_penerima',
                ]
            );
        }

        return [
            'total_diproses' => $ranked->count(),
            'total_penerima' => min($kuota, $ranked->count()),
        ];
    }
}
