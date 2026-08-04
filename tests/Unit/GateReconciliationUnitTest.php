<?php

namespace Tests\Unit;

use App\Services\SyncReconciliationService;
use Tests\TestCase;

class GateReconciliationUnitTest extends TestCase
{
    protected SyncReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SyncReconciliationService;
    }

    /** @test */
    public function it_categorizes_users_into_all_eight_reconciliation_categories_correctly()
    {
        $gateUsers = [
            // 1. matched
            [
                'uuid' => 'uuid-1',
                'name' => 'User Match',
                'email' => 'match@example.com',
                'username' => 'matchuser',
                'type' => 'teacher',
                'status' => 'active',
                'application_access' => ['has_access' => true, 'role' => 'guru'],
            ],
            // 2. needs_update
            [
                'uuid' => 'uuid-2',
                'name' => 'User Update New Name',
                'email' => 'update@example.com',
                'username' => 'updateuser',
                'type' => 'teacher',
                'status' => 'active',
                'application_access' => ['has_access' => true, 'role' => 'guru'],
            ],
            // 3. missing_in_application
            [
                'uuid' => 'uuid-3',
                'name' => 'New Gate User',
                'email' => 'new@example.com',
                'username' => 'newuser',
                'type' => 'staff',
                'status' => 'active',
                'application_access' => ['has_access' => true, 'role' => 'karyawan'],
            ],
            // 4. access_revoked
            [
                'uuid' => 'uuid-4',
                'name' => 'Revoked User',
                'email' => 'revoked@example.com',
                'username' => 'revokeduser',
                'type' => 'staff',
                'status' => 'active',
                'application_access' => ['has_access' => false],
            ],
            // 5. inactive_in_gate
            [
                'uuid' => 'uuid-5',
                'name' => 'Inactive Gate User',
                'email' => 'inactive@example.com',
                'username' => 'inactiveuser',
                'type' => 'staff',
                'status' => 'suspended',
                'application_access' => ['has_access' => true],
            ],
            // 6. reactivation_required
            [
                'uuid' => 'uuid-6',
                'name' => 'Reactivate User',
                'email' => 'reactivate@example.com',
                'username' => 'reactivateuser',
                'type' => 'staff',
                'status' => 'active',
                'application_access' => ['has_access' => true],
            ],
            // 8. conflict (unlinked matching email)
            [
                'uuid' => 'uuid-8',
                'name' => 'Conflict User',
                'email' => 'conflict@example.com',
                'username' => 'conflictuser',
                'type' => 'staff',
                'status' => 'active',
                'application_access' => ['has_access' => true],
            ],
        ];

        $localUsers = [
            // local for uuid-1
            [
                'id' => 1,
                'gate_user_uuid' => 'uuid-1',
                'name' => 'User Match',
                'email' => 'match@example.com',
                'username' => 'matchuser',
                'application_role' => 'guru',
                'status' => 'aktif',
            ],
            // local for uuid-2 (name differs)
            [
                'id' => 2,
                'gate_user_uuid' => 'uuid-2',
                'name' => 'User Update Old Name',
                'email' => 'update@example.com',
                'username' => 'updateuser',
                'application_role' => 'guru',
                'status' => 'aktif',
            ],
            // local for uuid-4
            [
                'id' => 4,
                'gate_user_uuid' => 'uuid-4',
                'name' => 'Revoked User',
                'email' => 'revoked@example.com',
                'username' => 'revokeduser',
                'status' => 'aktif',
            ],
            // local for uuid-5
            [
                'id' => 5,
                'gate_user_uuid' => 'uuid-5',
                'name' => 'Inactive Gate User',
                'email' => 'inactive@example.com',
                'username' => 'inactiveuser',
                'status' => 'aktif',
            ],
            // local for uuid-6 (currently suspended in local DB)
            [
                'id' => 6,
                'gate_user_uuid' => 'uuid-6',
                'name' => 'Reactivate User',
                'email' => 'reactivate@example.com',
                'username' => 'reactivateuser',
                'status' => 'suspended',
            ],
            // local for uuid-7 (7. local_only)
            [
                'id' => 7,
                'gate_user_uuid' => null,
                'name' => 'Local Only User',
                'email' => 'localonly@example.com',
                'username' => 'localonly',
                'status' => 'aktif',
            ],
            // local matching conflict (same email as uuid-8, but gate_user_uuid is NULL)
            [
                'id' => 8,
                'gate_user_uuid' => null,
                'name' => 'Conflict Local User',
                'email' => 'conflict@example.com',
                'username' => 'conflictuser',
                'status' => 'aktif',
            ],
        ];

        $report = $this->service->reconcile($gateUsers, $localUsers);

        $this->assertCount(1, $report['matched']);
        $this->assertCount(1, $report['needs_update']);
        $this->assertCount(1, $report['missing_in_application']);
        $this->assertCount(1, $report['access_revoked']);
        $this->assertCount(1, $report['inactive_in_gate']);
        $this->assertCount(1, $report['reactivation_required']);
        $this->assertCount(1, $report['local_only']);
        $this->assertCount(1, $report['conflict']);
    }
}
