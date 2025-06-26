<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentPromotionController extends Controller
{
    public function index()
    {
        $students = Student::orderBy('nama_lengkap')->get();

        $toClasses = ClassGroup::whereHas('academicYear', fn($q) => $q->where('is_active', true))->get();

        return view('admin.promotion.index', [
            'students' => $students,
            'toClasses' => $toClasses,
        ]);
    }

    public function promote(Request $request)
    {
        $request->validate([
            'to_class_id' => 'required|exists:class_groups,id',
            'student_ids' => 'required|array',
        ]);

        $class = ClassGroup::with('academicYear')->findOrFail($request->to_class_id);
        $inserted = 0;
        $errors = [];

        foreach ($request->student_ids as $studentId) {
            $alreadyInSameClass = ClassGroupStudent::where('student_id', $studentId)
                ->where('class_group_id', $class->id)
                ->exists();

            if ($alreadyInSameClass) {
                $student = Student::find($studentId);
                $errors[] = "{$student->nama_lengkap} sudah ada di kelas {$class->nama_kelas}.";
                continue;
            }

            $alreadyInSameJenis = ClassGroupStudent::where('student_id', $studentId)
                ->whereHas('classGroup', function ($q) use ($class) {
                    $q->where('academic_year_id', $class->academic_year_id)
                    ->where('jenis_kelas', $class->jenis_kelas)
                    ->where('id', '!=', $class->id);
                })
                ->exists();

            if ($alreadyInSameJenis) {
                $student = Student::find($studentId);
                $errors[] = "{$student->nama_lengkap} sudah ada di kelas lain dengan jenis {$class->jenis_kelas}.";
                continue;
            }

            ClassGroupStudent::create([
                'student_id' => $studentId,
                'academic_year_id' => $class->academic_year_id,
                'class_group_id' => $class->id,
            ]);

            $inserted++;
        }

        if ($inserted > 0) {
            session()->flash('success', "$inserted siswa berhasil dipindahkan.");
        }

        if (!empty($errors)) {
            session()->flash('error', implode('<br>', $errors));
        }

        return back();
    }

}
