<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    protected $guarded = [];

    public function educationProgram()
    {
        return $this->belongsTo(EducationProgram::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('academic_year_id', 'joined_at', 'left_at', 'status', 'enrollment_source', 'invalidated_at', 'invalidated_by', 'invalidation_reason')
            ->withTimestamps();
    }

    public function activeStudents()
    {
        return $this->students()
            ->wherePivot('status', 'active')
            ->where(function ($query) {
                $query->whereNull('class_group_student.joined_at')->orWhereDate('class_group_student.joined_at', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('class_group_student.left_at')->orWhereDate('class_group_student.left_at', '>=', today());
            });
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
