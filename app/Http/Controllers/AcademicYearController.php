<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create()
    {
        return view('admin.academic-years.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            // 'is_active' => 'nullable|boolean',
        ]);

        // Hanya jika checkbox "jadikan aktif" dicentang
        $isActive = $request->has('is_active');

        \DB::transaction(function () use ($request, $isActive) {
            // Kalau tahun ajaran baru ingin dijadikan aktif,
            // maka nonaktifkan semua tahun ajaran lain dulu
            if ($isActive) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            // Simpan tahun ajaran baru
            AcademicYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }


    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        $academicYear->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        // Cek apakah ada class_group_student yang masih memakai tahun ajaran ini
        if ($academicYear->classGroupStudents()->exists()) {
            return redirect()->route('academic-years.index')
                ->with('error', 'Tahun ajaran tidak bisa dihapus karena masih digunakan.');
        }

        $academicYear->delete();
        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

}
