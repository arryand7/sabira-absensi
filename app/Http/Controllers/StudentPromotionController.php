<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use Illuminate\Http\Request;

class StudentPromotionController extends Controller
{
    public function index()
    {
        $inactiveYears = AcademicYear::where('is_active', false)->pluck('id');
        $activeYears = AcademicYear::where('is_active', true)->pluck('id');

        $fromClasses = ClassGroup::with('academicYear')
            ->whereIn('academic_year_id', $inactiveYears)
            ->get();

        $toClasses = ClassGroup::with('academicYear')
            ->whereIn('academic_year_id', $activeYears)
            ->get();

        return view('admin.promotion.index', [
            'fromClasses' => $fromClasses,
            'toClasses' => $toClasses,
        ]);
    }


    public function promote(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:class_groups,id',
            'to_class_id' => 'required|exists:class_groups,id',
        ]);

        $fromClass = ClassGroup::findOrFail($request->from_class_id);
        $toClass = ClassGroup::findOrFail($request->to_class_id);

        $students = ClassGroupStudent::where('class_group_id', $fromClass->id)
            ->where('academic_year_id', $fromClass->academic_year_id)
            ->get();

        $count = 0;

        foreach ($students as $student) {
            // Cek apakah sudah dipromosikan
            $alreadyPromoted = ClassGroupStudent::where('class_group_id', $toClass->id)
                ->where('academic_year_id', $toClass->academic_year_id)
                ->where('student_id', $student->student_id)
                ->exists();

            if (!$alreadyPromoted) {
                ClassGroupStudent::create([
                    'class_group_id' => $toClass->id,
                    'student_id' => $student->student_id,
                    'academic_year_id' => $toClass->academic_year_id,
                ]);
                $count++;
            }
        }

        return back()->with('success', "Sebanyak {$count} siswa berhasil dipromosikan.");
    }

}
