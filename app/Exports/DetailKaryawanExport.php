<?php

namespace App\Exports;

use App\Models\AbsensiKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class DetailKaryawanExport implements FromCollection
{
    protected $userId;
    protected $bulan;
    protected $tahun;

    public function __construct($userId, $bulan, $tahun)
    {
        $this->userId = $userId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        $user = User::findOrFail($this->userId);

        $data = collect();

        $data->push([
            "REKAP ABSENSI KARYAWAN: {$user->name}",
        ]);
        $data->push([]);
        $data->push(['Tanggal', 'Jam Masuk', 'Status']);

        $absensi = AbsensiKaryawan::where('user_id', $user->id)
            ->when($this->bulan, fn($q) => $q->whereMonth('check_in', $this->bulan))
            ->when($this->tahun, fn($q) => $q->whereYear('check_in', $this->tahun))
            ->orderBy('check_in')
            ->get();

        foreach ($absensi as $a) {
            $data->push([
                Carbon::parse($a->check_in)->format('Y-m-d'),
                Carbon::parse($a->check_in)->format('H:i:s'),
                ucfirst($a->status),
            ]);
        }

        return $data;
    }
}
