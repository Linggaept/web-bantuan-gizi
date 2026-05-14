<?php

namespace Database\Factories;

use App\Models\Lansia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lansia>
 */
class LansiaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('################'),
            'nama' => fake()->name(),
            'tanggal_lahir' => fake()->dateTimeBetween('-90 years', '-60 years')->format('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'alamat' => fake()->address(),
            'rt' => fake()->numerify('0#'),
            'rw' => fake()->numerify('0#'),
            'foto_ktp' => null,
            'created_by' => User::factory(),
        ];
    }
}
