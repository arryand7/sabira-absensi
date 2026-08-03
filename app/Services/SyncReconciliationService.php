<?php

namespace App\Services;

class SyncReconciliationService
{
    /**
     * Categorize users into 8 reconciliation categories.
     */
    public function reconcile(array $gateUsers, iterable $localUsers): array
    {
        $categories = [
            'matched' => [],
            'needs_update' => [],
            'missing_in_application' => [],
            'access_revoked' => [],
            'inactive_in_gate' => [],
            'reactivation_required' => [],
            'local_only' => [],
            'conflict' => [],
        ];

        // Index local users by UUID, email, and username
        $localByUuid = [];
        $localByEmail = [];
        $localByUsername = [];

        foreach ($localUsers as $user) {
            $userArray = is_object($user) ? $user->toArray() : $user;
            if (! empty($userArray['gate_user_uuid'])) {
                $localByUuid[$userArray['gate_user_uuid']] = $userArray;
            }
            if (! empty($userArray['email'])) {
                $localByEmail[strtolower($userArray['email'])] = $userArray;
            }
            if (! empty($userArray['username'])) {
                $localByUsername[strtolower($userArray['username'])] = $userArray;
            }
        }

        $processedLocalUserIds = [];

        foreach ($gateUsers as $gu) {
            $uuid = $gu['uuid'] ?? null;
            $email = strtolower($gu['email'] ?? '');
            $username = strtolower($gu['username'] ?? '');

            $gateStatus = $gu['status'] ?? 'active';
            $access = $gu['application_access'] ?? ['has_access' => true, 'role' => 'karyawan'];
            $hasAccess = $access['has_access'] ?? true;

            // Scenario A: Linked via UUID
            if ($uuid && isset($localByUuid[$uuid])) {
                $lu = $localByUuid[$uuid];
                $processedLocalUserIds[] = $lu['id'];

                $localStatus = $lu['status'] ?? 'aktif';
                $isLocalSuspended = in_array($localStatus, ['suspended', 'nonaktif'], true);

                if (! $hasAccess) {
                    $categories['access_revoked'][] = [
                        'gate_user' => $gu,
                        'local_user' => $lu,
                        'suggested_action' => 'suspend_local',
                    ];
                } elseif ($gateStatus !== 'active') {
                    $categories['inactive_in_gate'][] = [
                        'gate_user' => $gu,
                        'local_user' => $lu,
                        'suggested_action' => 'suspend_local',
                    ];
                } elseif ($isLocalSuspended) {
                    $categories['reactivation_required'][] = [
                        'gate_user' => $gu,
                        'local_user' => $lu,
                        'suggested_action' => 'reactivate_local',
                    ];
                } else {
                    // Check field differences
                    $differences = $this->computeDifferences($gu, $lu);
                    if (! empty($differences)) {
                        $categories['needs_update'][] = [
                            'gate_user' => $gu,
                            'local_user' => $lu,
                            'differences' => $differences,
                            'suggested_action' => 'update_local',
                        ];
                    } else {
                        $categories['matched'][] = [
                            'gate_user' => $gu,
                            'local_user' => $lu,
                            'suggested_action' => 'no_change',
                        ];
                    }
                }

                continue;
            }

            // Scenario B: UUID not linked, but email or username matches local user (Conflict)
            $matchingLocalByEmail = $email ? ($localByEmail[$email] ?? null) : null;
            $matchingLocalByUsername = $username ? ($localByUsername[$username] ?? null) : null;
            $matchingLocal = $matchingLocalByEmail ?: $matchingLocalByUsername;

            if ($matchingLocal && empty($matchingLocal['gate_user_uuid'])) {
                $processedLocalUserIds[] = $matchingLocal['id'];
                $categories['conflict'][] = [
                    'gate_user' => $gu,
                    'local_user' => $matchingLocal,
                    'conflict_reason' => 'Email atau username cocok tetapi gate_user_uuid belum terhubung.',
                    'suggested_action' => 'manual_review',
                ];

                continue;
            }

            // Scenario C: No local user found (Missing in application)
            if ($hasAccess && $gateStatus === 'active') {
                $categories['missing_in_application'][] = [
                    'gate_user' => $gu,
                    'suggested_action' => 'create_local',
                ];
            }
        }

        // Scenario D: Local users not found in Gate (Local Only)
        foreach ($localUsers as $user) {
            $userArray = is_object($user) ? $user->toArray() : $user;
            if (! in_array($userArray['id'], $processedLocalUserIds, true)) {
                $categories['local_only'][] = [
                    'local_user' => $userArray,
                    'suggested_action' => 'manual_review',
                ];
            }
        }

        return $categories;
    }

    protected function computeDifferences(array $gu, array $lu): array
    {
        $diff = [];

        if (($gu['name'] ?? '') !== ($lu['name'] ?? '')) {
            $diff['name'] = ['gate' => $gu['name'] ?? '', 'local' => $lu['name'] ?? ''];
        }
        if (($gu['email'] ?? '') !== ($lu['email'] ?? '')) {
            $diff['email'] = ['gate' => $gu['email'] ?? '', 'local' => $lu['email'] ?? ''];
        }
        if (($gu['username'] ?? '') !== ($lu['username'] ?? '')) {
            $diff['username'] = ['gate' => $gu['username'] ?? '', 'local' => $lu['username'] ?? ''];
        }
        $gateRole = $gu['application_access']['role'] ?? ($gu['type'] ?? '');
        $localRole = $lu['application_role'] ?? ($lu['role'] ?? '');
        if ($gateRole && $gateRole !== $localRole) {
            $diff['role'] = ['gate' => $gateRole, 'local' => $localRole];
        }

        return $diff;
    }
}
