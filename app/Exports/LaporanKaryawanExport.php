<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class LaporanKaryawanExport implements FromCollection
{
    protected $divisi;
    protected $bulan;
    protected $tahun;

    public function __construct($divisi, $bulan, $tahun)
    {
        $this->divisi = $divisi;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        $users = User::with(['karyawan.divisi'])
            ->when($this->divisi, function ($q) {
                $q->whereHas('karyawan.divisi', function ($q2) {
                    $q2->where('nama', $this->divisi);
                });
            })
            ->get();

        $data = collect();

        // Tambahkan header/title di awal
        $data->push([
            'LAPORAN KARYAWAN PONDOK PESANTREN SABILLUL RAHMA',
        ]);
        $data->push([]); // spasi
        $data->push(['Nama', 'Email', 'Divisi', 'Total Hadir', 'Total Absen']);

        foreach ($users as $user) {
            $absensi = $user->absensis()
                ->when($this->bulan, fn($q) => $q->whereMonth('check_in', $this->bulan))
                ->when($this->tahun, fn($q) => $q->whereYear('check_in', $this->tahun))
                ->get();

            $hadir = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $jumlahHari = $this->bulan && $this->tahun
                ? Carbon::create($this->tahun, $this->bulan)->daysInMonth
                : $absensi->count();
            $absen = $jumlahHari - $hadir;

            $data->push([
                $user->name,
                $user->email,
                $user->karyawan->divisi->nama ?? '-',
                $hadir,
                max(0, $absen),
            ]);
        }

        return $data;
    }
}
