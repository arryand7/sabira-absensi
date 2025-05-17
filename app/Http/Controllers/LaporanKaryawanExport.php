<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class LaporanKaryawanExport implements FromView
{
    protected $divisi, $bulan, $tahun;

    public function __construct($divisi = null, $bulan = null, $tahun = null)
    {
        $this->divisi = $divisi;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        $users = User::with(['karyawan.divisi'])
            ->when($this->divisi, function ($query) {
                $query->whereHas('karyawan.divisi', function ($q) {
                    $q->where('nama', $this->divisi);
                });
            })
            ->get();

        $laporan = [];

        foreach ($users as $user) {
            $absensi = $user->absensis()
                ->when($this->bulan, fn($q) => $q->whereMonth('created_at', $this->bulan))
                ->when($this->tahun, fn($q) => $q->whereYear('created_at', $this->tahun))
                ->get();

            $hadir = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $jumlahHari = $this->bulan && $this->tahun
                ? Carbon::create($this->tahun, $this->bulan)->daysInMonth
                : $absensi->count();
            $absen = $jumlahHari - $hadir;

            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'absen' => max(0, $absen),
            ];
        }

        return view('exports.laporan-karyawan', [
            'laporan' => $laporan,
            'bulan' => $this->bulan,
            'tahun' => $this->tahun,
            'divisi' => $this->divisi,
        ]);
    }
}
