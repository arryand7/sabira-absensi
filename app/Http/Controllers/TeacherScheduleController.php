<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use App\Models\ScheduleSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TeacherScheduleController extends Controller
{
    public function index()
    {
        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

        $user = Auth::user();
        $guru = $user;

        // It's good practice to ensure the user has a 'guru' profile.
        if (!$guru->guru) {
            // Redirect with an error if the user with 'guru' role has no associated guru record.
            return redirect()->route('dashboard')->with('error', 'Profil guru tidak ditemukan.');
        }

        $schedules = Schedule::with(['classGroup', 'subject'])
            // ->where('user_id', Auth::id())
            ->where('user_id', $user->id)
            ->where('academic_year_id', $tahunAktif?->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $gridData = $this->buildScheduleGrid($schedules);

        return view('guru.schedule.index', array_merge(compact('schedules', 'guru'), $gridData));
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

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('guru.schedule.create', compact(
            'teachers', 'subjects', 'classGroups', 'selectedGuruId', 'academicYears', 'tahunAktif'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'details' => 'required|array|min:1',
            'details.*.class_group_id' => 'required|exists:class_groups,id',
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


        foreach ($validated['details'] as $detail) {
            $guruConflict = Schedule::with(['user', 'subject', 'classGroup'])->where('user_id', $request->user_id)
                ->where('hari', $detail['hari'])
                ->where(function ($query) use ($detail) {
                    $query->where(function ($q) use ($detail) {
                        $q->where('jam_mulai', '>=', $detail['jam_mulai'])
                            ->where('jam_mulai', '<', $detail['jam_selesai']);
                    })->orWhere(function ($q) use ($detail) {
                        $q->where('jam_selesai', '>', $detail['jam_mulai'])
                            ->where('jam_selesai', '<=', $detail['jam_selesai']);
                    })->orWhere(function ($q) use ($detail) {
                        $q->where('jam_mulai', '<=', $detail['jam_mulai'])
                            ->where('jam_selesai', '>=', $detail['jam_selesai']);
                    });
                })
                ->first();

            $kelasConflict = Schedule::with(['user', 'subject', 'classGroup'])->where('class_group_id', $detail['class_group_id'])
                ->where('hari', $detail['hari'])
                ->where(function ($query) use ($detail) {
                    $query->where(function ($q) use ($detail) {
                        $q->where('jam_mulai', '>=', $detail['jam_mulai'])
                            ->where('jam_mulai', '<', $detail['jam_selesai']);
                    })->orWhere(function ($q) use ($detail) {
                        $q->where('jam_selesai', '>', $detail['jam_mulai'])
                            ->where('jam_selesai', '<=', $detail['jam_selesai']);
                    })->orWhere(function ($q) use ($detail) {
                        $q->where('jam_mulai', '<=', $detail['jam_mulai'])
                            ->where('jam_selesai', '>=', $detail['jam_selesai']);
                    });
                })
                ->first();

            if ($guruConflict || $kelasConflict) {
                $conflictingSchedule = $guruConflict ?: $kelasConflict;
                $detailBentrok = ' dengan jadwal ' . $conflictingSchedule->subject->nama_mapel . ' oleh ' . $conflictingSchedule->user->name . ' di kelas ' . $conflictingSchedule->classGroup->nama_kelas . ' pada jam ' . date('H:i', strtotime($conflictingSchedule->jam_mulai)) . ' - ' . date('H:i', strtotime($conflictingSchedule->jam_selesai));

                return redirect()->back()->withInput()->withErrors([
                    'jadwal' => ($guruConflict ? 'Jadwal guru bentrok' : 'Jadwal kelas bentrok') . $detailBentrok,
                ]);
            }

            $classGroup = ClassGroup::findOrFail($detail['class_group_id']);

            Schedule::create([
                'user_id' => $request->user_id,
                'subject_id' => $request->subject_id,
                'class_group_id' => $detail['class_group_id'],
                'hari' => $detail['hari'],
                'jam_mulai' => $detail['jam_mulai'],
                'jam_selesai' => $detail['jam_selesai'],
                'academic_year_id' => $request->academic_year_id,
            ]);
        }

        return redirect()->route('guru.schedule.show-by-teacher', $request->user_id)->with('success', 'Jadwal berhasil dibuat.');
    }

    public function showByTeacher($id)
    {
        $guru = User::with('guru')->findOrFail($id);

        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

        $schedules = Schedule::with(['subject', 'classGroup'])
            ->where('user_id', $id)
            ->where('academic_year_id', $tahunAktif?->id)
            ->get();

        $gridData = $this->buildScheduleGrid($schedules);

        return view('guru.schedule.index', array_merge(compact('guru', 'schedules'), $gridData));
    }

    public function edit(Schedule $schedule)
    {
        $teachers = User::where('role', 'guru')->get(); // ambil semua guru

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $subjects = Subject::all();
        $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get(); // semua kelas tahun aktif

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('guru.schedule.edit', compact(
            'schedule', 'teachers', 'subjects', 'classGroups', 'academicYears', 'tahunAktif'
        ));
    }


    public function update(Request $request, Schedule $schedule)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_group_id' => 'required|exists:class_groups,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required|after:jam_mulai',
        ]);

        // Cek bentrok guru
        $guruConflict = Schedule::with(['user', 'subject', 'classGroup'])->where('user_id', $request->user_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('jam_mulai', '>=', $request->jam_mulai)
                        ->where('jam_mulai', '<', $request->jam_selesai);
                })->orWhere(function ($q) use ($request) {
                    $q->where('jam_selesai', '>', $request->jam_mulai)
                        ->where('jam_selesai', '<=', $request->jam_selesai);
                })->orWhere(function ($q) use ($request) {
                    $q->where('jam_mulai', '<=', $request->jam_mulai)
                        ->where('jam_selesai', '>=', $request->jam_selesai);
                });
            })
            ->first();

        // Cek bentrok kelas
        $kelasConflict = Schedule::with(['user', 'subject', 'classGroup'])->where('class_group_id', $request->class_group_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('jam_mulai', '>=', $request->jam_mulai)
                        ->where('jam_mulai', '<', $request->jam_selesai);
                })->orWhere(function ($q) use ($request) {
                    $q->where('jam_selesai', '>', $request->jam_mulai)
                        ->where('jam_selesai', '<=', $request->jam_selesai);
                })->orWhere(function ($q) use ($request) {
                    $q->where('jam_mulai', '<=', $request->jam_mulai)
                        ->where('jam_selesai', '>=', $request->jam_selesai);
                });
            })
            ->first();

        if ($guruConflict || $kelasConflict) {
            $conflictingSchedule = $guruConflict ?: $kelasConflict;
            $detailBentrok = ' dengan jadwal ' . $conflictingSchedule->subject->nama_mapel . ' oleh ' . $conflictingSchedule->user->name . ' di kelas ' . $conflictingSchedule->classGroup->nama_kelas . ' pada jam ' . date('H:i', strtotime($conflictingSchedule->jam_mulai)) . ' - ' . date('H:i', strtotime($conflictingSchedule->jam_selesai));

            return redirect()->back()->withInput()->withErrors([
                'jadwal' => ($guruConflict ? 'Jadwal guru bentrok' : 'Jadwal kelas bentrok') . $detailBentrok,
            ]);
        }

        $classGroup = ClassGroup::findOrFail($request->class_group_id);

        $schedule->update(array_merge($validated, [
            'academic_year_id' => $classGroup->academic_year_id,
        ]));

        return redirect()->route('guru.schedule.show-by-teacher', $validated['user_id'])->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $userId = $schedule->user_id;
        $schedule->delete();

        return redirect()->route('guru.schedule.show-by-teacher', $userId)->with('success', 'Jadwal berhasil dihapus.');
    }

    public function absen(Schedule $schedule)
    {

        if ($schedule->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

        $classGroup = ClassGroup::with(['students' => function ($q) use ($tahunAktif) {
            $q->wherePivot('academic_year_id', $tahunAktif->id);
        }])->findOrFail($schedule->class_group_id);

        return view('guru.schedule.absen', compact('classGroup', 'schedule'));
    }

    public function submitAbsen(Request $request, $classGroupId)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'attendance' => 'required|array',
            'pertemuan' => 'required|integer|min:1',
            'materi' => 'required|nullable|string',
        ]);

        $scheduleId = $request->input('schedule_id');
        $tanggal = now()->toDateString();
        $jamMulai = $request->input('jam_mulai');
        $jamSelesai = $request->input('jam_selesai');
        $materi = $request->input('materi');
        $pertemuan = $request->input('pertemuan');

        $schedule = Schedule::with('subject')->findOrFail($request->schedule_id);

        $duplicatePertemuan = ScheduleSession::where('subject_id', $schedule->subject_id)
            ->where('class_group_id', $schedule->class_group_id)
            ->where('academic_year_id', $schedule->academic_year_id)
            ->where('meeting_no', $request->pertemuan)
            ->exists();

        if ($duplicatePertemuan) {
            return back()
                ->withInput()
                ->with('error', 'Pertemuan ke-' . $request->pertemuan . ' untuk mata pelajaran dan kelas ini sudah pernah diisi.');
        }

        $existingSession = ScheduleSession::where('schedule_id', $scheduleId)
            ->where('date', $tanggal)
            ->first();

        if ($existingSession) {
            return back()
                ->withInput()
                ->with('error', 'Sesi pertemuan untuk jadwal ini sudah dibuat hari ini.');
        }

        DB::beginTransaction();
        try {
            $session = ScheduleSession::create([
                'schedule_id' => $scheduleId,
                'subject_id' => $schedule->subject_id,
                'class_group_id' => $schedule->class_group_id,
                'academic_year_id' => $schedule->academic_year_id,
                'date' => $tanggal,
                'start_time' => $jamMulai,
                'end_time' => $jamSelesai,
                'meeting_no' => $pertemuan,
                'created_by' => Auth::id(),
                'status' => 'open',
            ]);

            foreach ($request->attendance as $studentId => $status) {
                Attendance::create([
                    'schedule_id' => $scheduleId,
                    'schedule_session_id' => $session->id,
                    'tanggal' => $tanggal,
                    'pertemuan' => $pertemuan,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'materi' => $materi,
                    'student_id' => $studentId,
                    'status' => $status,
                ]);
            }

            DB::commit();

            return redirect()->route('guru.schedule')->with('success', 'Absen berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan absensi.');
        }
    }

    private function buildScheduleGrid($schedules): array
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

        $slotRanges = [
            ['index' => 1, 'start' => '07:15', 'end' => '07:55'],
            ['index' => 2, 'start' => '07:55', 'end' => '08:35'],
            ['index' => 3, 'start' => '08:35', 'end' => '09:15'],
            ['index' => 4, 'start' => '09:15', 'end' => '09:55'],
            ['index' => 5, 'start' => '10:25', 'end' => '11:05'],
            ['index' => 6, 'start' => '11:05', 'end' => '11:45'],
            ['index' => 7, 'start' => '11:45', 'end' => '12:25'],
            ['index' => 8, 'start' => '12:25', 'end' => '13:05'],
        ];

        $toMinutes = function (string $time): int {
            $parts = explode(':', $time);
            return ((int) $parts[0] * 60) + (int) $parts[1];
        };

        $slotRanges = collect($slotRanges)->map(function ($slot) use ($toMinutes) {
            return array_merge($slot, [
                'start_minutes' => $toMinutes($slot['start']),
                'end_minutes' => $toMinutes($slot['end']),
            ]);
        })->values();

        $slotBuckets = [];
        $outsideSchedules = [];

        foreach ($schedules as $schedule) {
            $day = $schedule->hari;
            $startMinutes = $toMinutes(substr($schedule->jam_mulai, 0, 5));
            $endMinutes = $toMinutes(substr($schedule->jam_selesai, 0, 5));
            $matched = false;

            foreach ($slotRanges as $slot) {
                if ($day === 'Jumat' && $slot['index'] > 5) {
                    continue;
                }

                if ($startMinutes < $slot['end_minutes'] && $endMinutes > $slot['start_minutes']) {
                    $slotBuckets[$day][$slot['index']][] = $schedule;
                    $matched = true;
                }
            }

            if (!$matched) {
                $outsideSchedules[$day][] = $schedule;
            }
        }

        return compact('days', 'slotRanges', 'slotBuckets', 'outsideSchedules');
    }

}
