<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaTest extends TestCase
{
    use RefreshDatabase;

    protected User $gestorInterno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);

        $permisoVer = Permission::firstOrCreate(
            ['slug' => 'facturas.ver'],
            ['nombre' => 'Ver Facturas', 'activo' => true]
        );
        $permisoCrear = Permission::firstOrCreate(['slug' => 'facturas.crear'], ['nombre' => 'Crear Facturas', 'activo' => true]);
        $permisoEditar = Permission::firstOrCreate(['slug' => 'facturas.editar'], ['nombre' => 'Editar Facturas', 'activo' => true]);
        $permisoEliminar = Permission::firstOrCreate(['slug' => 'facturas.eliminar'], ['nombre' => 'Eliminar Facturas', 'activo' => true]);
        $permisoExportar = Permission::firstOrCreate(['slug' => 'facturas.exportar'], ['nombre' => 'Exportar Facturas', 'activo' => true]);

        $rolGestor = Role::firstOrCreate(
            ['slug' => 'gestor_facturas'],
            ['nombre' => 'Gestor Facturas', 'activo' => true]
        );
        $rolGestor->permissions()->sync([
            $permisoVer->id_permiso,
            $permisoCrear->id_permiso,
            $permisoEditar->id_permiso,
            $permisoEliminar->id_permiso,
            $permisoExportar->id_permiso,
        ]);

        $this->gestorInterno = User::factory()->create(['id_contexto' => 3]);
        $this->gestorInterno->roles()->sync([$rolGestor->id_rol]);
        $this->gestorInterno->contextos()->sync([
            1 => ['es_contexto_principal' => false, 'activo' => true],
            2 => ['es_contexto_principal' => false, 'activo' => true],
            3 => ['es_contexto_principal' => true, 'activo' => true],
        ]);
    }

    public function test_usuario_con_multiples_contextos_ve_facturas_de_todos_sus_contextos_accesibles(): void
    {
        $trabajoMoeve = $this->createTrabajoForContext(1);
        $trabajoRepsol = $this->createTrabajoForContext(2);

        Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_empresa_cliente' => $trabajoMoeve->id_empresa_cliente,
            'orden_factura' => 1,
        ]);
        Factura::factory()->repsol()->create([
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_empresa_cliente' => $trabajoRepsol->id_empresa_cliente,
            'orden_factura' => 1,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->getJson('/api/v1/facturas')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_store_aplica_reglas_segun_el_contexto_real_del_trabajo(): void
    {
        $contratoMoeve = $this->createContratoForContext(1);
        $trabajoMoeve = $this->createTrabajoForContext(1, $contratoMoeve);
        $empresaMoeve = $this->createEmpresaFacturadoraPermitida($contratoMoeve);
        $itemMoeve = $this->createPedidoItem($trabajoMoeve, 'PED-CTX-MOEVE', 1, 100);
        $contratoRepsol = $this->createContratoForContext(2);
        $trabajoRepsol = $this->createTrabajoForContext(2, $contratoRepsol);
        $empresaRepsol = $this->createEmpresaFacturadoraPermitida($contratoRepsol);
        $itemRepsol = $this->createPedidoItem($trabajoRepsol, 'PED-CTX-REPSOL', 1, 100);

        $basePayload = [
            'numero_factura' => 'FAC-CTX-TEST',
            'fecha_emision' => '2026-04-25',
            'estado' => 'emitida',
            'base_imponible' => 100,
            'iva' => 21,
            'total' => 121,
        ];

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', $basePayload + [
                'id_trabajo' => $trabajoMoeve->id_trabajo,
                'id_empresa_facturadora' => $empresaMoeve->id_empresa,
                'items' => [
                    ['id_pedido_item' => $itemMoeve->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['numero_factura_ccp']);

        $this->gestorInterno->setActiveContextSelection(2);

        $this->postJson('/api/v1/facturas', $basePayload + [
                'numero_factura' => 'FAC-CTX-TEST-2',
                'id_trabajo' => $trabajoRepsol->id_trabajo,
                'id_empresa_facturadora' => $empresaRepsol->id_empresa,
                'items' => [
                    ['id_pedido_item' => $itemRepsol->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['orden_factura']);
    }

    public function test_update_repsol_enforces_uniqueness_by_real_context_and_work(): void
    {
        $trabajoRepsol = $this->createTrabajoForContext(2);

        $facturaUno = Factura::factory()->repsol()->create([
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_empresa_cliente' => $trabajoRepsol->id_empresa_cliente,
            'orden_factura' => 1,
        ]);
        $facturaDos = Factura::factory()->repsol()->create([
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_empresa_cliente' => $trabajoRepsol->id_empresa_cliente,
            'orden_factura' => 2,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(2);

        $this->patchJson("/api/v1/facturas/{$facturaDos->id_factura}", [
                'orden_factura' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['orden_factura']);

        $this->assertDatabaseHas('facturas', [
            'id_factura' => $facturaDos->id_factura,
            'orden_factura' => 2,
        ]);
    }

    public function test_repsol_allows_higher_invoice_order_when_it_is_unique_within_the_work(): void
    {
        $contratoRepsol = $this->createContratoForContext(2);
        $trabajoRepsol = $this->createTrabajoForContext(2, $contratoRepsol);
        $empresaRepsol = $this->createEmpresaFacturadoraPermitida($contratoRepsol);
        $item = $this->createPedidoItem($trabajoRepsol, 'PED-REPSOL-ORD-3', 1, 100);

        Factura::factory()->repsol()->create([
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_empresa_cliente' => $trabajoRepsol->id_empresa_cliente,
            'orden_factura' => 1,
        ]);
        Factura::factory()->repsol()->create([
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'id_empresa_cliente' => $trabajoRepsol->id_empresa_cliente,
            'orden_factura' => 2,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(2);

        $this->postJson('/api/v1/facturas', [
                'id_trabajo' => $trabajoRepsol->id_trabajo,
                'id_empresa_facturadora' => $empresaRepsol->id_empresa,
                'numero_factura' => 'FAC-REPSOL-ORD-3',
                'fecha_emision' => '2026-04-26',
                'estado' => 'emitida',
                'base_imponible' => 100,
                'iva' => 21,
                'total' => 121,
                'orden_factura' => 3,
                'autofactura' => true,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.orden_factura', 3);

        $this->assertDatabaseHas('facturas', [
            'id_contexto' => 2,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'numero_factura' => 'FAC-REPSOL-ORD-3',
            'orden_factura' => 3,
        ]);
    }

    public function test_store_creates_factura_items_from_multiple_pedidos_and_trabajos(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajoUno = $this->createTrabajoForContext(1, $contrato);
        $trabajoDos = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $itemUno = $this->createPedidoItem($trabajoUno, 'PED-MULTI-1', 1, 120);
        $itemDos = $this->createPedidoItem($trabajoDos, 'PED-MULTI-2', 2, 180);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-ITEMS-MULTI',
                'numero_factura_ccp' => 'CCP-ITEMS-MULTI',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 300,
                'iva' => 0,
                'total' => 300,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    [
                        'id_pedido_item' => $itemUno->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 120,
                        'observaciones' => 'Linea 1',
                    ],
                    [
                        'id_pedido_item' => $itemDos->id_pedido_item,
                        'unidades_facturadas' => 2,
                        'importe_facturado' => 180,
                        'observaciones' => 'Linea 2',
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_trabajo', $trabajoUno->id_trabajo)
            ->assertJsonPath('data.importe_asignado', 300)
            ->assertJsonPath('data.diferencia', 0)
            ->assertJsonPath('data.estado_cuadre', 'cuadrada')
            ->assertJsonPath('data.empresa_facturadora.id_empresa', $empresaFacturadora->id_empresa)
            ->assertJsonPath('data.sociedad_cif_validada', true)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonCount(2, 'data.trabajos');

        $this->assertDatabaseHas('factura_items', [
            'id_pedido_item' => $itemUno->id_pedido_item,
            'importe_facturado' => 120,
        ]);
        $this->assertDatabaseHas('factura_items', [
            'id_pedido_item' => $itemDos->id_pedido_item,
            'importe_facturado' => 180,
        ]);
    }

    public function test_store_derives_header_work_from_invoice_items_even_with_conflicting_input(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajoConItem = $this->createTrabajoForContext(1, $contrato);
        $trabajoCabeceraIncorrecta = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajoConItem, 'PED-DERIVE-WORK', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'id_trabajo' => $trabajoCabeceraIncorrecta->id_trabajo,
                'numero_factura' => 'FAC-DERIVE-WORK',
                'numero_factura_ccp' => 'CCP-DERIVE-WORK',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_trabajo', $trabajoConItem->id_trabajo);

        $this->assertDatabaseHas('facturas', [
            'numero_factura' => 'FAC-DERIVE-WORK',
            'id_trabajo' => $trabajoConItem->id_trabajo,
            'id_empresa_cliente' => $trabajoConItem->id_empresa_cliente,
            'id_contrato' => $contrato->id_contrato,
        ]);
    }

    public function test_store_requires_invoice_number_for_issued_or_sent_invoices(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-ISSUED-NUMBER', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => null,
                'numero_factura_ccp' => 'CCP-ISSUED-NUMBER',
                'fecha_emision' => '2026-05-05',
                'estado' => 'emitida',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['numero_factura']);
    }

    public function test_store_blocks_legacy_billing_statuses(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-LEGACY-STATUS', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-LEGACY-STATUS',
                'numero_factura_ccp' => 'CCP-LEGACY-STATUS',
                'fecha_emision' => '2026-05-05',
                'estado' => 'cobrada',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['estado']);
    }

    public function test_store_allows_same_invoice_number_in_different_context(): void
    {
        $contratoMoeve = $this->createContratoForContext(1);
        $trabajoMoeve = $this->createTrabajoForContext(1, $contratoMoeve);
        $empresaMoeve = $this->createEmpresaFacturadoraPermitida($contratoMoeve);
        Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoMoeve->id_trabajo,
            'id_contrato' => $contratoMoeve->id_contrato,
            'id_empresa_facturadora' => $empresaMoeve->id_empresa,
            'id_empresa_cliente' => $trabajoMoeve->id_empresa_cliente,
            'numero_factura' => 'FAC-SHARED-NUMBER',
            'numero_factura_ccp' => 'CCP-SHARED-NUMBER-1',
        ]);

        $contratoRepsol = $this->createContratoForContext(2);
        $trabajoRepsol = $this->createTrabajoForContext(2, $contratoRepsol);
        $empresaRepsol = $this->createEmpresaFacturadoraPermitida($contratoRepsol);
        $itemRepsol = $this->createPedidoItem($trabajoRepsol, 'PED-SHARED-CTX', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(2);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SHARED-NUMBER',
                'fecha_emision' => '2026-05-05',
                'estado' => 'emitida',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'orden_factura' => 1,
                'autofactura' => true,
                'id_empresa_facturadora' => $empresaRepsol->id_empresa,
                'items' => [
                    ['id_pedido_item' => $itemRepsol->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertCreated();
    }

    public function test_store_allows_same_invoice_number_with_different_billing_company(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajoExistente = $this->createTrabajoForContext(1, $contrato);
        $sociedadUno = $this->createEmpresaFacturadoraPermitida($contrato, ['cif' => 'B11111111']);
        $sociedadDos = $this->createEmpresaFacturadoraPermitida($contrato, ['cif' => 'B22222222']);
        Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoExistente->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $sociedadUno->id_empresa,
            'id_empresa_cliente' => $trabajoExistente->id_empresa_cliente,
            'numero_factura' => 'FAC-SAME-SOCIEDAD-SCOPE',
            'numero_factura_ccp' => 'CCP-SAME-SOCIEDAD-1',
        ]);

        $trabajoNuevo = $this->createTrabajoForContext(1, $contrato);
        $item = $this->createPedidoItem($trabajoNuevo, 'PED-SAME-DIFF-SOC', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SAME-SOCIEDAD-SCOPE',
                'numero_factura_ccp' => 'CCP-SAME-SOCIEDAD-2',
                'fecha_emision' => '2026-05-05',
                'estado' => 'emitida',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $sociedadDos->id_empresa,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertCreated();
    }

    public function test_store_blocks_duplicate_invoice_number_for_same_context_and_billing_company(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajoExistente = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoExistente->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajoExistente->id_empresa_cliente,
            'numero_factura' => 'FAC-DUP-SAME-SCOPE',
            'numero_factura_ccp' => 'CCP-DUP-SAME-SCOPE-1',
        ]);

        $trabajoNuevo = $this->createTrabajoForContext(1, $contrato);
        $item = $this->createPedidoItem($trabajoNuevo, 'PED-DUP-SAME-SCOPE', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-DUP-SAME-SCOPE',
                'numero_factura_ccp' => 'CCP-DUP-SAME-SCOPE-2',
                'fecha_emision' => '2026-05-05',
                'estado' => 'emitida',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    ['id_pedido_item' => $item->id_pedido_item, 'importe_facturado' => 100],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['numero_factura']);
    }

    public function test_update_blocks_duplicate_invoice_number_for_same_context_and_billing_company(): void
    {
        $contrato = $this->createContratoForContext(1);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $trabajoUno = $this->createTrabajoForContext(1, $contrato);
        $trabajoDos = $this->createTrabajoForContext(1, $contrato);

        Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoUno->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajoUno->id_empresa_cliente,
            'numero_factura' => 'FAC-UPD-DUP-SAME-SCOPE',
            'numero_factura_ccp' => 'CCP-UPD-DUP-SAME-SCOPE-1',
        ]);
        $facturaDos = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajoDos->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajoDos->id_empresa_cliente,
            'numero_factura' => 'FAC-UPD-ORIGINAL',
            'numero_factura_ccp' => 'CCP-UPD-DUP-SAME-SCOPE-2',
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->patchJson("/api/v1/facturas/{$facturaDos->id_factura}", [
                'numero_factura' => 'FAC-UPD-DUP-SAME-SCOPE',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['numero_factura']);
    }

    public function test_store_blocks_factura_items_without_sociedad_facturadora(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-SIN-SOCIEDAD', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SIN-SOCIEDAD',
                'numero_factura_ccp' => 'CCP-SIN-SOCIEDAD',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'items' => [
                    [
                        'id_pedido_item' => $item->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 100,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_empresa_facturadora']);
    }

    public function test_store_blocks_sociedad_not_permitted_for_contract(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $sociedadNoPermitida = Empresa::factory()->create([
            'id_contexto' => 1,
            'cif' => 'B99999991',
            'tipo_empresa' => 'cliente',
        ]);
        $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-SOC-INCOMPATIBLE', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SOC-INCOMPATIBLE',
                'numero_factura_ccp' => 'CCP-SOC-INCOMPATIBLE',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $sociedadNoPermitida->id_empresa,
                'items' => [
                    [
                        'id_pedido_item' => $item->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 100,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_empresa_facturadora']);
    }

    public function test_store_blocks_sociedad_without_cif_even_if_pivot_allows_it(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $sociedadSinCif = $this->createEmpresaFacturadoraPermitida($contrato, ['cif' => null]);
        $item = $this->createPedidoItem($trabajo, 'PED-SOC-SIN-CIF', 1, 100);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SOC-SIN-CIF',
                'numero_factura_ccp' => 'CCP-SOC-SIN-CIF',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 100,
                'iva' => 0,
                'total' => 100,
                'id_empresa_facturadora' => $sociedadSinCif->id_empresa,
                'items' => [
                    [
                        'id_pedido_item' => $item->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 100,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_empresa_facturadora']);
    }

    public function test_store_allows_otros_clientes_with_own_permitted_sociedad(): void
    {
        $contrato = $this->createContratoForContext(3);
        $trabajo = $this->createTrabajoForContext(3, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato, ['cif' => 'B33333333']);
        $item = $this->createPedidoItem($trabajo, 'PED-OTROS-SOCIEDAD', 1, 140);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(3);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-OTROS-SOCIEDAD',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 140,
                'iva' => 0,
                'total' => 140,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    [
                        'id_pedido_item' => $item->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 140,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.id_contexto', 3)
            ->assertJsonPath('data.empresa_facturadora.cif', 'B33333333')
            ->assertJsonPath('data.sociedad_cif_validada', true);
    }

    public function test_store_blocks_billing_more_than_pending_for_pedido_item(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-PARTIAL', 1, 100);
        $facturaPrevia = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => 'FAC-PREVIA-PARCIAL',
            'numero_factura_ccp' => 'CCP-PREVIA-PARCIAL',
            'orden_factura' => 1,
            'base_imponible' => 60,
            'importe' => 60,
            'total' => 60,
            'estado' => 'pendiente',
        ]);
        FacturaItem::create([
            'id_factura' => $facturaPrevia->id_factura,
            'id_pedido_item' => $item->id_pedido_item,
            'unidades_facturadas' => 0.6,
            'importe_facturado' => 60,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->postJson('/api/v1/facturas', [
                'numero_factura' => 'FAC-SOBREFACTURA',
                'numero_factura_ccp' => 'CCP-SOBREFACTURA',
                'fecha_emision' => '2026-05-05',
                'estado' => 'pendiente',
                'base_imponible' => 50,
                'iva' => 0,
                'total' => 50,
                'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
                'items' => [
                    [
                        'id_pedido_item' => $item->id_pedido_item,
                        'unidades_facturadas' => 0.5,
                        'importe_facturado' => 50,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.importe_facturado']);

        $this->assertDatabaseMissing('facturas', [
            'numero_factura' => 'FAC-SOBREFACTURA',
        ]);
    }

    public function test_update_preserves_factura_item_ids_and_creates_new_lines(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $itemUno = $this->createPedidoItem($trabajo, 'PED-UPD-1', 1, 100);
        $itemDos = $this->createPedidoItem($trabajo, 'PED-UPD-2', 1, 50);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => 'FAC-UPD-ITEMS',
            'numero_factura_ccp' => 'CCP-UPD-ITEMS',
            'orden_factura' => 1,
            'base_imponible' => 100,
            'importe' => 100,
            'total' => 100,
            'estado' => 'pendiente',
        ]);
        $lineaExistente = FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $itemUno->id_pedido_item,
            'unidades_facturadas' => 0.4,
            'importe_facturado' => 40,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->putJson("/api/v1/facturas/{$factura->id_factura}", [
                'total' => 125,
                'base_imponible' => 125,
                'iva' => 0,
                'items' => [
                    [
                        'id_factura_item' => $lineaExistente->id_factura_item,
                        'id_pedido_item' => $itemUno->id_pedido_item,
                        'unidades_facturadas' => 0.75,
                        'importe_facturado' => 75,
                    ],
                    [
                        'id_pedido_item' => $itemDos->id_pedido_item,
                        'unidades_facturadas' => 1,
                        'importe_facturado' => 50,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.importe_asignado', 125)
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('factura_items', [
            'id_factura_item' => $lineaExistente->id_factura_item,
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $itemUno->id_pedido_item,
            'importe_facturado' => 75,
        ]);
        $this->assertSame(2, FacturaItem::where('id_factura', $factura->id_factura)->count());
    }

    public function test_update_blocks_changing_facturadora_to_incompatible_sociedad(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $sociedadNoPermitida = Empresa::factory()->create([
            'id_contexto' => 1,
            'cif' => 'B88888881',
            'tipo_empresa' => 'cliente',
        ]);
        $item = $this->createPedidoItem($trabajo, 'PED-UPD-SOCIEDAD', 1, 100);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => 'FAC-UPD-SOCIEDAD',
            'numero_factura_ccp' => 'CCP-UPD-SOCIEDAD',
            'orden_factura' => 1,
            'base_imponible' => 100,
            'importe' => 100,
            'total' => 100,
            'estado' => 'pendiente',
        ]);
        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $item->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 100,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->patchJson("/api/v1/facturas/{$factura->id_factura}", [
                'id_empresa_facturadora' => $sociedadNoPermitida->id_empresa,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['id_empresa_facturadora']);

        $this->assertDatabaseHas('facturas', [
            'id_factura' => $factura->id_factura,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
        ]);
    }

    public function test_destroy_anula_factura_y_conserva_factura_items(): void
    {
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $item = $this->createPedidoItem($trabajo, 'PED-VOID-FACTURA', 1, 100);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => 'FAC-VOID-001',
            'numero_factura_ccp' => 'CCP-VOID-001',
            'orden_factura' => 1,
            'base_imponible' => 100,
            'importe' => 100,
            'total' => 100,
            'estado' => 'emitida',
        ]);
        $facturaItem = FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $item->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 100,
        ]);

        $this->actingAs($this->gestorInterno);
        $this->gestorInterno->setActiveContextSelection(1);

        $this->deleteJson("/api/v1/facturas/{$factura->id_factura}")
            ->assertOk()
            ->assertJsonPath('data.estado', 'anulada');

        $this->assertDatabaseHas('facturas', [
            'id_factura' => $factura->id_factura,
            'estado' => 'anulada',
        ]);
        $this->assertDatabaseHas('factura_items', [
            'id_factura_item' => $facturaItem->id_factura_item,
            'id_factura' => $factura->id_factura,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'facturas',
            'registro_id' => $factura->id_factura,
            'accion' => 'cambiar_estado',
            'campo' => 'estado',
        ]);
    }

    private function createTrabajoForContext(int $contextId, ?Contrato $contrato = null): Trabajo
    {
        $empresa = $contrato
            ? Empresa::query()->findOrFail($contrato->id_empresa_cliente)
            : Empresa::factory()->create([
                'id_contexto' => $contextId,
                'cif' => 'B' . $contextId . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
            ]);

        return Trabajo::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contrato' => $contrato?->id_contrato,
        ]);
    }

    private function createContratoForContext(int $contextId): Contrato
    {
        $empresaCliente = Empresa::factory()->create([
            'id_contexto' => $contextId,
            'cif' => 'A' . $contextId . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
            'tipo_empresa' => 'cliente',
        ]);

        return Contrato::create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresaCliente->id_empresa,
            'codigo_contrato' => 'TEST-' . $contextId . '-' . uniqid(),
            'nombre' => 'Contrato test contexto ' . $contextId,
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
    }

    private function createEmpresaFacturadoraPermitida(Contrato $contrato, array $attributes = []): Empresa
    {
        $empresa = Empresa::factory()->create(array_merge([
            'id_contexto' => $contrato->id_contexto,
            'cif' => 'B' . $contrato->id_contexto . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ], $attributes));

        ContratoEmpresaFacturadora::create([
            'id_contexto' => $contrato->id_contexto,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $empresa->id_empresa,
            'activo' => true,
            'observaciones' => 'Relacion de prueba P0-02.',
        ]);

        return $empresa;
    }

    private function createPedidoItem(Trabajo $trabajo, string $numeroPedido, float $cantidad, float $importe): PedidoItem
    {
        $pedido = Pedido::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'numero_pedido' => $numeroPedido,
            'fecha_solicitud' => '2026-05-01',
            'importe_pedido' => $importe,
            'importe_solicitado' => $importe,
            'importe_facturado' => 0,
            'unidades_pedido' => $cantidad,
            'unidades_solicitadas' => $cantidad,
            'estado' => 'recibido',
            'pedido_completo' => true,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ]);

        return PedidoItem::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_pedido' => $pedido->id_pedido,
            'codigo_servicio' => 'SERV-' . $numeroPedido,
            'descripcion_servicio' => 'Servicio ' . $numeroPedido,
            'precio_unitario' => $importe / max($cantidad, 1),
            'cantidad' => $cantidad,
            'total_linea' => $importe,
        ]);
    }
}
