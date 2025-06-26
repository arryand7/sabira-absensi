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

        $tahunAktif = AcademicYear::where('is_active', true)->first();

        // Ambil semua mapel dan semua kelas (tanpa filter jenis)
        $subjects = Subject::all();
        $classGroups = ClassGroup::where('academic_year_id', $tahunAktif?->id)->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.schedules.create', compact(
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
            $guruConflict = Schedule::where('user_id', $request->user_id)
                ->where('hari', $detail['hari'])
                ->where(function ($query) use ($detail) {
                    $query->whereBetween('jam_mulai', [$detail['jam_mulai'], $detail['jam_selesai']])
                        ->orWhereBetween('jam_selesai', [$detail['jam_mulai'], $detail['jam_selesai']])
                        ->orWhere(function ($q) use ($detail) {
                            $q->where('jam_mulai', '<', $detail['jam_mulai'])
                            ->where('jam_selesai', '>', $detail['jam_selesai']);
                        });
                })->exists();

            $kelasConflict = Schedule::where('class_group_id', $detail['class_group_id'])
                ->where('hari', $detail['hari'])
                ->where(function ($query) use ($detail) {
                    $query->whereBetween('jam_mulai', [$detail['jam_mulai'], $detail['jam_selesai']])
                        ->orWhereBetween('jam_selesai', [$detail['jam_mulai'], $detail['jam_selesai']])
                        ->orWhere(function ($q) use ($detail) {
                            $q->where('jam_mulai', '<', $detail['jam_mulai'])
                            ->where('jam_selesai', '>', $detail['jam_selesai']);
                        });
                })->exists();

            if ($guruConflict || $kelasConflict) {
                return back()->withInput()->withErrors([
                    'jadwal' => $guruConflict
                        ? 'Jadwal bentrok: Guru sudah mengajar di jam tersebut.'
                        : 'Jadwal bentrok: Kelas sudah memiliki pelajaran di jam tersebut.',
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

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $import = new ScheduleImport();
        Excel::import($import, $request->file('file'));

        // Pastikan semua yang dikirim adalah string
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
