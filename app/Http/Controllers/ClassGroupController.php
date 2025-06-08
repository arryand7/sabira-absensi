<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use Illuminate\Http\Request;
use App\Models\Guru;

class ClassGroupController extends Controller
{
    public function index()
    {
        $classGroups = ClassGroup::all();
        return view('admin.class-groups.index', compact('classGroups'));
    }

    public function create()
    {
        $gurus = Guru::with('user')->get();
        return view('admin.class-groups.create', compact('gurus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jenis_kelas' => 'required|in:akademik,muadalah',
            'tahun_ajaran' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        ClassGroup::create($request->all());

        return redirect()->route('admin.class-groups.index')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(ClassGroup $classGroup)
    {
        $gurus = Guru::with('user')->get();
        return view('admin.class-groups.edit', compact('classGroup', 'gurus'));
    }

    public function update(Request $request, ClassGroup $classGroup)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jenis_kelas' => 'required|in:akademik,muadalah',
            'tahun_ajaran' => 'required|string|max:255',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        $classGroup->update($request->all());

        return redirect()->route('admin.class-groups.index')->with('success', 'Kelas berhasil diupdate');
    }

    public function destroy(ClassGroup $classGroup)
    {
        $classGroup->delete();

        return redirect()->route('admin.class-groups.index')->with('success', 'Kelas berhasil dihapus');
    }
}
