<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AbsensiKaryawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKaryawanExport;
use PDF;


class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->buildLaporan($request);

        return view('admin.laporan.index', [
            'laporanKaryawan' => $data['laporanKaryawan'],
            'laporanGuru' => $data['laporanGuru'],
            'divisis' => $data['divisis'],
        ]);
    }

    public function export(Request $request)
    {
        $divisi = $request->divisi;
        $jenisGuru = $request->jenis_guru;

        // Jika tidak ada tanggal dikirim, default ke hari ini
        $start_date = $request->start_date ?? Carbon::now()->format('Y-m-d');
        $end_date = $request->end_date ?? Carbon::now()->format('Y-m-d');

        $label = 'semua';
        if ($divisi) $label = $divisi;
        if ($jenisGuru) $label = $jenisGuru;

        $filename = 'absensi_' . str_replace(' ', '_', strtolower($label)) . '_' . $start_date . '_sampai_' . $end_date . '.xlsx';

        return Excel::download(new LaporanKaryawanExport(
            $divisi,
            $jenisGuru,
            $start_date,
            $end_date
        ), $filename);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildLaporan($request);

        $filters = $data['filters'];

        $pdf = PDF::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ])->loadView('admin.laporan.pdf-karyawan', [
            'laporanKaryawan' => $data['laporanKaryawan'],
            'laporanGuru' => $data['laporanGuru'],
            'filters' => $filters,
        ]);

        return $pdf->stream('laporan_absensi_karyawan.pdf');
    }


    public function detail($id, Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $user = User::with('karyawan')->findOrFail($id);

        $absensi = AbsensiKaryawan::where('user_id', $user->id)
            ->when($bulan, fn($q) => $q->whereMonth('waktu_absen', $bulan))
            ->when($tahun, fn($q) => $q->whereYear('waktu_absen', $tahun))
            ->orderByDesc('waktu_absen')
            ->get()
            ->map(function ($item) {
                $item->tanggal = \Carbon\Carbon::parse($item->waktu_absen)->format('Y-m-d');
                $item->jam = $item->check_in ?? '-';
                $item->check_out = $item->check_out ?? '-';
                return $item;
            });

        return view('admin.laporan.karyawan-detail', compact('user', 'absensi'));
    }

    public function exportDetail($id, Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $user = User::findOrFail($id);

        $namaBulan = $bulan ? \Carbon\Carbon::create()->month($bulan)->locale('id')->monthName : 'Semua-Bulan';
        $filename = 'rekap-' . str()->slug($user->name) . "-{$namaBulan}-{$tahun}.xlsx";

        return Excel::download(new \App\Exports\DetailKaryawanExport($id, $bulan, $tahun), $filename);
    }

    private function buildLaporan(Request $request): array
    {
        $divisi = $request->divisi;
        $start_date = $request->start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $end_date = $request->end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $jenisGuru = $request->jenis_guru;
        $divisis = \App\Models\Divisi::all();

        $users = User::with(['karyawan.divisi', 'guru'])
            ->where('status', 'aktif')
            ->whereHas('karyawan')
            ->when($divisi, function ($q) use ($divisi) {
                $q->whereHas('karyawan.divisi', function ($q2) use ($divisi) {
                    $q2->where('nama', $divisi);
                });
            })
            ->when($jenisGuru, function ($q) use ($jenisGuru) {
                $q->whereHas('guru', function ($q2) use ($jenisGuru) {
                    $q2->where('jenis', $jenisGuru);
                });
            })
            ->get();

        $laporanKaryawan = [];
        $laporanGuru = [
            'formal' => [],
            'muadalah' => [],
        ];

        foreach ($users as $user) {
            $absensi = $user->absensis()
                ->whereBetween('waktu_absen', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay()
                ])
                ->get();

            $hadir = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $absen = $absensi->where('status', 'Tidak Hadir')->count();

            $item = [
                'user' => $user,
                'hadir' => $hadir,
                'absen' => $absen,
            ];

            if ($user->guru) {
                $jenis = $user->guru->jenis;
                $laporanGuru[$jenis][] = $item;
            } else {
                $laporanKaryawan[] = $item;
            }
        }

        $divisiLabel = 'Semua Karyawan';
        if ($jenisGuru) {
            $divisiLabel = 'Guru (' . ucfirst($jenisGuru) . ')';
        } elseif ($divisi) {
            $divisiLabel = ucfirst($divisi);
        }

        return [
            'laporanKaryawan' => $laporanKaryawan,
            'laporanGuru' => $laporanGuru,
            'divisis' => $divisis,
            'filters' => [
                'divisi' => $divisi,
                'jenis_guru' => $jenisGuru,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'divisi_label' => $divisiLabel,
            ],
        ];
    }
}
