<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Trabajo;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PedidoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $gestorMoeve;
    protected User $gestorRepsol;
    protected User $usuarioLector;

    protected function setUp(): void
    {
        parent::setUp();

        // 0. Cargar los contextos base en la BD de testing
        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);

        // 1. Configuración de Permisos (Adaptado al esquema nativo de Ciete)
        $permisoVer = Permission::firstOrCreate(
            ['slug' => 'pedidos.ver'],
            ['nombre' => 'Ver Pedidos', 'activo' => true]
        );
        $permisoCrear = Permission::firstOrCreate(
            ['slug' => 'pedidos.crear'],
            ['nombre' => 'Crear Pedidos', 'activo' => true]
        );
        $permisoEditar = Permission::firstOrCreate(
            ['slug' => 'pedidos.editar'],
            ['nombre' => 'Editar Pedidos', 'activo' => true]
        );
        $permisoEliminar = Permission::firstOrCreate(
            ['slug' => 'pedidos.eliminar'],
            ['nombre' => 'Eliminar Pedidos', 'activo' => true]
        );

        // 2. Configuración de Roles
        $rolGestor = Role::firstOrCreate(
            ['slug' => 'gestor'],
            ['nombre' => 'Gestor Operativo', 'activo' => true]
        );
        $rolGestor->permissions()->sync([
            $permisoVer->id_permiso,
            $permisoCrear->id_permiso,
            $permisoEditar->id_permiso,
            $permisoEliminar->id_permiso,
        ]);

        $rolLector = Role::firstOrCreate(
            ['slug' => 'usuario'],
            ['nombre' => 'Usuario Base', 'activo' => true]
        );
        $rolLector->permissions()->sync([
            $permisoVer->id_permiso
        ]);

        // 3. Creación de Usuarios con Contextos (1 = MOEVE, 2 = REPSOL)
        $this->gestorMoeve = User::factory()->create(['id_contexto' => 1]);
        $this->gestorMoeve->roles()->sync([$rolGestor->id_rol]);

        $this->gestorRepsol = User::factory()->create(['id_contexto' => 2]);
        $this->gestorRepsol->roles()->sync([$rolGestor->id_rol]);

        $this->usuarioLector = User::factory()->create(['id_contexto' => 1]);
        $this->usuarioLector->roles()->sync([$rolLector->id_rol]);
    }

    public function test_visitante_no_puede_acceder_a_pedidos(): void
    {
        $response = $this->getJson('/api/v1/pedidos');
        $response->assertStatus(401);
    }

    public function test_usuario_lector_no_puede_crear_pedido(): void
    {
        Sanctum::actingAs($this->usuarioLector);
        $response = $this->postJson('/api/v1/pedidos', []);
        $response->assertStatus(403);
    }

    public function test_gestor_solo_ve_pedidos_de_su_contexto(): void
    {
        // Crear jerarquía completa y coherente para MOEVE (Contexto 1)
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        Pedido::factory()->count(2)->create(['id_contexto' => 1, 'id_trabajo' => $trabajoMoeve->id_trabajo]);

        // Crear jerarquía completa y coherente para REPSOL (Contexto 2)
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);
        Pedido::factory()->count(3)->create(['id_contexto' => 2, 'id_trabajo' => $trabajoRepsol->id_trabajo]);

        Sanctum::actingAs($this->gestorMoeve);

        $response = $this->getJson('/api/v1/pedidos');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // Solo debe devolver los 2 de MOEVE
            ->assertJsonPath('data.0.id_contexto', 1);
    }

    public function test_gestor_moeve_no_puede_crear_pedido_para_trabajo_repsol(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        // Creamos un Trabajo que pertenece a REPSOL (contexto 2)
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);

        $payload = [
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'numero_pedido' => 'PED-HACK-001',
            'importe_pedido' => 100,
            'importe_solicitado' => 100,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        // La validación debe bloquear el intento (422) porque el Trabajo no pertenece a MOEVE
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_trabajo']);
    }

    public function test_gestor_puede_crear_pedido_completo_con_items(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        // Preparar entorno coherente
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);

        $payload = [
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-TEST-OK-01',
            'fecha_solicitud' => '2026-04-20',
            'importe_pedido' => 1500.50,
            'importe_solicitado' => 1500.50,
            'importe_facturado' => 0,
            'unidades_pedido' => 2,
            'unidades_solicitadas' => 2,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => true,
            'facturado_completo' => false,
            'observaciones' => 'Test de integración',
            'items' => [
                [
                    'codigo_servicio' => 'SRV-01',
                    'descripcion_servicio' => 'Desplazamiento técnico',
                    'cantidad' => 2,
                    'precio_unitario' => 750.25,
                    'total_linea' => 1500.50
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.numero_pedido', 'PED-TEST-OK-01')
            ->assertJsonPath('data.importe_pedido', 1500.5);

        // Verificar BBDD principal
        $this->assertDatabaseHas('pedidos', [
            'numero_pedido' => 'PED-TEST-OK-01',
            'id_contexto' => 1,
            'importe_pedido' => 1500.50,
            'estado' => 'pendiente'
        ]);

        // Verificar líneas anidadas
        $this->assertDatabaseHas('pedido_items', [
            'codigo_servicio' => 'SRV-01',
            'total_linea' => 1500.50
        ]);
    }

    public function test_gestor_repsol_no_puede_crear_pedido_con_cantidad_o_unidades_solicitadas_decimales(): void
    {
        Sanctum::actingAs($this->gestorRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);

        $response = $this->postJson('/api/v1/pedidos', [
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'numero_pedido' => 'PED-REPSOL-DEC-01',
            'fecha_solicitud' => '2026-04-20',
            'importe_pedido' => 150.50,
            'importe_solicitado' => 150.50,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1.5,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
            'items' => [
                [
                    'codigo_servicio' => 'SRV-DEC',
                    'descripcion_servicio' => 'Servicio decimal no permitido',
                    'cantidad' => 1.5,
                    'precio_unitario' => 100.33,
                    'total_linea' => 150.50,
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unidades_solicitadas', 'items.0.cantidad']);
    }

    public function test_gestor_puede_ver_actualizar_y_cancelar_un_pedido_de_su_contexto(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-ORIGINAL-01',
        ]);

        $this->getJson("/api/v1/pedidos/{$pedido->id_pedido}")
            ->assertOk()
            ->assertJsonPath('data.id_pedido', $pedido->id_pedido);

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'numero_pedido' => 'PED-ACTUALIZADO-01',
            'estado' => 'solicitado',
        ])->assertOk()
            ->assertJsonPath('data.numero_pedido', 'PED-ACTUALIZADO-01');

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'numero_pedido' => 'PED-ACTUALIZADO-01',
            'estado' => 'solicitado',
        ]);

        $this->deleteJson("/api/v1/pedidos/{$pedido->id_pedido}")
            ->assertOk();

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'estado' => 'cancelado',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'pedidos',
            'registro_id' => $pedido->id_pedido,
            'accion' => 'cambiar_estado',
            'campo' => 'estado',
        ]);
    }

    public function test_gestor_no_puede_actualizar_pedido_con_estado_borrador_legacy(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'estado' => 'pendiente',
        ]);

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'estado' => 'borrador',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['estado']);

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'estado' => 'pendiente',
        ]);
    }

    public function test_actualizar_pedido_conserva_ids_actualiza_crea_y_elimina_solo_items_no_facturados(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-ITEMS-STABLE-01',
        ]);
        $itemActualizable = $this->createPedidoItem($pedido, [
            'codigo_servicio' => 'OLD-01',
            'descripcion_servicio' => 'Linea original',
            'cantidad' => 1,
            'precio_unitario' => 100,
            'total_linea' => 100,
        ]);
        $itemEliminable = $this->createPedidoItem($pedido, [
            'codigo_servicio' => 'DROP-01',
        ]);

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'items' => [
                [
                    'id_pedido_item' => $itemActualizable->id_pedido_item,
                    'codigo_servicio' => 'UPD-01',
                    'descripcion_servicio' => 'Linea actualizada',
                    'cantidad' => 2,
                    'precio_unitario' => 125,
                    'total_linea' => 250,
                ],
                [
                    'codigo_servicio' => 'NEW-01',
                    'descripcion_servicio' => 'Linea nueva',
                    'cantidad' => 3,
                    'precio_unitario' => 50,
                    'total_linea' => 150,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('data.items.0.id_pedido_item', $itemActualizable->id_pedido_item)
            ->assertJsonPath('data.items.0.codigo_servicio', 'UPD-01');

        $this->assertDatabaseHas('pedido_items', [
            'id_pedido_item' => $itemActualizable->id_pedido_item,
            'codigo_servicio' => 'UPD-01',
            'total_linea' => 250,
        ]);
        $this->assertDatabaseMissing('pedido_items', [
            'id_pedido_item' => $itemEliminable->id_pedido_item,
        ]);
        $this->assertDatabaseHas('pedido_items', [
            'id_pedido' => $pedido->id_pedido,
            'codigo_servicio' => 'NEW-01',
            'total_linea' => 150,
        ]);
        $this->assertSame(2, PedidoItem::query()->where('id_pedido', $pedido->id_pedido)->count());
    }

    public function test_actualizar_pedido_no_elimina_items_vinculados_a_factura_items(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-ITEMS-BILLED-01',
        ]);
        $itemFacturado = $this->createPedidoItem($pedido, [
            'codigo_servicio' => 'BILLED-01',
            'descripcion_servicio' => 'Linea facturada',
            'total_linea' => 300,
        ]);
        $itemEditable = $this->createPedidoItem($pedido, [
            'codigo_servicio' => 'EDIT-01',
            'descripcion_servicio' => 'Linea editable',
            'total_linea' => 100,
        ]);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'orden_factura' => 1,
        ]);
        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $itemFacturado->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 150,
        ]);

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'items' => [
                [
                    'id_pedido_item' => $itemEditable->id_pedido_item,
                    'codigo_servicio' => 'EDIT-OK',
                    'descripcion_servicio' => 'Linea editable actualizada',
                    'cantidad' => 2,
                    'precio_unitario' => 80,
                    'total_linea' => 160,
                ],
            ],
        ])->assertOk()
            ->assertJsonPath('meta.items_bloqueados.0.id_pedido_item', $itemFacturado->id_pedido_item)
            ->assertJsonPath('data.items.0.id_pedido_item', $itemFacturado->id_pedido_item)
            ->assertJsonPath('data.items.0.esta_facturado', true);

        $this->assertDatabaseHas('pedido_items', [
            'id_pedido_item' => $itemFacturado->id_pedido_item,
            'codigo_servicio' => 'BILLED-01',
        ]);
        $this->assertDatabaseHas('pedido_items', [
            'id_pedido_item' => $itemEditable->id_pedido_item,
            'codigo_servicio' => 'EDIT-OK',
            'total_linea' => 160,
        ]);
    }

    public function test_destroy_bloquea_cancelacion_si_el_pedido_tiene_items_facturados(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-BLOCK-DELETE-01',
        ]);
        $itemFacturado = $this->createPedidoItem($pedido, [
            'codigo_servicio' => 'LOCK-01',
            'total_linea' => 200,
        ]);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'orden_factura' => 1,
        ]);

        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $itemFacturado->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 200,
        ]);

        $this->deleteJson("/api/v1/pedidos/{$pedido->id_pedido}")
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'PEDIDO_ITEMS_FACTURADOS');

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'estado' => $pedido->estado,
        ]);
    }

    public function test_usuario_con_multiples_contextos_ve_pedidos_de_todos_sus_contextos_accesibles(): void
    {
        $gestor = Role::query()->where('slug', 'gestor')->firstOrFail();
        $multiContextUser = User::factory()->create(['id_contexto' => 3]);
        $multiContextUser->roles()->sync([$gestor->id_rol]);
        $multiContextUser->contextos()->sync([
            1 => ['es_contexto_principal' => false, 'activo' => true],
            2 => ['es_contexto_principal' => false, 'activo' => true],
            3 => ['es_contexto_principal' => true, 'activo' => true],
        ]);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        Pedido::factory()->create(['id_contexto' => 1, 'id_trabajo' => $trabajoMoeve->id_trabajo]);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);
        Pedido::factory()->create(['id_contexto' => 2, 'id_trabajo' => $trabajoRepsol->id_trabajo]);

        Sanctum::actingAs($multiContextUser);
        $multiContextUser->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->getJson('/api/v1/pedidos')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    private function createPedidoItem(Pedido $pedido, array $overrides = []): PedidoItem
    {
        return PedidoItem::create(array_merge([
            'id_contexto' => $pedido->id_contexto,
            'id_pedido' => $pedido->id_pedido,
            'id_tarifario_linea' => null,
            'codigo_servicio' => 'SRV-TEST',
            'numero_tarifa' => null,
            'descripcion_servicio' => 'Linea de pedido',
            'cantidad' => 1,
            'precio_unitario' => 100,
            'total_linea' => 100,
        ], $overrides));
    }
}
