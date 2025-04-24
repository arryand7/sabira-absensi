<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Hash;

class DummyKaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Budi Santoso', 'email' => 'budi@gmail.com', 'divisi' => 'Guru'],
            ['name' => 'Siti Aminah', 'email' => 'siti@gmail.com', 'divisi' => 'Administrasi'],
            ['name' => 'Rizky Hakim', 'email' => 'rizky@gmail.com', 'divisi' => 'Kebersihan'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gmail.com', 'divisi' => 'Keamanan'],
            ['name' => 'Andi Wijaya', 'email' => 'andi@gmail.com', 'divisi' => 'Guru'],
        ];

        foreach ($data as $item) {
            $user = User::create([
                'name' => $item['name'],
                'email' => $item['email'],
                'password' => Hash::make('password'), // password default
                'role' => 'karyawan',
            ]);

            Karyawan::create([
                'user_id' => $user->id,
                'divisi' => $item['divisi'],
                'nama_lengkap' => $item['name'],
            ]);
        }
    }
}
