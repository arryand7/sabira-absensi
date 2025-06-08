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
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $divisis = \App\Models\Divisi::all();

        $users = \App\Models\User::with(['karyawan.divisi'])
            ->when($divisi, function ($q) use ($divisi) {
                $q->whereHas('karyawan.divisi', function ($q2) use ($divisi) {
                    $q2->where('nama', $divisi);
                });
            })
            ->get();

        $laporan = [];

        foreach ($users as $user) {
            $absensi = $user->absensis()
                ->when($start_date && $end_date, function ($q) use ($start_date, $end_date) {
                    $q->whereBetween('waktu_absen', [
                        Carbon::parse($start_date)->startOfDay(),
                        Carbon::parse($end_date)->endOfDay()
                    ]);
                })
                ->get();

            $hadir = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $absen = $absensi->where('status', 'Tidak Hadir')->count();

            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'absen' => $absen,
            ];
        }

        return view('admin.laporan.index', compact('laporan', 'divisis'));
    }


    public function export(Request $request)
    {
        $divisi = $request->divisi;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        return Excel::download(new LaporanKaryawanExport($divisi, $start_date, $end_date), 'laporan-karyawan.xlsx');
    }

    public function laporanKaryawan()
    {
        $laporan = User::with('karyawan')
        ->where('role', 'karyawan')
        ->get()
        ->map(function ($user) {
            $absensi = AbsensiKaryawan::where('user_id', $user->id)->get();
            $hadir = $absensi->where('status', 'hadir')->count();
            $absen = $absensi->where('status', 'absen')->count();

            return [
                'user' => $user,
                'karyawan' => $user->karyawan, // pastikan ini gak null
                'hadir' => $hadir,
                'absen' => $absen,
            ];
        });
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
