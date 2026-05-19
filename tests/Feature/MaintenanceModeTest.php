<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    private string $flagFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->flagFile = storage_path('framework/maintenance_mode');

        if (file_exists($this->flagFile)) {
            unlink($this->flagFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->flagFile)) {
            unlink($this->flagFile);
        }

        parent::tearDown();
    }

    public function test_admin_can_toggle_maintenance_mode_on(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/maintenance')
            ->assertRedirect();

        $this->assertFileExists($this->flagFile);
    }

    public function test_admin_can_toggle_maintenance_mode_off(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        file_put_contents($this->flagFile, json_encode(['time' => now()->toIso8601String()]));

        $this->actingAs($admin)
            ->post('/admin/maintenance')
            ->assertRedirect();

        $this->assertFileDoesNotExist($this->flagFile);
    }

    public function test_non_admin_cannot_toggle_maintenance(): void
    {
        $this->seed(DatabaseSeeder::class);
        $usuario = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($usuario)
            ->post('/admin/maintenance')
            ->assertForbidden();
    }

    public function test_direction_cannot_toggle_maintenance(): void
    {
        $this->seed(DatabaseSeeder::class);
        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)
            ->post('/admin/maintenance')
            ->assertForbidden();
    }

    public function test_non_admin_sees_maintenance_page_when_active(): void
    {
        $this->seed(DatabaseSeeder::class);
        $usuario = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        file_put_contents($this->flagFile, json_encode(['time' => now()->toIso8601String()]));

        $response = $this->actingAs($usuario)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Maintenance'));
    }

    public function test_admin_bypasses_maintenance_mode(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        file_put_contents($this->flagFile, json_encode(['time' => now()->toIso8601String()]));

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Admin/Dashboard'));
    }

    public function test_direction_does_not_bypass_maintenance_mode(): void
    {
        $this->seed(DatabaseSeeder::class);
        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        file_put_contents($this->flagFile, json_encode(['time' => now()->toIso8601String()]));

        $response = $this->actingAs($director)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn($page) => $page->component('Maintenance'));
    }

    public function test_maintenance_state_is_shared_via_inertia(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertInertia(fn($page) => $page->where('maintenance.active', false));

        file_put_contents($this->flagFile, json_encode(['time' => now()->toIso8601String()]));

        $this->actingAs($admin)
            ->get('/admin')
            ->assertInertia(fn($page) => $page->where('maintenance.active', true));
    }
}
