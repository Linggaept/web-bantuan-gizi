<?php

namespace Database\Factories;

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PemeriksaanKesehatan>
 */
class PemeriksaanKesehatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'tanggal_periksa' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'berat_badan' => fake()->randomFloat(2, 40, 80),
            'tekanan_darah' => fake()->numerify('###/##'),
            'hasil_periksa' => fake()->randomElement(['baik', 'sedang', 'buruk']),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
