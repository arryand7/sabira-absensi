<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassGroup;
use App\Models\Student;

class ClassStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $students = Student::all();
        $akademik = ClassGroup::where('nama_kelas', '7A')->first();
        $muadalah = ClassGroup::where('nama_kelas', 'Muadalah 1')->first();

        foreach ($students as $student) {
            $student->classGroups()->attach([$akademik->id, $muadalah->id]);
        }
    }
}
