<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MapelExport;

use PDF;

class LaporanMuridController extends Controller
{
    public function dashboard()
    {
        return view('admin.laporan.murid.dashboard');
    }

    public function index(Request $request)
    {
        $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();

        $academicYears = \App\Models\AcademicYear::orderByDesc('start_date')->get();

        // Ambil semua kelas, tidak harus difilter dulu
        $kelasList = ClassGroup::pluck('nama_kelas')->sort()->unique();

        // Ambil semua murid dengan filter tahun ajaran dan kelas
        $students = Student::whereHas('classGroups', function ($query) use ($request) {
            if ($request->kelas) {
                $query->where('nama_kelas', $request->kelas);
            }

            if ($request->tahun_ajaran) {
                $query->whereRaw('class_group_student.academic_year_id = ?', [$request->tahun_ajaran]);
            }
        })
        ->with(['classGroups' => function ($query) use ($request) {
            if ($request->tahun_ajaran) {
                $query->whereRaw('class_group_student.academic_year_id = ?', [$request->tahun_ajaran]);
            }
        }])
        ->orderBy('nama_lengkap')
        ->get()
        ->map(function ($student) use ($activeYear) {
            $kelasAktif = $student->classGroups->filter(function ($group) use ($activeYear) {
                return $group->pivot->academic_year_id == ($activeYear->id ?? null);
            })->pluck('nama_kelas')->join(', ');

            return (object)[
                'id' => $student->id,
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
                'kelas' => $kelasAktif,
            ];
        });

        return view('admin.laporan.murid.index', compact('students', 'kelasList', 'academicYears', 'activeYear'));
    }


    public function download(Student $student, Request $request)
    {
        $tahunAjaranId = \App\Models\AcademicYear::where('is_active', true)->value('id');

        $grouped = Attendance::with(['schedule.subject', 'schedule.classGroup'])
            ->where('student_id', $student->id)
            ->whereHas('schedule.classGroup', function ($q) use ($tahunAjaranId) {
                $q->when($tahunAjaranId, function ($subQ) use ($tahunAjaranId) {
                    $subQ->where('academic_year_id', $tahunAjaranId);
                });
            })
            ->get()
            ->filter(fn($absen) => $absen->schedule && $absen->schedule->subject)
            ->groupBy(fn($absen) => $absen->schedule->subject->jenis_mapel);

        $rekap = [];

        foreach ($grouped as $jenis => $absensiPerJenis) {
            foreach ($absensiPerJenis->groupBy(fn($a) => $a->schedule->subject->nama_mapel) as $mapel => $absensi) {
                $rekap[$jenis][$mapel] = [
                    'H' => $absensi->where('status', 'hadir')->count(),
                    'I' => $absensi->where('status', 'izin')->count(),
                    'S' => $absensi->where('status', 'sakit')->count(),
                    'A' => $absensi->where('status', 'alpa')->count(),
                ];
            }
        }

        $pdf = PDF::loadView('admin.laporan.murid.pdf', [
            'student' => $student,
            'rekap' => $rekap,
        ]);

        return $pdf->stream("laporan_absensi_{$student->nama_lengkap}.pdf");
    }

    public function laporanMapel(Request $request)
    {
        $rekapMapel = null;

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $tahunAktif = AcademicYear::where('is_active', true)->first();

        $selectedTahun = $request->tahun_ajaran; // hanya dari request

        if ($request->filled(['jenis', 'kelas', 'mapel', 'tahun_ajaran'])) {
            $absensi = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup'])
                ->whereHas('schedule.subject', fn($q) =>
                    $q->where('jenis_mapel', $request->jenis)
                    ->where('nama_mapel', $request->mapel)
                )
                ->whereHas('schedule.classGroup', fn($q) =>
                    $q->where('nama_kelas', $request->kelas)
                    ->where('jenis_kelas', $request->jenis)
                )
                ->whereHas('schedule.academicYear', fn($q) =>
                    $q->where('id', $selectedTahun)
                )
                ->get();

            $rekapMapel = $absensi->groupBy('student_id')->map(function ($item) {
                $first = $item->first();
                return [
                    'nama' => $first->student->nama_lengkap ?? '-',
                    'nis' => $first->student->nis ?? '-',
                    'kelas' => $first->schedule->classGroup->nama_kelas ?? '-',
                    'H' => $item->where('status', 'hadir')->count(),
                    'I' => $item->where('status', 'izin')->count(),
                    'S' => $item->where('status', 'sakit')->count(),
                    'A' => $item->where('status', 'alpa')->count(),
                ];
            });
        }

        $kelasFormal = ClassGroup::where('jenis_kelas', 'formal')
            ->pluck('nama_kelas')->unique()->sort()->values();

        $kelasMuadalah = ClassGroup::where('jenis_kelas', 'muadalah')
            ->pluck('nama_kelas')->unique()->sort()->values();

        $mapelFormal = Subject::where('jenis_mapel', 'formal')
            ->whereHas('schedules')
            ->pluck('nama_mapel')->unique()->sort()->values();

        $mapelMuadalah = Subject::where('jenis_mapel', 'muadalah')
            ->whereHas('schedules')
            ->pluck('nama_mapel')->unique()->sort()->values();

        return view('admin.laporan.murid.mapel', compact(
            'rekapMapel',
            'kelasFormal',
            'kelasMuadalah',
            'mapelFormal',
            'mapelMuadalah',
            'academicYears',
            'tahunAktif'
        ));
    }


