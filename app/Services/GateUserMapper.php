<?php

namespace App\Services;

use DomainException;

class GateUserMapper
{
    public function map(array $gateUser): array
    {
        $type = $this->normalize($gateUser['type'] ?? $gateUser['user_type'] ?? null);
        $status = $this->normalize($gateUser['status'] ?? 'active');
        $applicationRole = $this->normalize($gateUser['application_access']['role'] ?? null);

        if (! in_array($type, config('gate-sync.canonical_types', []), true)) {
            throw new DomainException("Gate type tidak didukung: {$type}");
        }

        if (! in_array($status, config('gate-sync.canonical_statuses', []), true)) {
            throw new DomainException("Gate status tidak didukung: {$status}");
        }

        $localRole = $applicationRole === null
            ? config("gate-sync.type_to_local_role_map.{$type}")
            : config("gate-sync.application_role_map.{$applicationRole}");

        if ($localRole === null) {
            $source = $applicationRole === null ? "type {$type}" : "application role {$applicationRole}";

            throw new DomainException("Belum ada mapping role lokal untuk {$source}.");
        }

        if (! in_array($localRole, config('gate-sync.allowed_local_roles', []), true)) {
            throw new DomainException("Role lokal tidak diterima schema Sabira Absensi: {$localRole}");
        }

        $localStatus = config("gate-sync.status_map.{$status}");
        if ($localStatus === null) {
            throw new DomainException("Belum ada mapping status lokal untuk Gate status {$status}.");
        }

        return [
            'gate_user_uuid' => $gateUser['gate_user_uuid'] ?? $gateUser['uuid'] ?? null,
            'name' => $gateUser['name'] ?? null,
            'email' => $gateUser['email'] ?? null,
            'username' => $gateUser['username'] ?? null,
            'type' => $type,
            'role' => $localRole,
            'application_role' => $applicationRole,
            'status' => $localStatus,
            'auth_source' => 'gate',
        ];
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return strtolower(trim($value));
    }
}
