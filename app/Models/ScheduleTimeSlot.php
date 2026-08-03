<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTimeSlot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_break' => 'boolean',
        'friday_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function educationProgram(): BelongsTo
    {
        return $this->belongsTo(EducationProgram::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTeaching(Builder $query): Builder
    {
        return $query->where('is_break', false);
    }
}
