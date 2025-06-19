<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AdminScheduleController extends Controller
{
    public function index()
    {
        $teachers = User::where('role', 'guru')
            ->where('status', 'aktif')
            ->with('guru')
            ->get();
        return view('admin.schedules.index', compact('teachers'));
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

        $selectedGuru = $selectedGuruId ? User::with('guru')->find($selectedGuruId) : null;
        $jenisGuru = $selectedGuru?->guru?->jenis;

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        if ($jenisGuru === 'muadalah') {
            $subjects = Subject::where('jenis_mapel', 'muadalah')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'muadalah')
                ->where('academic_year_id', $tahunAktif?->id)
                ->get();
        } elseif ($jenisGuru === 'akademik') {
            $subjects = Subject::where('jenis_mapel', 'akademik')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'akademik')
                ->where('academic_year_id', $tahunAktif?->id)
                ->get();
        } else {
            $subjects = Subject::all();
            $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get();
        }

        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();
        return view('admin.schedules.create', compact(
            'teachers', 'subjects', 'classGroups', 'selectedGuruId', 'academicYears', 'tahunAktif'
        ));
    }

    public function store(Request $request)
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
        $guruConflict = Schedule::where('user_id', $request->user_id)
            ->where('hari', $request->hari)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_mulai)
                          ->where('jam_selesai', '>', $request->jam_selesai);
                    });
            })
            ->exists();

        // Cek bentrok kelas
        $kelasConflict = Schedule::where('class_group_id', $request->class_group_id)
            ->where('hari', $request->hari)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_mulai)
                          ->where('jam_selesai', '>', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($guruConflict || $kelasConflict) {
            return redirect()->back()->withInput()->withErrors([
                'jadwal' => $guruConflict
                    ? 'Jadwal bentrok: Guru sudah mengajar di jam tersebut.'
                    : 'Jadwal bentrok: Kelas sudah memiliki pelajaran di jam tersebut.',
            ]);
        }

        $classGroup = ClassGroup::findOrFail($request->class_group_id);

        $schedule = new Schedule($validated);
        $schedule->academic_year_id = $classGroup->academic_year_id; // otomatis ambil dari classGroup
        $schedule->save();

        return redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])->with('success', 'Jadwal berhasil dibuat.');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $teachers = User::where('role', 'guru')->get();
        $selectedGuru = User::with('guru')->find($schedule->user_id);
        $jenisGuru = $selectedGuru?->guru?->jenis;
        $tahunAktif = AcademicYear::where('is_active', true)->first();

        if ($jenisGuru === 'muadalah') {
            $subjects = Subject::where('jenis_mapel', 'muadalah')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'muadalah')
                ->where('academic_year_id', $tahunAktif?->id)
                ->get();
        } elseif ($jenisGuru === 'akademik') {
            $subjects = Subject::where('jenis_mapel', 'akademik')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'akademik')
                ->where('academic_year_id', $tahunAktif?->id)
                ->get();
        } else {
            $subjects = Subject::all();
            $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get();
        }

        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();

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
        $guruConflict = Schedule::where('user_id', $request->user_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_mulai)
                          ->where('jam_selesai', '>', $request->jam_selesai);
                    });
            })
            ->exists();

        // Cek bentrok kelas
        $kelasConflict = Schedule::where('class_group_id', $request->class_group_id)
            ->where('hari', $request->hari)
            ->where('id', '!=', $schedule->id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_mulai)
                          ->where('jam_selesai', '>', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($guruConflict || $kelasConflict) {
            return redirect()->back()->withInput()->withErrors([
                'jadwal' => $guruConflict
                    ? 'Jadwal bentrok: Guru sudah mengajar di jam tersebut.'
                    : 'Jadwal bentrok: Kelas sudah memiliki pelajaran di jam tersebut.',
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
}
