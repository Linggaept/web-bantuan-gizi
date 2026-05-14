<?php

namespace Database\Factories;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BantuanGizi>
 */
class BantuanGiziFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'skor_ranking' => null,
            'status_penerima' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
