<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassGroup;

class ClassGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ClassGroup::insert([
            ['nama_kelas' => '7A', 'jenis_kelas' => 'akademik', 'tahun_ajaran' => '2024/2025'],
            ['nama_kelas' => 'Muadalah 1', 'jenis_kelas' => 'muadalah', 'tahun_ajaran' => '2024/2025'],
        ]);
    }
}
