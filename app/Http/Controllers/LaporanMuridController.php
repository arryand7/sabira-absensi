<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\ClassGroup;
use App\Models\Subject;
use App\Models\AcademicYear;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKelasExport;
use App\Exports\LaporanSiswaExport;

use PDF;

class LaporanMuridController extends Controller
{
    public function dashboard()
    {
        return view('admin.laporan.murid.dashboard');
    }

    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->tahun_ajaran ?? $activeYear?->id;

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $kelasList = ClassGroup::when($selectedYear, fn($q) => $q->where('academic_year_id', $selectedYear))
            ->pluck('nama_kelas')
            ->sort()
            ->unique();

        // Ambil semua murid dengan filter tahun ajaran dan kelas
        $students = Student::whereHas('classGroups', function ($query) use ($request, $selectedYear) {
            if ($request->kelas) {
                $query->where('nama_kelas', $request->kelas);
            }

            if ($selectedYear) {
                $query->whereRaw('class_group_student.academic_year_id = ?', [$selectedYear]);
            }
        })
        ->with(['classGroups' => function ($query) use ($selectedYear) {
            if ($selectedYear) {
                $query->whereRaw('class_group_student.academic_year_id = ?', [$selectedYear]);
            }
        }])
        ->orderBy('nama_lengkap')
        ->get()
        ->map(function ($student) use ($selectedYear) {
            $groups = $student->classGroups;
            if ($selectedYear) {
                $groups = $groups->filter(function ($group) use ($selectedYear) {
                    return (int) $group->pivot->academic_year_id === (int) $selectedYear;
                });
            }

            $kelasAktif = $groups->pluck('nama_kelas')->join(', ');

            return (object)[
                'id' => $student->id,
                'nama_lengkap' => $student->nama_lengkap,
                'nis' => $student->nis,
                'kelas' => $kelasAktif,
            ];
        });

