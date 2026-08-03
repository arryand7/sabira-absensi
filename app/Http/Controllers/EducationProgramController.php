<?php

namespace App\Http\Controllers;

use App\Models\EducationProgram;
use Illuminate\Http\Request;

class EducationProgramController extends Controller
{
    public function index()
    {
        $programs = EducationProgram::withCount(['classGroups', 'teachers'])
            ->orderBy('id')
            ->get();

        return view('admin.education-programs.index', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:education_programs,code',
            'name' => 'required|string|max:255',
            'default_start_time' => 'nullable|date_format:H:i',
            'default_end_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        EducationProgram::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'default_start_time' => $validated['default_start_time'] ?? null,
            'default_end_time' => $validated['default_end_time'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('admin.education-programs.index')
            ->with('success', 'Program Pendidikan berhasil ditambahkan.');
    }

    public function update(Request $request, EducationProgram $educationProgram)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:education_programs,code,'.$educationProgram->id,
            'name' => 'required|string|max:255',
            'default_start_time' => 'nullable',
            'default_end_time' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $educationProgram->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'default_start_time' => $validated['default_start_time'] ?? $educationProgram->default_start_time,
            'default_end_time' => $validated['default_end_time'] ?? $educationProgram->default_end_time,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : $educationProgram->is_active,
        ]);

        return redirect()->route('admin.education-programs.index')
            ->with('success', 'Program Pendidikan berhasil diperbarui.');
    }

    public function destroy(EducationProgram $educationProgram)
    {
        if ($educationProgram->classGroups()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Program Pendidikan tidak dapat dihapus karena masih digunakan oleh kelompok kelas.');
        }

        $educationProgram->delete();

        return redirect()->route('admin.education-programs.index')
            ->with('success', 'Program Pendidikan berhasil dihapus.');
    }
}
