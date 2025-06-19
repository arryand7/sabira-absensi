<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKaryawanExport implements FromArray, WithStyles, WithTitle, WithEvents
{
    protected $divisi;
    protected $jenisGuru;
    protected $start_date;
    protected $end_date;
    protected $statusMatrix = [];

    public function __construct($divisi, $jenisGuru, $start_date, $end_date)
    {
        $this->divisi = $divisi;
        $this->jenisGuru = $jenisGuru;
        $this->start_date = Carbon::parse($start_date);
        $this->end_date = Carbon::parse($end_date);
    }

    public function array(): array
    {
        $headerTanggal = [];
        $current = $this->start_date->copy();
        while ($current->lte($this->end_date)) {
            $headerTanggal[] = $current->format('d');
            $current->addDay();
        }

        $header = ['No', 'Nama'];
        $header = array_merge($header, $headerTanggal, ['Jumlah Kehadiran']);

        $data = [
            ['Absensi Karyawan'],
            ['Divisi: ' . $this->getDivisiLabel()],
            ['Periode: ' . $this->start_date->format('d M Y') . ' s.d ' . $this->end_date->format('d M Y')],
            $header
        ];

        $users = User::with([
            'karyawan.divisi',
            'guru',
            'absensis' => function ($q) {
                $q->whereBetween('waktu_absen', [$this->start_date, $this->end_date]);
            }
        ])
        ->whereNotIn('role', ['admin', 'organisasi']) // Tambahkan baris ini
        ->when($this->jenisGuru, function ($q) {
            $q->whereHas('guru', function ($query) {
                $query->where(DB::raw('LOWER(jenis)'), strtolower($this->jenisGuru));
            });
        })
        ->when($this->divisi, function ($q) {
            $q->whereHas('karyawan.divisi', function ($query) {
                $query->where(DB::raw('LOWER(nama)'), strtolower($this->divisi));
            });
        })
        ->get();


        // debug
        // dd([
        //     'jenisGuru' => $this->jenisGuru,
        //     'divisi' => $this->divisi,
        //     'filtered_users' => $users->pluck('name'),
        // ]);

        $no = 1;

        foreach ($users as $user) {
            $row = [$no++, $user->name];
            $hadirCount = 0;
            $tanggal = $this->start_date->copy();
            $statusList = [];

            while ($tanggal->lte($this->end_date)) {
                $absen = $user->absensis->firstWhere(fn($a) =>
                    Carbon::parse($a->waktu_absen)->isSameDay($tanggal)
                );

                if ($absen) {
                    if ($absen->status === 'Hadir') {
                        $row[] = '✓';
                        $statusList[] = 'hadir';
                        $hadirCount++;
                    } elseif ($absen->status === 'Terlambat') {
                        $row[] = '✓';
                        $statusList[] = 'terlambat';
                        $hadirCount++;
                    } else {
                        $row[] = '-';
                        $statusList[] = 'tidak_hadir';
                    }
                } else {
                    $row[] = '-';
                    $statusList[] = 'kosong';
                }

                $tanggal->addDay();
            }

            $row[] = $hadirCount;
            $data[] = $row;
            $this->statusMatrix[] = $statusList;
        }

        return $data;
    }

    protected function getDivisiLabel()
    {
        if ($this->jenisGuru) {
            return 'Divisi: Guru (' . ucfirst($this->jenisGuru) . ')';
        } elseif ($this->divisi) {
            return 'Divisi: ' . ucfirst($this->divisi);
        }

        return 'Divisi: Semua Karyawan';
    }



    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:Z1');
        $sheet->mergeCells('A2:Z2');
        $sheet->mergeCells('A3:Z3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setItalic(true);

        return [];
    }

    public function title(): string
    {
        return 'Laporan Absensi';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $startRow = 5;

                foreach ($this->statusMatrix as $r => $statusList) {
                    foreach ($statusList as $c => $status) {
                        $colIndex = $c + 3;
                        $rowIndex = $startRow + $r;
                        $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                        $color = match ($status) {
                            'hadir' => 'C6EFCE',
                            'terlambat' => 'FFEB9C',
                            'tidak_hadir', 'kosong' => 'FFC7CE',
                            default => null,
                        };

                        if ($color) {
                            $sheet->getStyle($cell)->getFill()
                                ->setFillType('solid')
                                ->getStartColor()
                                ->setARGB($color);
                        }
                    }
                }
            }
        ];
    }
}
