<?php

namespace Tests\Feature;

use App\Models\AbsensiKaryawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CanonicalAppShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_representative_role_pages_render_the_canonical_shell_and_theme_controls(): void
    {
        $cases = [
            ['super_admin', 'admin.dashboard'],
            ['guru', 'guru.dashboard'],
            ['karyawan', 'karyawan.dashboard'],
            ['organisasi', 'asrama.index'],
            ['siswa', 'profile.edit'],
            ['wali', 'profile.edit'],
        ];

        foreach ($cases as [$role, $routeName]) {
            $user = User::factory()->create(['role' => $role, 'status' => 'aktif']);
            $response = $this->actingAs($user)->get(route($routeName));

            $response->assertOk()
                ->assertSee('sabira-app-shell', false)
                ->assertSee('sabira-footer-content', false)
                ->assertSee('SABIRA ABSENSI')
                ->assertSee('Monitoring Kehadiran dan Pembelajaran')
                ->assertSee('sabira-theme', false)
                ->assertSee('data-theme-mode', false)
                ->assertDontSee('adminlte', false)
                ->assertDontSee('jquery', false);
        }
    }

    public function test_navigation_is_role_aware_and_uses_real_named_routes(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'status' => 'aktif']);
        $this->actingAs($guru)->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee(route('guru.schedule'), false)
            ->assertSee(route('guru.history.index'), false)
            ->assertDontSee(route('users.index'), false)
            ->assertDontSee('href="#"', false);

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'aktif']);
        $this->actingAs($superAdmin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('users.index'), false)
            ->assertSee(route('admin.sync.index'), false)
            ->assertDontSee('href="#"', false);
    }

    public function test_all_interactive_page_templates_use_only_the_canonical_shell(): void
    {
        $roots = ['admin', 'auth', 'guru', 'karyawan', 'organisasi', 'profile'];

        foreach ($roots as $root) {
            foreach (File::allFiles(resource_path("views/{$root}")) as $file) {
                $path = $file->getPathname();
                if (str_contains($path, DIRECTORY_SEPARATOR.'exports'.DIRECTORY_SEPARATOR)
                    || str_contains($path, DIRECTORY_SEPARATOR.'pdf')
                    || str_contains($path, DIRECTORY_SEPARATOR.'partials'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                $source = File::get($path);
                $this->assertStringContainsString('<x-app-shell', $source, "Canonical shell tidak ditemukan pada {$path}");
                $this->assertStringNotContainsString('AdminLTE', $source, "AdminLTE masih aktif pada {$path}");
                $this->assertStringNotContainsString('DataTable(', $source, "DataTables masih aktif pada {$path}");
                $this->assertStringNotContainsString('href="#"', $source, "Placeholder link ditemukan pada {$path}");
            }
        }
    }

    public function test_theme_initializer_supports_system_light_dark_and_os_changes(): void
    {
        $source = File::get(resource_path('views/components/app-shell.blade.php'));

        $this->assertStringContainsString("const validModes = ['system', 'light', 'dark']", $source);
        $this->assertStringContainsString('localStorage.getItem(storageKey)', $source);
        $this->assertStringContainsString("matchMedia('(prefers-color-scheme: dark)')", $source);
        $this->assertStringContainsString("media.addEventListener('change'", $source);
        $this->assertStringContainsString('data-theme="light" data-theme-mode="system"', $source);
    }

    public function test_admin_can_open_the_attendance_edit_route_in_the_canonical_shell(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'status' => 'aktif']);
        $employee = User::factory()->create(['role' => 'karyawan', 'status' => 'aktif']);
        $attendance = AbsensiKaryawan::factory()->create([
            'user_id' => $employee->id,
            'waktu_absen' => now(),
            'check_in' => '07:30:00',
        ]);

        $this->actingAs($admin)->get(route('admin.absensi.edit', $attendance))
            ->assertOk()
            ->assertSee('sabira-app-shell', false)
            ->assertSee('Edit Kehadiran Kerja')
            ->assertSee($employee->name);
    }
}
