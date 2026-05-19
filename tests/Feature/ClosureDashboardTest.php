<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Legalizacion;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\ClosureDashboardService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClosureDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null): HtmlString
            {
                return new HtmlString('');
            }
        });

        $this->seed(DatabaseSeeder::class);
    }

    public function test_director_can_view_dashboard_with_real_data_and_admin_cannot_access_it(): void
    {
        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get('/cierre')
            ->assertForbidden();

        $this->actingAs($cesar)
            ->get('/cierre')
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Cierre/Dashboard')
                ->has('works')
                ->has('contexts'));
    }

    public function test_director_can_mark_reviewed_and_close_eligible_work(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure($cesar, [
            'estado' => 'terminado',
            'fecha_terminacion' => '2026-02-10',
            'bloqueado_cierre' => false,
        ]);
        $trabajo = $scenario['trabajo'];

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->post(route('cierre.review'), ['ids' => [$trabajo->id_trabajo]])
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'bloqueado_cierre' => false,
        ]);

        $this->post(route('cierre.close', $trabajo))
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'finalizado',
            'bloqueado_cierre' => false,
        ]);
    }

    public function test_pending_legalizations_do_not_block_finalization_when_checklist_is_ok(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure($cesar, [
            'estado' => 'terminado',
            'fecha_terminacion' => '2026-02-10',
            'bloqueado_cierre' => false,
        ]);
        $trabajo = $scenario['trabajo'];

        Legalizacion::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_usuario_responsable' => $cesar->id_usuario,
            'tipo_legalizacion' => 'Seguimiento documental',
            'numero_expediente' => 'LEG-' . $trabajo->id_trabajo,
            'estado' => 'pendiente',
            'descripcion_seleccionable' => 'Pendiente de revision administrativa',
            'observaciones' => 'No bloquea la finalizacion en la fase actual.',
        ]);

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->post(route('cierre.review'), ['ids' => [$trabajo->id_trabajo]])
            ->assertRedirect();

        $this->post(route('cierre.close', $trabajo))
            ->assertRedirect();

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'finalizado',
            'bloqueado_cierre' => false,
        ]);
    }

    public function test_finalized_state_is_treated_as_finalized_without_closed_flag(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure($cesar, [
            'estado' => 'finalizado',
            'fecha_terminacion' => '2026-02-10',
        ]);
        $trabajo = $scenario['trabajo'];

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $payload = app(ClosureDashboardService::class)->buildDashboardPayload($cesar);
        $serialized = collect($payload['works'])->firstWhere('id', $trabajo->id_trabajo);

        $this->assertNotNull($serialized);
        $this->assertTrue($serialized['finalizado']);
        $this->assertFalse($serialized['finalizadoLegacy']);
        $this->assertTrue($serialized['revisionFinalizacionMarcada']);
        $this->assertArrayNotHasKey('fechaCierre', $serialized['trazabilidad']);
        $this->assertArrayHasKey('fechaFinalizacion', $serialized['trazabilidad']);
    }

    public function test_dashboard_uses_factura_items_amounts_for_economy(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure(
            $cesar,
            [
                'estado' => 'finalizado',
                'fecha_terminacion' => '2026-05-04',
            ],
            true,
            [
                'importe_pedido' => 6000,
                'importe_solicitado' => 6000,
                'importe_facturado' => 9000,
            ],
        );

        $trabajo = $scenario['trabajo'];
        $pedido = $scenario['pedido'];

        $pedidoItem = PedidoItem::create([
            'id_contexto' => $trabajo->id_contexto,
            'id_pedido' => $pedido->id_pedido,
            'codigo_servicio' => 'SERV-CIERRE-' . $trabajo->id_trabajo,
            'descripcion_servicio' => 'Linea demo de cierre',
            'precio_unitario' => 4500,
            'cantidad' => 1,
            'total_linea' => 4500,
        ]);

        $factura = Factura::factory()->create([
            'id_contexto' => $trabajo->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'id_contrato' => $trabajo->id_contrato,
            'numero_factura' => 'FAC-CIERRE-' . $trabajo->id_trabajo,
            'numero_factura_ccp' => 'CCP-CIERRE-' . $trabajo->id_trabajo,
            'orden_factura' => 1,
            'base_imponible' => 9000,
            'importe' => 9000,
            'total' => 9000,
        ]);

        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $pedidoItem->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => 4500,
        ]);

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $payload = app(ClosureDashboardService::class)->buildDashboardPayload($cesar);
        $serialized = collect($payload['works'])->firstWhere('id', $trabajo->id_trabajo);

        $this->assertNotNull($serialized);
        $this->assertEquals(4500.0, $serialized['importeTrabajo']);
        $this->assertNotEquals(9000.0, $serialized['importeTrabajo']);
    }

    public function test_director_cannot_close_blocked_work(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure($cesar, [
            'estado' => 'terminado',
            'fecha_terminacion' => null,
            'bloqueado_cierre' => false,
        ]);
        $trabajo = $scenario['trabajo'];

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->post(route('cierre.close', $trabajo))
            ->assertSessionHasErrors(['message']);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'terminado',
        ]);
    }

    public function test_closed_work_cannot_be_reopened(): void
    {
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $scenario = $this->createTrabajoForClosure($cesar, [
            'estado' => 'finalizado',
            'fecha_terminacion' => '2026-02-11',
        ]);
        $trabajo = $scenario['trabajo'];

        $this->actingAs($cesar);
        $cesar->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->post("/cierre/{$trabajo->id_trabajo}/reabrir", [
            'reason' => 'Falta revisar importes finales.',
        ])
            ->assertStatus(405);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'finalizado',
        ]);
    }

    private function createTrabajoForClosure(
        User $user,
        array $trabajoOverrides = [],
        bool $createPedido = true,
        array $pedidoOverrides = [],
    ): array {
        $contextId = (int) ($trabajoOverrides['id_contexto'] ?? $user->id_contexto ?? 1);

        $empresa = Empresa::factory()->create([
            'id_contexto' => $contextId,
            'tipo_empresa' => 'cliente',
        ]);

        $estacion = EstacionServicio::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);

        $contractCode = 'CTR-CIERRE-' . uniqid();
        $contratoId = (int) DB::table('contratos')->insertGetId([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => $contractCode,
            'nombre' => 'Contrato cierre test',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tarifarioId = (int) DB::table('tarifarios')->insertGetId([
            'id_contexto' => $contextId,
            'id_contrato' => $contratoId,
            'nombre' => 'Tarifario cierre ' . uniqid(),
            'version' => 'v1',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trabajoData = array_merge([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'id_tarifario' => $tarifarioId,
            'numero_trabajo' => random_int(800000, 999999),
            'numero_estacion' => 'EST-' . random_int(1000, 9999),
            'descripcion_trabajo' => 'Trabajo de prueba para cierre',
            'fecha_encargo' => '2026-05-01',
            'fecha_terminacion' => '2026-05-02',
            'estado' => 'terminado',
            'bloqueado_cierre' => false,
        ], $trabajoOverrides);

        $trabajo = Trabajo::factory()->create($trabajoData);

        $pedido = null;

        if ($createPedido) {
            $pedido = Pedido::create(array_merge([
                'id_contexto' => $contextId,
                'id_trabajo' => $trabajo->id_trabajo,
                'id_tarifario' => $tarifarioId,
                'numero_pedido' => 'PED-CIERRE-' . $trabajo->id_trabajo,
                'fecha_solicitud' => '2026-05-01',
                'importe_pedido' => 1000,
                'importe_solicitado' => 1000,
                'importe_facturado' => 0,
                'unidades_pedido' => 1,
                'unidades_solicitadas' => 1,
                'estado' => 'recibido',
                'pedido_completo' => true,
                'tiene_mas_de_1_item' => false,
                'facturado_completo' => false,
            ], $pedidoOverrides));
        }

        return [
            'trabajo' => $trabajo,
            'pedido' => $pedido,
            'empresa' => $empresa,
            'estacion' => $estacion,
            'id_contrato' => $contratoId,
            'id_tarifario' => $tarifarioId,
        ];
    }
}
