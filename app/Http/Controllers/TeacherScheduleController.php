<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TeacherScheduleController extends Controller
{
    public function index()
    {
        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

        $user = Auth::user();
        $guru = $user->guru;

        // It's good practice to ensure the user has a 'guru' profile.
        if (!$guru) {
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

        return view('guru.schedule.index', compact('schedules','guru'));
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

        return redirect()->route('guru.schedules.show-by-teacher', $request->user_id)->with('success', 'Jadwal berhasil dibuat.');
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

        $duplicatePertemuan = Attendance::whereHas('schedule', function ($query) use ($schedule) {
                $query->where('subject_id', $schedule->subject_id)
                    ->where('class_group_id', $schedule->class_group_id);
            })
            ->where('pertemuan', $request->pertemuan)
            ->exists();

        if ($duplicatePertemuan) {
            return back()
                ->withInput()
                ->with('error', 'Pertemuan ke-' . $request->pertemuan . ' untuk mata pelajaran dan kelas ini sudah pernah diisi.');
        }

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $studentId => $status) {
                Attendance::create([
                    'schedule_id' => $scheduleId,
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

}
