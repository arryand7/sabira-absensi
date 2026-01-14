<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;

class LaporanPertemuanExport implements FromArray
{
    protected $sessions;
    protected $summary;
    protected $filters;

    public function __construct($sessions, array $summary, array $filters)
    {
        $this->sessions = $sessions;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['Laporan Pertemuan Guru'];
        $data[] = ['Periode', $this->filters['start_date'] . ' s.d ' . $this->filters['end_date']];
        $data[] = ['Total Pertemuan', $this->summary['total_sessions']];
        $data[] = ['Total Hadir', $this->summary['hadir']];
        $data[] = ['Total Izin', $this->summary['izin']];
        $data[] = ['Total Sakit', $this->summary['sakit']];
        $data[] = ['Total Alpa', $this->summary['alpa']];
        $data[] = [];

        $data[] = ['Tanggal', 'Pertemuan', 'Guru', 'Mata Pelajaran', 'Kelas', 'Jam', 'Hadir', 'Izin', 'Sakit', 'Alpa'];

        foreach ($this->sessions as $session) {
            $data[] = [
                $session->date,
                $session->meeting_no ?? '-',
                $session->schedule->user->name ?? '-',
                $session->schedule->subject->nama_mapel ?? '-',
                $session->schedule->classGroup->nama_kelas ?? '-',
                $session->start_time . ' - ' . $session->end_time,
                $session->hadir_count,
                $session->izin_count,
                $session->sakit_count,
                $session->alpa_count,
            ];
        }

        return $data;
    }
}
