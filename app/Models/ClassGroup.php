<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class ClassGroup extends Model
{
    protected $guarded = [];

    // App\Models\ClassGroup.php
    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withPivot('academic_year_id')
            ->withTimestamps()
            ->wherePivot('academic_year_id', function ($query) {
                $query->select('id')
                    ->from('academic_years')
                    ->where('is_active', true)
                    ->limit(1);
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
