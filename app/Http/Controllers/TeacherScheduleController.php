<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAttendanceRequest;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\EducationProgram;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleConflictService;
use App\Services\ScheduleGridService;
use App\Services\ScheduleProgramResolver;
use App\Services\SubmitTeachingSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TeacherScheduleController extends Controller
{
    public function index(Request $request, ScheduleGridService $gridService)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->integer('academic_year_id') ?: $activeYear?->id;
        $selectedSemester = $request->input('semester', AcademicYear::currentSemester());

        $user = Auth::user();
        $guru = $user;

        // It's good practice to ensure the user has a 'guru' profile.
        if (! $guru->guru) {
            // Redirect with an error if the user with 'guru' role has no associated guru record.
            return redirect()->route('dashboard')->with('error', 'Profil guru tidak ditemukan.');
        }

        $schedules = Schedule::with([
            'classGroup.educationProgram',
            'educationProgram',
            'subject',
            'sessions' => fn ($query) => $query
                ->whereDate('date', today())
                ->with(['scheduledTeacher', 'actualTeacher']),
        ])
            // ->where('user_id', Auth::id())
            ->where('user_id', $user->id)
            ->when($selectedYear, fn ($query) => $query->where('academic_year_id', $selectedYear))
            ->where('semester', $selectedSemester)
            ->when($request->class_group_id, fn ($query, $classId) => $query->where('class_group_id', $classId))
            ->when($request->program_id, fn ($query, $programId) => $query->where(function ($programQuery) use ($programId) {
                $programQuery->where('education_program_id', $programId)
                    ->orWhere(fn ($fallbackQuery) => $fallbackQuery
                        ->whereNull('education_program_id')
                        ->whereHas('classGroup', fn ($classQuery) => $classQuery->where('education_program_id', $programId)));
            }))
            ->when($request->hari, fn ($query, $day) => $query->where('hari', $day))
            ->withCount(['pendingConflicts', 'pendingConflictsAsExisting'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $educationPrograms = EducationProgram::query()
            ->where('is_active', true)
            ->with('activeTimeSlots')
            ->orderBy('name')
            ->get();
        $displayPrograms = $request->filled('program_id')
            ? $educationPrograms->where('id', $request->integer('program_id'))->values()
            : $educationPrograms;
        $gridData = $gridService->build($schedules, $displayPrograms);
        $classGroups = ClassGroup::query()
            ->when($selectedYear, fn ($query) => $query->where('academic_year_id', $selectedYear))
            ->when($request->program_id, fn ($query, $programId) => $query->where('education_program_id', $programId))
            ->orderBy('nama_kelas')
            ->get();

        return view('guru.schedule.index', array_merge(compact(
            'schedules',
            'guru',
            'activeYear',
            'selectedYear',
            'selectedSemester',
            'academicYears',
            'educationPrograms',
            'classGroups'
        ), $gridData));
    }

    public function create(Request $request)
    {
        $teachers = User::where('role', 'guru')
            ->where('id', auth()->id())
            ->get();

        $selectedGuruId = $request->guru_id;

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        // Ambil semua mapel dan semua kelas (tanpa filter jenis)
        $subjects = Subject::all();
        $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get();
        $educationPrograms = EducationProgram::with('activeTimeSlots')->where('is_active', true)->orderBy('name')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('guru.schedule.create', compact(
            'teachers', 'subjects', 'classGroups', 'selectedGuruId', 'academicYears', 'tahunAktif', 'educationPrograms'
        ));
    }

    public function store(Request $request, ScheduleConflictService $conflictService, ScheduleProgramResolver $programResolver)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::in([auth()->id()])],
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => ['nullable', Rule::in(['ganjil', 'genap'])],
            'details' => 'required|array|min:1',
            'details.*.class_group_id' => 'required|exists:class_groups,id',
            'details.*.education_program_id' => 'nullable|exists:education_programs,id',
            'details.*.hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad',
            'details.*.jam_mulai' => 'required|date_format:H:i',
            'details.*.jam_selesai' => 'required|date_format:H:i|after:details.*.jam_mulai',
        ], [
            // Custom error messages
            'details.*.jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai.',
            'details.*.hari.required' => 'Hari wajib diisi.',
            'details.*.jam_mulai.required' => 'Jam mulai wajib diisi.',
            'details.*.jam_selesai.required' => 'Jam selesai wajib diisi.',
            'details.*.class_group_id.required' => 'Kelas wajib diisi.',
        ]);

        $conflictCount = DB::transaction(function () use ($validated, $conflictService, $programResolver) {
            $count = 0;
            foreach ($validated['details'] as $detail) {
                $classGroup = ClassGroup::findOrFail($detail['class_group_id']);
                $schedule = Schedule::create([
                    'user_id' => $validated['user_id'],
                    'subject_id' => $validated['subject_id'],
                    'class_group_id' => $detail['class_group_id'],
                    'education_program_id' => $programResolver->resolve(
                        $classGroup,
                        $detail['jam_mulai'],
                        $detail['jam_selesai'],
                        isset($detail['education_program_id']) ? (int) $detail['education_program_id'] : null,
                    ),
                    'hari' => $detail['hari'],
                    'jam_mulai' => $detail['jam_mulai'],
                    'jam_selesai' => $detail['jam_selesai'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'semester' => $validated['semester'] ?? AcademicYear::currentSemester(),
                ]);
                $count += $conflictService->refreshFor($schedule)->count();
            }

            return $count;
        });

        $redirect = redirect()->route('guru.schedule')->with('success', 'Jadwal berhasil dibuat.');

        return $conflictCount > 0
            ? $redirect->with('warning', 'Jadwal tersimpan, tetapi terdeteksi benturan dan memerlukan verifikasi admin.')
            : $redirect;
    }

    public function showByTeacher($id)
    {
        abort_unless((int) $id === (int) auth()->id(), 403);

        return $this->index(request());
    }

    public function edit(Schedule $schedule)
    {
        $this->authorize('view', $schedule);
        $teachers = User::whereKey(auth()->id())->get();

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $subjects = Subject::all();
        $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get(); // semua kelas tahun aktif
        $educationPrograms = EducationProgram::where('is_active', true)->orderBy('name')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('guru.schedule.edit', compact(
            'schedule', 'teachers', 'subjects', 'classGroups', 'academicYears', 'tahunAktif', 'educationPrograms'
        ));
    }

    public function update(Request $request, Schedule $schedule, ScheduleConflictService $conflictService, ScheduleProgramResolver $programResolver)
    {
        $this->authorize('view', $schedule);
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', Rule::in([auth()->id()])],
            'subject_id' => 'required|exists:subjects,id',
            'class_group_id' => 'required|exists:class_groups,id',
            'education_program_id' => 'nullable|exists:education_programs,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'semester' => ['nullable', Rule::in(['ganjil', 'genap'])],
        ]);

        $classGroup = ClassGroup::findOrFail($request->class_group_id);
        $conflictCount = DB::transaction(function () use ($schedule, $validated, $classGroup, $conflictService, $programResolver) {
            $schedule->update(array_merge($validated, [
                'education_program_id' => $programResolver->resolve(
                    $classGroup,
                    $validated['jam_mulai'],
                    $validated['jam_selesai'],
                    isset($validated['education_program_id']) ? (int) $validated['education_program_id'] : null,
                ),
                'academic_year_id' => $classGroup->academic_year_id,
                'semester' => $validated['semester'] ?? $schedule->semester ?? AcademicYear::currentSemester(),
            ]));

            return $conflictService->refreshFor($schedule)->count();
        });

        $redirect = redirect()->route('guru.schedule')->with('success', 'Jadwal berhasil diperbarui.');

        return $conflictCount > 0
            ? $redirect->with('warning', 'Perubahan tersimpan, tetapi jadwal bentrok dan memerlukan verifikasi admin.')
            : $redirect;
    }

    public function destroy(Schedule $schedule)
    {
        $this->authorize('view', $schedule);
        $userId = $schedule->user_id;
        $schedule->delete();

        return redirect()->route('guru.schedule.show-by-teacher', $userId)->with('success', 'Jadwal berhasil dihapus.');
    }

    public function absen(Schedule $schedule)
    {
        $this->authorize('submitAttendance', $schedule);

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $classGroup = ClassGroup::with(['students' => function ($q) use ($tahunAktif) {
            if ($tahunAktif) {
                $q->wherePivot('academic_year_id', $tahunAktif->id);
            }
            $q->wherePivot('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('class_group_student.joined_at')
                        ->orWhereDate('class_group_student.joined_at', '<=', today());
                })
                ->where(function ($query) {
                    $query->whereNull('class_group_student.left_at')
                        ->orWhereDate('class_group_student.left_at', '>=', today());
                });
        }])->findOrFail($schedule->class_group_id);

        $draftSession = ScheduleSession::where('schedule_id', $schedule->id)
            ->whereDate('date', today())
            ->whereIn('status', ['open', 'draft'])
            ->first();
        $draft = $draftSession?->draft_payload ?? [];
        $nextMeeting = ((int) ScheduleSession::where('subject_id', $schedule->subject_id)
            ->where('class_group_id', $schedule->class_group_id)
            ->where('academic_year_id', $schedule->academic_year_id)
            ->whereNotNull('meeting_no')
            ->max('meeting_no')) + 1;

        return view('guru.schedule.absen', compact('classGroup', 'schedule', 'draftSession', 'draft', 'nextMeeting'));
    }

    public function saveDraft(Request $request, Schedule $schedule)
    {
        $this->authorize('submitAttendance', $schedule);

        $validated = $request->validate([
            'pertemuan' => ['nullable', 'integer', 'min:1'],
            'materi' => ['nullable', 'string', 'max:500'],
            'classroom_condition' => ['nullable', 'string', 'max:500'],
            'teacher_notes' => ['nullable', 'string', 'max:500'],
            'attendance' => ['nullable', 'array'],
            'attendance.*' => ['nullable', 'in:hadir,sakit,izin,alpa'],
        ]);

        $validStudentIds = $schedule->classGroup->students()
            ->wherePivot('academic_year_id', $schedule->academic_year_id)
            ->wherePivot('status', 'active')
            ->pluck('students.id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $attendance = collect($validated['attendance'] ?? [])
            ->filter(fn ($status, $studentId) => in_array((string) $studentId, $validStudentIds, true))
            ->all();

        $session = ScheduleSession::firstOrNew([
            'schedule_id' => $schedule->id,
            'date' => today()->toDateString(),
        ]);

        if ($session->exists && $session->status === 'completed') {
            return response()->json(['message' => 'Sesi sudah selesai dan tidak dapat diubah sebagai draft.'], 409);
        }

        $session->fill([
            'subject_id' => $schedule->subject_id,
            'class_group_id' => $schedule->class_group_id,
            'academic_year_id' => $schedule->academic_year_id,
            'scheduled_teacher_id' => $schedule->user_id,
            'actual_teacher_id' => auth()->id(),
            'start_time' => $schedule->jam_mulai,
            'end_time' => $schedule->jam_selesai,
            'created_by' => $session->created_by ?: auth()->id(),
            'draft_payload' => array_merge($validated, ['attendance' => $attendance]),
            'status' => 'draft',
        ])->save();

        return response()->json([
            'message' => 'Draft tersimpan.',
            'saved_at' => now()->format('H:i:s'),
        ]);
    }

    public function submitAbsen(SubmitAttendanceRequest $request, $classGroupId, SubmitTeachingSessionService $service)
    {
        $schedule = $request->getSchedule();
        if (! $schedule) {
            $schedule = Schedule::with('subject')->findOrFail($request->input('schedule_id'));
            $this->authorize('submitAttendance', $schedule);
        }

        $scheduleId = $schedule->id;
        $tanggal = now()->toDateString();
        $pertemuan = $request->input('pertemuan');

        $existingSession = ScheduleSession::where('schedule_id', $scheduleId)
            ->whereDate('date', $tanggal)
            ->first();

        if ($existingSession?->status === 'completed') {
            return back()
                ->withInput()
                ->with('error', 'Sesi pertemuan untuk jadwal ini sudah diselesaikan hari ini.');
        }

        $duplicatePertemuan = ScheduleSession::where('subject_id', $schedule->subject_id)
            ->where('class_group_id', $schedule->class_group_id)
            ->where('academic_year_id', $schedule->academic_year_id)
            ->where('meeting_no', $pertemuan)
            ->when($existingSession, fn ($query) => $query->whereKeyNot($existingSession->id))
            ->exists();

        if ($duplicatePertemuan) {
            return back()
                ->withInput()
                ->with('error', 'Pertemuan ke-'.$pertemuan.' untuk mata pelajaran dan kelas ini sudah pernah diisi.');
        }

        try {
            $service->execute(array_merge($request->validated(), [
                'schedule_id' => $scheduleId,
                'date' => $tanggal,
            ]));

            return redirect()->route('guru.schedule')->with('success', 'Absen berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan absensi siswa: '.$e->getMessage(), [
                'schedule_id' => $scheduleId,
                'teacher_id' => Auth::id(),
                'exception' => $e,
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan absensi.');
        }
    }
}
