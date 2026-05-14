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

        $rwList = ['01', '02', '03'];

        foreach ($rwList as $rw) {
            $operator = $operators->firstWhere('username', "kader_rw{$rw}");

            Lansia::factory(10)->create([
                'rw' => $rw,
                'created_by' => $operator->id,
            ]);
        }
    }
}
