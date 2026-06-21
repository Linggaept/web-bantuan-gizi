<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin Kelurahan
        User::create([
            'name' => 'Admin Kelurahan',
            'username' => 'admin',
            'email' => 'admin@kelurahan.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Lurah
        User::create([
            'name' => 'Kelurahan Banjaran',
            'username' => 'lurah',
            'email' => 'lurah@kelurahan.id',
            'password' => Hash::make('password'),
            'role' => 'lurah',
            'is_active' => true,
        ]);

        // Operator / Kader RW
        $operators = [
            ['name' => 'Kader RW 01', 'username' => 'kader_rw01', 'email' => 'kader.rw01@kelurahan.id', 'rw' => '01'],
            ['name' => 'Kader RW 02', 'username' => 'kader_rw02', 'email' => 'kader.rw02@kelurahan.id', 'rw' => '02'],
            ['name' => 'Kader RW 03', 'username' => 'kader_rw03', 'email' => 'kader.rw03@kelurahan.id', 'rw' => '03'],
        ];

        foreach ($operators as $op) {
            User::create([
                'name' => $op['name'],
                'username' => $op['username'],
                'email' => $op['email'],
                'password' => Hash::make('password'),
                'role' => 'operator',
                'is_active' => true,
            ]);
        }
    }
}
