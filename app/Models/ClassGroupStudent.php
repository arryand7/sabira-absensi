<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClassGroupStudent extends Model
{
    protected $table = 'class_group_student'; // pastikan nama tabel pivot kamu ini

    protected $guarded = [];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'invalidated_at' => 'datetime',
    ];

    public function scopeActive(Builder $query, $referenceDate = null): Builder
    {
        $referenceDate ??= today();

        return $query->where('status', 'active')
            ->where(function (Builder $query) use ($referenceDate) {
                $query->whereNull('joined_at')->orWhereDate('joined_at', '<=', $referenceDate);
            })
            ->where(function (Builder $query) use ($referenceDate) {
                $query->whereNull('left_at')->orWhereDate('left_at', '>=', $referenceDate);
            });
    }

    // Relasi (optional)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function invalidatedBy()
    {
        return $this->belongsTo(User::class, 'invalidated_by');
    }
}
