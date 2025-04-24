<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Divisi;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisis = ['Guru', 'Kepala Sekolah', 'Administrasi'];

        foreach ($divisis as $nama) {
            Divisi::create(['nama' => $nama]);
        }
    }
}
