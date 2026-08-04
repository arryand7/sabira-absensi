<?php

namespace Tests\Feature;

use App\Models\GateSyncRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GateSyncFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $guruUser;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.gate.url', 'https://gate.sabira-iibs.id');
        Config::set('services.gate.client_id', 'test-client-id');
        Config::set('services.gate.client_secret', 'test-client-secret');

        $this->adminUser = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        $this->guruUser = User::factory()->create([
            'role' => 'guru',
            'status' => 'aktif',
        ]);
    }

    /** @test */
    public function non_admin_users_cannot_access_sync_endpoints()
    {
        $response = $this->actingAs($this->guruUser)
            ->get(route('admin.sync.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_admin_cannot_access_gate_sync_endpoints()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'aktif']);

        $this->actingAs($admin)->get(route('admin.sync.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.sync.preview'))->assertForbidden();
    }

    /** @test */
    public function dry_run_preview_fetches_gate_users_and_creates_preview_run_without_altering_user_table()
    {
        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response([
                'users' => [
                    [
                        'uuid' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                        'name' => 'Santri Baru',
                        'email' => 'santri@example.com',
                        'username' => 'santribaru',
                        'type' => 'student',
                        'status' => 'active',
                        'application_access' => ['has_access' => true, 'role' => 'karyawan'],
                    ],
                ],
            ], 200),
        ]);

        $initialUserCount = User::count();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.sync.preview'));

        $run = GateSyncRun::latest()->first();

        $response->assertRedirect(route('admin.sync.show', $run->id));
        $this->assertDatabaseCount('users', $initialUserCount); // No user table alteration during preview!
        $this->assertDatabaseHas('gate_sync_runs', [
            'id' => $run->id,
            'status' => 'previewed',
        ]);
        $this->assertDatabaseHas('gate_sync_items', [
            'gate_sync_run_id' => $run->id,
            'category' => 'missing_in_application',
        ]);
    }

    /** @test */
    public function applying_sync_creates_local_user_and_reports_result_to_gate_sso()
    {
        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response([
                'users' => [
                    [
                        'uuid' => 'b2c3d4e5-f6a7-8901-bcde-f23456789012',
                        'name' => 'Guru Gate',
                        'email' => 'gurugate@example.com',
                        'username' => 'gurugate',
                        'type' => 'teacher',
                        'status' => 'active',
                        'application_access' => ['has_access' => true, 'role' => 'guru'],
                    ],
                ],
            ], 200),
            'https://gate.sabira-iibs.id/api/provisioning/sync-results' => Http::response([
                'success' => true,
            ], 200),
        ]);

        // Step 1: Preview
        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->first();

        // Step 2: Apply
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.sync.apply', $run->id));

        $response->assertRedirect(route('admin.sync.show', $run->id));
        $response->assertSessionHas('success');

        // Verify local DB transaction applied
        $this->assertDatabaseHas('users', [
            'gate_user_uuid' => 'b2c3d4e5-f6a7-8901-bcde-f23456789012',
            'email' => 'gurugate@example.com',
            'status' => 'aktif',
            'auth_source' => 'gate',
        ]);

        // Verify result reported to Gate API
        Http::assertSent(function ($request) {
            return $request->url() === 'https://gate.sabira-iibs.id/api/provisioning/sync-results' &&
                $request->hasHeader('X-Client-Id', 'test-client-id') &&
                $request->hasHeader('X-Client-Secret', 'test-client-secret');
        });
    }

    public function test_all_canonical_gate_user_types_are_mapped_to_supported_local_roles(): void
    {
        $gateUsers = [
            $this->canonicalGateUser('11111111-1111-4111-8111-111111111111', 'student', 'student@example.com'),
            $this->canonicalGateUser('22222222-2222-4222-8222-222222222222', 'teacher', 'teacher@example.com'),
            $this->canonicalGateUser('33333333-3333-4333-8333-333333333333', 'parent', null),
            $this->canonicalGateUser('44444444-4444-4444-8444-444444444444', 'staff', 'staff@example.com'),
            $this->canonicalGateUser('55555555-5555-4555-8555-555555555555', 'admin', 'gate-admin@example.com'),
        ];

        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response(['users' => $gateUsers]),
            'https://gate.sabira-iibs.id/api/provisioning/sync-results' => Http::response(['success' => true]),
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->firstOrFail();

        $this->assertSame(5, $run->items()->where('category', 'missing_in_application')->count());
        $this->post(route('admin.sync.apply', $run))->assertSessionHas('success');

        foreach ([
            'student' => 'siswa',
            'teacher' => 'guru',
            'parent' => 'wali',
            'staff' => 'karyawan',
            'admin' => 'admin',
        ] as $type => $role) {
            $this->assertDatabaseHas('users', [
                'username' => "gate-{$type}",
                'type' => $type,
                'role' => $role,
                'application_role' => null,
                'status' => 'aktif',
                'auth_source' => 'gate',
            ]);
        }

        $studentUser = User::query()->where('username', 'gate-student')->firstOrFail();
        $teacherUser = User::query()->where('username', 'gate-teacher')->firstOrFail();
        $staffUser = User::query()->where('username', 'gate-staff')->firstOrFail();

        $this->assertDatabaseHas('students', [
            'user_id' => $studentUser->id,
            'nis' => 'NIS-STUDENT',
            'nama_lengkap' => 'Gate Student',
        ]);
        $this->assertDatabaseHas('karyawan', [
            'user_id' => $teacherUser->id,
            'nip' => 'NIP-TEACHER',
            'nama_lengkap' => 'Gate Teacher',
        ]);
        $this->assertDatabaseHas('gurus', ['user_id' => $teacherUser->id, 'jenis' => 'formal']);
        $this->assertDatabaseHas('karyawan', [
            'user_id' => $staffUser->id,
            'nip' => 'NIP-STAFF',
            'nama_lengkap' => 'Gate Staff',
        ]);
    }

    public function test_unknown_gate_application_role_becomes_preview_conflict_and_is_not_inserted(): void
    {
        $gateUser = $this->canonicalGateUser(
            '66666666-6666-4666-8666-666666666666',
            'staff',
            'unknown-role@example.com',
            'finance-approver',
        );

        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response(['users' => [$gateUser]]),
            'https://gate.sabira-iibs.id/api/provisioning/sync-results' => Http::response(['success' => true]),
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->firstOrFail();

        $this->assertDatabaseHas('gate_sync_items', [
            'gate_sync_run_id' => $run->id,
            'category' => 'conflict',
            'error_code' => 'unsupported_gate_mapping',
            'selected_action' => 'manual_review',
        ]);

        $this->post(route('admin.sync.apply', $run))->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['username' => 'gate-staff']);
        $this->assertDatabaseHas('gate_sync_items', [
            'gate_sync_run_id' => $run->id,
            'result_status' => 'skipped',
            'error_code' => 'unsupported_gate_mapping',
        ]);
    }

    public function test_existing_synced_users_with_missing_domain_profiles_are_repaired_on_next_apply(): void
    {
        $studentPayload = $this->canonicalGateUser(
            '77777777-7777-4777-8777-777777777777',
            'student',
            'linked-student@example.com',
        );
        $teacherPayload = $this->canonicalGateUser(
            '88888888-8888-4888-8888-888888888888',
            'teacher',
            'linked-teacher@example.com',
        );

        $studentUser = User::factory()->create([
            'gate_user_uuid' => $studentPayload['uuid'],
            'name' => $studentPayload['name'],
            'email' => $studentPayload['email'],
            'username' => $studentPayload['username'],
            'type' => 'student',
            'role' => 'siswa',
            'application_role' => null,
            'status' => 'aktif',
            'auth_source' => 'gate',
        ]);
        $teacherUser = User::factory()->create([
            'gate_user_uuid' => $teacherPayload['uuid'],
            'name' => $teacherPayload['name'],
            'email' => $teacherPayload['email'],
            'username' => $teacherPayload['username'],
            'type' => 'teacher',
            'role' => 'guru',
            'application_role' => null,
            'status' => 'aktif',
            'auth_source' => 'gate',
        ]);

        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response([
                'users' => [$studentPayload, $teacherPayload],
            ]),
            'https://gate.sabira-iibs.id/api/provisioning/sync-results' => Http::response(['success' => true]),
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->firstOrFail();

        $this->assertSame(2, $run->items()->where('category', 'needs_update')->count());
        $this->post(route('admin.sync.apply', $run))->assertSessionHas('success');

        $this->assertDatabaseHas('students', ['user_id' => $studentUser->id, 'nis' => 'NIS-STUDENT']);
        $this->assertDatabaseHas('karyawan', [
            'user_id' => $teacherUser->id,
            'nip' => 'NIP-TEACHER',
            'nama_lengkap' => 'Gate Teacher',
        ]);
        $this->assertDatabaseHas('gurus', ['user_id' => $teacherUser->id, 'jenis' => 'formal']);
    }

    /** @test */
    public function suspended_user_is_logged_out_and_blocked_by_middleware()
    {
        $suspendedUser = User::factory()->create([
            'role' => 'karyawan',
            'status' => 'suspended',
        ]);

        $response = $this->actingAs($suspendedUser)
            ->get(route('karyawan.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_super_admin_can_choose_to_skip_an_item_before_apply(): void
    {
        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response([
                'users' => [[
                    'uuid' => 'c3d4e5f6-a7b8-9012-cdef-345678901234',
                    'name' => 'Akun Yang Dilewati',
                    'email' => 'skip@example.com',
                    'type' => 'staff',
                    'status' => 'active',
                    'application_access' => ['has_access' => true, 'role' => 'karyawan'],
                ]],
            ]),
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->firstOrFail();
        $item = $run->items()->where('category', 'missing_in_application')->firstOrFail();

        $this->put(route('admin.sync.actions.update', $run), [
            'actions' => [$item->id => 'no_change'],
        ])->assertSessionHas('success');

        $this->post(route('admin.sync.apply', $run))->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['email' => 'skip@example.com']);
        $this->assertDatabaseHas('gate_sync_items', [
            'id' => $item->id,
            'selected_action' => 'no_change',
            'result_status' => 'skipped',
        ]);
    }

    public function test_report_pending_can_be_retried_from_ui_without_reapplying_local_changes(): void
    {
        Http::fake([
            'https://gate.sabira-iibs.id/api/provisioning/users' => Http::response([
                'users' => [[
                    'uuid' => 'd4e5f6a7-b8c9-0123-defa-456789012345',
                    'name' => 'Akun Retry',
                    'email' => 'retry@example.com',
                    'type' => 'staff',
                    'status' => 'active',
                    'application_access' => ['has_access' => true, 'role' => 'karyawan'],
                ]],
            ]),
            'https://gate.sabira-iibs.id/api/provisioning/sync-results' => Http::sequence()
                ->push(['message' => 'temporary failure'], 503)
                ->push(['success' => true], 200),
        ]);

        $this->actingAs($this->adminUser)->post(route('admin.sync.preview'));
        $run = GateSyncRun::latest()->firstOrFail();
        $this->post(route('admin.sync.apply', $run));
        $this->assertDatabaseHas('gate_sync_runs', ['id' => $run->id, 'status' => 'report_pending']);
        $this->assertDatabaseCount('users', 3);

        $this->post(route('admin.sync.retry-report', $run))->assertSessionHas('success');

        $this->assertDatabaseHas('gate_sync_runs', ['id' => $run->id, 'status' => 'completed']);
        $this->assertDatabaseCount('users', 3);
        Http::assertSentCount(3);
    }

    private function canonicalGateUser(string $uuid, string $type, ?string $email, ?string $applicationRole = null): array
    {
        $data = [
            'uuid' => $uuid,
            'gate_user_uuid' => $uuid,
            'name' => 'Gate '.ucfirst($type),
            'email' => $email,
            'username' => "gate-{$type}",
            'type' => $type,
            'user_type' => $type,
            'status' => 'active',
            'application_access' => [
                'has_access' => true,
                'status' => 'active',
                'role' => $applicationRole,
            ],
        ];

        if ($type === 'student') {
            $data['nis'] = 'NIS-STUDENT';
        }

        if (in_array($type, ['teacher', 'staff'], true)) {
            $data['nip'] = 'NIP-'.strtoupper($type);
        }

        return $data;
    }
}
