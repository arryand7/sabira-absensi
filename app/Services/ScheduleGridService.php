<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ScheduleGridService
{
    public function build(Collection $schedules, Collection $programs): array
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $programGrids = $programs->map(function ($program) use ($days, $schedules) {
            $rows = $program->activeTimeSlots->values();
            $programSchedules = $schedules
                ->filter(fn ($schedule) => (int) ($schedule->education_program_id ?: $schedule->classGroup?->education_program_id) === (int) $program->id)
                ->values();
            $buckets = [];
            $matchedScheduleIds = [];

            foreach ($programSchedules as $schedule) {
                if (! in_array($schedule->hari, $days, true)) {
                    continue;
                }

                $start = $this->toMinutes($schedule->jam_mulai);
                $end = $this->toMinutes($schedule->jam_selesai);

                foreach ($rows->where('is_break', false) as $slot) {
                    if ($schedule->hari === 'Jumat' && ! $slot->friday_enabled) {
                        continue;
                    }

                    if ($start < $this->toMinutes($slot->end_time) && $end > $this->toMinutes($slot->start_time)) {
                        $buckets[$schedule->hari][$slot->id][] = $schedule;
                        $matchedScheduleIds[$schedule->id] = true;
                    }
                }
            }

            return [
                'program' => $program,
                'rows' => $rows,
                'buckets' => $buckets,
                'outside_schedules' => $programSchedules->reject(fn ($schedule) => isset($matchedScheduleIds[$schedule->id]))->values(),
            ];
        })->values();

        $knownProgramIds = $programs->pluck('id')->map(fn ($id) => (int) $id);
        $unassignedSchedules = $schedules->reject(fn ($schedule) => $knownProgramIds->contains((int) ($schedule->education_program_id ?: $schedule->classGroup?->education_program_id)))->values();

        return compact('days', 'programGrids', 'unassignedSchedules');
    }

    private function toMinutes(?string $time): int
    {
        [$hour, $minute] = array_pad(explode(':', (string) $time), 2, 0);

        return ((int) $hour * 60) + (int) $minute;
    }
}
