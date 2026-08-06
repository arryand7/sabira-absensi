<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupStudent;
use App\Models\EducationProgram;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $superAdminUser;

    protected User $regularUser;

    protected AcademicYear $academicYear;

    protected EducationProgram $formalProgram;

    protected EducationProgram $muadalahProgram;

    protected ClassGroup $formalRegClass1;

    protected ClassGroup $formalRegClass2;

    protected ClassGroup $formalNonRegClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->superAdminUser = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'karyawan',
            'status' => 'aktif',
        ]);

        $this->academicYear = AcademicYear::create([
            'name' => '2025/2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->formalProgram = EducationProgram::where('code', 'formal')->first()
            ?? EducationProgram::create([
                'code' => 'formal',
                'name' => 'Formal',
                'default_start_time' => '07:15:00',
                'default_end_time' => '13:05:00',
                'is_active' => true,
            ]);

        $this->muadalahProgram = EducationProgram::where('code', 'muadalah')->first()
            ?? EducationProgram::create([
                'code' => 'muadalah',
                'name' => 'Muadalah',
                'default_start_time' => '16:00:00',
                'default_end_time' => '20:00:00',
                'is_active' => true,
            ]);

        $this->formalRegClass1 = ClassGroup::create([
            'nama_kelas' => 'X IPA 1',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'reguler',
            'grade_level' => 'X',
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->formalRegClass2 = ClassGroup::create([
            'nama_kelas' => 'XI IPA 1',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'reguler',
            'grade_level' => 'XI',
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);

        $this->formalNonRegClass = ClassGroup::create([
            'nama_kelas' => 'Klub ICT',
            'jenis_kelas' => 'formal',
            'education_program_id' => $this->formalProgram->id,
            'class_type' => 'non_reguler',
            'grade_level' => null,
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_access_promotion_page()
    {
        $response = $this->actingAs($this->adminUser)->get('/promotion');

        $response->assertStatus(200);
        $response->assertSee('Keanggotaan Siswa');
    }

    /** @test */
    public function superadmin_can_access_promotion_page()
    {
        $response = $this->actingAs($this->superAdminUser)->get('/promotion');

        $response->assertStatus(200);
        $response->assertSee('Keanggotaan Siswa');
    }

    /** @test */
    public function unauthorized_user_is_forbidden_from_promotion_page()
    {
        $response = $this->actingAs($this->regularUser)->get('/promotion');

        $response->assertStatus(403);
    }

    /** @test */
    public function search_by_student_name_and_nis_works()
    {
        $studentA = Student::create(['nama_lengkap' => 'Achmad Fauzi', 'nis' => '1001', 'jenis_kelamin' => 'L']);
        $studentB = Student::create(['nama_lengkap' => 'Budi Santoso', 'nis' => '1002', 'jenis_kelamin' => 'L']);

        // Search name
        $resName = $this->actingAs($this->adminUser)->get('/promotion?search=fauzi');
        $resName->assertSee('Achmad Fauzi');
        $resName->assertDontSee('Budi Santoso');

        // Search NIS
        $resNis = $this->actingAs($this->adminUser)->get('/promotion?search=1002');
        $resNis->assertSee('Budi Santoso');
        $resNis->assertDontSee('Achmad Fauzi');
    }

    /** @test */
    public function filter_by_education_program_works()
    {
        $studentFormal = Student::create(['nama_lengkap' => 'Student Formal', 'nis' => '2001', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentFormal->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $muadalahClass = ClassGroup::create([
            'nama_kelas' => 'Kelas 5A',
            'jenis_kelas' => 'muadalah',
            'education_program_id' => $this->muadalahProgram->id,
            'class_type' => 'reguler',
            'academic_year_id' => $this->academicYear->id,
            'is_active' => true,
        ]);
        $studentMuadalah = Student::create(['nama_lengkap' => 'Student Muadalah', 'nis' => '2002', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentMuadalah->id,
            'class_group_id' => $muadalahClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/promotion?program_id='.$this->formalProgram->id);
        $response->assertSee('Student Formal');
        $response->assertDontSee('Student Muadalah');
    }

    /** @test */
    public function filter_by_class_type_works()
    {
        $studentReg = Student::create(['nama_lengkap' => 'Reguler Student', 'nis' => '3001', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentReg->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $studentNonReg = Student::create(['nama_lengkap' => 'NonReguler Student', 'nis' => '3002', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentNonReg->id,
            'class_group_id' => $this->formalNonRegClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/promotion?class_type=non_reguler');
        $response->assertSee('NonReguler Student');
        $response->assertDontSee('3001');
    }

    /** @test */
    public function filter_by_source_class_works()
    {
        $student1 = Student::create(['nama_lengkap' => 'Student Class One', 'nis' => '4001', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $student1->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $student2 = Student::create(['nama_lengkap' => 'Student Class Two', 'nis' => '4002', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $student2->id,
            'class_group_id' => $this->formalRegClass2->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/promotion?source_class_group_id='.$this->formalRegClass1->id);
        $response->assertSee('Student Class One');
        $response->assertDontSee('Student Class Two');
    }

    /** @test */
    public function filter_membership_status_works()
    {
        $studentActive = Student::create(['nama_lengkap' => 'Student Active', 'nis' => '5001', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentActive->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $studentNoClass = Student::create(['nama_lengkap' => 'Student NoClass', 'nis' => '5002', 'jenis_kelamin' => 'L']);

        // Test no_active
        $resNoActive = $this->actingAs($this->adminUser)->get('/promotion?membership_status=no_active');
        $resNoActive->assertSee('Student NoClass');
        $resNoActive->assertDontSee('Student Active');

        // Test has_active
        $resActive = $this->actingAs($this->adminUser)->get('/promotion?membership_status=has_active');
        $resActive->assertSee('Student Active');
        $resActive->assertDontSee('Student NoClass');
    }

    /** @test */
    public function hide_target_members_checkbox_filters_out_existing_target_members()
    {
        $studentInTarget = Student::create(['nama_lengkap' => 'Target Member', 'nis' => '6001', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentInTarget->id,
            'class_group_id' => $this->formalRegClass2->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $studentOutside = Student::create(['nama_lengkap' => 'Outside Member', 'nis' => '6002', 'jenis_kelamin' => 'L']);
        ClassGroupStudent::create([
            'student_id' => $studentOutside->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get('/promotion?to_class_id='.$this->formalRegClass2->id.'&hide_target_members=1');
        $response->assertSee('Outside Member');
        $response->assertDontSee('Target Member');
    }

    /** @test */
    public function pagination_preserves_query_string_filters()
    {
        for ($i = 1; $i <= 30; $i++) {
            Student::create(['nama_lengkap' => sprintf('Student %02d', $i), 'nis' => (string) (7000 + $i), 'jenis_kelamin' => 'L']);
        }

        $response = $this->actingAs($this->adminUser)->get('/promotion?search=Student&per_page=25&page=2');
        $response->assertStatus(200);
        $response->assertSee('search=Student');
        $response->assertSee('per_page=25');
    }

    /** @test */
    public function add_to_class_mode_creates_membership_and_preserves_existing_classes()
    {
        $student = Student::create(['nama_lengkap' => 'Ahmad AddMode', 'nis' => '8001', 'jenis_kelamin' => 'L']);

        // Student is already in RegClass1
        ClassGroupStudent::create([
            'student_id' => $student->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Add to NonRegClass using 'add' mode
        $response = $this->actingAs($this->adminUser)->post('/promote', [
            'to_class_id' => $this->formalNonRegClass->id,
            'action_mode' => 'add',
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Old membership is still active
        $this->assertDatabaseHas('class_group_student', [
            'student_id' => $student->id,
            'class_group_id' => $this->formalRegClass1->id,
            'status' => 'active',
        ]);

        // New membership created
        $this->assertDatabaseHas('class_group_student', [
            'student_id' => $student->id,
            'class_group_id' => $this->formalNonRegClass->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function transfer_regular_class_mode_closes_old_regular_membership_and_creates_new_target()
    {
        $student = Student::create(['nama_lengkap' => 'Budi TransferMode', 'nis' => '8002', 'jenis_kelamin' => 'L']);

        // Active in formalRegClass1 (Reguler)
        $oldRegPivot = ClassGroupStudent::create([
            'student_id' => $student->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Active in formalNonRegClass (Nonreguler)
        $nonRegPivot = ClassGroupStudent::create([
            'student_id' => $student->id,
            'class_group_id' => $this->formalNonRegClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Transfer to formalRegClass2 (Reguler)
        $response = $this->actingAs($this->adminUser)->post('/promote', [
            'to_class_id' => $this->formalRegClass2->id,
            'action_mode' => 'transfer',
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Old regular class is closed with status 'transferred' and left_at set
        $this->assertDatabaseHas('class_group_student', [
            'id' => $oldRegPivot->id,
            'status' => 'transferred',
        ]);
        $this->assertNotNull($oldRegPivot->fresh()->left_at);

        // Non-regular class remains untouched
        $this->assertDatabaseHas('class_group_student', [
            'id' => $nonRegPivot->id,
            'status' => 'active',
        ]);

        // New target regular class created
        $this->assertDatabaseHas('class_group_student', [
            'student_id' => $student->id,
            'class_group_id' => $this->formalRegClass2->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function transfer_mode_on_non_regular_target_class_is_rejected()
    {
        $student = Student::create(['nama_lengkap' => 'Charlie Reject', 'nis' => '8003', 'jenis_kelamin' => 'L']);

        $response = $this->actingAs($this->adminUser)->post('/promote', [
            'to_class_id' => $this->formalNonRegClass->id,
            'action_mode' => 'transfer',
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['action_mode']);
    }

    /** @test */
    public function duplicate_active_target_membership_is_skipped_with_informative_message()
    {
        $student = Student::create(['nama_lengkap' => 'Dedi Duplicate', 'nis' => '8004', 'jenis_kelamin' => 'L']);

        ClassGroupStudent::create([
            'student_id' => $student->id,
            'class_group_id' => $this->formalRegClass1->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
        ]);

        // Attempting to add again to same class
        $response = $this->actingAs($this->adminUser)->post('/promote', [
            'to_class_id' => $this->formalRegClass1->id,
            'action_mode' => 'add',
            'student_ids' => [$student->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Only 1 record exists in class_group_student
        $this->assertEquals(1, ClassGroupStudent::where('student_id', $student->id)->where('class_group_id', $this->formalRegClass1->id)->count());
    }

    /** @test */
    public function unauthorized_user_cannot_submit_promotion_action()
    {
        $student = Student::create(['nama_lengkap' => 'Eka Unauthorized', 'nis' => '8005', 'jenis_kelamin' => 'L']);

        $response = $this->actingAs($this->regularUser)->post('/promote', [
            'to_class_id' => $this->formalRegClass1->id,
            'action_mode' => 'add',
            'student_ids' => [$student->id],
        ]);

        $response->assertStatus(403);
    }
}
