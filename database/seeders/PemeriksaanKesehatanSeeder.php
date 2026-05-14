<?php

namespace Database\Seeders;

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Database\Seeder;

class PemeriksaanKesehatanSeeder extends Seeder
{
    public function run(): void
    {
        Lansia::all()->each(function (Lansia $lansia) {
            // 1-3 riwayat pemeriksaan per lansia
            PemeriksaanKesehatan::factory(rand(1, 3))->create([
                'lansia_id' => $lansia->lansia_id,
            ]);
        });
    }
}
