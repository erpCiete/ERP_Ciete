<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Pedido;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use App\Support\ContextGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContextCreationGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
    }

    public function test_all_context_blocks_contextual_api_creation(): void
    {
        $user = $this->createUserWithPermissions([
            'trabajos.ver',
            'trabajos.crear',
            'pedidos.ver',
            'pedidos.crear',
            'facturas.ver',
            'facturas.crear',
            'estaciones.ver',
            'estaciones.crear',
            'clientes.crear',
            'importaciones.ejecutar',
        ]);

        $this->actingAs($user);
        $user->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        foreach ([
            '/api/v1/clientes',
            '/api/v1/estaciones',
            '/api/v1/trabajos',
            '/api/v1/pedidos',
            '/api/v1/facturas',
        ] as $endpoint) {
            $this->postJson($endpoint, [])
                ->assertStatus(403)
                ->assertJsonPath('message', ContextGuard::CREATE_FROM_ALL_MESSAGE);
        }

        $this->get('/importaciones/subir')
            ->assertRedirect(route('importaciones.index'))
            ->assertSessionHas('error', ContextGuard::CREATE_FROM_ALL_MESSAGE);
    }

    public function test_all_context_can_edit_existing_records_from_accessible_contexts(): void
    {
        $user = $this->createUserWithPermissions([
            'pedidos.ver',
            'pedidos.editar',
            'estaciones.ver',
            'estaciones.editar',
            'clientes.editar',
        ]);

        $empresa = Empresa::factory()->create([
            'id_contexto' => 1,
            'nombre' => 'Cliente editable desde TODOS',
            'cif' => 'B11111119',
            'tipo_empresa' => 'cliente',
        ]);
        $estacion = EstacionServicio::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_estacion' => 'CTX-EDIT-1',
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
        ]);
        $pedido = Pedido::create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'numero_pedido' => 'PED-EDIT-TODOS',
            'fecha_solicitud' => '2026-05-06',
            'estado' => 'solicitado',
            'importe_pedido' => 10,
            'importe_solicitado' => 10,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ]);

        $this->actingAs($user);
        $user->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->patchJson("/api/v1/clientes/{$empresa->id_empresa}", [
            'nombre_comercial' => 'Cliente editado desde TODOS',
        ])->assertOk();

        $this->patchJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}", [
            'nombre' => 'Estacion editada desde TODOS',
        ])->assertOk();

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'estado' => 'recibido',
        ])->assertOk();

        $this->assertDatabaseHas('empresas', [
            'id_empresa' => $empresa->id_empresa,
            'nombre_comercial' => 'Cliente editado desde TODOS',
        ]);
        $this->assertDatabaseHas('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'nombre' => 'Estacion editada desde TODOS',
        ]);
        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'estado' => 'recibido',
        ]);
    }

    public function test_otros_clientes_has_real_context_display_name(): void
    {
        $this->assertSame('otros', ContextGuard::workspaceKeyForContextId(3));
        $this->assertSame('OTROS CLIENTES', ContextGuard::displayName('OTROS', 'OTROS CLIENTES'));
    }

    public function test_otros_clientes_can_create_contextual_records(): void
    {
        $user = $this->createUserWithPermissions([
            'trabajos.ver',
            'trabajos.crear',
            'pedidos.ver',
            'pedidos.crear',
            'estaciones.ver',
            'estaciones.crear',
            'clientes.crear',
        ]);

        $this->actingAs($user);
        $user->setActiveContextSelection(3);

        $this->postJson('/api/v1/clientes', [
            'nombre' => 'Cliente OTROS P0-05',
            'nombre_comercial' => 'OTROS CLIENTES',
            'razon_social' => 'Cliente OTROS P0-05 SL',
            'cif' => 'B12345674',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ])->assertCreated();

        $empresa = Empresa::withoutGlobalScopes()
            ->where('nombre', 'Cliente OTROS P0-05')
            ->firstOrFail();

        $this->assertSame(3, (int) $empresa->id_contexto);

        $this->postJson('/api/v1/estaciones', [
            'id_empresa_cliente' => $empresa->id_empresa,
            'nombre' => 'Estacion OTROS P0-05',
            'codigo_estacion' => 'OTROS-P005',
            'codigo_postal' => '28001',
            'poblacion' => 'Madrid',
            'provincia' => 'Madrid',
            'pais' => 'Espana',
            'activo' => true,
        ])->assertCreated();

        $estacion = EstacionServicio::withoutGlobalScopes()
            ->where('codigo_estacion', 'OTROS-P005')
            ->firstOrFail();

        $this->assertSame(3, (int) $estacion->id_contexto);

        $this->postJson('/api/v1/trabajos', [
            'numero_trabajo' => 30501,
            'descripcion_trabajo' => 'Trabajo OTROS P0-05',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => '2026-05-06',
            'estado' => 'en_curso',
        ])->assertCreated();

        $trabajo = Trabajo::withoutGlobalScopes()
            ->where('numero_trabajo', 30501)
            ->firstOrFail();

        $this->assertSame(3, (int) $trabajo->id_contexto);

        $this->postJson('/api/v1/pedidos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'numero_pedido' => 'PED-OTROS-P005',
            'fecha_solicitud' => '2026-05-06',
            'estado' => 'solicitado',
            'items' => [
                [
                    'codigo_servicio' => 'OTROS-SRV',
                    'descripcion_servicio' => 'Servicio comun OTROS',
                    'cantidad' => 1,
                    'precio_unitario' => 75,
                    'total_linea' => 75,
                ],
            ],
        ])->assertCreated();

        $pedido = Pedido::withoutGlobalScopes()
            ->where('numero_pedido', 'PED-OTROS-P005')
            ->firstOrFail();

        $this->assertSame(3, (int) $pedido->id_contexto);
        $this->assertDatabaseHas('pedido_items', [
            'id_pedido' => $pedido->id_pedido,
            'id_contexto' => 3,
            'codigo_servicio' => 'OTROS-SRV',
        ]);
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     */
    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $permissions = collect($permissionSlugs)
            ->map(fn (string $slug) => Permission::firstOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => $slug,
                    'activo' => true,
                ],
            ));

        $role = Role::firstOrCreate(
            ['slug' => 'context_guard_test_role'],
            [
                'nombre' => 'Context guard test role',
                'activo' => true,
            ],
        );
        $role->permissions()->sync($permissions->pluck('id_permiso')->all());

        $user = User::factory()->create(['id_contexto' => 3]);
        $user->roles()->sync([$role->id_rol]);
        $user->contextos()->sync([
            1 => ['es_contexto_principal' => false, 'activo' => true],
            2 => ['es_contexto_principal' => false, 'activo' => true],
            3 => ['es_contexto_principal' => true, 'activo' => true],
        ]);

        return $user;
    }
}
