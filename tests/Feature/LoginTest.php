<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_redirected_to_dashboard_admin()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/redirect-after-login');

        $this->followingRedirects()
            ->get('/redirect-after-login')
            ->assertViewIs('admin.dashboard'); // ganti dengan nama view kamu
    }

    public function test_karyawan_redirected_to_dashboard_karyawan()
    {
        $user = User::factory()->create([
            'email' => 'karyawan@example.com',
            'password' => bcrypt('password'),
            'role' => 'karyawan',
        ]);

        $response = $this->post('/login', [
            'email' => 'karyawan@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/redirect-after-login');

        $this->followingRedirects()
            ->get('/redirect-after-login')
            ->assertViewIs('karyawan.dashboard');
    }

    public function test_guru_redirected_to_dashboard_guru()
    {
        $user = User::factory()->create([
            'email' => 'guru@example.com',
            'password' => bcrypt('password'),
            'role' => 'guru',
        ]);

        $response = $this->post('/login', [
            'email' => 'guru@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/redirect-after-login');

        $this->followingRedirects()
            ->get('/redirect-after-login')
            ->assertViewIs('guru.dashboard');
    }

    public function test_organisasi_redirected_to_asrama_index()
    {
        $user = User::factory()->create([
            'email' => 'org@example.com',
            'password' => bcrypt('password'),
            'role' => 'organisasi',
        ]);

        $response = $this->post('/login', [
            'email' => 'org@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/redirect-after-login');

        $this->followingRedirects()
            ->get('/redirect-after-login')
            ->assertViewIs('organisasi.index');
    }

    public function test_gate_student_and_parent_roles_redirect_to_their_profile(): void
    {
        foreach (['siswa', 'wali'] as $role) {
            $user = User::factory()->create(['role' => $role, 'status' => 'aktif']);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertRedirect(route('profile.edit'));
        }
    }
}
