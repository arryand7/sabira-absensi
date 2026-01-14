<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\ScheduleSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Ambil tahun ajaran aktif
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        // Handle jika tidak ada tahun ajaran aktif
        if (!$activeYear) {
            return back()->withErrors(['Tahun ajaran aktif tidak ditemukan.']);
        }

        $query = ScheduleSession::with([
            'schedule.subject',
            'schedule.classGroup',
            'attendances:id,schedule_session_id,materi',
        ])
            ->whereHas('schedule', function ($q) use ($user, $activeYear) {
                $q->where('user_id', $user->id)
                    ->where('academic_year_id', $activeYear->id);
            });

        // Filter berdasarkan request (kelas / mapel)
        if ($request->filled('kelas')) {
            $query->whereHas('schedule.classGroup', function ($q) use ($request) {
                $q->where('nama_kelas', $request->kelas);
            });
        }

        if ($request->filled('mapel')) {
            $query->whereHas('schedule.subject', function ($q) use ($request) {
                $q->where('nama_mapel', $request->mapel);
            });
        }

        $sessions = $query->orderBy('date', 'desc')->get();

        // List kelas & mapel hanya dari jadwal di tahun ajaran aktif
        $kelasList = \App\Models\Schedule::where('user_id', $user->id)
            ->where('academic_year_id', $activeYear->id)
            ->with('classGroup')
            ->get()
            ->pluck('classGroup.nama_kelas')
            ->unique()
            ->sort();

        $mapelList = \App\Models\Schedule::where('user_id', $user->id)
            ->where('academic_year_id', $activeYear->id)
            ->with('subject')
            ->get()
            ->pluck('subject.nama_mapel')
            ->unique()
            ->sort();

        return view('guru.history.index', compact('sessions', 'kelasList', 'mapelList'));
    }

    public function detail($scheduleId, $pertemuan)
    {
        $schedule = $this->resolveOwnedSchedule($scheduleId);
        $session = ScheduleSession::where('schedule_id', $schedule->id)
            ->where('meeting_no', $pertemuan)
            ->first();

        $absensiQuery = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup']);
        if ($session) {
            $absensiQuery->where('schedule_session_id', $session->id);
        } else {
            $absensiQuery->where('schedule_id', $schedule->id)
                ->where('pertemuan', $pertemuan);
        }

        $absensi = $absensiQuery->get();

        return view('guru.history.detail', compact('absensi'));
    }

    public function edit($scheduleId, $pertemuan)
    {
        $schedule = $this->resolveOwnedSchedule($scheduleId);
        $session = ScheduleSession::where('schedule_id', $schedule->id)
            ->where('meeting_no', $pertemuan)
            ->first();

        $absensiQuery = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup']);
        if ($session) {
            $absensiQuery->where('schedule_session_id', $session->id);
        } else {
            $absensiQuery->where('schedule_id', $schedule->id)
                ->where('pertemuan', $pertemuan);
        }

        $absensi = $absensiQuery->get();

        return view('guru.history.edit', compact('absensi'));
    }

    public function update(Request $request, $scheduleId, $pertemuan)
    {
        $schedule = $this->resolveOwnedSchedule($scheduleId);
        $session = ScheduleSession::where('schedule_id', $schedule->id)
            ->where('meeting_no', $pertemuan)
            ->first();

        $data = $request->validate([
            'materi' => 'required|string',
            'pertemuan' => 'required|integer|min:1',
            'attendance' => 'required|array',
        ]);

        $newPertemuan = $data['pertemuan'];

        if ($newPertemuan != $pertemuan) {
            $exists = ScheduleSession::where('subject_id', $schedule->subject_id)
                ->where('class_group_id', $schedule->class_group_id)
                ->where('academic_year_id', $schedule->academic_year_id)
                ->where('meeting_no', $newPertemuan)
                ->exists();

            if ($exists) {
                return back()->withErrors(['pertemuan' => 'Nomor pertemuan sudah ada untuk mata pelajaran dan kelas ini di tahun ajaran tersebut.'])->withInput();
            }

            if ($session) {
                $session->update(['meeting_no' => $newPertemuan]);
            }
        }

        // Update materi dan pertemuan (jika berubah)
        foreach ($data['attendance'] as $studentId => $status) {
            $attendanceQuery = Attendance::where('student_id', $studentId);
            if ($session) {
                $attendanceQuery->where('schedule_session_id', $session->id);
            } else {
                $attendanceQuery->where('schedule_id', $scheduleId)
                    ->where('pertemuan', $pertemuan);
            }

            $attendanceQuery->update([
                'status' => $status,
                'materi' => $data['materi'],
                'pertemuan' => $newPertemuan,
            ]);
        }

        return redirect()->route('guru.history.index')->with('success', 'Absensi berhasil diperbarui.');
    }

    private function resolveOwnedSchedule($scheduleId): Schedule
    {
        $user = Auth::user();

        $schedule = Schedule::where('id', $scheduleId)
            ->where('user_id', $user->id)
            ->first();

        if (!$schedule) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        return $schedule;
    }
}
