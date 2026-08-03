<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'draft_payload' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function scheduledTeacher()
    {
        return $this->belongsTo(User::class, 'scheduled_teacher_id');
    }

    public function actualTeacher()
    {
        return $this->belongsTo(User::class, 'actual_teacher_id');
    }

    public function teachingAttendance()
    {
        return $this->hasOne(TeacherTeachingAttendance::class, 'schedule_session_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'schedule_session_id');
    }

    public function corrections()
    {
        return $this->hasMany(AttendanceCorrection::class, 'schedule_session_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSubstituted(): bool
    {
        return $this->scheduled_teacher_id !== null &&
            $this->actual_teacher_id !== null &&
            $this->scheduled_teacher_id !== $this->actual_teacher_id;
    }
}
