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
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        // Ambil semua divisi untuk dropdown
        $divisis = \App\Models\Divisi::all();

        // Ambil user yang punya karyawan dan relasi divisi
        $users = \App\Models\User::with(['karyawan.divisi'])
            ->when($divisi, function ($q) use ($divisi) {
                $q->whereHas('karyawan.divisi', function ($q2) use ($divisi) {
                    $q2->where('nama', $divisi);
                });
            })
            ->get();

        $laporan = [];

        foreach ($users as $user) {
            // Ambil absensi user
            $absensi = $user->absensis()
                ->when($bulan, fn($q) => $q->whereMonth('created_at', $bulan))
                ->when($tahun, fn($q) => $q->whereYear('created_at', $tahun))
                ->get();

            $hadir = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $jumlahHari = $bulan && $tahun ? \Carbon\Carbon::create($tahun, $bulan)->daysInMonth : $absensi->count();
            $absen = $jumlahHari - $hadir;

            $laporan[] = [
                'user' => $user,
                'hadir' => $hadir,
                'absen' => max(0, $absen),
            ];
        }

        return view('admin.laporan.index', compact('laporan', 'divisis'));
    }

    // public function show($id)
    // {
    //     $user = User::findOrFail($id);
    //     $absensis = AbsensiKaryawan::where('user_id', $id)->orderByDesc('created_at')->get();

    //     return view('admin.laporan.detail', compact('user', 'absensis'));
    // }

    public function export(Request $request)
    {
        $divisi = $request->divisi;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        return Excel::download(new LaporanKaryawanExport($divisi, $bulan, $tahun), 'laporan-karyawan.xlsx');
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


        return view('laporan.karyawan', compact('laporan'));
    }

    public function detail($id)
    {
        $user = User::with('karyawan')->findOrFail($id);
        $absensi = AbsensiKaryawan::where('user_id', $user->id)->get()->map(function ($item) {
            $item->tanggal = \Carbon\Carbon::parse($item->waktu_absen)->format('Y-m-d');
            return $item;
        });


        return view('admin.laporan.karyawan-detail', compact('user', 'absensi'));
    }


}
