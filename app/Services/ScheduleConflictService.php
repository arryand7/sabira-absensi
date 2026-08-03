<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\ScheduleConflict;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleConflictService
{
    public function refreshFor(Schedule $schedule): Collection
    {
        return DB::transaction(function () use ($schedule) {
            $schedule->refresh();
            $candidates = $this->candidatesFor($schedule);
            $activeKeys = $candidates
                ->map(fn (array $candidate) => $this->pairKey($schedule->id, $candidate['schedule']->id, $candidate['type']))
                ->all();

            ScheduleConflict::query()
                ->pending()
                ->where(fn ($query) => $query
                    ->where('schedule_id', $schedule->id)
                    ->orWhere('conflicting_schedule_id', $schedule->id))
                ->get()
                ->each(function (ScheduleConflict $conflict) use ($activeKeys) {
                    $key = $this->pairKey($conflict->schedule_id, $conflict->conflicting_schedule_id, $conflict->conflict_type);
                    if (! in_array($key, $activeKeys, true)) {
                        $conflict->update([
                            'status' => ScheduleConflict::STATUS_DISMISSED,
                            'resolved_at' => now(),
                            'resolution_note' => 'Benturan tidak lagi berlaku setelah jadwal diperbarui.',
                        ]);
                    }
                });

            return $candidates->map(function (array $candidate) use ($schedule) {
                $other = $candidate['schedule'];
                $conflict = ScheduleConflict::query()
                    ->where('conflict_type', $candidate['type'])
                    ->where(function ($query) use ($schedule, $other) {
                        $query->where(fn ($pair) => $pair
                            ->where('schedule_id', $schedule->id)
                            ->where('conflicting_schedule_id', $other->id))
                            ->orWhere(fn ($pair) => $pair
                                ->where('schedule_id', $other->id)
                                ->where('conflicting_schedule_id', $schedule->id));
                    })
                    ->first();

                $values = [
                    'teacher_id' => $schedule->user_id,
                    'conflict_scope' => 'weekly_recurring',
                    'status' => ScheduleConflict::STATUS_PENDING,
                    'detected_at' => now(),
                    'resolved_at' => null,
                    'resolved_by' => null,
                    'resolution_note' => null,
                ];

                if ($conflict) {
                    $conflict->update($values);

                    return $conflict->refresh();
                }

                return ScheduleConflict::create(array_merge($values, [
                    'schedule_id' => $schedule->id,
                    'conflicting_schedule_id' => $other->id,
                    'conflict_type' => $candidate['type'],
                ]));
            })->values();
        });
    }

    public function resolve(ScheduleConflict $conflict, string $resolution, User $resolver, ?string $note = null): ScheduleConflict
    {
        return DB::transaction(function () use ($conflict, $resolution, $resolver, $note) {
            $conflict = ScheduleConflict::query()->lockForUpdate()->findOrFail($conflict->id);

            if (! $conflict->isPending()) {
                throw ValidationException::withMessages([
                    'resolution' => 'Benturan jadwal ini sudah pernah diselesaikan.',
                ]);
            }

            $losingScheduleId = null;
            $status = match ($resolution) {
                'keep_current' => ScheduleConflict::STATUS_KEEP_CURRENT,
                'keep_existing' => ScheduleConflict::STATUS_KEEP_EXISTING,
                'keep_both' => ScheduleConflict::STATUS_CONFIRMED,
                'dismiss' => ScheduleConflict::STATUS_DISMISSED,
                default => throw ValidationException::withMessages(['resolution' => 'Keputusan benturan tidak valid.']),
            };

            if ($resolution === 'keep_current') {
                $losingScheduleId = $conflict->conflicting_schedule_id;
            } elseif ($resolution === 'keep_existing') {
                $losingScheduleId = $conflict->schedule_id;
            }

            if ($losingScheduleId) {
                Schedule::query()->findOrFail($losingScheduleId)->delete();
                ScheduleConflict::query()
                    ->pending()
                    ->whereKeyNot($conflict->id)
                    ->where(fn ($query) => $query
                        ->where('schedule_id', $losingScheduleId)
                        ->orWhere('conflicting_schedule_id', $losingScheduleId))
                    ->update([
                        'status' => ScheduleConflict::STATUS_DISMISSED,
                        'resolved_at' => now(),
                        'resolved_by' => $resolver->id,
                        'resolution_note' => 'Ditutup otomatis karena jadwal terkait dinonaktifkan.',
                        'updated_at' => now(),
                    ]);
            }

            $conflict->update([
                'status' => $status,
                'resolved_at' => now(),
                'resolved_by' => $resolver->id,
                'resolution_note' => $note,
            ]);

            return $conflict->refresh();
        });
    }

    private function candidatesFor(Schedule $schedule): Collection
    {
        $base = Schedule::query()
            ->whereKeyNot($schedule->id)
            ->where('academic_year_id', $schedule->academic_year_id)
            ->where('semester', $schedule->semester)
            ->where('hari', $schedule->hari)
            ->where('jam_mulai', '<', $schedule->jam_selesai)
            ->where('jam_selesai', '>', $schedule->jam_mulai);

        $teacherConflicts = (clone $base)
            ->where('user_id', $schedule->user_id)
            ->get()
            ->map(fn (Schedule $other) => ['schedule' => $other, 'type' => 'teacher_overlap']);

        $classConflicts = (clone $base)
            ->where('class_group_id', $schedule->class_group_id)
            ->get()
            ->map(fn (Schedule $other) => ['schedule' => $other, 'type' => 'class_overlap']);

        return $teacherConflicts
            ->concat($classConflicts)
            ->unique(fn (array $candidate) => $candidate['type'].'-'.$candidate['schedule']->id)
            ->values();
    }

    private function pairKey(int $first, int $second, string $type): string
    {
        return min($first, $second).'-'.max($first, $second).'-'.$type;
    }
}
