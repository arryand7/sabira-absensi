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
            ->withPivot('academic_year_id', 'joined_at', 'left_at', 'status', 'enrollment_source')
            ->withTimestamps();
    }

    public function activeStudents()
    {
        return $this->students()
            ->wherePivot('status', 'active');
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
