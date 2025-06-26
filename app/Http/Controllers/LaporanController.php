<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AbsensiKaryawan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanKaryawanExport;

class LaporanController extends Controller
{
    public function index(Request $request)
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

        return view('admin.laporan.index', compact('laporanKaryawan', 'laporanGuru', 'divisis'));
    }

    public function export(Request $request)
    {
        $divisi = $request->divisi;
        $jenisGuru = $request->jenis_guru;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

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
                return $item;
            });

        return view('admin.laporan.karyawan-detail', compact('user', 'absensi'));
    }

    public function exportDetail($id, Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        return Excel::download(new \App\Exports\DetailKaryawanExport($id, $bulan, $tahun), 'rekap-karyawan.xlsx');
    }
}
