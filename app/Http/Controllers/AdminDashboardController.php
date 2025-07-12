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
        $absensis = AbsensiKaryawan::with('user')
            ->whereHas('user', function ($query) {
                $query->where('status', 'aktif');
            })
            ->whereDate('waktu_absen', now()->toDateString())
            ->orderByDesc('waktu_absen')
            ->get();

        $totalKaryawan = Karyawan::whereHas('user', function ($query) {
            $query->where('status', 'aktif');
        })->count();

        $sudahAbsenUserIds = AbsensiKaryawan::whereDate('waktu_absen', now()->toDateString())
            ->whereHas('user', function ($query) {
                $query->where('status', 'aktif');
            })
            ->distinct('user_id')
            ->pluck('user_id');

        $totalSudahAbsen = $sudahAbsenUserIds->count();
        $totalBelumHadir = $totalKaryawan - $totalSudahAbsen;

        $karyawanBelumAbsen = Karyawan::whereHas('user', function ($query) {
            $query->where('status', 'aktif');
        })
        ->whereNotIn('user_id', $sudahAbsenUserIds)
        ->with('user')
        ->get();

        return view('admin.dashboard', compact(
            'absensis',
            'totalKaryawan',
            'totalSudahAbsen',
            'totalBelumHadir',
            'karyawanBelumAbsen'
        ));
    }

}
