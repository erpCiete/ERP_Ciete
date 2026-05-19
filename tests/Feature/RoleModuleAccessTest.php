<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModuleAccessTest extends TestCase
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

    public function test_admin_can_access_home_technical_admin_and_read_only_operations_but_not_direction(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/profile')->assertOk();
        $this->actingAs($admin)->get('/mensajes')->assertOk();
        $this->actingAs($admin)->get('/estado')->assertOk();
        $this->actingAs($admin)->get('/soporte')->assertRedirect(route('admin.support.index'));
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/maestros')->assertOk();
        $this->actingAs($admin)->get('/admin/usuarios')->assertOk();
        $this->actingAs($admin)->get('/admin/auditoria')->assertOk();
        $this->actingAs($admin)->get('/clientes')->assertOk();
        $this->actingAs($admin)->get('/estaciones')->assertOk();
        $this->actingAs($admin)->get('/cierre')->assertForbidden();
        $this->actingAs($admin)->get('/trabajos')->assertOk();
        $this->actingAs($admin)->get('/trabajos/crear')->assertForbidden();
        $this->actingAs($admin)->get('/pedidos')->assertOk();
        $this->actingAs($admin)->get('/pedidos/crear')->assertForbidden();
        $this->actingAs($admin)->get('/facturas')->assertOk();
        $this->actingAs($admin)->get('/facturas/crear')->assertForbidden();
        $this->actingAs($admin)->get('/importaciones')->assertOk();
    }

    public function test_director_can_access_direction_operations_and_masters_but_not_imports(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)->get('/')->assertOk();
        $this->actingAs($director)->get('/dashboard')->assertOk();
        $this->actingAs($director)->get('/profile')->assertOk();
        $this->actingAs($director)->get('/mensajes')->assertOk();
        $this->actingAs($director)->get('/estado')->assertOk();
        $this->actingAs($director)->get('/soporte')->assertOk();
        $this->actingAs($director)->get('/cierre')->assertOk();
        $this->actingAs($director)->get('/trabajos')->assertOk();
        $this->actingAs($director)->get('/pedidos')->assertOk();
        $this->actingAs($director)->get('/pedidos/crear')->assertOk();
        $this->actingAs($director)->get('/facturas')->assertOk();
        $this->actingAs($director)->get('/facturas/crear')->assertOk();
        $this->actingAs($director)->get('/clientes')->assertOk();
        $this->actingAs($director)->get('/estaciones')->assertOk();
        $this->actingAs($director)->get('/maestros')->assertOk();
        $this->actingAs($director)->get('/admin/usuarios')->assertOk();
        $this->actingAs($director)->get('/admin/auditoria')->assertOk();
        $this->actingAs($director)->get('/admin/soporte')->assertForbidden();
        $this->actingAs($director)->get('/admin')->assertForbidden();
        $this->actingAs($director)->get('/importaciones')->assertForbidden();
    }

    public function test_execution_can_access_home_and_operations_but_not_admin_or_direction_panels(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($ejecucion)->get('/')->assertOk();
        $this->actingAs($ejecucion)->get('/profile')->assertOk();
        $this->actingAs($ejecucion)->get('/mensajes')->assertOk();
        $this->actingAs($ejecucion)->get('/estado')->assertOk();
        $this->actingAs($ejecucion)->get('/soporte')->assertOk();
        $this->actingAs($ejecucion)->get('/dashboard')->assertForbidden();
        $this->actingAs($ejecucion)->get('/trabajos')->assertOk();
        $this->actingAs($ejecucion)->get('/pedidos')->assertOk();
        $this->actingAs($ejecucion)->get('/estaciones')->assertOk();
        $this->actingAs($ejecucion)->get('/clientes')->assertOk();
        $this->actingAs($ejecucion)->get('/maestros')->assertForbidden();
        $this->actingAs($ejecucion)->get('/importaciones')->assertForbidden();
        $this->actingAs($ejecucion)->get('/admin')->assertForbidden();
        $this->actingAs($ejecucion)->get('/cierre')->assertForbidden();
        $this->actingAs($ejecucion)->get('/facturas')->assertForbidden();
    }

    public function test_context_execution_profiles_keep_their_operational_access_without_global_panels(): void
    {
        $this->seed(DatabaseSeeder::class);

        $moeve = User::query()->where('email', 'moeve@ciete.es')->firstOrFail();
        $repsol = User::query()->where('email', 'repsol@ciete.es')->firstOrFail();

        foreach ([$moeve, $repsol] as $user) {
            $this->actingAs($user)->get('/')->assertOk();
            $this->actingAs($user)->get('/profile')->assertOk();
            $this->actingAs($user)->get('/mensajes')->assertOk();
            $this->actingAs($user)->get('/estado')->assertOk();
            $this->actingAs($user)->get('/soporte')->assertOk();
            $this->actingAs($user)->get('/dashboard')->assertForbidden();
            $this->actingAs($user)->get('/trabajos')->assertOk();
            $this->actingAs($user)->get('/pedidos')->assertOk();
            $this->actingAs($user)->get('/estaciones')->assertOk();
            $this->actingAs($user)->get('/clientes')->assertOk();
            $this->actingAs($user)->get('/maestros')->assertForbidden();
            $this->actingAs($user)->get('/importaciones')->assertForbidden();
            $this->actingAs($user)->get('/admin')->assertForbidden();
            $this->actingAs($user)->get('/cierre')->assertForbidden();
            $this->actingAs($user)->get('/facturas')->assertForbidden();
        }
    }

    public function test_contable_can_access_home_orders_and_invoices_but_not_works_or_admin_or_direction(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($contable)->get('/')->assertOk();
        $this->actingAs($contable)->get('/profile')->assertOk();
        $this->actingAs($contable)->get('/mensajes')->assertOk();
        $this->actingAs($contable)->get('/estado')->assertOk();
        $this->actingAs($contable)->get('/soporte')->assertOk();
        $this->actingAs($contable)->get('/dashboard')->assertForbidden();
        $this->actingAs($contable)->get('/pedidos')->assertOk();
        $this->actingAs($contable)->get('/pedidos/crear')->assertForbidden();
        $this->actingAs($contable)->get('/facturas')->assertOk();
        $this->actingAs($contable)->get('/facturas/crear')->assertOk();
        $this->actingAs($contable)->get('/trabajos')->assertForbidden();
        $this->actingAs($contable)->get('/maestros')->assertForbidden();
        $this->actingAs($contable)->get('/estaciones')->assertForbidden();
        $this->actingAs($contable)->get('/clientes')->assertForbidden();
        $this->actingAs($contable)->get('/importaciones')->assertForbidden();
        $this->actingAs($contable)->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($contable)->get('/admin/auditoria')->assertForbidden();
        $this->actingAs($contable)->get('/admin')->assertForbidden();
        $this->actingAs($contable)->get('/cierre')->assertForbidden();
    }
}
