<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroupStudent extends Model
{
    protected $table = 'class_group_student'; // pastikan nama tabel pivot kamu ini
    public $timestamps = false; // kalau pivot tidak pakai timestamps

    protected $fillable = [
        'class_group_id',
        'student_id',
        'academic_year_id',
    ];

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
}
