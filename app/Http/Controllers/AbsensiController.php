<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;


class AbsensiController extends Controller
{
    public function index()
    {
        $absensis = AbsensiKaryawan::with('user')->latest()->get();
        return view('admin.absensi.index', compact('absensis'));
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // KM
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance; // dalam KM
    }

    public function checkin(Request $request)
    {
        $user = Auth::user();

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        // $sekolahLat = -7.3077831;
        // $sekolahLng = 112.7256599;
        // $sekolahLat = -7.3107911;
        // $sekolahLng = 112.7291219;
        $sekolahLat = -7.3138501;
        $sekolahLng = 112.7256289;
        $jarak = $this->haversine($latitude, $longitude, $sekolahLat, $sekolahLng);

        if ($jarak > 0.1) { // > 100 meter
            return back()->with("error", "Gagal Check-In: Lokasi terlalu jauh dari sekolah.");
        }

        $waktuSekarang = now();
        $jamCheckin = $waktuSekarang->format('H:i:s');

        // Default: Hadir
        $status = 'Hadir';

        if ($jamCheckin > '07:00:00') {
            $status = 'Terlambat';
        }

        AbsensiKaryawan::create([
            'user_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'waktu_absen' => $waktuSekarang,
            'check_in' => $jamCheckin,
            'status' => $status,
        ]);

        return back()->with('success', 'Berhasil Check-In!');
    }

    public function checkout(Request $request)
    {
        $user = Auth::user();

        // Cari absensi hari ini
        $absensi = AbsensiKaryawan::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$absensi) {
            return back()->with('error', 'Belum Check-In hari ini!');
        }

        if ($absensi->check_out) {
            return back()->with('error', 'Sudah Check-Out sebelumnya!');
        }

        $absensi->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Berhasil Check-Out!');
    }

    public function history()
    {
        $user = Auth::user();
        $absensis = AbsensiKaryawan::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('karyawan.history', compact('absensis'));
    }


}
