<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\TeacherTeachingAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmitTeachingSessionService
{
    public function __construct(
        protected GeofenceService $geofenceService
    ) {}

    public function execute(array $data): ScheduleSession
    {
        $schedule = Schedule::with(['subject', 'classGroup'])->findOrFail($data['schedule_id']);
        // Identitas guru aktual selalu berasal dari sesi login, bukan payload browser.
        $actualTeacherId = auth()->id();
        $scheduledTeacherId = $schedule->user_id;

        $date = $data['date'] ?? now()->toDateString();
        $meetingNo = $data['pertemuan'];
        $startTime = $data['jam_mulai'] ?? $schedule->jam_mulai;
        $endTime = $data['jam_selesai'] ?? $schedule->jam_selesai;
        $materi = $data['materi'] ?? null;
        $classroomCondition = $data['classroom_condition'] ?? null;
        $teacherNotes = $data['teacher_notes'] ?? null;

        // Validate Geofence coordinates
        $geoResult = $this->geofenceService->validateLocation(
            $data['latitude'] ?? null,
            $data['longitude'] ?? null,
            $data['location_accuracy'] ?? null
        );

        $isSubstitute = ($scheduledTeacherId !== $actualTeacherId);
        $teachingAttendanceStatus = $isSubstitute ? 'substitute' : 'hadir';
        $teachingAttendanceSource = $isSubstitute ? 'substitute_teacher' : 'journal_submission';

        return DB::transaction(function () use (
            $schedule,
            $scheduledTeacherId,
            $actualTeacherId,
            $date,
            $meetingNo,
            $startTime,
            $endTime,
            $materi,
            $classroomCondition,
            $teacherNotes,
            $data,
            $geoResult,
            $teachingAttendanceStatus,
            $teachingAttendanceSource
        ) {
            // 1. Create or update ScheduleSession
            $session = ScheduleSession::updateOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'date' => $date,
                ],
                [
                    'subject_id' => $schedule->subject_id,
                    'class_group_id' => $schedule->class_group_id,
                    'academic_year_id' => $schedule->academic_year_id,
                    'meeting_no' => $meetingNo,
                    'scheduled_teacher_id' => $scheduledTeacherId,
                    'actual_teacher_id' => $actualTeacherId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'classroom_condition' => $classroomCondition,
                    'teacher_notes' => $teacherNotes,
                    'draft_payload' => null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'location_accuracy' => $data['location_accuracy'] ?? null,
                    'distance_from_location' => $geoResult['distance_meters'],
                    'location_validation_status' => $geoResult['validation_status'],
                    'submitted_at' => now(),
                    'completed_at' => now(),
                    'created_by' => auth()->id(),
                    'status' => 'completed',
                ]
            );

            // 2. Save Student Attendance
            if (isset($data['attendance']) && is_array($data['attendance'])) {
                foreach ($data['attendance'] as $studentId => $status) {
                    Attendance::updateOrCreate(
                        [
                            'schedule_session_id' => $session->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'schedule_id' => $schedule->id,
                            'tanggal' => $date,
                            'pertemuan' => $meetingNo,
                            'jam_mulai' => $startTime,
                            'jam_selesai' => $endTime,
                            'materi' => $materi,
                            'status' => $status,
                        ]
                    );
                }
            }

            // 3. Create Teacher Teaching Attendance Record
            TeacherTeachingAttendance::updateOrCreate(
                [
                    'schedule_session_id' => $session->id,
                    'teacher_id' => $actualTeacherId,
                ],
                [
                    'schedule_id' => $schedule->id,
                    'attendance_date' => $date,
                    'check_in_at' => now(),
                    'status' => $teachingAttendanceStatus,
                    'source' => $teachingAttendanceSource,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'location_accuracy' => $data['location_accuracy'] ?? null,
                    'location_validation_status' => $geoResult['validation_status'],
                ]
            );

            Log::info('Teaching session submitted successfully', [
                'session_id' => $session->id,
                'schedule_id' => $schedule->id,
                'actual_teacher_id' => $actualTeacherId,
                'location_status' => $geoResult['validation_status'],
            ]);

            return $session;
        });
    }
}
