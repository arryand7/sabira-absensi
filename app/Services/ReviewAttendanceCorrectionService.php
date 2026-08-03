<?php

namespace App\Services;

use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewAttendanceCorrectionService
{
    public function execute(AttendanceCorrection $correction, User $reviewer, string $decision, ?string $notes): AttendanceCorrection
    {
        return DB::transaction(function () use ($correction, $reviewer, $decision, $notes) {
            $correction = AttendanceCorrection::query()->lockForUpdate()->findOrFail($correction->id);

            if ($correction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'decision' => 'Permintaan koreksi ini sudah ditinjau.',
                ]);
            }

            if ($decision === 'approved') {
                $payload = $correction->proposed_payload;
                $session = $correction->session()->lockForUpdate()->firstOrFail();
                $session->update([
                    'classroom_condition' => $payload['classroom_condition'] ?? null,
                    'teacher_notes' => $payload['teacher_notes'] ?? null,
                ]);

                foreach ($payload['attendance'] as $studentId => $status) {
                    $session->attendances()->where('student_id', $studentId)->update([
                        'status' => $status,
                        'materi' => $payload['materi'],
                    ]);
                }
            }

            $correction->update([
                'status' => $decision,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $correction->fresh(['session', 'requester', 'reviewer']);
        });
    }
}
