<?php

namespace App\Http\Controllers;

use App\Imports\ScheduleImport;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\EducationProgram;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScheduleConflictService;
use App\Services\ScheduleProgramResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class AdminScheduleController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->tahun_ajaran ?? $activeYear?->id;
        $selectedSemester = $request->input('semester', AcademicYear::currentSemester());

        $teachers = User::where('role', 'guru')
            ->where('status', 'aktif')
            ->with('guru')
            ->orderBy('name')
            ->get();

        $classGroups = ClassGroup::when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear))
            ->orderBy('nama_kelas')
            ->get();

        $subjects = Subject::orderBy('nama_mapel')->get();

        $schedules = Schedule::with(['subject', 'classGroup', 'user'])
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear))
            ->where('semester', $selectedSemester)
            ->when($request->guru_id, fn ($q) => $q->where('user_id', $request->guru_id))
            ->when($request->class_group_id, fn ($q) => $q->where('class_group_id', $request->class_group_id))
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->hari, fn ($q) => $q->where('hari', $request->hari))
            ->withCount(['pendingConflicts', 'pendingConflictsAsExisting'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $summary = [
            'total' => $schedules->count(),
            'teachers' => $schedules->pluck('user_id')->unique()->count(),
            'classes' => $schedules->pluck('class_group_id')->unique()->count(),
        ];

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.index', compact(
            'teachers',
            'classGroups',
            'subjects',
            'schedules',
            'summary',
            'academicYears',
            'activeYear',
            'selectedYear', 'selectedSemester'
        ));
    }

    public function showByTeacher($id)
    {
        $teacher = User::with('guru')->findOrFail($id);

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $schedules = Schedule::with(['subject', 'classGroup'])
            ->where('user_id', $id)
            ->where('academic_year_id', $tahunAktif?->id)
            ->get();

        return view('admin.schedules.show', compact('teacher', 'schedules'));
    }

    public function create(Request $request)
    {
        $teachers = User::where('role', 'guru')->get();
        $selectedGuruId = $request->guru_id;

        $tahunAktif = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->tahun_ajaran ?? $tahunAktif?->id;

        // Ambil semua mapel dan semua kelas (tanpa filter jenis)
        $subjects = Subject::all();
        $classGroups = ClassGroup::with('educationProgram')->where('academic_year_id', $selectedYear)->get();
        $slotPolicies = $this->slotPolicies();
        $educationPrograms = EducationProgram::where('is_active', true)->orderBy('name')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.create', compact(
            'teachers',
            'subjects',
            'classGroups',
            'selectedGuruId',
            'academicYears',
            'tahunAktif',
            'selectedYear',
            'slotPolicies',
            'educationPrograms'
        ));
    }

    public function store(Request $request, ScheduleConflictService $conflictService, ScheduleProgramResolver $programResolver)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $redirect = redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])
            ->with('success', 'Jadwal berhasil dibuat.');

        return $conflictCount > 0
            ? $redirect->with('warning', 'Jadwal tersimpan, tetapi terdeteksi benturan dan memerlukan verifikasi admin.')
            : $redirect;
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::where('role', 'guru')->get(); // ambil semua guru

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $subjects = Subject::all();
        $classGroups = ClassGroup::with('educationProgram')->where('academic_year_id', $tahunAktif?->id)->get(); // semua kelas tahun aktif
        $slotPolicies = $this->slotPolicies();
        $educationPrograms = EducationProgram::where('is_active', true)->orderBy('name')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.edit', compact(
            'schedule', 'teachers', 'subjects', 'classGroups', 'academicYears', 'tahunAktif', 'slotPolicies', 'educationPrograms'
        ));
    }

    public function update(Request $request, Schedule $schedule, ScheduleConflictService $conflictService, ScheduleProgramResolver $programResolver)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $redirect = redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])
            ->with('success', 'Jadwal berhasil diperbarui.');

        return $conflictCount > 0
            ? $redirect->with('warning', 'Perubahan tersimpan, tetapi jadwal bentrok dan memerlukan verifikasi admin.')
            : $redirect;
    }

    public function destroy(Schedule $schedule)
    {
        $userId = $schedule->user_id;
        $schedule->delete();

        return redirect()->route('admin.schedules.show-by-teacher', $userId)->with('success', 'Jadwal berhasil dihapus.');
    }

    public function assignSubstitute(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'substitute_teacher_id' => ['required', 'integer', 'exists:users,id', 'different:scheduled_teacher_id'],
        ]);

        $substitute = User::query()
            ->whereKey($validated['substitute_teacher_id'])
            ->where('role', 'guru')
            ->where('status', 'aktif')
            ->firstOrFail();

        $session = ScheduleSession::firstOrNew([
            'schedule_id' => $schedule->id,
            'date' => $validated['date'],
        ]);

        if ($session->exists && $session->status === 'completed') {
            return back()->with('error', 'Guru pengganti tidak dapat diubah karena sesi sudah selesai.');
        }

        $session->fill([
            'subject_id' => $schedule->subject_id,
            'class_group_id' => $schedule->class_group_id,
            'academic_year_id' => $schedule->academic_year_id,
            'scheduled_teacher_id' => $schedule->user_id,
            'actual_teacher_id' => $substitute->id,
            'start_time' => $schedule->jam_mulai,
            'end_time' => $schedule->jam_selesai,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ])->save();

        return back()->with('success', "{$substitute->name} ditugaskan sebagai guru pengganti pada {$validated['date']}.");
    }

    public function import(Request $request, ScheduleConflictService $conflictService)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $import = new ScheduleImport($conflictService);
        Excel::import($import, $request->file('file'));

        if ($import->successRows === [] && $import->failures === []) {
            return back()->withErrors(['file' => 'File tidak mengandung data valid atau formatnya salah.']);
        }

        $success = collect($import->successRows)->map(function ($row) {
            return is_array($row) ? json_encode($row) : (string) $row;
        })->toArray();

        $failures = collect($import->failures)->map(function ($row) {
            return is_array($row) ? json_encode($row) : (string) $row;
        })->toArray();

        return back()->with([
            'success' => $success,
            'errors_import' => $failures,
        ]);
    }

    private function slotPolicies(): array
    {
        return EducationProgram::query()
            ->with(['activeTimeSlots' => fn ($query) => $query->where('is_break', false)])
            ->get()
            ->mapWithKeys(fn ($program) => [
                $program->id => $program->activeTimeSlots->map(fn ($slot) => [
                    'id' => $slot->id,
                    'number' => $slot->slot_number,
                    'label' => $slot->label ?: 'Jam '.$slot->slot_number,
                    'start' => substr($slot->start_time, 0, 5),
                    'end' => substr($slot->end_time, 0, 5),
                    'friday_enabled' => $slot->friday_enabled,
                ])->values()->all(),
            ])
            ->all();
    }
}
