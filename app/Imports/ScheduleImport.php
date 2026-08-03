<?php

namespace App\Imports;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleConflictService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ScheduleImport implements ToCollection, WithHeadingRow
{
    public $failures = [];

    public $successRows = [];

    protected $activeYear;

    public function __construct(private readonly ScheduleConflictService $conflictService)
    {
        $this->activeYear = AcademicYear::where('is_active', true)->first();
    }

    public function collection(Collection $rows)
    {
        $requiredColumns = ['guru', 'mapel', 'kelas', 'hari', 'jam_mulai', 'jam_selesai'];

        // Cek kolom hanya sekali di baris pertama
        $firstRow = $rows->first();
        if (! $firstRow || collect($requiredColumns)->diff(array_keys($firstRow->toArray()))->isNotEmpty()) {
            $this->failures[] = 'Format file tidak sesuai. Pastikan kolom: guru, mapel, kelas, hari, jam_mulai, jam_selesai tersedia.';

            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $guru = User::where('name', $row['guru'])->first();
            if (! $guru) {
                $this->addFailure($rowNumber, "Guru '{$row['guru']}' tidak ditemukan.");

                continue;
            }

            $subject = Subject::where('nama_mapel', $row['mapel'])->first();
            if (! $subject) {
                $this->addFailure($rowNumber, "Mapel '{$row['mapel']}' tidak ditemukan.");

                continue;
            }

            $class = ClassGroup::where('nama_kelas', $row['kelas'])
                ->where('academic_year_id', $this->activeYear->id)
                ->first();
            if (! $class) {
                $this->addFailure($rowNumber, "Kelas '{$row['kelas']}' tidak ditemukan.");

                continue;
            }

            // Convert jam_mulai & jam_selesai
            $jamMulai = $this->parseExcelTime($row['jam_mulai']);
            $jamSelesai = $this->parseExcelTime($row['jam_selesai']);

            if (! $jamMulai || ! $jamSelesai) {
                $this->addFailure($rowNumber, 'Format jam tidak valid.');

                continue;
            }

            if ($jamSelesai <= $jamMulai) {
                $this->addFailure($rowNumber, 'Jam selesai harus setelah jam mulai.');

                continue;
            }

            $schedule = Schedule::create([
                'user_id' => $guru->id,
                'subject_id' => $subject->id,
                'class_group_id' => $class->id,
                'hari' => ucfirst($row['hari']),
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'academic_year_id' => $this->activeYear->id,
                'semester' => AcademicYear::currentSemester(),
            ]);

            $conflictCount = $this->conflictService->refreshFor($schedule)->count();

            $suffix = $conflictCount > 0 ? " ({$conflictCount} benturan perlu verifikasi admin)" : '';
            $this->successRows[] = "Baris {$rowNumber}: Jadwal untuk {$guru->name} berhasil ditambahkan{$suffix}.";
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
            $time = Date::excelToDateTimeObject($value);

            return $time->format('H:i:s');
        }

        // Jika string seperti "12:00" atau "12:00:00"
        if (preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $value)) {
            // Tambahkan detik jika belum ada
            return strlen($value) === 5 ? $value.':00' : $value;
        }

        return false;
    }
}
