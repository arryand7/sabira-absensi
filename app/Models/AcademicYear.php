<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'start_date', 'end_date', 'is_active'
    ];

    public function classGroupStudents()
    {
        return $this->hasMany(ClassGroupStudent::class);
    }

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->is_active) {
                AcademicYear::where('id', '!=', $model->id)->update(['is_active' => false]);
            }
        });
    }


}
