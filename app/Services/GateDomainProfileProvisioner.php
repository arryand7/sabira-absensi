<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Karyawan;
use App\Models\Student;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Schema;

class GateDomainProfileProvisioner
{
    public function validate(array $gateUser, array $mappedUser, ?int $localUserId = null): void
    {
        if ($mappedUser['role'] === 'siswa') {
            $nis = $this->identifier($gateUser['nis'] ?? null);
            if ($nis === null) {
                throw new DomainException('User student tidak memiliki NIS; profil siswa tidak dapat dibuat otomatis.');
            }

            $existing = Student::query()->where('nis', $nis)->first();
            if ($existing?->user_id !== null && $existing->user_id !== $localUserId) {
                throw new DomainException("NIS {$nis} sudah terhubung ke user lokal lain.");
            }
        }

        if (in_array($mappedUser['role'], ['guru', 'karyawan', 'organisasi'], true)) {
            $nip = $this->identifier($gateUser['nip'] ?? null);
            if ($nip !== null) {
                $existing = Karyawan::query()->where('nip', $nip)->first();
                if ($existing?->user_id !== null && $existing->user_id !== $localUserId) {
                    throw new DomainException("NIP {$nip} sudah terhubung ke user lokal lain.");
                }
            }
        }
    }

    public function differences(array $gateUser, array $mappedUser, ?int $localUserId): array
    {
        if ($localUserId === null) {
            return [];
        }

        if (! Schema::hasTable('users') || ! User::query()->whereKey($localUserId)->exists()) {
            return [];
        }

        $differences = [];

        if ($mappedUser['role'] === 'siswa') {
            $student = Student::query()->where('user_id', $localUserId)->first();
            $nis = $this->identifier($gateUser['nis'] ?? null);

            if (! $student) {
                $differences['student_profile'] = ['gate' => 'create_or_link', 'local' => null];
            } elseif ($student->nis !== $nis || $student->nama_lengkap !== $mappedUser['name']) {
                $differences['student_profile'] = [
                    'gate' => ['nis' => $nis, 'name' => $mappedUser['name']],
                    'local' => ['nis' => $student->nis, 'name' => $student->nama_lengkap],
                ];
            }
        }

        if (in_array($mappedUser['role'], ['guru', 'karyawan', 'organisasi'], true)) {
            $employee = Karyawan::query()->where('user_id', $localUserId)->first();
            $nip = $this->identifier($gateUser['nip'] ?? null);

            if (! $employee) {
                $differences['employee_profile'] = ['gate' => 'create_or_link', 'local' => null];
            } elseif ($employee->nama_lengkap !== $mappedUser['name']
                || ($nip !== null && $employee->nip !== $nip)) {
                $differences['employee_profile'] = [
                    'gate' => ['nip' => $nip, 'name' => $mappedUser['name']],
                    'local' => ['nip' => $employee->nip, 'name' => $employee->nama_lengkap],
                ];
            }
        }

        if ($mappedUser['role'] === 'guru'
            && ! Guru::query()->where('user_id', $localUserId)->exists()) {
            $differences['teacher_profile'] = ['gate' => 'create', 'local' => null];
        }

        return $differences;
    }

    public function sync(User $user, array $gateUser): void
    {
        if ($user->role === 'siswa') {
            $this->syncStudent($user, $gateUser);
        }

        if (in_array($user->role, ['guru', 'karyawan', 'organisasi'], true)) {
            $this->syncEmployee($user, $gateUser);
        }

        if ($user->role === 'guru') {
            Guru::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['jenis' => config('gate-sync.profile_defaults.teacher_kind', 'formal')],
            );
        }
    }

    private function syncStudent(User $user, array $gateUser): void
    {
        $nis = $this->identifier($gateUser['nis'] ?? null);
        $student = Student::query()->where('user_id', $user->id)->first()
            ?? Student::query()->where('nis', $nis)->first()
            ?? new Student;

        $student->fill([
            'user_id' => $user->id,
            'nama_lengkap' => $user->name,
            'nis' => $nis,
        ]);
        $student->save();
    }

    private function syncEmployee(User $user, array $gateUser): void
    {
        $nip = $this->identifier($gateUser['nip'] ?? null);
        $employee = Karyawan::query()->where('user_id', $user->id)->first();

        if (! $employee && $nip !== null) {
            $employee = Karyawan::query()->where('nip', $nip)->first();
        }

        $employee ??= new Karyawan;
        $employee->user_id = $user->id;
        $employee->nama_lengkap = $user->name;
        if ($nip !== null) {
            $employee->nip = $nip;
        }
        $employee->save();
    }

    private function identifier(mixed $value): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
