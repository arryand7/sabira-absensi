<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Imports\ScheduleImport;
use Maatwebsite\Excel\Facades\Excel;

class AdminScheduleController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->tahun_ajaran ?? $activeYear?->id;

        $teachers = User::where('role', 'guru')
            ->where('status', 'aktif')
            ->with('guru')
            ->orderBy('name')
            ->get();

        $classGroups = ClassGroup::when($selectedYear, fn($q) => $q->where('academic_year_id', $selectedYear))
            ->orderBy('nama_kelas')
            ->get();

        $subjects = Subject::orderBy('nama_mapel')->get();

        $schedules = Schedule::with(['subject', 'classGroup', 'user'])
            ->when($selectedYear, fn($q) => $q->where('academic_year_id', $selectedYear))
            ->when($request->guru_id, fn($q) => $q->where('user_id', $request->guru_id))
            ->when($request->class_group_id, fn($q) => $q->where('class_group_id', $request->class_group_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

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
            'days',
            'slotRanges',
            'slotBuckets',
            'outsideSchedules',
            'summary',
            'academicYears',
            'activeYear',
            'selectedYear'
        ));
    }

    public function showByTeacher($id)
    {
        $teacher = User::with('guru')->findOrFail($id);

        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

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
        $classGroups = ClassGroup::where('academic_year_id', $selectedYear)->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.create', compact(
            'teachers',
            'subjects',
            'classGroups',
            'selectedGuruId',
            'academicYears',
            'tahunAktif',
            'selectedYear'
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

        return redirect()->route('admin.schedules.show-by-teacher', $request->user_id)->with('success', 'Jadwal berhasil dibuat.');
    }


    public function edit(Schedule $schedule)
    {
        $teachers = User::where('role', 'guru')->get(); // ambil semua guru

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $subjects = Subject::all();
        $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get(); // semua kelas tahun aktif

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.edit', compact(
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

        return redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $userId = $schedule->user_id;
        $schedule->delete();

        return redirect()->route('admin.schedules.show-by-teacher', $userId)->with('success', 'Jadwal berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $import = new ScheduleImport();
        Excel::import($import, $request->file('file'));

        if ($import->successRows === [] && $import->failures === []) {
            return back()->withErrors(['file' => 'File tidak mengandung data valid atau formatnya salah.']);
        }

        $success = collect($import->successRows)->map(function ($row) {
            return is_array($row) ? json_encode($row) : (string)$row;
        })->toArray();

        $failures = collect($import->failures)->map(function ($row) {
            return is_array($row) ? json_encode($row) : (string)$row;
        })->toArray();

        return back()->with([
            'success' => $success,
            'errors_import' => $failures,
        ]);
    }



}
