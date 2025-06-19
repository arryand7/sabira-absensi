<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\AcademicYear;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $errors = [];

        $required = ['nama', 'nis', 'jenis_kelamin'];
        foreach ($required as $field) {
            if (!isset($row[$field]) || trim($row[$field]) === '') {
                $errors[] = "Kolom '$field' wajib diisi.";
            }
        }

        $gender = strtoupper(trim($row['jenis_kelamin'] ?? ''));
        if (!in_array($gender, ['L', 'P'])) {
            $errors[] = "Jenis kelamin harus 'L' atau 'P'.";
        }

        $activeYearId = AcademicYear::where('is_active', true)->value('id');
        if (!$activeYearId) {
            $errors[] = "Tahun ajaran aktif tidak ditemukan.";
        }

        $akademik = null;
        if (!empty($row['kelas_akademik'])) {
            $akademik = ClassGroup::where('nama_kelas', trim($row['kelas_akademik']))
                ->where('jenis_kelas', 'akademik')
                ->where('academic_year_id', $activeYearId)
                ->first();

            if (!$akademik) {
                $errors[] = "Kelas akademik '{$row['kelas_akademik']}' tidak ditemukan untuk tahun ajaran aktif.";
            }
        }

        $muadalah = null;
        if (!empty($row['kelas_muadalah'])) {
            $muadalah = ClassGroup::where('nama_kelas', trim($row['kelas_muadalah']))
                ->where('jenis_kelas', 'muadalah')
                ->where('academic_year_id', $activeYearId)
                ->first();

            if (!$muadalah) {
                $errors[] = "Kelas muadalah '{$row['kelas_muadalah']}' tidak ditemukan untuk tahun ajaran aktif.";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages([
                'row' => $errors
            ]);
        }

        $student = new Student([
            'nama_lengkap' => $row['nama'],
            'nis' => $row['nis'],
            'jenis_kelamin' => $gender,
        ]);
        $student->save();

        if ($akademik) {
            $student->classGroups()->attach($akademik->id, [
                'academic_year_id' => $activeYearId,
            ]);
        }

        if ($muadalah) {
            $student->classGroups()->attach($muadalah->id, [
                'academic_year_id' => $activeYearId,
            ]);
        }

        return $student;
    }
}
