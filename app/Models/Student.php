<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function classGroups()
    {
        return $this->belongsToMany(ClassGroup::class)
            ->withPivot('academic_year_id', 'joined_at', 'left_at', 'status', 'enrollment_source')
            ->withTimestamps();
    }

    public function activeClassGroups()
    {
        return $this->classGroups()
            ->wherePivot('status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function formalClass()
    {
        return $this->classGroups->firstWhere('jenis_kelas', 'formal');
    }

    public function muadalahClass()
    {
        return $this->classGroups->firstWhere('jenis_kelas', 'muadalah');
    }

    public function nonRegularClasses()
    {
        return $this->classGroups->where('class_type', 'non_reguler');
    }

    public function absensiAsrama()
    {
        return $this->hasMany(AbsensiAsrama::class);
    }
}