    public function downloadMapel(Request $request)
    {
        $absensi = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup'])
            ->whereHas('schedule.subject', fn($q) =>
                $q->where('jenis_mapel', $request->jenis)
                ->where('nama_mapel', $request->mapel)
            )
            ->whereHas('schedule.classGroup', fn($q) =>
                $q->where('nama_kelas', $request->kelas)
                ->where('jenis_kelas', $request->jenis)
            )
            ->whereHas('schedule.academicYear', fn($q) =>
                $q->where('id', $request->tahun_ajaran)
            )
            ->get();

        $totalPertemuan = $absensi
            ->unique(fn($item) => $item->schedule_id . '-' . $item->pertemuan)
            ->count();

        $rekapMapel = $absensi->groupBy('student_id')->map(function ($items) {
            $first = $items->first();
            return [
                'nama' => $first->student->nama_lengkap ?? '-',
                'nis' => $first->student->nis ?? '-',
                'H' => $items->where('status', 'hadir')->count(),
                'I' => $items->where('status', 'izin')->count(),
                'S' => $items->where('status', 'sakit')->count(),
                'A' => $items->where('status', 'alpa')->count(),
            ];
        });

        $kelasLabel = $request->kelas ?? 'Semua';
        $tahun = AcademicYear::find($request->tahun_ajaran)?->name ?? 'Tanpa Tahun';

        $pdf = PDF::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])
        ->loadView('admin.laporan.murid.pdf-mapel', [
            'rekapMapel' => $rekapMapel,
            'kelas' => $kelasLabel,
            'mapel' => $request->mapel,
            'tahun' => $tahun,
            'totalPertemuan' => $totalPertemuan,
        ]);

        return $pdf->stream("laporan_mapel_{$request->kelas}_{$request->mapel}.pdf");
    }

    public function exportExcel(Request $request)
    {
        $absensi = Attendance::with(['student', 'schedule.subject', 'schedule.classGroup'])
            ->whereHas('schedule.subject', fn($q) =>
                $q->where('jenis_mapel', $request->jenis)
                    ->where('nama_mapel', $request->mapel)
            )
            ->whereHas('schedule.classGroup', fn($q) =>
                $q->where('nama_kelas', $request->kelas)
                    ->where('jenis_kelas', $request->jenis)
            )
            ->whereHas('schedule.academicYear', fn($q) =>
                $q->where('id', $request->tahun_ajaran)
            )
            ->get();

        $totalPertemuan = $absensi
            ->unique(fn($item) => $item->schedule_id . '-' . $item->pertemuan)
            ->count();

        $rekapMapel = $absensi->groupBy('student_id')->map(function ($items) {
            $first = $items->first();
            return [
                'nama' => $first->student->nama_lengkap ?? '-',
                'nis' => $first->student->nis ?? '-',
                'H' => $items->where('status', 'hadir')->count(),
                'I' => $items->where('status', 'izin')->count(),
                'S' => $items->where('status', 'sakit')->count(),
                'A' => $items->where('status', 'alpa')->count(),
            ];
        });

        $kelas = $request->kelas ?? 'Semua';
        $mapel = $request->mapel ?? 'Semua';
        $tahun = \App\Models\AcademicYear::find($request->tahun_ajaran)?->name ?? 'Tanpa Tahun';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapMapelExport($rekapMapel, $kelas, $mapel, $tahun, $totalPertemuan),
            'rekap-absensi.xlsx'
        );
    }

}
