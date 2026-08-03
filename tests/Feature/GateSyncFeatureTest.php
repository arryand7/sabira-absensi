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
                        'type' => 'santri',
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
                        'type' => 'guru',
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
}
