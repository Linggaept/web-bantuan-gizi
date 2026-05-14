<?php

namespace Database\Seeders;

use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PendataanSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $operators = User::where('role', 'operator')->get();

        Lansia::all()->each(function (Lansia $lansia) use ($admin, $operators) {
            $operator = $operators->random();

            // 80% terverifikasi, 10% ditolak, 10% menunggu
            $rand = rand(1, 10);
            $status = match (true) {
                $rand <= 8 => 'terverifikasi',
                $rand === 9 => 'ditolak',
                default => 'menunggu',
            };

            Pendataan::create([
                'lansia_id' => $lansia->lansia_id,
                'user_id' => $operator->id,
                'status_verifikasi' => $status,
                'verified_by' => $status !== 'menunggu' ? $admin->id : null,
                'verified_at' => $status !== 'menunggu' ? now()->subDays(rand(1, 30)) : null,
                'tanggal_input' => now()->subDays(rand(1, 60))->toDateString(),
            ]);
        });
    }
}
