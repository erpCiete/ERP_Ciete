<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
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

    public function test_admin_can_access_common_and_technical_administration_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/')->assertOk();
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
        $this->actingAs($admin)->get('/admin/auditoria')->assertOk();
        $this->actingAs($admin)->get('/admin/soporte')->assertOk();
    }

    public function test_admin_cannot_access_direction_or_closure_by_default(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/cierre')->assertForbidden();
    }

    public function test_contable_and_execution_cannot_access_administration_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();
        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        foreach ([$contable, $ejecucion] as $user) {
            $this->actingAs($user)->get('/admin')->assertForbidden();
            $this->actingAs($user)->get('/admin/usuarios')->assertForbidden();
            $this->actingAs($user)->get('/admin/auditoria')->assertForbidden();
            $this->actingAs($user)->get('/admin/soporte')->assertForbidden();
            $this->actingAs($user)->post('/admin/maintenance')->assertForbidden();
        }
    }
}
