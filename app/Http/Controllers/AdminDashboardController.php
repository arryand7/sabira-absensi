<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Data absensi hari ini
        $absensis = AbsensiKaryawan::with('user')
            ->whereDate('waktu_absen', now()->toDateString())
            ->orderByDesc('waktu_absen')
            ->get();

        // Total karyawan
        $totalKaryawan = Karyawan::count();

        // Karyawan yang sudah absen hari ini (distinct user_id)
        $sudahAbsenUserIds = AbsensiKaryawan::whereDate('waktu_absen', now()->toDateString())
            ->distinct('user_id')
            ->pluck('user_id');
        $totalSudahAbsen = $sudahAbsenUserIds->count();

        // Karyawan yang belum absen
        $totalBelumHadir = $totalKaryawan - $totalSudahAbsen;

        return view('admin.dashboard', compact(
            'absensis',
            'totalKaryawan',
            'totalSudahAbsen',
            'totalBelumHadir'
        ));
    }
}
