<?php

namespace App\Imports;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Subject;
use App\Models\ClassGroup;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleImport implements ToCollection, WithHeadingRow
{
    public $failures = [];
    public $successRows = [];

    protected $activeYear;

    public function __construct()
    {
        $this->activeYear = AcademicYear::where('is_active', true)->first();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $guru = User::where('name', $row['guru'])->first();
            if (!$guru) {
                $this->addFailure($rowNumber, "Guru '{$row['guru']}' tidak ditemukan.");
                continue;
            }

            $subject = Subject::where('nama_mapel', $row['mapel'])->first();
            if (!$subject) {
                $this->addFailure($rowNumber, "Mapel '{$row['mapel']}' tidak ditemukan.");
                continue;
            }

            $class = ClassGroup::where('nama_kelas', $row['kelas'])
                ->where('academic_year_id', $this->activeYear->id)
                ->first();
            if (!$class) {
                $this->addFailure($rowNumber, "Kelas '{$row['kelas']}' tidak ditemukan.");
                continue;
            }

            // Convert jam_mulai & jam_selesai
            $jamMulai = $this->parseExcelTime($row['jam_mulai']);
            $jamSelesai = $this->parseExcelTime($row['jam_selesai']);

            if (!$jamMulai || !$jamSelesai) {
                $this->addFailure($rowNumber, "Format jam tidak valid.");
                continue;
            }

            if ($jamSelesai <= $jamMulai) {
                $this->addFailure($rowNumber, "Jam selesai harus setelah jam mulai.");
                continue;
            }

            // Cek bentrok
            $conflict = Schedule::where('user_id', $guru->id)
                ->where('hari', ucfirst($row['hari']))
                ->where('academic_year_id', $this->activeYear->id)
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                        ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai])
                        ->orWhere(function ($q2) use ($jamMulai, $jamSelesai) {
                            $q2->where('jam_mulai', '<=', $jamMulai)
                                ->where('jam_selesai', '>=', $jamSelesai);
                        });
                })
                ->first();

            if ($conflict) {
                $this->addFailure($rowNumber, "Jadwal bentrok dengan jadwal lain.");
                continue;
            }

            Schedule::create([
                'user_id' => $guru->id,
                'subject_id' => $subject->id,
                'class_group_id' => $class->id,
                'hari' => ucfirst($row['hari']),
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'academic_year_id' => $this->activeYear->id,
            ]);

            $this->successRows[] = "Baris {$rowNumber}: Jadwal untuk {$guru->name} berhasil ditambahkan.";
        }
    }

    protected function addFailure($row, $message)
    {
        $this->failures[] = "Baris {$row}: {$message}";
    }

    protected function parseExcelTime($value)
    {
        // Jika waktu dalam bentuk DateTime object
        if ($value instanceof \DateTime) {
            return $value->format('H:i:s');
        }

        // Jika waktu dalam bentuk float Excel (misal: 0.5)
        if (is_numeric($value)) {
            $time = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $time->format('H:i:s');
        }

        // Jika string seperti "12:00" atau "12:00:00"
        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $value)) {
            // Tambahkan detik jika belum ada
            return strlen($value) === 5 ? $value . ':00' : $value;
        }

        return false;
    }
}
