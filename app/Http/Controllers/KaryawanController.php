<?php

namespace App\Http\Controllers;

use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with(['user.guru', 'divisi'])->orderByDesc('id')->get();

        return view('admin.karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        // Hanya user yang belum punya data karyawan
        $users = User::doesntHave('karyawan')->whereIn('role', ['guru', 'karyawan'])->orderBy('name')->get();
        $divisis = Divisi::orderBy('nama')->get();

        return view('admin.karyawan.create', compact('users', 'divisis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:karyawan,user_id',
            'divisi_id' => 'nullable|exists:divisis,id',
            'alamat' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:30',
        ]);

        Karyawan::create($validated);

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Karyawan $karyawan)
    {
        $karyawan->load(['user.guru.educationPrograms', 'divisi']);
        $absensi = $karyawan->user->absensis()->latest('waktu_absen')->paginate(15);
        $schedules = Schedule::with(['subject', 'classGroup', 'academicYear'])
            ->where('user_id', $karyawan->user_id)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.karyawan.show', compact('karyawan', 'absensi', 'schedules'));
    }

    public function edit(Karyawan $karyawan)
    {
        $karyawan->load('user');
        $divisis = Divisi::orderBy('nama')->get();

        return view('admin.karyawan.edit', compact('karyawan', 'divisis'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $validated = $request->validate([
            'divisi_id' => 'nullable|exists:divisis,id',
            'alamat' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:30',
        ]);

        $karyawan->update($validated);

        return redirect()->route('karyawan.show', $karyawan)->with('success', 'Profil pegawai berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('success', 'Profil pegawai berhasil dihapus tanpa menghapus akun pengguna.');
    }
}
