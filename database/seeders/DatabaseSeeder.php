<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LansiaSeeder::class,
            PemeriksaanKesehatanSeeder::class,
            PendataanSeeder::class,
            BantuanGiziSeeder::class,
        ]);
    }
}
