<?php

namespace Database\Seeders;

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Database\Seeder;

class PemeriksaanKesehatanSeeder extends Seeder
{
    public function run(): void
    {
        $periodes = [[1, 2026], [4, 2026], [7, 2025], [10, 2025]];

        Lansia::all()->each(function (Lansia $lansia) use ($periodes) {
            // seed up to 4 quarterly periods per lansia
            $selectedPeriodes = collect($periodes)->shuffle()->take(rand(1, 4));

            foreach ($selectedPeriodes as [$bulan, $tahun]) {
                $exists = PemeriksaanKesehatan::where('lansia_id', $lansia->lansia_id)
                    ->where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->exists();

                if (! $exists) {
                    PemeriksaanKesehatan::factory()->create([
                        'lansia_id' => $lansia->lansia_id,
                        'periode_bulan' => $bulan,
                        'periode_tahun' => $tahun,
                    ]);
                }
            }
        });
    }
}
