<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use App\Models\Divisi;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $divisis = Divisi::all(); // ambil semua data divisi
        return view('admin.users.create', compact('divisis'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'role' => 'required|in:admin,karyawan,guru',
            'password' => 'required|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => $request->role === 'karyawan' ? 'required|exists:divisis,id' : 'nullable',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
        ]);

        if ($user->role === 'karyawan') {
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('foto_karyawan', 'public');
            }

            Karyawan::create([
                'user_id' => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'divisi_id' => $request->divisi_id,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'foto' => $fotoPath,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat!');
    }

    public function edit(User $user)
    {
        $divisis = Divisi::all(); // Tambahkan untuk keperluan dropdown divisi
        return view('admin.users.edit', compact('user', 'divisis'));
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,karyawan,guru',
            'password' => 'nullable|min:6',

            // validasi tambahan jika role karyawan
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => $request->role === 'karyawan' ? 'required|exists:divisis,id' : 'nullable',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update data user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        // Handle karyawan data
        if ($request->role === 'karyawan') {
            $karyawanData = [
                'nama_lengkap' => $request->nama_lengkap,
                'divisi_id' => $request->divisi_id,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
            ];

            // handle upload foto jika ada
            if ($request->hasFile('foto')) {
                // Hapus foto lama kalau ada
                if ($user->karyawan && $user->karyawan->foto) {
                    Storage::disk('public')->delete($user->karyawan->foto);
                }

                $karyawanData['foto'] = $request->file('foto')->store('foto_karyawan', 'public');
            }

            if ($user->karyawan) {
                $user->karyawan->update($karyawanData);
            } else {
                $karyawanData['user_id'] = $user->id;
                Karyawan::create($karyawanData);
            }
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}
