<?php

namespace Tests\Feature;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Trabajo;
use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\EstacionMoeveExt;
use App\Models\Tarifario;
use App\Models\TarifarioLinea;
use App\Models\Unidad;
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

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        [$tarifario, $lineaTarifa] = $this->createTariffSetup($trabajoMoeve, [
            'codigo_tarifa' => 'SRV-01',
            'actuacion' => 'Desplazamiento técnico',
            'tarifa_aplicada' => 750.25,
        ]);

        $payload = [
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-TEST-OK-01',
            'fecha_solicitud' => '2026-04-20',
            'id_tarifario' => $tarifario->id_tarifario,
            'importe_facturado' => 0,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
            'observaciones' => 'Test de integración',
            'items' => [
                [
                    'id_tarifario_linea' => $lineaTarifa->id_tarifario_linea,
                    'codigo_servicio' => 'SRV-01',
                    'descripcion_servicio' => 'Desplazamiento técnico',
                    'cantidad' => 2.5,
                    'precio_unitario' => 750.25,
                    'total_linea' => 1875.63,
                ],
            ]
        ];

        $response = $this->postJson('/api/v1/pedidos', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.numero_pedido', 'PED-TEST-OK-01')
            ->assertJsonPath('data.id_tarifario', $tarifario->id_tarifario)
            ->assertJsonPath('data.importe_pedido', 1875.63)
            ->assertJsonPath('data.unidades_pedido', 2.5);

        $this->assertDatabaseHas('pedidos', [
            'numero_pedido' => 'PED-TEST-OK-01',
            'id_contexto' => 1,
            'id_tarifario' => $tarifario->id_tarifario,
            'importe_pedido' => 1875.63,
            'estado' => 'pendiente'
        ]);

        $this->assertDatabaseHas('pedido_items', [
            'id_tarifario_linea' => $lineaTarifa->id_tarifario_linea,
            'codigo_servicio' => 'SRV-01',
            'cantidad' => 2.500,
            'total_linea' => 1875.63
        ]);
    }

    public function test_gestor_repsol_puede_crear_pedido_con_cantidad_y_unidades_solicitadas_decimales(): void
    {
        Sanctum::actingAs($this->gestorRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);
        $trabajoRepsol = Trabajo::factory()->create(['id_contexto' => 2, 'id_empresa_cliente' => $empresaRepsol->id_empresa]);
        [$tarifario, $lineaTarifa] = $this->createTariffSetup($trabajoRepsol, [
            'codigo_tarifa' => 'SRV-DEC',
            'actuacion' => 'Servicio decimal permitido',
            'tarifa_aplicada' => 100.33,
        ]);

        $response = $this->postJson('/api/v1/pedidos', [
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_tarifario' => $tarifario->id_tarifario,
            'numero_pedido' => 'PED-REPSOL-DEC-01',
            'fecha_solicitud' => '2026-04-20',
            'importe_solicitado' => 150.50,
            'importe_facturado' => 0,
            'unidades_solicitadas' => 1.5,
            'estado' => 'pendiente',
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
            'items' => [
                [
                    'id_tarifario_linea' => $lineaTarifa->id_tarifario_linea,
                    'codigo_servicio' => 'SRV-DEC',
                    'descripcion_servicio' => 'Servicio decimal permitido',
                    'cantidad' => 1.5,
                    'precio_unitario' => 100.33,
                    'total_linea' => 150.50,
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.importe_pedido', 150.5)
            ->assertJsonPath('data.unidades_pedido', 1.5);
    }

    public function test_no_se_puede_crear_pedido_si_el_trabajo_no_tiene_tarifario(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'id_tarifario' => null,
        ]);

        $this->postJson('/api/v1/pedidos', [
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'numero_pedido' => 'PED-SIN-TARIFA-01',
            'fecha_solicitud' => '2026-04-20',
            'estado' => 'pendiente',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['id_trabajo']);
    }

    public function test_actualizar_pedido_rechaza_tarifario_distinto_al_del_trabajo(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $contratoA = \App\Models\Contrato::create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'codigo_contrato' => 'PED-TAR-A',
            'nombre' => 'Contrato pedido A',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
        $contratoB = \App\Models\Contrato::create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'codigo_contrato' => 'PED-TAR-B',
            'nombre' => 'Contrato pedido B',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
        $tarifarioTrabajo = \App\Models\Tarifario::create([
            'id_contexto' => 1,
            'id_contrato' => $contratoA->id_contrato,
            'nombre' => 'Tarifa trabajo pedido',
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
        $tarifarioAlternativo = \App\Models\Tarifario::create([
            'id_contexto' => 1,
            'id_contrato' => $contratoB->id_contrato,
            'nombre' => 'Tarifa alternativa pedido',
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'id_contrato' => $contratoA->id_contrato,
            'id_tarifario' => $tarifarioTrabajo->id_tarifario,
        ]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_tarifario' => $tarifarioTrabajo->id_tarifario,
            'numero_pedido' => 'PED-HEREDA-01',
        ]);

        $this->patchJson("/api/v1/pedidos/{$pedido->id_pedido}", [
            'id_tarifario' => $tarifarioAlternativo->id_tarifario,
            'numero_pedido' => 'PED-HEREDA-01',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['id_tarifario']);

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'id_tarifario' => $tarifarioTrabajo->id_tarifario,
        ]);
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
        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'importe_pedido' => 400.00,
            'unidades_pedido' => 5.000,
            'tiene_mas_de_1_item' => true,
        ]);
    }

    public function test_crear_pedido_rechaza_linea_de_otro_tarifario(): void
    {
        Sanctum::actingAs($this->gestorMoeve);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create(['id_contexto' => 1, 'id_empresa_cliente' => $empresaMoeve->id_empresa]);
        [$tarifarioTrabajo] = $this->createTariffSetup($trabajoMoeve, [
            'codigo_tarifa' => 'OK-01',
            'actuacion' => 'Tarifa válida',
            'tarifa_aplicada' => 125,
        ]);

        $contratoAlternativo = Contrato::create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'codigo_contrato' => 'PED-LINEA-ALT',
            'nombre' => 'Contrato alternativo',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
        $tarifarioAlternativo = Tarifario::create([
            'id_contexto' => 1,
            'id_contrato' => $contratoAlternativo->id_contrato,
            'nombre' => 'Tarifa alternativa línea',
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
        $unidad = Unidad::create([
            'nombre' => 'Unidad alternativa',
            'abreviatura' => 'ud',
            'activo' => true,
        ]);
        $lineaAlternativa = TarifarioLinea::create([
            'id_contexto' => 1,
            'id_tarifario' => $tarifarioAlternativo->id_tarifario,
            'codigo_tarifa' => 'ALT-01',
            'actuacion' => 'Linea no compatible',
            'descripcion' => 'Linea de otro tarifario',
            'tarifa_base' => 300,
            'tarifa_aplicada' => 300,
            'id_unidad' => $unidad->id_unidad,
            'activo' => true,
        ]);

        $this->postJson('/api/v1/pedidos', [
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_tarifario' => $tarifarioTrabajo->id_tarifario,
            'numero_pedido' => 'PED-LINEA-INVALIDA-01',
            'fecha_solicitud' => '2026-04-20',
            'estado' => 'pendiente',
            'items' => [
                [
                    'id_tarifario_linea' => $lineaAlternativa->id_tarifario_linea,
                    'codigo_servicio' => 'ALT-01',
                    'descripcion_servicio' => 'Linea no compatible',
                    'cantidad' => 1,
                    'precio_unitario' => 300,
                    'total_linea' => 300,
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id_tarifario_linea']);
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

    public function test_exportacion_moeve_csv_incluye_cabecera_y_lineas_del_pedido(): void
    {
        $this->actingAs($this->gestorMoeve);
        $this->gestorMoeve->setActiveContextSelection(1);

        [$pedido] = $this->createPedidoExportable([
            'numero_pedido' => 'PED-EXPORT-CSV-01',
            'fecha_solicitud' => '2026-06-01',
        ], [
            'codigo_servicio' => '165052',
            'descripcion_servicio' => 'Servicio exportado CSV',
            'cantidad' => 1.5,
            'precio_unitario' => 100.33,
            'total_linea' => 150.50,
        ]);

        $response = $this->get("/pedidos/{$pedido->id_pedido}/export/moeve/csv");

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Producto;"Posición de la cesta";Cantidad', $content);
        $this->assertStringContainsString('"Texto Proveedor";CLIENTE;Descripción;"Centro /Concesión"', $content);
        $this->assertStringContainsString('165052', $content);
        $this->assertStringContainsString('Servicio exportado CSV', $content);
        $this->assertStringContainsString('1,5', $content);
        $this->assertStringContainsString('150,5', $content);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'pedidos',
            'registro_id' => $pedido->id_pedido,
            'accion' => 'exportar',
        ]);
    }

    public function test_exportacion_moeve_pdf_devuelve_html_imprimible(): void
    {
        $this->actingAs($this->gestorMoeve);
        $this->gestorMoeve->setActiveContextSelection(1);

        [$pedido] = $this->createPedidoExportable([
            'numero_pedido' => 'PED-EXPORT-PDF-01',
        ], [
            'codigo_servicio' => '165052',
            'descripcion_servicio' => 'Servicio exportado PDF',
        ]);

        $this->get("/pedidos/{$pedido->id_pedido}/export/moeve/pdf")
            ->assertOk()
            ->assertSee('OFERTA PRECIOS ACUERDO')
            ->assertSee('E.S. Nº')
            ->assertSee('Código MOEVE')
            ->assertSee('PED-EXPORT-PDF-01')
            ->assertSee('165052')
            ->assertSee('Total')
            ->assertSee('Servicio exportado PDF');
    }

    public function test_exportacion_moeve_ariba_devuelve_cuadro_resumen(): void
    {
        $this->actingAs($this->gestorMoeve);
        $this->gestorMoeve->setActiveContextSelection(1);

        [$pedido] = $this->createPedidoExportable([
            'numero_pedido' => 'PED-EXPORT-ARIBA-01',
        ], [
            'codigo_servicio' => '165052',
            'descripcion_servicio' => 'Servicio exportado ARIBA',
        ]);

        $this->get("/pedidos/{$pedido->id_pedido}/export/moeve/ariba")
            ->assertOk()
            ->assertSee('ARIBA - TRAMITACION DE PEDIDOS')
            ->assertSee('PED-EXPORT-ARIBA-01')
            ->assertSee('Sociedad')
            ->assertSee('Producto')
            ->assertSee('Cantidad')
            ->assertSee('Texto Proveedor')
            ->assertSee('Centro /Concesión')
            ->assertSee('Proveedor/ Contrato')
            ->assertSee('Confirmar')
            ->assertSee('Servicio exportado ARIBA');
    }

    public function test_no_se_puede_exportar_un_pedido_sin_lineas(): void
    {
        $this->actingAs($this->gestorMoeve);
        $this->gestorMoeve->setActiveContextSelection(1);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
        ]);
        [$tarifario] = $this->createTariffSetup($trabajoMoeve);

        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_tarifario' => $tarifario->id_tarifario,
            'numero_pedido' => 'PED-SIN-LINEAS-EXPORT',
            'importe_pedido' => 0,
            'unidades_pedido' => 0,
        ]);

        $this->get("/pedidos/{$pedido->id_pedido}/export/moeve/csv")
            ->assertStatus(422);
    }

    public function test_no_se_puede_exportar_si_hay_lineas_de_otro_tarifario_en_el_pedido(): void
    {
        $this->actingAs($this->gestorMoeve);
        $this->gestorMoeve->setActiveContextSelection(1);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
        ]);
        [$tarifarioTrabajo] = $this->createTariffSetup($trabajoMoeve, [
            'codigo_tarifa' => 'EXP-OK-01',
        ]);

        $contratoAlternativo = Contrato::create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'codigo_contrato' => 'EXP-ALT-01',
            'nombre' => 'Contrato export alternativo',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
        $tarifarioAlternativo = Tarifario::create([
            'id_contexto' => 1,
            'id_contrato' => $contratoAlternativo->id_contrato,
            'nombre' => 'Tarifa export alternativa',
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
        $unidad = Unidad::create([
            'nombre' => 'Unidad export alternativa',
            'abreviatura' => 'ud',
            'activo' => true,
        ]);
        $lineaAlternativa = TarifarioLinea::create([
            'id_contexto' => 1,
            'id_tarifario' => $tarifarioAlternativo->id_tarifario,
            'codigo_tarifa' => 'EXP-ALT-LINE',
            'actuacion' => 'Linea export no valida',
            'descripcion' => 'Linea export no valida',
            'tarifa_base' => 200,
            'tarifa_aplicada' => 200,
            'id_unidad' => $unidad->id_unidad,
            'activo' => true,
        ]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_tarifario' => $tarifarioTrabajo->id_tarifario,
            'numero_pedido' => 'PED-EXPORT-LINEA-INVALIDA',
            'importe_pedido' => 200,
            'unidades_pedido' => 1,
        ]);

        $this->createPedidoItem($pedido, [
            'id_tarifario_linea' => $lineaAlternativa->id_tarifario_linea,
            'codigo_servicio' => 'EXP-ALT-LINE',
            'descripcion_servicio' => 'Linea de otro tarifario',
            'cantidad' => 1,
            'precio_unitario' => 200,
            'total_linea' => 200,
        ]);

        $this->get("/pedidos/{$pedido->id_pedido}/export/moeve/csv")
            ->assertStatus(422);
    }

    private function createTariffSetup(Trabajo $trabajo, array $lineaOverrides = []): array
    {
        $contrato = Contrato::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'codigo_contrato' => 'PED-TAR-' . $this->faker->unique()->numerify('####'),
            'nombre' => 'Contrato pedido test',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
        $tarifario = Tarifario::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_contrato' => $contrato->id_contrato,
            'nombre' => 'Tarifa pedido ' . $this->faker->unique()->lexify('????'),
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
        $unidad = Unidad::create([
            'nombre' => 'Horas',
            'abreviatura' => 'h',
            'activo' => true,
        ]);
        $linea = TarifarioLinea::create(array_merge([
            'id_contexto' => $trabajo->id_contexto,
            'id_tarifario' => $tarifario->id_tarifario,
            'codigo_tarifa' => 'LINEA-' . $this->faker->unique()->numerify('###'),
            'actuacion' => 'Actuación de pedido',
            'descripcion' => 'Línea de prueba',
            'tarifa_base' => 100,
            'tarifa_aplicada' => 100,
            'id_unidad' => $unidad->id_unidad,
            'activo' => true,
        ], $lineaOverrides));

        $trabajo->update([
            'id_contrato' => $contrato->id_contrato,
            'id_tarifario' => $tarifario->id_tarifario,
        ]);

        return [$tarifario, $linea, $contrato];
    }

    private function createPedidoExportable(array $pedidoOverrides = [], array $itemOverrides = []): array
    {
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => 1]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'descripcion_trabajo' => 'Legalización',
            'responsable_cliente' => 'Beatriz Llueca',
            'id_responsable_ciete' => $this->gestorMoeve->id_usuario,
        ]);
        [$tarifario, $linea] = $this->createTariffSetup($trabajoMoeve, [
            'codigo_tarifa' => '165052',
            'actuacion' => 'Linea exportable',
            'descripcion' => 'Detalle exportable',
            'tarifa_aplicada' => 100.33,
        ]);
        $estacion = EstacionServicio::create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'codigo_estacion' => 'ES334500',
            'nombre' => 'E.S. LA SENYERA I - Nº33450',
            'direccion' => 'Calle Export 1',
            'codigo_postal' => '28001',
            'poblacion' => 'CUART DE POBLET',
            'provincia' => 'VALENCIA',
            'estado' => 'activa',
            'activo' => true,
        ]);
        EstacionMoeveExt::create([
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'tecnico_gestion' => 'Beatriz Llueca',
            'responsable_gestor' => 'Beatriz Llueca',
            'cod_sociedad' => '1024',
            'sociedad' => 'MOEVE',
        ]);
        $trabajoMoeve->update(['id_estacion_servicio' => $estacion->id_estacion_servicio]);
        $this->gestorMoeve->forceFill([
            'nombre' => 'César',
            'apellidos' => 'García Villalonga',
        ])->save();

        $pedido = Pedido::factory()->create(array_merge([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_tarifario' => $tarifario->id_tarifario,
            'numero_pedido' => 'PED-EXPORT-BASE',
            'fecha_solicitud' => '2026-06-01',
            'importe_pedido' => 150.50,
            'unidades_pedido' => 1.500,
            'importe_solicitado' => 150.50,
            'unidades_solicitadas' => 1.500,
            'estado' => 'pendiente',
        ], $pedidoOverrides));

        $item = $this->createPedidoItem($pedido, array_merge([
            'id_tarifario_linea' => $linea->id_tarifario_linea,
            'codigo_servicio' => '165052',
            'numero_tarifa' => 'PED-ALT-3472',
            'descripcion_servicio' => 'Linea exportable',
            'cantidad' => 1.5,
            'precio_unitario' => 100.33,
            'total_linea' => 150.50,
        ], $itemOverrides));

        return [$pedido, $linea, $trabajoMoeve, $item];
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
