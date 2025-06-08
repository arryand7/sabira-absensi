<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use App\Models\Divisi;
use App\Models\Guru;


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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'role' => 'required|in:admin,karyawan,guru',
            'password' => 'required|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => in_array($request->role, ['karyawan', 'guru']) ? 'required|exists:divisis,id' : 'nullable',
            'jenis_guru' => $request->role === 'guru' ? 'required|in:akademik,muadalah,asrama' : 'nullable',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        if (in_array($user->role, ['karyawan', 'guru'])) {
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

            if ($user->role === 'guru') {
                Guru::create([
                    'user_id' => $user->id,
                    'jenis' => $request->jenis_guru,
                ]);
            }
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat!');
    }



    public function edit($id)
    {
        $user = User::with(['karyawan', 'guru'])->findOrFail($id);
        $divisis = Divisi::all();
        return view('admin.users.edit', compact('user', 'divisis'));
    }



    public function update(Request $request, $id)
    {
        $user = User::with('karyawan', 'guru')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,karyawan,guru',
            'password' => 'nullable|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => 'nullable|exists:divisis,id',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'jenis' => 'nullable|in:akademik,muadalah,asrama',
        ]);

        // Validasi konsistensi divisi dan role
        $divisi = Divisi::find($request->divisi_id);
        if ($request->role === 'guru') {
            if (!$divisi || strtolower($divisi->nama) !== 'guru') {
                return back()->withErrors(['divisi_id' => 'Divisi harus divisi Guru untuk role Guru'])->withInput();
            }
        } elseif ($request->role === 'karyawan') {
            if ($divisi && strtolower($divisi->nama) === 'guru') {
                return back()->withErrors(['divisi_id' => 'Divisi Guru tidak bisa untuk role Karyawan'])->withInput();
            }
        }

        // Update user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        if (in_array($request->role, ['karyawan', 'guru'])) {
            $dataKaryawan = [
                'nama_lengkap' => $request->nama_lengkap,
                'divisi_id' => $request->divisi_id,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'jenis' => $request->role === 'guru' ? $request->jenis : null,
            ];

            if ($request->hasFile('foto')) {
                if ($user->karyawan && $user->karyawan->foto) {
                    Storage::delete('public/foto/' . $user->karyawan->foto);
                }
                $dataKaryawan['foto'] = $request->file('foto')->store('foto', 'public');
            }

            if ($user->karyawan) {
                $user->karyawan->update($dataKaryawan);
            } else {
                $dataKaryawan['user_id'] = $user->id;
                Karyawan::create($dataKaryawan);
            }

            if ($request->role === 'guru') {
                if ($user->guru) {
                    $user->guru->update(['jenis' => $request->jenis]);
                } else {
                    Guru::create(['user_id' => $user->id, 'jenis' => $request->jenis]);
                }
            } else {
                // Kalau bukan guru, hapus record guru kalau ada
                if ($user->guru) {
                    $user->guru->delete();
                }
            }
        } else {
            // Kalau role admin, hapus karyawan dan guru jika ada
            if ($user->karyawan) $user->karyawan->delete();
            if ($user->guru) $user->guru->delete();
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}
