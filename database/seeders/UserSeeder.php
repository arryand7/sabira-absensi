<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('SABIRA_ADMIN_EMAIL', 'admin@sabira.test');
        $password = env('SABIRA_ADMIN_PASSWORD');
        $existing = User::where('email', $email)->first();

        if (! $existing && ! $password && app()->environment('production')) {
            throw new RuntimeException('SABIRA_ADMIN_PASSWORD wajib dikonfigurasi sebelum menjalankan seeder di production.');
        }

        if (! $existing && ! $password) {
            $password = Str::password(24);
            $this->command?->warn("Password superadmin lokal dibuat otomatis: {$password}");
        }

        $user = $existing ?? User::create([
            'name' => 'Admin Sabira',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'status' => 'aktif',
        ]);

        if ($existing) {
            $user->update(['role' => 'super_admin', 'status' => 'aktif']);
        }
    }
}
