<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Attendance::with(['schedule.subject', 'schedule.classGroup'])
            ->whereHas('schedule', fn($q) => $q->where('user_id', $user->id))
            ->select('schedule_id', 'pertemuan', 'tanggal', 'materi')
            ->groupBy('schedule_id', 'pertemuan', 'tanggal', 'materi');

        // Filter berdasarkan request
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

        $attendances = $query->orderBy('tanggal', 'desc')->get();

        // Hanya ambil mapel & kelas sesuai jenis guru
        $mapelList = \App\Models\Subject::where('jenis_mapel', $user->guru?->jenis)
        ->orderBy('nama_mapel')
        ->pluck('nama_mapel')
        ->unique();

        $kelasList = \App\Models\Schedule::where('user_id', $user->id)
        ->whereHas('classGroup', function ($q) use ($user) {
            $q->where('jenis_kelas', $user->guru?->jenis);
        })
        ->with('classGroup')
        ->get()
        ->pluck('classGroup.nama_kelas')
        ->unique()
        ->sort();


        return view('guru.history.index', compact('attendances', 'kelasList', 'mapelList'));
    }



    public function detail($scheduleId, $pertemuan)
    {
        $absensi = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup'])
            ->where('schedule_id', $scheduleId)
            ->where('pertemuan', $pertemuan)
            ->get();

        return view('guru.history.detail', compact('absensi'));
    }

    public function edit($scheduleId, $pertemuan)
    {
        $absensi = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup'])
            ->where('schedule_id', $scheduleId)
            ->where('pertemuan', $pertemuan)
            ->get();

        return view('guru.history.edit', compact('absensi'));
    }

    public function update(Request $request, $scheduleId, $pertemuan)
    {
        $data = $request->validate([
            'materi' => 'required|string',
            'pertemuan' => 'required|integer|min:1',
            'attendance' => 'required|array',
        ]);

        $newPertemuan = $data['pertemuan'];

        // Cek jika pertemuan baru sudah ada (kecuali pertemuan lama yang sedang diedit)
        $exists = \App\Models\Attendance::where('schedule_id', $scheduleId)
            ->where('pertemuan', $newPertemuan)
            ->where('pertemuan', '!=', $pertemuan)
            ->exists();

        if ($exists) {
            return back()->withErrors(['pertemuan' => 'Nomor pertemuan sudah ada untuk jadwal ini. Gunakan nomor lain.'])->withInput();
        }

        // Update materi dan pertemuan (jika berubah)
        foreach ($data['attendance'] as $studentId => $status) {
            \App\Models\Attendance::where('schedule_id', $scheduleId)
                ->where('pertemuan', $pertemuan) // pertemuan lama
                ->where('student_id', $studentId)
                ->update([
                    'status' => $status,
                    'materi' => $data['materi'],
                    'pertemuan' => $newPertemuan,
                ]);
        }

        return redirect()->route('guru.history.index')->with('success', 'Absensi berhasil diperbarui.');
    }
}
