<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Guru;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ClassGroupController extends Controller
{
    public function index()
    {
        $activeYear = AcademicYear::where('is_active', true)->first();

        $classGroups = ClassGroup::with(['waliKelas.user', 'academicYear'])
            ->when($activeYear, function ($query) use ($activeYear) {
                $query->where('academic_year_id', $activeYear->id);
            })
            ->get();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.class-groups.index', compact('classGroups', 'academicYears'));
    }



    public function create()
    {
        $gurus = Guru::with('user')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('admin.class-groups.create', compact('gurus', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_groups')->where(function ($query) use ($request) {
                    return $query->where('academic_year_id', $request->academic_year_id);
                }),
            ],
            'jenis_kelas' => 'required|in:formal,muadalah',
            'academic_year_id' => 'required|exists:academic_years,id',
            'wali_kelas_id' => 'nullable|exists:gurus,id',
        ]);

        ClassGroup::create($request->all());

        return redirect()->route('admin.class-groups.index')->with('success', 'Kelas berhasil ditambahkan');
    }


    public function edit(ClassGroup $classGroup)
    {
        $gurus = Guru::with('user')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('admin.class-groups.edit', compact('classGroup', 'gurus', 'academicYears'));
    }

    public function update(Request $request, ClassGroup $classGroup)
    {
        $request->validate([
            'nama_kelas' => [
                'required',
                'string',
                'max:255',
                Rule::unique('class_groups')
                    ->where(function ($query) use ($request) {
                        return $query->where('academic_year_id', $request->academic_year_id);
                    })
                    ->ignore($classGroup->id), // Abaikan ID-nya sendiri saat validasi
            ],
            'jenis_kelas' => 'required|in:formal,muadalah',
            'academic_year_id' => 'required|exists:academic_years,id',
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

    public function duplicateForm()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        return view('admin.class-groups.duplicate', compact('academicYears'));
    }

    public function duplicate(Request $request)
    {
        $request->validate([
            'source_year' => 'required|exists:academic_years,id',
            'target_year' => 'required|exists:academic_years,id|different:source_year',
        ]);

        $sourceYear = $request->source_year;
        $targetYear = $request->target_year;

        $sourceClasses = ClassGroup::where('academic_year_id', $sourceYear)->get();

        foreach ($sourceClasses as $class) {
            ClassGroup::create([
                'nama_kelas' => $class->nama_kelas,
                'jenis_kelas' => $class->jenis_kelas,
                'academic_year_id' => $targetYear,
                'wali_kelas_id' => null,
            ]);
        }

        return redirect()->route('admin.class-groups.index')->with('success', 'Kelas berhasil diduplikat ke tahun ajaran baru.');
    }


}
