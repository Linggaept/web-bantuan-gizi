<?php

namespace Database\Factories;

use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pendataan>
 */
class PendataanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'user_id' => User::factory()->operator(),
            'status_verifikasi' => 'menunggu',
            'verified_by' => null,
            'verified_at' => null,
            'tanggal_input' => now()->toDateString(),
        ];
    }

    public function terverifikasi(): static
    {
        return $this->state(function () {
            $admin = User::factory()->admin()->create();

            return [
                'status_verifikasi' => 'terverifikasi',
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ];
        });
    }
}
