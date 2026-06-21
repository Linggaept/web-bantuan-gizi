<?php

namespace Database\Seeders;

use App\Models\Lansia;
use App\Models\User;
use Illuminate\Database\Seeder;

class LansiaSeeder extends Seeder
{
    public function run(): void
    {
        $operators = User::where('role', 'operator')->get();

        $rwConfig = [
            '01' => ['kader_rw01', 15],
            '02' => ['kader_rw02', 18],
            '03' => ['kader_rw03', 12],
            '04' => ['kader_rw01', 10],
            '05' => ['kader_rw02', 14],
            '06' => ['kader_rw03', 11],
            '07' => ['kader_rw01', 8],
        ];

        foreach ($rwConfig as $rw => [$username, $count]) {
            $operator = $operators->firstWhere('username', $username);

            Lansia::factory($count)->create([
                'rw' => $rw,
                'created_by' => $operator->id,
            ]);
        }
    }
}
