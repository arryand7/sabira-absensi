<?php

namespace App\Exports;

use App\Models\AbsensiKaryawan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
// use Maatwebsite\Excel\Concerns\WithTitle;


class DetailKaryawanExport implements FromCollection, WithStyles
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
        $data->push(["REKAP ABSENSI KARYAWAN: {$user->name}"]);
        $data->push([]);
        $data->push(['Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status']);

        $absensi = AbsensiKaryawan::where('user_id', $user->id)
            ->when($this->bulan, fn($q) => $q->whereMonth('waktu_absen', $this->bulan))
            ->when($this->tahun, fn($q) => $q->whereYear('waktu_absen', $this->tahun))
            ->orderBy('waktu_absen')
            ->get();

        // Counters
        $totalHadir = 0;
        $totalTelat = 0;
        $totalTidakHadir = 0;

        foreach ($absensi as $a) {
            $status = ucfirst($a->status ?? 'Tidak Diketahui');
            if ($status === 'Hadir') {
                $totalHadir++;
            } elseif ($status === 'Terlambat') {
                $totalTelat++;
            } else {
                $totalTidakHadir++;
            }

            $data->push([
                $a->waktu_absen ? Carbon::parse($a->waktu_absen)->format('Y-m-d') : '-',
                $a->check_in ?? '-',
                $a->check_out ?? '-',
                $status,
            ]);
        }

        // Tambahkan ringkasan total
        $data->push([]);
        $data->push(['Total Hadir', (string) $totalHadir]);
        $data->push(['Total Terlambat', (string) $totalTelat]);
        $data->push(['Total Tidak Hadir', (string) $totalTidakHadir]);
        
        return $data;
    }
    public function styles(Worksheet $sheet)
    {
        // Bold untuk judul
        $sheet->getStyle('A1')->getFont()->setBold(true);

        // Bold untuk header tabel, misal di baris ke-4 (menyesuaikan)
        $sheet->getStyle('A2:D2')->getFont()->setBold(true);

        // Cari baris terakhir untuk bold bagian total
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:B{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A" . ($lastRow - 1) . ":B" . ($lastRow - 1))->getFont()->setBold(true);
        $sheet->getStyle("A" . ($lastRow - 2) . ":B" . ($lastRow - 2))->getFont()->setBold(true);

        return [];
    }


}
