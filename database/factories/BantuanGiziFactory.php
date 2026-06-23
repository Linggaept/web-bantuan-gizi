<?php

namespace Database\Factories;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Services\PeriodeService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BantuanGizi>
 */
class BantuanGiziFactory extends Factory
{
    public function definition(): array
    {
        $periode = PeriodeService::current();

        return [
            'lansia_id' => Lansia::factory(),
            'periode_bulan' => $periode['bulan'],
            'periode_tahun' => $periode['tahun'],
            'skor_ranking' => null,
            'status_penerima' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
