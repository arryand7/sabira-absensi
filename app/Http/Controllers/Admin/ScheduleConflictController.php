<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveScheduleConflictRequest;
use App\Models\AcademicYear;
use App\Models\ScheduleConflict;
use App\Models\User;
use App\Services\ScheduleConflictService;
use Illuminate\Http\Request;

class ScheduleConflictController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ScheduleConflict::class);

        $conflicts = ScheduleConflict::query()
            ->with([
                'teacher',
                'resolver',
                'schedule.subject',
                'schedule.classGroup.educationProgram',
                'schedule.academicYear',
                'conflictingSchedule.subject',
                'conflictingSchedule.classGroup.educationProgram',
                'conflictingSchedule.academicYear',
            ])
            ->when(
                $request->has('status'),
                fn ($query) => $request->filled('status') ? $query->where('status', $request->status) : $query,
                fn ($query) => $query->pending()
            )
            ->when($request->teacher_id, fn ($query, $teacherId) => $query->where('teacher_id', $teacherId))
            ->when($request->hari, fn ($query, $day) => $query->whereHas('schedule', fn ($schedule) => $schedule->where('hari', $day)))
            ->when($request->academic_year_id, fn ($query, $yearId) => $query->whereHas('schedule', fn ($schedule) => $schedule->where('academic_year_id', $yearId)))
            ->when($request->semester, fn ($query, $semester) => $query->whereHas('schedule', fn ($schedule) => $schedule->where('semester', $semester)))
            ->latest('detected_at')
            ->paginate(20)
            ->withQueryString();

        $teachers = User::query()->where('role', 'guru')->orderBy('name')->get(['id', 'name']);
        $academicYears = AcademicYear::query()->orderByDesc('start_date')->get();
        $pendingCount = ScheduleConflict::pending()->count();

        return view('admin.schedule-conflicts.index', compact('conflicts', 'teachers', 'academicYears', 'pendingCount'));
    }

    public function show(ScheduleConflict $scheduleConflict)
    {
        $this->authorize('view', $scheduleConflict);

        $scheduleConflict->load([
            'teacher',
            'resolver',
            'schedule.subject',
            'schedule.classGroup.educationProgram',
            'schedule.academicYear',
            'schedule.user',
            'conflictingSchedule.subject',
            'conflictingSchedule.classGroup.educationProgram',
            'conflictingSchedule.academicYear',
            'conflictingSchedule.user',
        ]);

        return view('admin.schedule-conflicts.show', compact('scheduleConflict'));
    }

    public function resolve(
        ResolveScheduleConflictRequest $request,
        ScheduleConflict $scheduleConflict,
        ScheduleConflictService $service
    ) {
        $service->resolve(
            $scheduleConflict,
            $request->validated('resolution'),
            $request->user(),
            $request->validated('resolution_note')
        );

        return redirect()
            ->route('admin.schedule-conflicts.show', $scheduleConflict)
            ->with('success', 'Keputusan benturan jadwal berhasil disimpan.');
    }
}
