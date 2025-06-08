<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Validators\ValidationException;

class StudentController extends Controller
{

    public function index(Request $request)
    {
        $query = Student::with('classGroups');

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

        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')->get();
        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')->get();

        return view('admin.students.index', compact('students', 'academicClasses', 'muadalahClasses'));
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new \App\Imports\StudentsImport, $request->file('file'));
            return back()->with('success', 'Data murid berhasil diimpor!');
        } catch (ValidationException $e) {
            $failures = $e->failures();

            // Ambil pesan error dari setiap baris
            $messages = collect($failures)->map(function ($failure) {
                return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            });

            return back()->withErrors($messages)->withInput();
        } catch (\Throwable $e) {
            // Tangkap error lainnya (misalnya header tidak cocok)
            Log::error('Excel import error: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Terjadi kesalahan saat mengimpor file. Pastikan format file sesuai.'])->withInput();
        }
    }

    public function create()
    {
        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')->get();
        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')->get();

        return view('admin.students.create', compact('academicClasses', 'muadalahClasses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nis' => 'required|unique:students,nis',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_akademik' => 'nullable|exists:class_groups,id',
            'kelas_muadalah' => 'nullable|exists:class_groups,id',
        ]);

        $student = Student::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nis' => $request->nis,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        if ($request->kelas_akademik) {
            $student->classGroups()->attach($request->kelas_akademik);
        }

        if ($request->kelas_muadalah) {
            $student->classGroups()->attach($request->kelas_muadalah);
        }

        return redirect()->route('admin.students.index')->with('success', 'Murid berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $student = Student::with('classGroups')->findOrFail($id);
        $academicClasses = ClassGroup::where('jenis_kelas', 'akademik')->get();
        $muadalahClasses = ClassGroup::where('jenis_kelas', 'muadalah')->get();

        $kelasAkademikId = $student->classGroups->firstWhere('jenis_kelas', 'akademik')?->id;
        $kelasMuadalahId = $student->classGroups->firstWhere('jenis_kelas', 'muadalah')?->id;

        return view('admin.students.edit', compact(
            'student', 'academicClasses', 'muadalahClasses', 'kelasAkademikId', 'kelasMuadalahId'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'nis' => 'required|unique:students,nis,' . $id,
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_akademik' => 'nullable|exists:class_groups,id',
            'kelas_muadalah' => 'nullable|exists:class_groups,id',
        ]);

        $student = Student::findOrFail($id);
        $student->update([
            'nama_lengkap' => $request->nama_lengkap,
            'nis' => $request->nis,
            'jenis_kelamin' => $request->jenis_kelamin,
        ]);

        // Sync ulang class_groups
        $syncIds = [];
        if ($request->kelas_akademik) $syncIds[] = $request->kelas_akademik;
        if ($request->kelas_muadalah) $syncIds[] = $request->kelas_muadalah;
        $student->classGroups()->sync($syncIds);

        return redirect()->route('admin.students.index')->with('success', 'Data murid berhasil diperbarui.');
    }


    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Data murid berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_students', []);

        if (empty($ids)) {
            return back()->withErrors(['Tidak ada murid yang dipilih untuk dihapus.']);
        }

        Student::whereIn('id', $ids)->delete();

        return back()->with('success', 'Murid yang dipilih berhasil dihapus.');
    }



}
