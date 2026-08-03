<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\AcademicYear;
use App\Models\AttendanceCorrection;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TeacherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeYear = AcademicYear::where('is_active', true)->first();

        if (! $activeYear) {
            return back()->withErrors(['Tahun ajaran aktif tidak ditemukan.']);
        }

        $ownedSessions = fn ($query) => $query
            ->where('academic_year_id', $activeYear->id)
            ->where(function ($teacherQuery) use ($user) {
                $teacherQuery->where('scheduled_teacher_id', $user->id)
                    ->orWhere('actual_teacher_id', $user->id)
                    ->orWhereHas('schedule', fn ($schedule) => $schedule->where('user_id', $user->id));
            });

        $query = ScheduleSession::with([
            'schedule.subject',
            'schedule.classGroup',
            'attendances:id,schedule_session_id,materi',
            'corrections' => fn ($corrections) => $corrections->latest(),
        ])->where($ownedSessions);

        if ($request->filled('kelas')) {
            $query->whereHas('schedule.classGroup', fn ($q) => $q->where('nama_kelas', $request->kelas));
        }

        if ($request->filled('mapel')) {
            $query->whereHas('schedule.subject', fn ($q) => $q->where('nama_mapel', $request->mapel));
        }

        $sessions = $query->orderByDesc('date')->paginate(20)->withQueryString();

        $scheduleIds = ScheduleSession::query()->where($ownedSessions)->pluck('schedule_id');
        $teacherSchedules = Schedule::whereIn('id', $scheduleIds)->with(['classGroup', 'subject'])->get();
        $kelasList = $teacherSchedules->pluck('classGroup.nama_kelas')->filter()->unique()->sort();
        $mapelList = $teacherSchedules->pluck('subject.nama_mapel')->filter()->unique()->sort();

        return view('guru.history.index', compact('sessions', 'kelasList', 'mapelList'));
    }

    public function detail(ScheduleSession $session)
    {
        $this->ensureTeacherCanAccess($session);

        $session->load([
            'schedule.subject',
            'schedule.classGroup',
            'scheduledTeacher',
            'actualTeacher',
            'attendances.student',
            'corrections.requester',
            'corrections.reviewer',
        ]);

        return view('guru.history.detail', compact('session'));
    }

    public function requestCorrection(StoreAttendanceCorrectionRequest $request, ScheduleSession $session)
    {
        $this->ensureTeacherCanAccess($session);
        $session->load('attendances');

        if ($session->status !== 'completed') {
            throw ValidationException::withMessages([
                'reason' => 'Koreksi hanya dapat diajukan untuk sesi yang sudah selesai.',
            ]);
        }

        if ($session->corrections()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages([
                'reason' => 'Masih ada permintaan koreksi yang menunggu peninjauan.',
            ]);
        }

        $validated = $request->validated();
        $expectedStudentIds = $session->attendances->pluck('student_id')->map(fn ($id) => (string) $id)->sort()->values();
        $submittedStudentIds = collect(array_keys($validated['attendance']))->map(fn ($id) => (string) $id)->sort()->values();

        if ($expectedStudentIds->all() !== $submittedStudentIds->all()) {
            throw ValidationException::withMessages([
                'attendance' => 'Data kehadiran harus mencakup seluruh siswa pada sesi ini.',
            ]);
        }

        $currentMaterial = $session->attendances->first()?->materi;
        AttendanceCorrection::create([
            'schedule_session_id' => $session->id,
            'requested_by' => $request->user()->id,
            'reason' => $validated['reason'],
            'before_payload' => [
                'materi' => $currentMaterial,
                'classroom_condition' => $session->classroom_condition,
                'teacher_notes' => $session->teacher_notes,
                'attendance' => $session->attendances->pluck('status', 'student_id')->all(),
            ],
            'proposed_payload' => [
                'materi' => $validated['materi'],
                'classroom_condition' => $validated['classroom_condition'] ?? null,
                'teacher_notes' => $validated['teacher_notes'] ?? null,
                'attendance' => $validated['attendance'],
            ],
        ]);

        return redirect()->route('guru.history.detail', $session)
            ->with('success', 'Permintaan koreksi dikirim dan menunggu peninjauan admin.');
    }

    private function ensureTeacherCanAccess(ScheduleSession $session): void
    {
        $userId = Auth::id();
        $session->loadMissing('schedule:id,user_id');

        $allowed = in_array($userId, [
            $session->scheduled_teacher_id,
            $session->actual_teacher_id,
            $session->schedule?->user_id,
        ], true);

        abort_unless($allowed, 403, 'Anda tidak memiliki akses ke sesi ini.');
    }
}
