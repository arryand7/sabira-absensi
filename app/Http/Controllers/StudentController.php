<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\ClassGroup;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Imports\StudentsImport;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $activeYearIds = AcademicYear::where('is_active', true)->pluck('id');

        $query = Student::with(['classGroups' => function ($q) use ($activeYearIds) {
            $q->wherePivotIn('academic_year_id', $activeYearIds);
        }]);

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        $students = $query->get()->filter(function ($student) use ($request) {
            $akademikClass = true;
            $muadalahClass = true;

            if ($request->filled('kelas_akademik')) {
                $akademikClass = $student->classGroups->firstWhere('jenis_kelas', 'akademik')?->id == $request->kelas_akademik;
            }

            if ($request->filled('kelas_muadalah')) {
                $muadalahClass = $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->id == $request->kelas_muadalah;
            }

            return $akademikClass && $muadalahClass;
        });

        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        return view('admin.students.index', compact('students', 'academicClasses', 'muadalahClasses'));
    }

    public function create()
    {
        $activeYearIds = AcademicYear::where('is_active', true)->pluck('id');

        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        return view('admin.students.create', compact('academicClasses', 'muadalahClasses'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap'    => 'required',
            'nis'             => 'required|unique:students,nis',
            'jenis_kelamin'   => 'required|in:L,P',
            'kelas_akademik'  => 'nullable|exists:class_groups,id',
            'kelas_muadalah'  => 'nullable|exists:class_groups,id',
        ]);

        $student = Student::create($request->only(['nama_lengkap', 'nis', 'jenis_kelamin']));

        if ($request->kelas_akademik) {
            $classGroup = ClassGroup::find($request->kelas_akademik);
            $student->classGroups()->attach($classGroup->id, [
                'academic_year_id' => $classGroup->academic_year_id,
            ]);
        }

        if ($request->kelas_muadalah) {
            $classGroup = ClassGroup::find($request->kelas_muadalah);
            $student->classGroups()->attach($classGroup->id, [
                'academic_year_id' => $classGroup->academic_year_id,
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Murid berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $student = Student::with('classGroups')->findOrFail($id);

        $activeYearIds = AcademicYear::where('is_active', true)->pluck('id');

        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')
            ->whereIn('academic_year_id', $activeYearIds)
            ->get();

        $kelasAkademikId = $student->classGroups->firstWhere('jenis_kelas', 'akademik')?->id;
        $kelasMuadalahId = $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->id;

        return view('admin.students.edit', compact(
            'student', 'academicClasses', 'muadalahClasses', 'kelasAkademikId', 'kelasMuadalahId'
        ));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap'    => 'required',
            'nis'             => 'required|unique:students,nis,' . $id,
            'jenis_kelamin'   => 'required|in:L,P',
            'kelas_akademik'  => 'nullable|exists:class_groups,id',
            'kelas_muadalah'  => 'nullable|exists:class_groups,id',
        ]);

        $student = Student::findOrFail($id);
        $student->update($request->only(['nama_lengkap', 'nis', 'jenis_kelamin']));

        $syncData = [];

        if ($request->kelas_akademik) {
            $classGroup = ClassGroup::find($request->kelas_akademik);
            $syncData[$classGroup->id] = ['academic_year_id' => $classGroup->academic_year_id];
        }

        if ($request->kelas_muadalah) {
            $classGroup = ClassGroup::find($request->kelas_muadalah);
            $syncData[$classGroup->id] = ['academic_year_id' => $classGroup->academic_year_id];
        }

        $student->classGroups()->sync($syncData);

        return redirect()->route('admin.students.index')->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Data murid berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return back()->with('success', 'Data murid berhasil diimpor!');
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $messages = collect($failures)->map(function ($failure) {
                return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            });

            return back()->withErrors($messages)->withInput();
        } catch (\Throwable $e) {
            Log::error('Excel import error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Terjadi kesalahan saat mengimpor file. Pastikan format file sesuai.'])->withInput();
        }
    }

    public function bulkDelete(Request $request)
    {
        \Log::info('Bulk delete request:', [
            'data' => $request->all()
        ]);

        $ids = json_decode($request->student_ids_json, true);

        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->withErrors(['Tidak ada murid yang dipilih untuk dihapus.']);
        }

        Student::whereIn('id', $ids)->delete();

        return redirect()->route('admin.students.index')->with('success', 'Murid yang dipilih berhasil dihapus.');
    }


}
