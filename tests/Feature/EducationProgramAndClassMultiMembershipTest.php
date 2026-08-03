<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\EducationProgram;
use App\Models\Guru;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationProgramAndClassMultiMembershipTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $academicYear;

    protected EducationProgram $formalProgram;

    protected EducationProgram $muadalahProgram;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->formalProgram = EducationProgram::where('code', 'formal')->first();
        $this->muadalahProgram = EducationProgram::where('code', 'muadalah')->first();
    }

    /** @test */
    public function default_education_programs_exist()
    {
        $this->assertDatabaseHas('education_programs', [
            'code' => 'formal',
            'default_start_time' => '07:15:00',
            'default_end_time' => '13:05:00',
        ]);

        $this->assertDatabaseHas('education_programs', [
            'code' => 'muadalah',
            'default_start_time' => '16:00:00',
            'default_end_time' => '20:00:00',
        ]);
    }

    /** @test */
    public function student_can_belong_to_multiple_regular_and_non_regular_classes_simultaneously()
    {
        $student = Student::create([
            'nis' => '2001',
            'nama_lengkap' => 'Ahmad Multitalenta',
            'jenis_kelamin' => 'L',
        ]);

        // 1. Formal Reguler: XI Sains 1
        $formalReg = ClassGroup::create([
            'nama_kelas' => 'XI Sains 1',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'reguler',
            'academic_year_id' => $this->academicYear->id,
        ]);

        // 2. Formal Nonreguler: Cambridge ICT
        $formalNonReg1 = ClassGroup::create([
            'nama_kelas' => 'Cambridge ICT',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'non_reguler',
            'academic_year_id' => $this->academicYear->id,
        ]);

        // 3. Muadalah Reguler: Kelas 5A
        $muadalahReg = ClassGroup::create([
            'nama_kelas' => 'Kelas 5A',
            'jenis_kelas' => 'muadalah',
            'education_program_id' => $this->muadalahProgram->id,
            'class_type' => 'reguler',
            'academic_year_id' => $this->academicYear->id,
        ]);

        // 4. Muadalah Nonreguler: Tahfidz Intensif
        $muadalahNonReg = ClassGroup::create([
            'nama_kelas' => 'Tahfidz Intensif',
            'jenis_kelas' => 'muadalah',
            'education_program_id' => $this->muadalahProgram->id,
            'class_type' => 'non_reguler',
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Enroll student in all 4 classes
        foreach ([$formalReg, $formalNonReg1, $muadalahReg, $muadalahNonReg] as $class) {
            ClassGroupStudent::create([
                'class_group_id' => $class->id,
                'student_id' => $student->id,
                'academic_year_id' => $this->academicYear->id,
                'joined_at' => now(),
                'status' => 'active',
                'enrollment_source' => 'manual',
            ]);
        }

        $this->assertEquals(4, $student->classGroups()->count());
        $this->assertEquals(2, $student->nonRegularClasses()->count());
    }

    /** @test */
    public function student_membership_history_is_preserved_when_leaving_a_class()
    {
        $student = Student::create([
            'nis' => '2002',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
        ]);

        $classGroup = ClassGroup::create([
            'nama_kelas' => 'X IPA 2',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'reguler',
            'academic_year_id' => $this->academicYear->id,
        ]);

        $pivot = ClassGroupStudent::create([
            'class_group_id' => $classGroup->id,
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'joined_at' => '2025-07-01 08:00:00',
            'status' => 'active',
        ]);

        // Student leaves/transfers from class
        $pivot->update([
            'left_at' => '2025-12-31 17:00:00',
            'status' => 'transferred',
        ]);

        $this->assertDatabaseHas('class_group_student', [
            'student_id' => $student->id,
            'class_group_id' => $classGroup->id,
            'status' => 'transferred',
        ]);

        $this->assertEquals(0, $classGroup->activeStudents()->count());
        $this->assertEquals(1, $classGroup->students()->count()); // Historical record preserved
    }

    /** @test */
    public function teacher_can_be_assigned_to_both_formal_and_muadalah_programs()
    {
        $user = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'jenis' => 'formal',
        ]);

        // Attach both formal and muadalah programs
        $guru->educationPrograms()->attach([
            $this->formalProgram->id => ['status' => 'active'],
            $this->muadalahProgram->id => ['status' => 'active'],
        ]);

        $this->assertTrue($guru->teachesProgram('formal'));
        $this->assertTrue($guru->teachesProgram('muadalah'));
        $this->assertEquals(2, $guru->educationPrograms()->count());
    }
}
