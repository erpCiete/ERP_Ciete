<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Contrato;
use App\Models\ContratoEmpresaFacturadora;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tarifario;
use App\Models\TarifarioLinea;
use App\Models\Trabajo;
use App\Models\User;
use App\Support\ContextGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MaestrosTest extends TestCase
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

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
    }

    public function test_only_direction_can_view_maestros(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RolPermisosSeeder::class);

        $admin = $this->createUserForRole('admin', [1, 2, 3]);
        $director = $this->createUserForRole('director', [1, 2, 3]);

        $this->actingAs($admin)->get(route('maestros.index'))->assertForbidden();

        $this->actingAs($director)
            ->get(route('maestros.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Maestros/Index'));
    }

    public function test_user_without_permission_cannot_view_maestros(): void
    {
        $user = User::factory()->create(['id_contexto' => 1]);

        $this->actingAs($user)->get(route('maestros.index'))->assertForbidden();
    }

    public function test_all_context_blocks_contract_creation(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'contratos.ver',
            'contratos.crear',
        ], [1, 2]);

        $empresa = Empresa::factory()->create(['id_contexto' => 1]);

        $this->actingAs($user);
        $user->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $this->post(route('maestros.contratos.store'), [
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTX-ALL-BLOCK',
            'tipo' => 'marco',
            'estado' => 'vigente',
        ])->assertForbidden();
    }

    public function test_real_context_allows_contract_creation_and_audit(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'contratos.ver',
            'contratos.crear',
        ]);
        $empresa = Empresa::factory()->create(['id_contexto' => 1, 'nombre' => 'Cliente contrato']);

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->post(route('maestros.contratos.store'), [
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'MOEVE-MASTER-1',
            'nombre' => 'Contrato maestro MOEVE',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('contratos', [
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'MOEVE-MASTER-1',
            'activo' => true,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'accion' => 'crear',
            'tabla' => 'contratos',
            'modulo' => 'maestros.contratos',
            'id_contexto' => 1,
        ]);
    }

    public function test_contract_cannot_use_company_from_other_context(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'contratos.ver',
            'contratos.crear',
        ], [1, 2]);
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => 2]);

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->from(route('maestros.contratos.create'))
            ->post(route('maestros.contratos.store'), [
                'id_empresa_cliente' => $empresaRepsol->id_empresa,
                'codigo_contrato' => 'BAD-CONTEXT',
                'tipo' => 'marco',
                'estado' => 'vigente',
            ])
            ->assertSessionHasErrors('id_empresa_cliente');
    }

    public function test_sociedad_facturadora_requires_cif_same_context_and_no_duplicate(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'sociedades_facturadoras.ver',
            'sociedades_facturadoras.crear',
        ], [1, 2]);
        [$contrato] = $this->createContratoConEmpresa(1, 'SOC-CTX-1');
        $sinCif = Empresa::factory()->create(['id_contexto' => 1, 'cif' => null]);
        $otroContexto = Empresa::factory()->create(['id_contexto' => 2, 'cif' => 'B22222222']);
        $valida = Empresa::factory()->create(['id_contexto' => 1, 'cif' => 'B11111111']);

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->post(route('maestros.sociedades.store'), [
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $sinCif->id_empresa,
        ])->assertSessionHasErrors('id_empresa');

        $this->post(route('maestros.sociedades.store'), [
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $otroContexto->id_empresa,
        ])->assertSessionHasErrors('id_empresa');

        $this->post(route('maestros.sociedades.store'), [
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $valida->id_empresa,
        ])->assertRedirect(route('maestros.sociedades.index'));

        $this->post(route('maestros.sociedades.store'), [
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $valida->id_empresa,
        ])->assertSessionHasErrors('id_empresa');

        $this->assertDatabaseHas('contrato_empresas_facturadoras', [
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $valida->id_empresa,
            'id_contexto' => 1,
            'activo' => true,
        ]);
    }

    public function test_tarifario_cannot_use_contract_from_other_context_and_name_version_is_unique(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'tarifarios.ver',
            'tarifarios.crear',
        ], [1, 2]);
        [$contratoMoeve] = $this->createContratoConEmpresa(1, 'TAR-CTX-1');
        [$contratoRepsol] = $this->createContratoConEmpresa(2, 'TAR-CTX-2');

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->post(route('maestros.tarifarios.store'), [
            'id_contrato' => $contratoRepsol->id_contrato,
            'nombre' => 'Tarifa marco',
            'version' => '2026',
            'factor_multiplicador' => 1,
        ])->assertSessionHasErrors('id_contrato');

        $this->post(route('maestros.tarifarios.store'), [
            'id_contrato' => $contratoMoeve->id_contrato,
            'nombre' => 'Tarifa marco',
            'version' => '2026',
            'factor_multiplicador' => 1,
        ])->assertRedirect();

        $this->post(route('maestros.tarifarios.store'), [
            'id_contrato' => $contratoMoeve->id_contrato,
            'nombre' => 'Tarifa marco',
            'version' => '2026',
            'factor_multiplicador' => 1,
        ])->assertSessionHasErrors('nombre');
    }

    public function test_tarifario_line_code_is_unique_within_tarifario_and_context(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'tarifario_lineas.ver',
            'tarifario_lineas.crear',
        ]);
        $tarifario = $this->createTarifario(1, 'LIN-CTX-1');

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $payload = [
            'id_tarifario' => $tarifario->id_tarifario,
            'codigo_tarifa' => 'T-001',
            'actuacion' => 'Actuacion controlada',
            'tarifa_base' => 100,
            'tarifa_aplicada' => 100,
        ];

        $this->post(route('maestros.tarifario-lineas.store'), $payload)->assertRedirect();
        $this->post(route('maestros.tarifario-lineas.store'), $payload)->assertSessionHasErrors('codigo_tarifa');
    }

    public function test_deactivation_keeps_related_history_and_writes_audit(): void
    {
        $user = $this->createUserWithPermissions([
            'maestros.ver',
            'contratos.ver',
            'contratos.eliminar',
            'sociedades_facturadoras.ver',
            'sociedades_facturadoras.eliminar',
            'tarifarios.ver',
            'tarifarios.eliminar',
            'tarifario_lineas.ver',
            'tarifario_lineas.eliminar',
        ]);
        [$contrato] = $this->createContratoConEmpresa(1, 'HIST-1');
        $sociedadEmpresa = Empresa::factory()->create(['id_contexto' => 1, 'cif' => 'B33333333']);
        $relacion = ContratoEmpresaFacturadora::create([
            'id_contexto' => 1,
            'id_contrato' => $contrato->id_contrato,
            'id_empresa' => $sociedadEmpresa->id_empresa,
            'activo' => true,
        ]);
        $tarifario = $this->createTarifario(1, 'HIST-1', $contrato);
        $linea = TarifarioLinea::create([
            'id_contexto' => 1,
            'id_tarifario' => $tarifario->id_tarifario,
            'codigo_tarifa' => 'HIST-LIN',
            'actuacion' => 'Linea con historico',
            'tarifa_base' => 90,
            'tarifa_aplicada' => 90,
            'activo' => true,
        ]);
        $pedidoItem = $this->createPedidoItemForLinea($linea, $tarifario, $contrato);

        $this->actingAs($user);
        $user->setActiveContextSelection(1);

        $this->delete(route('maestros.tarifario-lineas.destroy', $linea->id_tarifario_linea))->assertRedirect();
        $this->delete(route('maestros.tarifarios.destroy', $tarifario->id_tarifario))->assertRedirect();
        $this->delete(route('maestros.sociedades.destroy', $relacion->id))->assertRedirect();
        $this->delete(route('maestros.contratos.destroy', $contrato->id_contrato))->assertRedirect();

        $this->assertDatabaseHas('tarifario_lineas', [
            'id_tarifario_linea' => $linea->id_tarifario_linea,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('pedido_items', [
            'id_pedido_item' => $pedidoItem->id_pedido_item,
            'id_tarifario_linea' => $linea->id_tarifario_linea,
        ]);
        $this->assertDatabaseHas('tarifarios', [
            'id_tarifario' => $tarifario->id_tarifario,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('contrato_empresas_facturadoras', [
            'id' => $relacion->id,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('contratos', [
            'id_contrato' => $contrato->id_contrato,
            'activo' => false,
            'estado' => 'cancelado',
        ]);

        $this->assertSame(4, AuditLog::query()->where('accion', 'desactivar')->count());
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     * @param  array<int, int>  $contexts
     */
    private function createUserWithPermissions(array $permissionSlugs, array $contexts = [1]): User
    {
        $role = Role::query()->create([
            'nombre' => 'Rol maestros ' . Str::random(8),
            'slug' => 'rol-maestros-' . Str::random(12),
            'activo' => true,
        ]);

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                ['nombre' => $slug, 'activo' => true],
            );
            $role->permissions()->syncWithoutDetaching([$permission->id_permiso]);
        }

        $user = User::factory()->create(['id_contexto' => $contexts[0]]);
        $directorRole = Role::query()->firstOrCreate(
            ['slug' => 'director'],
            ['nombre' => 'Direccion', 'descripcion' => 'Direccion', 'activo' => true],
        );
        $user->roles()->sync([$role->id_rol, $directorRole->id_rol]);
        $this->assignContexts($user, $contexts);

        return $user;
    }

    /**
     * @param  array<int, int>  $contexts
     */
    private function createUserForRole(string $roleSlug, array $contexts): User
    {
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
        $user = User::factory()->create(['id_contexto' => $contexts[0]]);
        $user->roles()->sync([$role->id_rol]);
        $this->assignContexts($user, $contexts);

        return $user;
    }

    /**
     * @param  array<int, int>  $contexts
     */
    private function assignContexts(User $user, array $contexts): void
    {
        foreach ($contexts as $index => $contextId) {
            DB::table('usuario_contextos')->updateOrInsert(
                ['id_usuario' => $user->id_usuario, 'id_contexto' => $contextId],
                [
                    'es_contexto_principal' => $index === 0,
                    'activo' => true,
                    'created_at' => now(),
                ],
            );
        }
    }

    /**
     * @return array{0: Contrato, 1: Empresa}
     */
    private function createContratoConEmpresa(int $contextId, string $codigo): array
    {
        $empresa = Empresa::factory()->create([
            'id_contexto' => $contextId,
            'cif' => 'B' . substr(str_pad((string) abs(crc32($codigo)), 8, '0', STR_PAD_LEFT), 0, 8),
        ]);

        $contrato = Contrato::create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => $codigo,
            'nombre' => 'Contrato ' . $codigo,
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
        ]);

        return [$contrato, $empresa];
    }

    private function createTarifario(int $contextId, string $codigo, ?Contrato $contrato = null): Tarifario
    {
        $contrato ??= $this->createContratoConEmpresa($contextId, $codigo)[0];

        return Tarifario::create([
            'id_contexto' => $contextId,
            'id_contrato' => $contrato->id_contrato,
            'nombre' => 'Tarifario ' . $codigo,
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
        ]);
    }

    private function createPedidoItemForLinea(
        TarifarioLinea $linea,
        Tarifario $tarifario,
        Contrato $contrato,
    ): PedidoItem {
        $empresa = $contrato->empresa;
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => 1,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contrato' => $contrato->id_contrato,
            'id_tarifario' => $tarifario->id_tarifario,
        ]);
        $pedido = Pedido::create([
            'id_contexto' => 1,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_tarifario' => $tarifario->id_tarifario,
            'numero_pedido' => 'PED-HIST-MASTER',
            'fecha_solicitud' => '2026-05-07',
            'estado' => 'solicitado',
            'importe_pedido' => 90,
            'importe_solicitado' => 90,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'pedido_completo' => false,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ]);

        return PedidoItem::create([
            'id_contexto' => 1,
            'id_pedido' => $pedido->id_pedido,
            'id_tarifario_linea' => $linea->id_tarifario_linea,
            'codigo_servicio' => 'HIST-LIN',
            'numero_tarifa' => 'HIST-LIN',
            'descripcion_servicio' => 'Linea historica',
            'precio_unitario' => 90,
            'cantidad' => 1,
            'total_linea' => 90,
        ]);
    }
}
