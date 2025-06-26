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
        $users = User::with(['karyawan.divisi', 'guru'])->get();
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
            'role' => 'required|in:admin,karyawan,guru,organisasi',
            'password' => 'required|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => $request->role === 'karyawan' ? 'required|exists:divisis,id' : 'nullable',
            'jenis_guru' => $request->role === 'guru' ? 'required|in:formal,muadalah,' : 'nullable',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'aktif',
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
            'role' => 'required|in:admin,karyawan,guru,organisasi',
            'password' => 'nullable|min:6',
            'nama_lengkap' => 'nullable|string|max:255',
            'divisi_id' => $request->role === 'karyawan' ? 'required|exists:divisis,id' : 'nullable',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'jenis' => $request->role === 'guru' ? 'required|in:formal,muadalah' : 'nullable',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        // Update user basic data
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            'status' => $request->status === 'aktif' ? 'aktif' : 'nonaktif',
        ]);

        if (in_array($request->role, ['karyawan', 'guru'])) {
            $fotoPath = $user->karyawan->foto ?? null;

            if ($request->hasFile('foto')) {
                if ($fotoPath && Storage::exists('public/' . $fotoPath)) {
                    Storage::delete('public/' . $fotoPath);
                }
                $fotoPath = $request->file('foto')->store('foto_karyawan', 'public');
            }

            $divisiId = $request->role === 'karyawan' ? $request->divisi_id : null;

            $user->karyawan()->updateOrCreate(['user_id' => $user->id], [
                'nama_lengkap' => $request->nama_lengkap,
                'divisi_id' => $divisiId,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'foto' => $fotoPath,
            ]);

            if ($request->role === 'guru') {
                $user->guru()->updateOrCreate(['user_id' => $user->id], ['jenis' => $request->jenis]);
            } else {
                $user->guru()->delete();
            }
        } else {
            $user->karyawan()->delete();
            $user->guru()->delete();
        }


        // Auto logout if user deactivates self
        if ($user->id == auth()->id() && $user->status !== 'aktif') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }



    public function destroy(User $user)
    {
        $user->delete();

        if ($user->karyawan && $user->karyawan->foto) {
            Storage::delete('public/' . $user->karyawan->foto);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }
}
