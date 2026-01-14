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

        $formal = null;
        if (!empty($row['kelas_formal'])) {
            $formal = ClassGroup::where('nama_kelas', trim($row['kelas_formal']))
                ->where('jenis_kelas', 'formal')
                ->where('academic_year_id', $activeYearId)
                ->first();

            if (!$formal) {
                $errors[] = "Kelas formal '{$row['kelas_formal']}' tidak ditemukan untuk tahun ajaran aktif.";
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

        $tambahan = null;
        if (!empty($row['kelas_tambahan'])) {
            $tambahan = ClassGroup::where('nama_kelas', trim($row['kelas_tambahan']))
                ->where('jenis_kelas', 'tambahan')
                ->where('academic_year_id', $activeYearId)
                ->first();

            if (!$tambahan) {
                $errors[] = "Kelas tambahan '{$row['kelas_tambahan']}' tidak ditemukan untuk tahun ajaran aktif.";
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

        if ($formal) {
            $student->classGroups()->attach($formal->id, [
                'academic_year_id' => $activeYearId,
            ]);
        }

        if ($muadalah) {
            $student->classGroups()->attach($muadalah->id, [
                'academic_year_id' => $activeYearId,
            ]);
        }

        if ($tambahan) {
            $student->classGroups()->attach($tambahan->id, [
                'academic_year_id' => $activeYearId,
            ]);
        }

        return $student;
    }
}
