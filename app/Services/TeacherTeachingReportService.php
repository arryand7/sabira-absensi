<?php

namespace App\Services;

use App\Models\ScheduleSession;
use App\Models\User;

class TeacherTeachingReportService
{
    /**
     * Get teaching execution summary for a teacher.
     */
    public function getTeacherSummary(int $teacherUserId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = ScheduleSession::where(function ($q) use ($teacherUserId) {
            $q->where('scheduled_teacher_id', $teacherUserId)
                ->orWhere('actual_teacher_id', $teacherUserId);
        });

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $sessions = $query->get();

        $totalScheduled = $sessions->where('scheduled_teacher_id', $teacherUserId)->count();
        $totalTaught = $sessions->where('actual_teacher_id', $teacherUserId)->count();
        $substituteTaught = $sessions->where('actual_teacher_id', $teacherUserId)
            ->where('scheduled_teacher_id', '!=', $teacherUserId)
            ->count();

        $insideGeofence = $sessions->where('actual_teacher_id', $teacherUserId)
            ->where('location_validation_status', 'inside_geofence')
            ->count();

        $outsideGeofence = $sessions->where('actual_teacher_id', $teacherUserId)
            ->where('location_validation_status', 'outside_geofence')
            ->count();

        $geofenceComplianceRate = $totalTaught > 0
            ? round(($insideGeofence / $totalTaught) * 100, 1)
            : 100.0;

        $hasAnomaly = false;
        $anomalyReasons = [];

        if ($outsideGeofence > 0 && ($outsideGeofence / max(1, $totalTaught)) > 0.2) {
            $hasAnomaly = true;
            $anomalyReasons[] = "Persentase mengajar di luar radius lokasi sekolah tinggi ({$outsideGeofence} dari {$totalTaught} sesi).";
        }

        if ($totalScheduled > 0 && ($totalTaught / $totalScheduled) < 0.8) {
            $hasAnomaly = true;
            $anomalyReasons[] = 'Pencapaian jam mengajar di bawah 80% dari jadwal ('.round(($totalTaught / $totalScheduled) * 100, 1).'%).';
        }

        return [
            'teacher_user_id' => $teacherUserId,
            'total_scheduled' => $totalScheduled,
            'total_taught' => $totalTaught,
            'substitute_taught' => $substituteTaught,
            'inside_geofence' => $insideGeofence,
            'outside_geofence' => $outsideGeofence,
            'geofence_compliance_rate' => $geofenceComplianceRate,
            'has_anomaly' => $hasAnomaly,
            'anomaly_reasons' => $anomalyReasons,
        ];
    }

    /**
     * Get all teachers with teaching anomalies.
     */
    public function getTeachingAnomalies(?string $startDate = null, ?string $endDate = null): array
    {
        $teachers = User::where('role', 'guru')->get();
        $anomalies = [];

        foreach ($teachers as $teacher) {
            $summary = $this->getTeacherSummary($teacher->id, $startDate, $endDate);
            if ($summary['has_anomaly']) {
                $summary['teacher'] = $teacher;
                $anomalies[] = $summary;
            }
        }

        return $anomalies;
    }
}
