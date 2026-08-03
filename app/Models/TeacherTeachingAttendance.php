<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTeachingAttendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'check_in_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'location_accuracy' => 'float',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function scheduleSession(): BelongsTo
    {
        return $this->belongsTo(ScheduleSession::class);
    }
}
