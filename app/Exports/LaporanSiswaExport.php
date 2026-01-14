<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class LaporanSiswaExport implements FromArray
{
    protected $rekap;
    protected $nama;
    protected $nis;
    protected $tahun;

    public function __construct(array $rekap, string $nama, string $nis, string $tahun)
    {
        $this->rekap = $rekap;
        $this->nama = $nama;
        $this->nis = $nis;
        $this->tahun = $tahun;
    }

    public function array(): array
    {
        $data = [];

        $data[] = ['Laporan Absensi Siswa'];
        $data[] = ['Nama', $this->nama];
        $data[] = ['NIS', $this->nis];
        $data[] = ['Tahun Ajaran', $this->tahun];
        $data[] = [];

        $data[] = ['Jenis Mapel', 'Mata Pelajaran', 'H', 'I', 'S', 'A'];

        if (empty($this->rekap)) {
            $data[] = ['Tidak ada data absensi'];
            return $data;
        }

        foreach ($this->rekap as $jenis => $mapels) {
            foreach ($mapels as $mapel => $counts) {
                $data[] = [
                    ucfirst($jenis),
                    $mapel,
                    $counts['H'] ?? 0,
                    $counts['I'] ?? 0,
                    $counts['S'] ?? 0,
                    $counts['A'] ?? 0,
                ];
            }
        }

        return $data;
    }
}
