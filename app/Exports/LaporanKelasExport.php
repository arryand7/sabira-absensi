<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class LaporanKelasExport implements FromArray
{
    protected $rows;
    protected $kelas;
    protected $tahun;
    protected $totalPertemuan;

    public function __construct(array $rows, string $kelas, string $tahun, int $totalPertemuan)
    {
        $this->rows = $rows;
        $this->kelas = $kelas;
        $this->tahun = $tahun;
        $this->totalPertemuan = $totalPertemuan;
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['Laporan Absensi Kelas'];
        $data[] = ['Kelas', $this->kelas];
        $data[] = ['Tahun Ajaran', $this->tahun];
        $data[] = ['Total Pertemuan', $this->totalPertemuan];
        $data[] = [];

        $data[] = ['No', 'Nama', 'NIS', 'H', 'I', 'S', 'A'];

        $index = 1;
        foreach ($this->rows as $row) {
            $data[] = [
                $index++,
                $row['nama'],
                $row['nis'],
                $row['H'],
                $row['I'],
                $row['S'],
                $row['A'],
            ];
        }

        return $data;
    }
}
