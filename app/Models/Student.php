<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classGroups()
    {
        return $this->belongsToMany(ClassGroup::class)
            ->withPivot('academic_year_id', 'joined_at', 'left_at', 'status', 'enrollment_source', 'invalidated_at', 'invalidated_by', 'invalidation_reason')
            ->withTimestamps();
    }

    public function activeClassGroups()
    {
        return $this->classGroups()
            ->wherePivot('status', 'active')
            ->where(function ($query) {
                $query->whereNull('class_group_student.joined_at')->orWhereDate('class_group_student.joined_at', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('class_group_student.left_at')->orWhereDate('class_group_student.left_at', '>=', today());
            });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function formalClass()
    {
        return $this->activeClassGroups->firstWhere('jenis_kelas', 'formal');
    }

    public function muadalahClass()
    {
        return $this->activeClassGroups->firstWhere('jenis_kelas', 'muadalah');
    }

    public function nonRegularClasses()
    {
        return $this->activeClassGroups->where('class_type', 'non_reguler');
    }

    public function absensiAsrama()
    {
        return $this->hasMany(AbsensiAsrama::class);
    }
}
