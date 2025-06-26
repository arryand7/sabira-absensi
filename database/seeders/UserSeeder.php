<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Sabira',
            'email' => 'admin@sabira.test',
            'password' => Hash::make('sabira123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }
}
