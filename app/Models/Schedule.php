<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleted(function (Schedule $schedule) {
            ScheduleConflict::query()
                ->pending()
                ->where(fn ($query) => $query
                    ->where('schedule_id', $schedule->id)
                    ->orWhere('conflicting_schedule_id', $schedule->id))
                ->update([
                    'status' => ScheduleConflict::STATUS_DISMISSED,
                    'resolved_at' => now(),
                    'resolution_note' => 'Ditutup otomatis karena salah satu jadwal dinonaktifkan.',
                    'updated_at' => now(),
                ]);
        });
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function educationProgram()
    {
        return $this->belongsTo(EducationProgram::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function sessions()
    {
        return $this->hasMany(ScheduleSession::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function conflicts()
    {
        return $this->hasMany(ScheduleConflict::class);
    }

    public function conflictsAsExisting()
    {
        return $this->hasMany(ScheduleConflict::class, 'conflicting_schedule_id');
    }

    public function pendingConflicts()
    {
        return $this->conflicts()->pending();
    }

    public function pendingConflictsAsExisting()
    {
        return $this->conflictsAsExisting()->pending();
    }

    public function getHasPendingConflictAttribute(): bool
    {
        if (array_key_exists('pending_conflicts_count', $this->attributes)
            || array_key_exists('pending_conflicts_as_existing_count', $this->attributes)) {
            return ((int) ($this->attributes['pending_conflicts_count'] ?? 0)
                + (int) ($this->attributes['pending_conflicts_as_existing_count'] ?? 0)) > 0;
        }

        return $this->pendingConflicts()->exists() || $this->pendingConflictsAsExisting()->exists();
    }
}
