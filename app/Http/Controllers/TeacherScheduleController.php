<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\ClassGroup;


class TeacherScheduleController extends Controller
{
    public function index()
    {
        $tahunAktif = \App\Models\AcademicYear::where('is_active', true)->first();

        $schedules = Schedule::with(['classGroup', 'subject'])
            ->where('user_id', Auth::id())
            ->where('academic_year_id', $tahunAktif?->id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.schedule.index', compact('schedules'));
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