        return view('admin.laporan.murid.index', compact('students', 'kelasList', 'academicYears', 'activeYear', 'selectedYear'));
    }


    public function download(Student $student, Request $request)
    {
        $tahunAjaranId = $request->tahun_ajaran ?? AcademicYear::where('is_active', true)->value('id');
        $tahunLabel = AcademicYear::find($tahunAjaranId)?->name ?? 'Semua Tahun';
        $rekap = $this->buildStudentRekap($student, $tahunAjaranId);

        $pdf = PDF::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.laporan.murid.pdf', [
            'student' => $student,
            'rekap' => $rekap,
            'tahun' => $tahunLabel,
        ]);

        return $pdf->stream("laporan_absensi_{$student->nama_lengkap}.pdf");
    }

    public function exportStudentExcel(Student $student, Request $request)
    {
        $tahunAjaranId = $request->tahun_ajaran ?? AcademicYear::where('is_active', true)->value('id');
        $tahunLabel = AcademicYear::find($tahunAjaranId)?->name ?? 'Semua Tahun';
        $rekap = $this->buildStudentRekap($student, $tahunAjaranId);

        $filename = 'laporan_absensi_' . str()->slug($student->nama_lengkap) . '.xlsx';

        return Excel::download(
            new LaporanSiswaExport($rekap, $student->nama_lengkap, $student->nis, $tahunLabel),
            $filename
        );
    }

    public function exportKelasPdf(Request $request)
    {
        if (!$request->kelas) {
            return redirect()->back()->with('error', 'Pilih kelas terlebih dahulu.');
        }

        $tahunAjaranId = $request->tahun_ajaran ?? AcademicYear::where('is_active', true)->value('id');
        $tahunLabel = AcademicYear::find($tahunAjaranId)?->name ?? 'Semua Tahun';

        [$classGroup, $rows, $totalPertemuan] = $this->buildClassRekap($request->kelas, $tahunAjaranId);

        if (!$classGroup) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan untuk tahun ajaran tersebut.');
        }

        $pdf = PDF::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.laporan.murid.pdf-kelas', [
            'kelas' => $classGroup->nama_kelas,
            'tahun' => $tahunLabel,
            'totalPertemuan' => $totalPertemuan,
            'rows' => $rows,
        ]);

        return $pdf->stream("laporan_absensi_kelas_{$classGroup->nama_kelas}.pdf");
    }

    public function exportKelasExcel(Request $request)
    {
        if (!$request->kelas) {
            return redirect()->back()->with('error', 'Pilih kelas terlebih dahulu.');
        }

        $tahunAjaranId = $request->tahun_ajaran ?? AcademicYear::where('is_active', true)->value('id');
        $tahunLabel = AcademicYear::find($tahunAjaranId)?->name ?? 'Semua Tahun';

        [$classGroup, $rows, $totalPertemuan] = $this->buildClassRekap($request->kelas, $tahunAjaranId);

        if (!$classGroup) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan untuk tahun ajaran tersebut.');
        }

        $filename = 'laporan_absensi_kelas_' . str()->slug($classGroup->nama_kelas) . '.xlsx';

        return Excel::download(
            new LaporanKelasExport($rows, $classGroup->nama_kelas, $tahunLabel, $totalPertemuan),
            $filename
        );
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
            ->unique(function ($item) {
                if ($item->schedule_session_id) {
                    return 's-' . $item->schedule_session_id;
                }
                return 'p-' . $item->schedule_id . '-' . $item->pertemuan;
            })
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
            ->unique(function ($item) {
                if ($item->schedule_session_id) {
                    return 's-' . $item->schedule_session_id;
                }
                return 'p-' . $item->schedule_id . '-' . $item->pertemuan;
            })
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
        $tahun = AcademicYear::find($request->tahun_ajaran)?->name ?? 'Tanpa Tahun';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RekapMapelExport($rekapMapel, $kelas, $mapel, $tahun, $totalPertemuan),
            'rekap-absensi.xlsx'
        );
    }

    private function buildStudentRekap(Student $student, ?int $tahunAjaranId): array
    {
        $grouped = Attendance::with(['schedule.subject', 'schedule.classGroup'])
            ->where('student_id', $student->id)
            ->when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->whereHas('schedule', function ($subQuery) use ($tahunAjaranId) {
                    $subQuery->where('academic_year_id', $tahunAjaranId);
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

        return $rekap;
    }

    private function buildClassRekap(string $kelas, ?int $tahunAjaranId): array
    {
        $classGroup = ClassGroup::where('nama_kelas', $kelas)
            ->when($tahunAjaranId, fn($q) => $q->where('academic_year_id', $tahunAjaranId))
            ->first();

        if (!$classGroup) {
            return [null, [], 0];
        }

        $absensi = Attendance::with(['student', 'schedule'])
            ->whereHas('schedule', function ($query) use ($classGroup, $tahunAjaranId) {
                $query->where('class_group_id', $classGroup->id);
                if ($tahunAjaranId) {
                    $query->where('academic_year_id', $tahunAjaranId);
                }
            })
            ->get();

        $totalPertemuan = $absensi
            ->unique(function ($item) {
                if ($item->schedule_session_id) {
                    return 's-' . $item->schedule_session_id;
                }
                return 'p-' . $item->schedule_id . '-' . $item->pertemuan;
            })
            ->count();

        $attendanceByStudent = $absensi->groupBy('student_id');

        $students = Student::whereHas('classGroups', function ($query) use ($classGroup, $tahunAjaranId) {
            $query->where('class_groups.id', $classGroup->id);
            if ($tahunAjaranId) {
                $query->whereRaw('class_group_student.academic_year_id = ?', [$tahunAjaranId]);
            }
        })
            ->orderBy('nama_lengkap')
            ->get();

        $rows = $students->map(function ($student) use ($attendanceByStudent) {
            $items = $attendanceByStudent->get($student->id, collect());
            return [
                'nama' => $student->nama_lengkap,
                'nis' => $student->nis,
                'H' => $items->where('status', 'hadir')->count(),
                'I' => $items->where('status', 'izin')->count(),
                'S' => $items->where('status', 'sakit')->count(),
                'A' => $items->where('status', 'alpa')->count(),
            ];
        })->values()->all();

        return [$classGroup, $rows, $totalPertemuan];
    }

}
