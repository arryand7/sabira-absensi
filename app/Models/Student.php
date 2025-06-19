<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    // Student.php
    public function classGroups()
    {
        return $this->belongsToMany(ClassGroup::class)
            ->withPivot('academic_year_id')
            ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function akademikClass()
    {
        return $this->classGroups->firstWhere('jenis_kelas', 'akademik');
    }

    public function muadalahClass()
    {
        return $this->classGroups->firstWhere('jenis_kelas', 'muadalah');
    }

    public function absensiAsrama()
    {
        return $this->hasMany(AbsensiAsrama::class);
    }
}
