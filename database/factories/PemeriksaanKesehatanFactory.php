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
            'tekanan_darah' => fake()->randomElement(['120/80', '130/85', '110/70', '140/90', '160/95', '90/60', '125/82']),
            'hasil_periksa' => fake()->randomElement(['sehat', 'sakit']),
            'catatan' => fake()->optional()->sentence(),
            'periode_bulan' => fake()->randomElement([1, 4, 7, 10]),
            'periode_tahun' => fake()->numberBetween(2024, 2026),
        ];
    }
}
