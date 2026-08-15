<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassGroupStudent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentMembershipService
{
    public function invalidate(array $studentIds, int $classGroupId, string $reason, User $actor): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        return DB::transaction(function () use ($studentIds, $classGroupId, $reason, $actor) {
            $summary = [
                'invalidated' => 0,
                'skipped' => 0,
                'attendance_history' => 0,
                'failed' => 0,
                'skip_reasons' => [],
            ];

            foreach ($studentIds as $studentId) {
                $membership = ClassGroupStudent::query()
                    ->where('student_id', $studentId)
                    ->where('class_group_id', $classGroupId)
                    ->lockForUpdate()
                    ->first();

                if (! $membership || ! $membership->newQuery()->whereKey($membership->getKey())->active()->exists()) {
                    $summary['skipped']++;
                    $summary['skip_reasons'][] = "Siswa #{$studentId}: membership tidak ditemukan atau sudah tidak aktif.";

                    continue;
                }

                $attendanceCount = Attendance::query()
                    ->where('student_id', $studentId)
                    ->whereHas('schedule', fn ($query) => $query->where('class_group_id', $classGroupId))
                    ->count();

                $previousStatus = $membership->status;
                $membership->update([
                    'status' => 'entered_in_error',
                    'left_at' => now(),
                    'invalidated_at' => now(),
                    'invalidated_by' => $actor->id,
                    'invalidation_reason' => $reason,
                ]);

                $summary['invalidated']++;
                $summary['attendance_history'] += $attendanceCount > 0 ? 1 : 0;

                Log::info('Student class membership invalidated', [
                    'student_id' => $studentId,
                    'class_group_id' => $classGroupId,
                    'membership_id' => $membership->id,
                    'previous_status' => $previousStatus,
                    'new_status' => 'entered_in_error',
                    'reason' => $reason,
                    'performed_by' => $actor->id,
                    'performed_at' => $membership->invalidated_at?->toIso8601String(),
                    'attendance_count' => $attendanceCount,
                ]);
            }

            return $summary;
        });
    }

    public function preview(array $studentIds, int $classGroupId): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));
        $valid = ClassGroupStudent::query()->active()
            ->where('class_group_id', $classGroupId)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id');

        $attendanceStudents = Attendance::query()
            ->whereIn('student_id', $valid)
            ->whereHas('schedule', fn ($query) => $query->where('class_group_id', $classGroupId))
            ->distinct()->count('student_id');

        return [
            'selected' => count($studentIds),
            'valid' => $valid->count(),
            'stale' => count($studentIds) - $valid->count(),
            'attendance_history' => $attendanceStudents,
        ];
    }
}
