<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function classGroups()
    {
        return $this->belongsToMany(ClassGroup::class);
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

}
