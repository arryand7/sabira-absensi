<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\EducationProgram;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPromotionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $programId = $request->input('program_id');
        $classType = $request->input('class_type');
        $sourceClassGroupId = $request->input('source_class_group_id');
        $gradeLevel = $request->input('grade_level');
        $membershipStatus = $request->input('membership_status', 'all');
        $toClassId = $request->input('to_class_id');
        $perPage = in_array((int) $request->input('per_page'), [25, 50, 100], true) ? (int) $request->input('per_page') : 25;

        // Default hide_target_members is true if to_class_id is present and hide_target_members is not explicitly passed, or if hide_target_members == '1'
        $hideTargetMembers = $request->has('hide_target_members')
            ? $request->boolean('hide_target_members')
            : ($request->filled('to_class_id') ? true : false);

        $query = Student::query()
            ->with([
                'activeClassGroups' => function ($q) {
                    $q->with(['educationProgram', 'academicYear']);
                },
            ]);

        // 1. Search nama / NIS
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // 2. Program pendidikan
        if ($programId) {
            $query->whereHas('activeClassGroups', function ($q) use ($programId) {
                $q->where('education_program_id', $programId);
            });
        }

        // 3. Jenis kelas (reguler / non_reguler)
        if ($classType) {
            $query->whereHas('activeClassGroups', function ($q) use ($classType) {
                $q->where('class_type', $classType);
            });
        }

        // 4. Kelas aktif / kelas asal
        if ($sourceClassGroupId) {
            $query->whereHas('activeClassGroups', function ($q) use ($sourceClassGroupId) {
                $q->where('class_groups.id', $sourceClassGroupId);
            });
        }

        // 5. Tingkat (grade_level)
        if ($gradeLevel) {
            $query->whereHas('activeClassGroups', function ($q) use ($gradeLevel) {
                $q->where('grade_level', $gradeLevel);
            });
        }

        // 6. Status keanggotaan
        if ($membershipStatus === 'has_active') {
            $query->whereHas('activeClassGroups');
        } elseif ($membershipStatus === 'no_active') {
            $query->whereDoesntHave('activeClassGroups');
        } elseif ($membershipStatus === 'in_target' && $toClassId) {
            $query->whereHas('activeClassGroups', function ($q) use ($toClassId) {
                $q->where('class_groups.id', $toClassId);
            });
        } elseif ($membershipStatus === 'not_in_target' && $toClassId) {
            $query->whereDoesntHave('activeClassGroups', function ($q) use ($toClassId) {
                $q->where('class_groups.id', $toClassId);
            });
        }

        // 7. Sembunyikan anggota kelas tujuan
        if ($hideTargetMembers && $toClassId) {
            $query->whereDoesntHave('activeClassGroups', function ($q) use ($toClassId) {
                $q->where('class_groups.id', $toClassId);
            });
        }

        $students = $query->orderBy('nama_lengkap')->paginate($perPage)->withQueryString();

        $toClasses = ClassGroup::with(['educationProgram', 'academicYear'])
            ->whereHas('academicYear', fn ($q) => $q->where('is_active', true))
            ->orderBy('nama_kelas')
            ->get();

        $sourceClasses = ClassGroup::orderBy('nama_kelas')->get();
        $educationPrograms = EducationProgram::orderBy('name')->get();
        $gradeLevels = ClassGroup::whereNotNull('grade_level')->where('grade_level', '!=', '')->distinct()->pluck('grade_level')->sort()->values();

        $selectedTargetClass = $toClassId ? $toClasses->firstWhere('id', (int) $toClassId) : null;

        return view('admin.promotion.index', [
            'students' => $students,
            'toClasses' => $toClasses,
            'sourceClasses' => $sourceClasses,
            'educationPrograms' => $educationPrograms,
            'gradeLevels' => $gradeLevels,
            'selectedTargetClass' => $selectedTargetClass,
            'filters' => [
                'search' => $search,
                'program_id' => $programId,
                'class_type' => $classType,
                'source_class_group_id' => $sourceClassGroupId,
                'grade_level' => $gradeLevel,
                'membership_status' => $membershipStatus,
                'hide_target_members' => $hideTargetMembers,
                'to_class_id' => $toClassId,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function promote(Request $request)
    {
        $request->validate([
            'to_class_id' => 'required|exists:class_groups,id',
            'action_mode' => 'required|in:add,transfer',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $targetClass = ClassGroup::with(['academicYear', 'educationProgram'])->findOrFail($request->to_class_id);

        if ($targetClass->class_type === 'non_reguler' && $request->action_mode === 'transfer') {
            return back()->withErrors([
                'action_mode' => 'Kelas tujuan nonreguler tidak mendukung mode pindah kelas reguler. Gunakan mode Tambahkan ke Kelas.',
            ])->withInput();
        }

        $studentIds = array_unique(array_map('intval', $request->student_ids));
        $actionMode = $request->action_mode;

        $addedCount = 0;
        $transferredCount = 0;
        $skippedTargetCount = 0;
        $closedOldCount = 0;
        $errors = [];

        DB::transaction(function () use (
            $targetClass,
            $studentIds,
            $actionMode,
            &$addedCount,
            &$transferredCount,
            &$skippedTargetCount,
            &$closedOldCount,
            &$errors
        ) {
            foreach ($studentIds as $studentId) {
                $student = Student::find($studentId);
                if (! $student) {
                    $errors[] = "Siswa ID #{$studentId} tidak ditemukan.";

                    continue;
                }

                // Check if already an active member of target class
                $alreadyInTarget = ClassGroupStudent::where('student_id', $studentId)
                    ->where('class_group_id', $targetClass->id)
                    ->where('status', 'active')
                    ->exists();

                if ($alreadyInTarget) {
                    $skippedTargetCount++;
                    $errors[] = "{$student->nama_lengkap} (NIS: {$student->nis}) sudah menjadi anggota aktif di {$targetClass->nama_kelas}.";

                    continue;
                }

                if ($actionMode === 'transfer') {
                    // Find active regular memberships in the same education program and academic year
                    $activeOldPivots = ClassGroupStudent::where('student_id', $studentId)
                        ->where('status', 'active')
                        ->where('class_group_id', '!=', $targetClass->id)
                        ->whereHas('classGroup', function ($q) use ($targetClass) {
                            $q->where('academic_year_id', $targetClass->academic_year_id)
                                ->where('class_type', 'reguler');

                            if ($targetClass->education_program_id) {
                                $q->where('education_program_id', $targetClass->education_program_id);
                            }
                        })
                        ->get();

                    foreach ($activeOldPivots as $oldPivot) {
                        $oldPivot->update([
                            'left_at' => now(),
                            'status' => 'transferred',
                        ]);
                        $closedOldCount++;
                    }

                    ClassGroupStudent::create([
                        'student_id' => $studentId,
                        'class_group_id' => $targetClass->id,
                        'academic_year_id' => $targetClass->academic_year_id,
                        'joined_at' => now(),
                        'status' => 'active',
                        'enrollment_source' => 'manual',
                    ]);

                    $transferredCount++;
                } else {
                    // Mode 'add'
                    ClassGroupStudent::create([
                        'student_id' => $studentId,
                        'class_group_id' => $targetClass->id,
                        'academic_year_id' => $targetClass->academic_year_id,
                        'joined_at' => now(),
                        'status' => 'active',
                        'enrollment_source' => 'manual',
                    ]);

                    $addedCount++;
                }
            }
        });

        Log::info('Bulk student promotion completed', [
            'actor_id' => auth()->id(),
            'target_class_id' => $targetClass->id,
            'action_mode' => $actionMode,
            'total_students' => count($studentIds),
            'added' => $addedCount,
            'transferred' => $transferredCount,
            'skipped_target' => $skippedTargetCount,
            'closed_old' => $closedOldCount,
        ]);

        $summaryText = [];
        if ($addedCount > 0) {
            $summaryText[] = "Berhasil ditambahkan: {$addedCount}";
        }
        if ($transferredCount > 0) {
            $summaryText[] = "Berhasil dipindahkan: {$transferredCount}";
        }
        if ($closedOldCount > 0) {
            $summaryText[] = "Keanggotaan reguler lama ditutup: {$closedOldCount}";
        }
        if ($skippedTargetCount > 0) {
            $summaryText[] = "Dilewati (sudah anggota target): {$skippedTargetCount}";
        }

        $message = implode(' | ', $summaryText);

        if (($addedCount > 0 || $transferredCount > 0) && empty($errors)) {
            session()->flash('success', $message ?: 'Proses keanggotaan siswa berhasil.');
        } elseif (($addedCount > 0 || $transferredCount > 0) && ! empty($errors)) {
            session()->flash('success', $message);
            session()->flash('warning', implode('<br>', $errors));
        } else {
            session()->flash('error', implode('<br>', $errors) ?: 'Tidak ada siswa yang diproses.');
        }

        return back();
    }
}
