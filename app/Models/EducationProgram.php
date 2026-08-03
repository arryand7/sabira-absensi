<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'default_start_time',
        'default_end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroup::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'teacher_programs')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(ScheduleTimeSlot::class)->orderBy('position');
    }

    public function activeTimeSlots(): HasMany
    {
        return $this->timeSlots()->where('is_active', true);
    }
}
