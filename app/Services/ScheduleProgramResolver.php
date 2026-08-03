<?php

namespace App\Services;

use App\Models\ClassGroup;
use App\Models\EducationProgram;

class ScheduleProgramResolver
{
    public function resolve(ClassGroup $classGroup, string $start, string $end, ?int $requestedProgramId = null): ?int
    {
        if ($requestedProgramId) {
            return $requestedProgramId;
        }

        $start = substr($start, 0, 5);
        $end = substr($end, 0, 5);
        $matchingPrograms = EducationProgram::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (EducationProgram $program) => $start >= substr($program->default_start_time, 0, 5)
                && $end <= substr($program->default_end_time, 0, 5));

        return $matchingPrograms->count() === 1
            ? $matchingPrograms->first()->id
            : $classGroup->education_program_id;
    }
}
