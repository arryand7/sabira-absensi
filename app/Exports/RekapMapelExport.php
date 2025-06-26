<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class RekapMapelExport implements FromCollection
{
    protected $rekapMapel;
    protected $kelas;
    protected $mapel;
    protected $tahun;

    public function __construct($rekapMapel, $kelas, $mapel, $tahun, $totalPertemuan)
    {
        $this->rekapMapel = $rekapMapel;
        $this->kelas = $kelas;
        $this->mapel = $mapel;
        $this->tahun = $tahun;
        $this->totalPertemuan = $totalPertemuan;
    }

    public function collection()
    {
        $data = collect([]);

        // Info laporan di atas tabel
        $data->push(['Laporan Absensi']);
        $data->push(['Mata Pelajaran:', $this->mapel]);
        $data->push(['Tahun Ajaran:', $this->tahun]);
        $data->push(['Kelas:', $this->kelas]);
        $data->push(['Total Pertemuan:', $this->totalPertemuan]);
        $data->push([]); // baris kosong
        $data->push(['No', 'Nama', 'NIS', 'H', 'I', 'S', 'A']); // Header tabel

        $index = 1;
        foreach ($this->rekapMapel as $row) {
            $data->push([
                $index++,
                $row['nama'],
                $row['nis'],
                $row['H'],
                $row['I'],
                $row['S'],
                $row['A'],
            ]);
        }

        return $data;
    }
}
