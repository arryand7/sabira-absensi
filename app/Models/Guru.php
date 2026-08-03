<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $fillable = ['user_id', 'jenis'];

    protected $table = 'gurus';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function educationPrograms()
    {
        return $this->belongsToMany(EducationProgram::class, 'teacher_programs')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function teachesProgram(string $programCode): bool
    {
        return $this->educationPrograms()
            ->where('code', $programCode)
            ->wherePivot('status', 'active')
            ->exists();
    }
}
