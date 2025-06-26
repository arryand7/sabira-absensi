<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required',
            'kode_mapel' => 'required',
            'jenis_mapel' => 'required|in:formal,muadalah',
        ]);

        Subject::create($request->all());

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'nama_mapel' => 'required',
            'kode_mapel' => 'required',
            'jenis_mapel' => 'required|in:formal,muadalah',
        ]);

        $subject->update($request->all());

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
