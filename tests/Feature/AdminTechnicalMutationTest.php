<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\Pedido;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTechnicalMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_base_cannot_mutate_operational_modules_or_master_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        [$trabajo, $pedido, $factura] = $this->createOperationalRecords($admin);

        $this->actingAs($admin)->get('/maestros')->assertOk();
        $this->actingAs($admin)->get('/maestros/contratos')->assertOk();
        $this->actingAs($admin)->get('/maestros/contratos/crear')->assertForbidden();
        $this->actingAs($admin)->get('/maestros/tarifarios/crear')->assertForbidden();

        $this->actingAs($admin)->postJson('/api/v1/trabajos', [])->assertForbidden();
        $this->actingAs($admin)
            ->patchJson("/api/v1/trabajos/{$trabajo->id_trabajo}", ['descripcion_trabajo' => 'No permitido'])
            ->assertForbidden();
        $this->actingAs($admin)->deleteJson("/api/v1/trabajos/{$trabajo->id_trabajo}")->assertForbidden();

        $this->actingAs($admin)->postJson('/api/v1/pedidos', [])->assertForbidden();
        $this->actingAs($admin)
            ->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", ['numero_pedido' => 'NO-ALLOWED'])
            ->assertForbidden();
        $this->actingAs($admin)->deleteJson("/api/v1/pedidos/{$pedido->id_pedido}")->assertForbidden();

        $this->actingAs($admin)->postJson('/api/v1/facturas', [])->assertForbidden();
        $this->actingAs($admin)
            ->patchJson("/api/v1/facturas/{$factura->id_factura}", ['numero_factura' => 'NO-ALLOWED'])
            ->assertForbidden();
        $this->actingAs($admin)->deleteJson("/api/v1/facturas/{$factura->id_factura}")->assertForbidden();

        $this->actingAs($admin)
            ->post("/cierre/{$trabajo->id_trabajo}/cerrar")
            ->assertForbidden();
    }

    public function test_admin_ignores_legacy_operational_mutation_permissions_even_if_the_role_keeps_them(): void
    {
        $this->seed(DatabaseSeeder::class);

        $adminRole = Role::query()->where('slug', 'admin')->firstOrFail();
        $legacyPermissions = Permission::query()
            ->whereIn('slug', ['trabajos.crear', 'trabajos.editar', 'trabajos.eliminar'])
            ->pluck('id_permiso')
            ->all();

        $adminRole->permissions()->syncWithoutDetaching($legacyPermissions);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail()->fresh();

        $this->assertTrue($admin->canAccessAdminPanel());
        $this->assertFalse($admin->hasPermission('trabajos.crear'));
        $this->assertFalse($admin->hasPermission('trabajos.editar'));
        $this->assertFalse($admin->canMutateOperationalData());
        $this->assertNotContains('trabajos.editar', $admin->permission_slugs);

        $this->actingAs($admin)->get('/trabajos/crear')->assertForbidden();
    }

    /**
     * @return array{0: Trabajo, 1: Pedido, 2: Factura}
     */
    private function createOperationalRecords(User $admin): array
    {
        $contextId = $admin->id_contexto;

        $empresa = Empresa::factory()->create(['id_contexto' => $contextId]);
        $estacion = EstacionServicio::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);

        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'estado' => 'en_curso',
        ]);

        $pedido = Pedido::factory()->create([
            'id_contexto' => $contextId,
            'id_trabajo' => $trabajo->id_trabajo,
        ]);

        $factura = Factura::factory()->create([
            'id_contexto' => $contextId,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);

        return [$trabajo, $pedido, $factura];
    }
}
