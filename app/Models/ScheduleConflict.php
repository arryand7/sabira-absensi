<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduleConflict extends Model
{
    public const STATUS_PENDING = 'pending_review';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_KEEP_CURRENT = 'resolved_keep_current';

    public const STATUS_KEEP_EXISTING = 'resolved_keep_existing';

    public const STATUS_DISMISSED = 'dismissed';

    protected $guarded = [];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class)->withTrashed();
    }

    public function conflictingSchedule()
    {
        return $this->belongsTo(Schedule::class, 'conflicting_schedule_id')->withTrashed();
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
