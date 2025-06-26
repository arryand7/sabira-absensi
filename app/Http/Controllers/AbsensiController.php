<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use App\Models\AbsensiLokasi;


class AbsensiController extends Controller
{
    public function index()
    {
        $lokasi = AbsensiLokasi::latest()->first();

        $absensis = AbsensiKaryawan::with('user')->latest()->get();
        return view('karyawan.absen', [
            'absensis' => $absensis,
            'lokasi' => $lokasi,
        ]);
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

        // Cek apakah sudah check-in hari ini
        $alreadyCheckedIn = AbsensiKaryawan::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return back()->with("error", "Anda sudah melakukan Check-In hari ini.");
        }


        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $lokasi = AbsensiLokasi::first();
        $sekolahLat = $lokasi->latitude ?? -7.310823820752337;
        $sekolahLng = $lokasi->longitude ?? 112.72923730812086;

        $jarak = $this->haversine($latitude, $longitude, $sekolahLat, $sekolahLng);

        if ($jarak > 0.2) {
            return back()->with("error", "Gagal Check-In: Lokasi terlalu jauh dari sekolah.");
        }

        $now = now();
        $jamSekarang = $now->format('H:i:s');

        $status = 'Hadir';

        if ($user->role === 'karyawan') {
            if ($jamSekarang >= '07:31:00' && $jamSekarang <= '16:00:00') {
                $status = 'Terlambat';
            } elseif ($jamSekarang > '16:00:00') {
                return back()->with("error", "Absen Gagal: Sudah melewati jam absen.");
            }
        } elseif ($user->role === 'guru') {
            $jenisGuru = optional($user->guru)->jenis;

            if ($jenisGuru === 'formal') {
                if ($jamSekarang >= '07:31:00' && $jamSekarang <= '16:00:00') {
                    $status = 'Terlambat';
                } elseif ($jamSekarang > '16:00:00') {
                    return back()->with("error", "Absen Gagal: Sudah melewati jam absen.");
                }
            } elseif ($jenisGuru === 'muadalah') {
                if ($jamSekarang >= '15:31:00' && $jamSekarang <= '20:30:00') {
                    $status = 'Terlambat';
                } elseif ($jamSekarang < '15:30:00') {
                    $status = 'Hadir';
                } elseif ($jamSekarang > '20:30:00') {
                    return back()->with("error", "Absen Gagal: Sudah melewati jam absen.");
                }
            } else {
                return back()->with("error", "Jenis guru tidak dikenali.");
            }
        } else {
            return back()->with("error", "Role tidak dikenali.");
        }

        AbsensiKaryawan::create([
            'user_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'waktu_absen' => $now,
            'check_in' => $jamSekarang,
            'status' => $status,
        ]);

        return back()->with('success', "Check-In berhasil. Status: $status");
    }


    public function checkout(Request $request)
    {

        $user = Auth::user();

        $absensi = AbsensiKaryawan::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$absensi) {
            return back()->with('error', 'Gagal Check-Out: Anda belum melakukan Check-In hari ini.');
        }

        if ($absensi->check_out) {
            return back()->with('error', 'Anda sudah melakukan Check-Out sebelumnya.');
        }

        $absensi->update([
            'check_out' => now()->format('H:i:s'),
        ]);

        return back()->with('success', 'Berhasil Check-Out!');
    }


    public function history(Request $request)
    {
        $user = Auth::user();

        // Ambil bulan & tahun dari request atau default sekarang
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        // Query absensi sesuai bulan dan tahun
        $query = AbsensiKaryawan::where('user_id', $user->id)
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun);

        $absensis = $query->orderBy('created_at', 'desc')->get();

        return view('karyawan.history', compact('absensis', 'bulan', 'tahun'));
    }

}
