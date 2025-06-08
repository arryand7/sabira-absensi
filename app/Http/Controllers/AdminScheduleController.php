<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use App\Models\ClassGroup;
use App\Models\Subject;
use Illuminate\Http\Request;

class AdminScheduleController extends Controller
{
    public function index()
    {
        $teachers = \App\Models\User::where('role', 'guru')->with('guru')->get();
        return view('admin.schedules.index', compact('teachers'));
    }

    public function showByTeacher($id)
    {
        $teacher = \App\Models\User::with('guru')->findOrFail($id);
        $schedules = Schedule::with(['subject', 'classGroup'])
            ->where('user_id', $id)
            ->get();

        return view('admin.schedules.show', compact('teacher', 'schedules'));
    }

    public function create(Request $request)
    {
        $teachers = User::where('role', 'guru')->get();
        $selectedGuruId = $request->guru_id;

        $selectedGuru = $selectedGuruId ? User::with('guru')->find($selectedGuruId) : null;
        $jenisGuru = $selectedGuru?->guru?->jenis;

        // Filter berdasarkan jenis guru
        if ($jenisGuru === 'muadalah') {
            $subjects = Subject::where('jenis_mapel', 'muadalah')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'muadalah')->get();
        } elseif ($jenisGuru === 'akademik') {
            $subjects = Subject::where('jenis_mapel', 'akademik')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'akademik')->get();
        } else {
            // Jika tidak ditemukan jenis, tampilkan semua
            $subjects = Subject::all();
            $classGroups = ClassGroup::all();
        }

        return view('admin.schedules.create', compact(
            'teachers', 'subjects', 'classGroups', 'selectedGuruId'
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

        Schedule::create($validated);

        return redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])->with('success', 'Jadwal berhasil dibuat.');
    }

    public function edit($id)
    {
        $schedule = Schedule::findOrFail($id);
        $teachers = User::where('role', 'guru')->get();
        $selectedGuru = User::with('guru')->find($schedule->user_id);
        $jenisGuru = $selectedGuru?->guru?->jenis;

        if ($jenisGuru === 'muadalah') {
            $subjects = Subject::where('jenis_mapel', 'muadalah')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'muadalah')->get();
        } elseif ($jenisGuru === 'akademik') {
            $subjects = Subject::where('jenis_mapel', 'akademik')->get();
            $classGroups = ClassGroup::where('jenis_kelas', 'akademik')->get();
        } else {
            $subjects = Subject::all();
            $classGroups = ClassGroup::all();
        }

        return view('admin.schedules.edit', compact(
            'schedule', 'teachers', 'subjects', 'classGroups'
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

        $schedule->update($validated);

        return redirect()->route('admin.schedules.show-by-teacher', $validated['user_id'])->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Schedule $schedule)
    {
        $userId = $schedule->user_id; // Simpan dulu ID gurunya
        $schedule->delete();

        return redirect()->route('admin.schedules.show-by-teacher', $userId)->with('success', 'Jadwal berhasil dihapus.');
    }

}
