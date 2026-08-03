<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassGroupStudent;
use App\Models\Student;

class StudentProgressReportService
{
    /**
     * Generate student attendance summary and risk level.
     */
    public function getStudentSummary(int $studentId, ?int $academicYearId = null, ?int $month = null, ?int $year = null): array
    {
        $query = Attendance::where('student_id', $studentId);

        if ($academicYearId) {
            $query->whereHas('schedule', fn ($schedule) => $schedule->where('academic_year_id', $academicYearId));
        }

        if ($month) {
            $query->whereMonth('tanggal', $month);
        }
        if ($year) {
            $query->whereYear('tanggal', $year);
        }

        $attendances = $query->get();

        $totalMeetings = $attendances->count();
        $hadir = $attendances->where('status', 'hadir')->count();
        $sakit = $attendances->where('status', 'sakit')->count();
        $izin = $attendances->where('status', 'izin')->count();
        $alpa = $attendances->where('status', 'alpa')->count();
        $terlambat = $attendances->where('status', 'terlambat')->count();
        $dispensasi = $attendances->where('status', 'dispensasi')->count();

        $attendanceRate = $totalMeetings > 0
            ? round((($hadir + $terlambat + $dispensasi) / $totalMeetings) * 100, 1)
            : 100.0;

        $riskLevel = 'low';
        $riskReasons = [];

        if ($alpa >= 3) {
            $riskLevel = 'high';
            $riskReasons[] = "Alpa sebanyak {$alpa} kali.";
        } elseif ($attendanceRate < 80.0 && $totalMeetings >= 5) {
            $riskLevel = 'high';
            $riskReasons[] = "Tingkat kehadiran di bawah 80% ({$attendanceRate}%).";
        } elseif ($alpa >= 1 || ($sakit + $izin) >= 5) {
            $riskLevel = 'medium';
            if ($alpa >= 1) {
                $riskReasons[] = "Terdapat {$alpa} kali Alpa.";
            }
            if (($sakit + $izin) >= 5) {
                $riskReasons[] = 'Izin/Sakit sebanyak '.($sakit + $izin).' kali.';
            }
        }

        return [
            'student_id' => $studentId,
            'total_meetings' => $totalMeetings,
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpa' => $alpa,
            'terlambat' => $terlambat,
            'dispensasi' => $dispensasi,
            'attendance_rate' => $attendanceRate,
            'risk_level' => $riskLevel,
            'risk_reasons' => $riskReasons,
        ];
    }

    /**
     * Get high risk students for executive monitoring dashboard.
     */
    public function getAtRiskStudents(?int $classGroupId = null, ?int $academicYearId = null): array
    {
        $academicYearId ??= AcademicYear::where('is_active', true)->value('id');
        $studentsQuery = Student::query()
            ->when($academicYearId, fn ($query) => $query->whereHas('classGroups', function ($group) use ($academicYearId) {
                $group->where('class_group_student.academic_year_id', $academicYearId)
                    ->where('class_group_student.status', 'active');
            }));

        if ($classGroupId) {
            $studentIds = ClassGroupStudent::where('class_group_id', $classGroupId)
                ->where('status', 'active')
                ->when($academicYearId, fn ($query) => $query->where('academic_year_id', $academicYearId))
                ->pluck('student_id');
            $studentsQuery->whereIn('id', $studentIds);
        }

        $students = $studentsQuery->get();
        $atRisk = [];

        foreach ($students as $student) {
            $summary = $this->getStudentSummary($student->id, $academicYearId);
            if ($summary['risk_level'] !== 'low') {
                $summary['student'] = $student;
                $atRisk[] = $summary;
            }
        }

        usort($atRisk, function ($a, $b) {
            return ($b['risk_level'] === 'high' ? 2 : 1) <=> ($a['risk_level'] === 'high' ? 2 : 1);
        });

        return $atRisk;
    }
}
