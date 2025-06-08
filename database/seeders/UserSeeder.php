<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Ustadz Ahmad',
            'email' => 'guru1@example.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        User::create([
            'name' => 'Ustadzah Fatimah',
            'email' => 'guru2@example.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
    }
}
