<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_masters_direction_operations_and_imports(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/clientes')->assertOk();
        $this->actingAs($admin)->get('/estaciones')->assertOk();
        $this->actingAs($admin)->get('/cierre')->assertOk();
        $this->actingAs($admin)->get('/trabajos')->assertOk();
        $this->actingAs($admin)->get('/pedidos')->assertOk();
        $this->actingAs($admin)->get('/facturas')->assertOk();
        $this->actingAs($admin)->get('/importaciones')->assertOk();
    }

    public function test_director_can_access_direction_operations_and_masters_but_not_imports(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)->get('/cierre')->assertOk();
        $this->actingAs($director)->get('/trabajos')->assertOk();
        $this->actingAs($director)->get('/pedidos')->assertOk();
        $this->actingAs($director)->get('/pedidos/crear')->assertOk();
        $this->actingAs($director)->get('/facturas')->assertOk();
        $this->actingAs($director)->get('/facturas/crear')->assertOk();
        $this->actingAs($director)->get('/clientes')->assertOk();
        $this->actingAs($director)->get('/estaciones')->assertOk();
        $this->actingAs($director)->get('/importaciones')->assertForbidden();
    }

    public function test_execution_cannot_access_direction_or_facturas_and_keeps_operational_catalogs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($ejecucion)->get('/trabajos')->assertOk();
        $this->actingAs($ejecucion)->get('/pedidos')->assertOk();
        $this->actingAs($ejecucion)->get('/estaciones')->assertOk();
        $this->actingAs($ejecucion)->get('/clientes')->assertOk();
        $this->actingAs($ejecucion)->get('/cierre')->assertForbidden();
        $this->actingAs($ejecucion)->get('/facturas')->assertForbidden();
    }

    public function test_context_execution_profiles_keep_their_operational_access_without_global_panels(): void
    {
        $this->seed(DatabaseSeeder::class);

        $moeve = User::query()->where('email', 'moeve@ciete.es')->firstOrFail();
        $repsol = User::query()->where('email', 'repsol@ciete.es')->firstOrFail();

        foreach ([$moeve, $repsol] as $user) {
            $this->actingAs($user)->get('/trabajos')->assertOk();
            $this->actingAs($user)->get('/pedidos')->assertOk();
            $this->actingAs($user)->get('/estaciones')->assertOk();
            $this->actingAs($user)->get('/clientes')->assertOk();
            $this->actingAs($user)->get('/cierre')->assertForbidden();
            $this->actingAs($user)->get('/facturas')->assertForbidden();
        }
    }

    public function test_contable_can_access_traceable_trabajos_pedidos_y_facturas_but_not_direction_or_masters(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($contable)->get('/pedidos')->assertOk();
        $this->actingAs($contable)->get('/pedidos/crear')->assertForbidden();
        $this->actingAs($contable)->get('/facturas')->assertOk();
        $this->actingAs($contable)->get('/facturas/crear')->assertOk();
        $this->actingAs($contable)->get('/trabajos')->assertOk();
        $this->actingAs($contable)->get('/cierre')->assertForbidden();
        $this->actingAs($contable)->get('/clientes')->assertForbidden();
    }
}
