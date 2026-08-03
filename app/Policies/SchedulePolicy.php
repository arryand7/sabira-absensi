<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Determine whether the user can view the schedule.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $schedule->user_id === $user->id;
    }

    /**
     * Determine whether the user can submit attendance for the schedule.
     */
    public function submitAttendance(User $user, Schedule $schedule): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'guru') {
            return false;
        }

        // Owner teacher
        if ($schedule->user_id === $user->id) {
            return true;
        }

        // Guru pengganti hanya sah bila telah ditugaskan admin pada sesi tanggal ini.
        return ScheduleSession::query()
            ->where('schedule_id', $schedule->id)
            ->whereDate('date', today())
            ->where('actual_teacher_id', $user->id)
            ->whereIn('status', ['open', 'draft'])
            ->exists();
    }
}
