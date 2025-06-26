<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    public function test_guru_redirected_to_dashboard_karyawan()
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
            ->assertViewIs('karyawan.dashboard');
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
}
