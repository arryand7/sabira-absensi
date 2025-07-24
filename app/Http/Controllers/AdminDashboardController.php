<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\AbsensiKaryawan;
use Carbon\Carbon;
use App\Models\AbsensiLokasi;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $absensis = AbsensiKaryawan::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'aktif'))
            ->whereDate('waktu_absen', now()->toDateString())
            ->orderByDesc('waktu_absen')
            ->get();

        $totalKaryawan = Karyawan::whereHas('user', fn($q) => $q->where('status', 'aktif'))->count();

        $sudahAbsenUserIds = AbsensiKaryawan::whereDate('waktu_absen', now()->toDateString())
            ->whereHas('user', fn($q) => $q->where('status', 'aktif'))
            ->distinct('user_id')
            ->pluck('user_id');

        $totalSudahAbsen = $sudahAbsenUserIds->count();
        $totalBelumHadir = $totalKaryawan - $totalSudahAbsen;

        $karyawanBelumAbsen = Karyawan::whereHas('user', fn($q) => $q->where('status', 'aktif'))
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

    public function storeManualAbsen(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $lokasi = AbsensiLokasi::first();

        $existingAbsensi = AbsensiKaryawan::where('user_id', $userId)
            ->whereDate('waktu_absen', Carbon::today())
            ->first();

        if ($existingAbsensi) {
            return redirect()->back()->with('error', 'Karyawan sudah absen hari ini.');
        }

        $now = Carbon::now();
        $checkIn = $now->format('H:i');

        $status = $now->gt(Carbon::createFromTime(8, 0)) ? 'Terlambat' : 'Hadir';

        AbsensiKaryawan::create([
            'user_id' => $userId,
            'latitude' => $lokasi?->latitude,
            'longitude' => $lokasi?->longitude,
            'waktu_absen' => now(),
            'check_in' => $checkIn,
            'status' => $status,
            'device_hash' => null,
        ]);

        return redirect()->back()->with('success', 'Absensi berhasil ditambahkan.');
    }


    public function editAbsen($id)
    {
        $absen = AbsensiKaryawan::with('user')->findOrFail($id);
        return view('admin.absensi.edit', compact('absen'));
    }

    public function updateAbsen(Request $request, $id)
    {
        // Custom validator
        $validator = Validator::make($request->all(), [
            'check_in' => 'nullable',
            'check_out' => 'nullable|after:check_in',
        ], [
            'check_out.after' => 'Waktu check-out harus setelah check-in.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('openModal', 'modal-edit-' . $id);
        }

        $absen = AbsensiKaryawan::with('user')->findOrFail($id);

        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        $status = $checkIn ? $this->tentukanStatus($absen->user, $checkIn) : $absen->status;

        $absen->update([
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'status' => $status,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Data absensi berhasil diperbarui.');
    }




    /**
     * Tentukan status absen berdasarkan role dan jam masuk
     */
    private function tentukanStatus($user, $checkIn)
    {
        if ($user->role === 'karyawan') {
            return $checkIn >= '07:31:00' ? 'Terlambat' : 'Hadir';
        }

        if ($user->role === 'guru') {
            $jenisGuru = optional($user->guru)->jenis;

            if ($jenisGuru === 'formal') {
                return $checkIn >= '07:31:00' ? 'Terlambat' : 'Hadir';
            }

            if ($jenisGuru === 'muadalah') {
                if ($checkIn > '20:30:00' || $checkIn >= '15:31:00') {
                    return 'Terlambat';
                } else {
                    return 'Hadir';
                }
            }
        }

        return 'Terlambat'; // Default kalau role/jenis tak dikenali
    }
}
