<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPertemuanExport;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ScheduleSession;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class LaporanPertemuanController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildReportData($request);
        $sessions = $data['sessions'];
        $summary = $data['summary'];
        $activeYear = $data['activeYear'];
        $selectedYear = $data['selectedYear'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $teachers = User::where('role', 'guru')->where('status', 'aktif')->orderBy('name')->get();
        $classGroups = ClassGroup::orderBy('nama_kelas')->get();
        $subjects = Subject::orderBy('nama_mapel')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.laporan.pertemuan.index', compact(
            'sessions',
            'summary',
            'teachers',
            'classGroups',
            'subjects',
            'academicYears',
            'activeYear',
            'selectedYear',
            'startDate',
            'endDate'
        ));
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildReportData($request);

        $pdf = PDF::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.laporan.pertemuan.pdf', [
            'sessions' => $data['sessions'],
            'summary' => $data['summary'],
            'filters' => $data['filters'],
        ]);

        return $pdf->stream('laporan_pertemuan_guru.pdf');
    }

    public function exportExcel(Request $request)
    {
        $data = $this->buildReportData($request);

        return Excel::download(
            new LaporanPertemuanExport($data['sessions'], $data['summary'], $data['filters']),
            'laporan_pertemuan_guru.xlsx'
        );
    }

    private function buildReportData(Request $request): array
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $selectedYear = $request->tahun_ajaran ?? $activeYear?->id;

        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

        $query = ScheduleSession::with([
                'schedule.user',
                'schedule.subject',
                'schedule.classGroup',
            ])
            ->when($selectedYear, fn($q) => $q->where('academic_year_id', $selectedYear))
            ->when($request->guru_id, fn($q) => $q->whereHas('schedule', fn($q2) => $q2->where('user_id', $request->guru_id)))
            ->when($request->kelas_id, fn($q) => $q->where('class_group_id', $request->kelas_id))
            ->when($request->mapel_id, fn($q) => $q->where('subject_id', $request->mapel_id))
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->withCount([
                'attendances as hadir_count' => fn($q) => $q->where('status', 'hadir'),
                'attendances as izin_count' => fn($q) => $q->where('status', 'izin'),
                'attendances as sakit_count' => fn($q) => $q->where('status', 'sakit'),
                'attendances as alpa_count' => fn($q) => $q->where('status', 'alpa'),
                'attendances as total_count',
            ])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc');

        $sessions = $query->get();

        $summary = [
            'total_sessions' => $sessions->count(),
            'hadir' => $sessions->sum('hadir_count'),
            'izin' => $sessions->sum('izin_count'),
            'sakit' => $sessions->sum('sakit_count'),
            'alpa' => $sessions->sum('alpa_count'),
        ];

        $filters = [
            'tahun_ajaran' => $selectedYear,
            'guru_id' => $request->guru_id,
            'kelas_id' => $request->kelas_id,
            'mapel_id' => $request->mapel_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return [
            'sessions' => $sessions,
            'summary' => $summary,
            'filters' => $filters,
            'activeYear' => $activeYear,
            'selectedYear' => $selectedYear,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
