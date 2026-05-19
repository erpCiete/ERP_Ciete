<?php

namespace Tests\Feature;

use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
    }

    public function test_usuario_con_permiso_exporta_listado_de_su_contexto(): void
    {
        $user = $this->createUserWithPermissions(['facturas.ver', 'facturas.exportar'], [1]);
        $visible = $this->createFacturaWithItem(1, 'FAC-EXPORT-1');
        $hidden = $this->createFacturaWithItem(2, 'FAC-EXPORT-2');

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $response = $this->get('/api/v1/facturas/export');

        $response->assertOk();
        $content = $response->streamedContent();

        $this->assertStringContainsString('Número factura', $content);
        $this->assertStringContainsString($visible->numero_factura, $content);
        $this->assertStringNotContainsString($hidden->numero_factura, $content);
    }

    public function test_usuario_sin_permiso_exportar_recibe_403(): void
    {
        $user = $this->createUserWithPermissions(['facturas.ver'], [1]);
        $this->createFacturaWithItem(1, 'FAC-SIN-PERMISO');

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->get('/api/v1/facturas/export')->assertForbidden();
    }

    public function test_desde_todos_exporta_solo_contextos_accesibles(): void
    {
        $user = $this->createUserWithPermissions(['facturas.ver', 'facturas.exportar'], [1, 2]);
        $moeve = $this->createFacturaWithItem(1, 'FAC-TODOS-MOEVE');
        $repsol = $this->createFacturaWithItem(2, 'FAC-TODOS-REPSOL');
        $otros = $this->createFacturaWithItem(3, 'FAC-TODOS-OTROS');

        $this->actingAs($user);
        $user->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $content = $this->get('/api/v1/facturas/export')->assertOk()->streamedContent();

        $this->assertStringContainsString($moeve->numero_factura, $content);
        $this->assertStringContainsString($repsol->numero_factura, $content);
        $this->assertStringNotContainsString($otros->numero_factura, $content);
    }

    public function test_exportacion_individual_incluye_factura_items(): void
    {
        $user = $this->createUserWithPermissions(['facturas.ver', 'facturas.exportar'], [1]);
        $factura = $this->createFacturaWithItem(1, 'FAC-DETALLE-ITEMS');

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $content = $this->get("/api/v1/facturas/{$factura->id_factura}/export")
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Lineas', $content);
        $this->assertStringContainsString('id_factura_item', $content);
        $this->assertStringContainsString('id_pedido_item', $content);
        $this->assertStringContainsString('Servicio PED-DETALLE-ITEMS', $content);
        $this->assertStringContainsString('PED-DETALLE-ITEMS', $content);
        $this->assertStringContainsString('factura_items', $content);
    }

    public function test_exportacion_individual_sin_items_no_inventa_lineas_legacy(): void
    {
        $user = $this->createUserWithPermissions(['facturas.ver', 'facturas.exportar'], [1]);
        $contrato = $this->createContratoForContext(1);
        $trabajo = $this->createTrabajoForContext(1, $contrato);
        $factura = Factura::factory()->create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => 'FAC-SIN-ITEMS-EXPORT',
            'numero_factura_ccp' => 'CCP-SIN-ITEMS-EXPORT',
            'orden_factura' => 1,
            'total' => 150,
            'importe' => 150,
            'estado' => 'emitida',
        ]);

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $content = $this->get("/api/v1/facturas/{$factura->id_factura}/export")
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('FAC-SIN-ITEMS-EXPORT', $content);
        $this->assertStringContainsString('sin_items', $content);
        $this->assertStringNotContainsString('legacy_factura_pedidos', $content);
        $this->assertStringNotContainsString('legacy_id_trabajo', $content);
    }

    /**
     * @param  array<int, string>  $permissions
     * @param  array<int, int>  $contexts
     */
    private function createUserWithPermissions(array $permissions, array $contexts): User
    {
        $permissionIds = collect($permissions)
            ->map(fn(string $slug) => Permission::firstOrCreate(
                ['slug' => $slug],
                ['nombre' => $slug, 'activo' => true]
            )->id_permiso)
            ->all();

        $role = Role::firstOrCreate(
            ['slug' => 'facturas_export_test_' . md5(implode('|', $permissions))],
            ['nombre' => 'Facturas Export Test', 'activo' => true]
        );
        $role->permissions()->sync($permissionIds);

        $user = User::factory()->create(['id_contexto' => $contexts[0]]);
        $user->roles()->sync([$role->id_rol]);
        $user->contextos()->sync(collect($contexts)->mapWithKeys(
            fn(int $contextId) => [$contextId => ['es_contexto_principal' => $contextId === $contexts[0], 'activo' => true]]
        )->all());

        return $user;
    }

    private function createFacturaWithItem(int $contextId, string $number): Factura
    {
        $contrato = $this->createContratoForContext($contextId);
        $trabajo = $this->createTrabajoForContext($contextId, $contrato);
        $empresaFacturadora = $this->createEmpresaFacturadoraPermitida($contrato);
        $pedidoItem = $this->createPedidoItem($trabajo, str_replace('FAC-', 'PED-', $number), 1, 121);

        $factura = Factura::factory()->create([
            'id_contexto' => $contextId,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa_facturadora' => $empresaFacturadora->id_empresa,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'numero_factura' => $number,
            'numero_factura_ccp' => 'CCP-' . $number,
            'orden_factura' => 1,
            'fecha_emision' => '2026-05-06',
            'fecha_vencimiento' => '2026-06-06',
            'base_imponible' => 100,
            'iva' => 21,
            'importe' => 121,
            'total' => 121,
            'estado' => 'emitida',
        ]);

        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $pedidoItem->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 121,
            'observaciones' => 'Linea export test',
        ]);

        return $factura;
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
            'codigo_contrato' => 'EXP-' . $contextId . '-' . uniqid(),
            'nombre' => 'Contrato export ' . $contextId,
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);
    }

    private function createTrabajoForContext(int $contextId, Contrato $contrato): Trabajo
    {
        return Trabajo::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $contrato->id_empresa_cliente,
            'id_contrato' => $contrato->id_contrato,
        ]);
    }

    private function createEmpresaFacturadoraPermitida(Contrato $contrato): Empresa
    {
        $empresa = Empresa::factory()->create([
            'id_contexto' => $contrato->id_contexto,
            'cif' => 'B' . $contrato->id_contexto . str_pad((string) random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        ContratoEmpresaFacturadora::create([
            'id_contexto' => $contrato->id_contexto,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $empresa->id_empresa,
            'activo' => true,
            'observaciones' => 'Relacion de prueba export.',
        ]);

        return $empresa;
    }

    private function createPedido(Trabajo $trabajo, string $numeroPedido, float $importe): Pedido
    {
        return Pedido::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'numero_pedido' => $numeroPedido,
            'fecha_solicitud' => '2026-05-01',
            'importe_pedido' => $importe,
            'importe_solicitado' => $importe,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'estado' => 'recibido',
            'pedido_completo' => true,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ]);
    }

    private function createPedidoItem(Trabajo $trabajo, string $numeroPedido, float $cantidad, float $importe): PedidoItem
    {
        $pedido = $this->createPedido($trabajo, $numeroPedido, $importe);

        return PedidoItem::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_pedido' => $pedido->id_pedido,
            'codigo_servicio' => 'SERV-' . $numeroPedido,
            'numero_tarifa' => 'TAR-' . $numeroPedido,
            'descripcion_servicio' => 'Servicio ' . $numeroPedido,
            'precio_unitario' => $importe / max($cantidad, 1),
            'cantidad' => $cantidad,
            'total_linea' => $importe,
        ]);
    }
}
