<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $groups = DB::table('student_attendance')
            ->select('schedule_id', 'pertemuan', 'tanggal', 'jam_mulai', 'jam_selesai')
            ->groupBy('schedule_id', 'pertemuan', 'tanggal', 'jam_mulai', 'jam_selesai')
            ->get();

        foreach ($groups as $group) {
            $schedule = DB::table('schedules')->where('id', $group->schedule_id)->first();

            if (!$schedule) {
                continue;
            }

            $existing = DB::table('schedule_sessions')
                ->where('schedule_id', $group->schedule_id)
                ->where('date', $group->tanggal)
                ->first();

            if ($existing) {
                $sessionId = $existing->id;
            } else {
                $meetingNo = $group->pertemuan;
                if (!is_null($meetingNo)) {
                    $duplicateMeeting = DB::table('schedule_sessions')
                        ->where('class_group_id', $schedule->class_group_id)
                        ->where('subject_id', $schedule->subject_id)
                        ->where('academic_year_id', $schedule->academic_year_id)
                        ->where('meeting_no', $meetingNo)
                        ->exists();

                    if ($duplicateMeeting) {
                        $meetingNo = null;
                    }
                }

                $sessionId = DB::table('schedule_sessions')->insertGetId([
                    'schedule_id' => $group->schedule_id,
                    'subject_id' => $schedule->subject_id,
                    'class_group_id' => $schedule->class_group_id,
                    'academic_year_id' => $schedule->academic_year_id,
                    'date' => $group->tanggal,
                    'start_time' => $group->jam_mulai,
                    'end_time' => $group->jam_selesai,
                    'meeting_no' => $meetingNo,
                    'created_by' => $schedule->user_id,
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $attendanceQuery = DB::table('student_attendance')
                ->where('schedule_id', $group->schedule_id)
                ->where('tanggal', $group->tanggal)
                ->where('jam_mulai', $group->jam_mulai)
                ->where('jam_selesai', $group->jam_selesai);

            if (is_null($group->pertemuan)) {
                $attendanceQuery->whereNull('pertemuan');
            } else {
                $attendanceQuery->where('pertemuan', $group->pertemuan);
            }

            $attendanceRows = $attendanceQuery
                ->select('id', 'student_id', 'schedule_session_id')
                ->orderBy('id')
                ->get()
                ->groupBy('student_id');

            foreach ($attendanceRows as $rows) {
                if ($rows->contains('schedule_session_id', $sessionId)) {
                    continue;
                }

                $studentId = $rows->first()->student_id ?? null;
                if ($studentId) {
                    $alreadyLinked = DB::table('student_attendance')
                        ->where('student_id', $studentId)
                        ->where('schedule_session_id', $sessionId)
                        ->exists();

                    if ($alreadyLinked) {
                        continue;
                    }
                }

                $rowToUpdate = $rows->firstWhere('schedule_session_id', null);
                if (!$rowToUpdate) {
                    continue;
                }

                try {
                    DB::table('student_attendance')
                        ->where('id', $rowToUpdate->id)
                        ->update(['schedule_session_id' => $sessionId]);
                } catch (QueryException $e) {
                    if (($e->errorInfo[1] ?? null) === 1062) {
                        continue;
                    }

                    throw $e;
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('student_attendance')
            ->whereNotNull('schedule_session_id')
            ->update(['schedule_session_id' => null]);

        DB::table('schedule_sessions')->delete();
    }
};
