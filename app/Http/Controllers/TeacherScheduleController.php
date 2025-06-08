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
        $schedules = Schedule::with('classGroup')
            ->where('user_id', Auth::id())
            ->get();

        return view('guru.schedule.index', compact('schedules'));
    }

    public function absen($classGroupId)
    {
        $classGroup = ClassGroup::with('students')->findOrFail($classGroupId);
        $schedule = Schedule::where('class_group_id', $classGroupId)
                            ->where('user_id', Auth::id())
                            ->firstOrFail();

        return view('guru.schedule.absen', compact('classGroup', 'schedule'));
    }

    public function submitAbsen(Request $request, $classGroupId)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'attendance' => 'required|array',
            'pertemuan' => 'required|integer|min:1',
            'materi' => 'nullable|string',
        ]);

        $scheduleId = $request->input('schedule_id');
        $tanggal = now()->toDateString();
        $jamMulai = $request->input('jam_mulai');   // Dari hidden input
        $jamSelesai = $request->input('jam_selesai'); // Dari hidden input
        $materi = $request->input('materi');
        $pertemuan = $request->input('pertemuan');

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
