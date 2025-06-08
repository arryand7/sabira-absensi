<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Student::insert([
            ['nama_lengkap' => 'Ahmad Syafiq', 'nis' => '123456', 'jenis_kelamin' => 'L'],
            ['nama_lengkap' => 'Fatimah Zahra', 'nis' => '123457', 'jenis_kelamin' => 'P'],
            ['nama_lengkap' => 'Hassan Ali', 'nis' => '123458', 'jenis_kelamin' => 'L'],
            ['nama_lengkap' => 'Aisyah Aminah', 'nis' => '123459', 'jenis_kelamin' => 'P'],
        ]);
    }
}
