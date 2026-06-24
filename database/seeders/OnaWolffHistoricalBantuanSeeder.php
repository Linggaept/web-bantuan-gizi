<?php

namespace Database\Seeders;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Services\PeriodeService;
use Illuminate\Database\Seeder;

class OnaWolffHistoricalBantuanSeeder extends Seeder
{
    public function run(): void
    {
        $lansia = Lansia::where('nama', 'Ms. Ona Wolff')->firstOrFail();

        $periods = [
            ['bulan' => 4, 'tahun' => 2025],
            ['bulan' => 7, 'tahun' => 2025],
            ['bulan' => 10, 'tahun' => 2025],
            ['bulan' => 1, 'tahun' => 2026],
        ];

        foreach ($periods as $p) {
            BantuanGizi::updateOrCreate(
                [
                    'lansia_id' => $lansia->lansia_id,
                    'periode_bulan' => $p['bulan'],
                    'periode_tahun' => $p['tahun'],
                ],
                [
                    'skor_ranking' => 0.934,
                    'status_penerima' => 'penerima',
                    'approved_at' => now(),
                ]
            );

            $label = PeriodeService::label($p['bulan'], $p['tahun']);
            $this->command->info("Seeded: {$lansia->nama} — {$label}");
        }
    }
}
