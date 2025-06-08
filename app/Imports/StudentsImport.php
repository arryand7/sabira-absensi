<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassGroup;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Cari class group akademik
        $akademik = ClassGroup::where('nama_kelas', $row['kelas_akademik'])
            ->where('jenis_kelas', 'akademik')
            ->first();

        // Cari class group muadalah
        $muadalah = ClassGroup::where('nama_kelas', $row['kelas_muadalah'])
            ->where('jenis_kelas', 'muadalah')
            ->first();

        // Buat student baru
        $student = new Student([
            'nama_lengkap' => $row['nama'],
            'nis' => $row['nis'],
            'jenis_kelamin' => $row['jenis_kelamin'],
        ]);
        $student->save(); // harus disimpan dulu sebelum attach relasi

        // Attach ke class groups kalau ada
        if ($akademik) {
            $student->classGroups()->attach($akademik->id);
        }

        if ($muadalah) {
            $student->classGroups()->attach($muadalah->id);
        }

        return $student;
    }
}
