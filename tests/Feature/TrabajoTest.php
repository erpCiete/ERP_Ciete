<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\TipoDocumento;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\TrabajoStateService;
use App\Support\TrabajoPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TrabajoTest extends TestCase
{
    use RefreshDatabase;

    private ContextoCliente $ctxMoeve;
    private ContextoCliente $ctxRepsol;

    protected function setUp(): void
    {
        parent::setUp();

        // Evitar error de Vite manifest en tests (frontend no compilado)
        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null): HtmlString
            {
                return new HtmlString('');
            }
        });

        // Crear los dos contextos base con IDs fijos (1=MOEVE, 2=REPSOL)
        // para coincidir con los hardcoded en StoreTrabajoRequest::esCliente()
        $this->ctxMoeve = ContextoCliente::factory()->create([
            'id_contexto' => 1,
            'nombre' => 'MOEVE',
            'codigo' => 'MOEVE',
        ]);
        $this->ctxRepsol = ContextoCliente::factory()->create([
            'id_contexto' => 2,
            'nombre' => 'REPSOL',
            'codigo' => 'REPSOL',
        ]);

        // Seeders de roles, permisos y asignación rol-permiso
        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermisosSeeder']);
        $this->artisan('db:seed', ['--class' => 'RolPermisosSeeder']);
    }

    /**
     * Crea un usuario con un contexto y un rol determinados, e inserta la
     * entrada en usuario_contextos para que getAccessibleContextIds funcione.
     */
    private function createUserWithContext(string $roleSlug, ContextoCliente $ctx): User
    {
        $user = User::factory()->create(['id_contexto' => $ctx->id_contexto]);

        // Asignar rol a través del pivot usuario_roles
        $role = \App\Models\Role::where('slug', $roleSlug)->firstOrFail();
        $user->roles()->attach($role->id_rol, ['created_at' => now()]);

        // Registrar entrada en usuario_contextos
        DB::table('usuario_contextos')->insert([
            'id_usuario' => $user->id_usuario,
            'id_contexto' => $ctx->id_contexto,
            'es_contexto_principal' => true,
            'activo' => true,
            'created_at' => now(),
        ]);

        return $user;
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/trabajos');
        $response->assertRedirect('/login');
    }

    public function test_context_isolation_repsol_cannot_see_moeve_jobs()
    {
        $gestorRepsol = $this->createUserWithContext('ejecucion_repsol', $this->ctxRepsol);

        $empresaMoeve = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'numero_trabajo' => 99901,
            'descripcion_trabajo' => 'Obra exclusiva MOEVE',
        ]);

        $this->actingAs($gestorRepsol);

        // Prueba de Listado — verificar aislamiento de contexto
        $response = $this->get('/trabajos');
        $response->assertOk();
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('Trabajos/Index')
                ->has('trabajos.data', 0) // Repsol user sees 0 works (the only one is MOEVE)
        );
    }

    public function test_validation_fails_if_moeve_job_misses_contrato()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);

        // Crear empresa y estación en contexto MOEVE para activar la regla
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        $response = $this->actingAs($gestorMoeve)->post('/trabajos', [
            'numero_trabajo' => '999',
            'descripcion_trabajo' => 'Obra de prueba MOEVE',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'en_curso',
            // Omitimos intencionadamente el 'id_contrato'
        ]);

        $response->assertSessionHasErrors(['id_contrato']);
    }

    public function test_create_view_includes_operational_catalogs_for_the_form()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
        $this->seed(\Database\Seeders\DatosBaseSeeder::class);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos/crear')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) =>
                $page->component('Trabajos/Form')
                    ->has('clientContexts')
                    ->has('contratos')
                    ->has('tiposDocumento')
                    ->has('tiposTrabajo')
            );
    }

    public function test_index_passes_excel_creation_catalogs_for_real_context_only()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => 'MOE-EXCEL',
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) =>
                $page->component('Trabajos/Index')
                    ->has('creationCatalogs.estaciones', 1)
            );
    }

    public function test_index_marks_default_tariff_inside_excel_creation_catalogs(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => 'MOE-DEFAULT',
        ]);

        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-DEFAULT-MOE',
            'nombre' => 'Contrato habitual MOEVE',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-DEFAULT-MOE')->value('id_contrato');

        DB::table('tarifarios')->insert([
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoId,
                'nombre' => 'Tarifa habitual',
                'version' => '2026',
                'es_predeterminado' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoId,
                'nombre' => 'Tarifa alternativa',
                'version' => '2026',
                'es_predeterminado' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('creationCatalogs.tarifarios.0.is_default', true)
            );
    }

    public function test_patch_field_blocks_tariff_change_when_work_already_has_pedidos(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        DB::table('contratos')->insert([
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_empresa_cliente' => $empresa->id_empresa,
                'codigo_contrato' => 'CTR-PATCH-A',
                'nombre' => 'Contrato patch A',
                'tipo' => 'marco',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_empresa_cliente' => $empresa->id_empresa,
                'codigo_contrato' => 'CTR-PATCH-B',
                'nombre' => 'Contrato patch B',
                'tipo' => 'marco',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $contratoA = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-PATCH-A')->value('id_contrato');
        $contratoB = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-PATCH-B')->value('id_contrato');

        DB::table('tarifarios')->insert([
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoA,
                'nombre' => 'Tarifa patch A',
                'version' => '2026',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoB,
                'nombre' => 'Tarifa patch B',
                'version' => '2026',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $tarifarioA = (int) DB::table('tarifarios')->where('nombre', 'Tarifa patch A')->value('id_tarifario');
        $tarifarioB = (int) DB::table('tarifarios')->where('nombre', 'Tarifa patch B')->value('id_tarifario');

        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoA,
            'id_tarifario' => $tarifarioA,
        ]);
        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_tarifario' => $tarifarioA,
        ]);

        $this->actingAs($gestorMoeve)
            ->patchJson(route('trabajos.patch-field', $trabajo), [
                'campo' => 'id_tarifario',
                'valor' => $tarifarioB,
                'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'No se puede cambiar el contrato/tarifa porque este trabajo ya tiene pedidos.');
    }

    public function test_index_can_find_work_by_related_pedido_number_and_station_data(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacionObjetivo = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => '33450',
            'nombre' => 'Moeve Valencia Centro',
            'poblacion' => 'Valencia',
            'provincia' => 'Valencia',
        ]);
        $estacionSecundaria = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => '99881',
            'nombre' => 'Moeve Castellon Puerto',
            'poblacion' => 'Castellon',
            'provincia' => 'Castellon',
        ]);

        $trabajoObjetivo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacionObjetivo->id_estacion_servicio,
            'numero_trabajo' => 55101,
            'descripcion_trabajo' => 'Trabajo con pedido operativo buscable',
        ]);
        $trabajoSecundario = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacionSecundaria->id_estacion_servicio,
            'numero_trabajo' => 55102,
            'descripcion_trabajo' => 'Trabajo secundario',
        ]);

        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoObjetivo->id_trabajo,
            'numero_pedido' => '600034631',
            'importe_pedido' => 5525,
        ]);
        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoSecundario->id_trabajo,
            'numero_pedido' => '700000001',
            'importe_pedido' => 810,
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?search=600034631')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->has('trabajos.data', 1)
                    ->where('trabajos.data.0.id_trabajo', $trabajoObjetivo->id_trabajo)
            );

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?search=33450')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->has('trabajos.data', 1)
                    ->where('trabajos.data.0.id_trabajo', $trabajoObjetivo->id_trabajo)
            );
    }

    public function test_index_supports_tarifa_and_pedido_flags_filters(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        DB::table('contratos')->insert([
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_empresa_cliente' => $empresa->id_empresa,
                'codigo_contrato' => 'CTR-772',
                'nombre' => 'Contrato MOEVE 772',
                'tipo' => 'marco',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_empresa_cliente' => $empresa->id_empresa,
                'codigo_contrato' => 'CTR-999',
                'nombre' => 'Contrato MOEVE 999',
                'tipo' => 'marco',
                'estado' => 'vigente',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $contratoObjetivo = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-772')->value('id_contrato');
        $contratoSecundario = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-999')->value('id_contrato');

        DB::table('tarifarios')->insert([
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoObjetivo,
                'nombre' => 'Tarifa 772 Operativa',
                'version' => '2026',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $this->ctxMoeve->id_contexto,
                'id_contrato' => $contratoSecundario,
                'nombre' => 'Tarifa 999 Secundaria',
                'version' => '2026',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $tarifaObjetivo = (int) DB::table('tarifarios')->where('nombre', 'Tarifa 772 Operativa')->value('id_tarifario');
        $tarifaSecundaria = (int) DB::table('tarifarios')->where('nombre', 'Tarifa 999 Secundaria')->value('id_tarifario');

        $trabajoObjetivo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoObjetivo,
            'id_tarifario' => $tarifaObjetivo,
            'numero_trabajo' => 77201,
            'descripcion_trabajo' => 'Trabajo con tarifa buscable',
        ]);
        $trabajoSecundario = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoSecundario,
            'id_tarifario' => $tarifaSecundaria,
            'numero_trabajo' => 99901,
            'descripcion_trabajo' => 'Trabajo sin multipedido',
        ]);

        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoObjetivo->id_trabajo,
            'numero_pedido' => '772000001',
            'importe_pedido' => 5525,
            'importe_solicitado' => 5525,
            'importe_facturado' => 5525,
        ]);
        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoObjetivo->id_trabajo,
            'numero_pedido' => '772000002',
            'importe_pedido' => 810,
            'importe_solicitado' => 810,
            'importe_facturado' => 810,
        ]);
        Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoSecundario->id_trabajo,
            'numero_pedido' => '999000001',
            'importe_pedido' => 0,
            'importe_solicitado' => 0,
            'importe_facturado' => 0,
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?search=772&id_tarifario=' . $tarifaObjetivo . '&has_pedidos=1&multi_pedido=1&pedido_importe=1&facturado=1&solicitado=1')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->has('trabajos.data', 1)
                    ->where('trabajos.data.0.id_trabajo', $trabajoObjetivo->id_trabajo)
            );
    }

    public function test_index_supports_server_side_sort_by_work_number_in_both_directions(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        $trabajoBajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 1001,
            'descripcion_trabajo' => 'Trabajo 1001',
            'estado' => 'en_curso',
        ]);
        $trabajoAlto = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 3001,
            'descripcion_trabajo' => 'Trabajo 3001',
            'estado' => 'en_curso',
        ]);
        $trabajoMedio = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 2001,
            'descripcion_trabajo' => 'Trabajo 2001',
            'estado' => 'en_curso',
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?sort=numero_trabajo&direction=asc')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('filters.sort', 'numero_trabajo')
                    ->where('filters.direction', 'asc')
                    ->where('trabajos.data.0.id_trabajo', $trabajoBajo->id_trabajo)
                    ->where('trabajos.data.1.id_trabajo', $trabajoMedio->id_trabajo)
                    ->where('trabajos.data.2.id_trabajo', $trabajoAlto->id_trabajo)
            );

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?sort=numero_trabajo&direction=desc')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('filters.sort', 'numero_trabajo')
                    ->where('filters.direction', 'desc')
                    ->where('trabajos.data.0.id_trabajo', $trabajoAlto->id_trabajo)
                    ->where('trabajos.data.1.id_trabajo', $trabajoMedio->id_trabajo)
                    ->where('trabajos.data.2.id_trabajo', $trabajoBajo->id_trabajo)
            );
    }

    public function test_index_default_order_keeps_cancelled_jobs_at_the_end_when_no_sort_is_active(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        $trabajoEnCurso = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 4101,
            'descripcion_trabajo' => 'Trabajo en curso',
            'estado' => 'en_curso',
            'fecha_encargo' => '2026-05-10',
        ]);
        $trabajoTerminado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 4102,
            'descripcion_trabajo' => 'Trabajo terminado',
            'estado' => 'terminado',
            'fecha_encargo' => '2026-05-20',
        ]);
        $trabajoCancelado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 4103,
            'descripcion_trabajo' => 'Trabajo cancelado',
            'estado' => 'cancelado',
            'fecha_encargo' => '2026-05-29',
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('trabajos.data.0.id_trabajo', $trabajoEnCurso->id_trabajo)
                    ->where('trabajos.data.1.id_trabajo', $trabajoTerminado->id_trabajo)
                    ->where('trabajos.data.2.id_trabajo', $trabajoCancelado->id_trabajo)
            );
    }

    public function test_finished_work_without_pedido_is_normalized_back_to_terminado(): void
    {
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 5101,
            'descripcion_trabajo' => 'Trabajo terminado sin pedido',
            'fecha_terminacion' => '2026-05-21',
            'estado' => 'pendiente_facturar',
        ]);

        app(TrabajoStateService::class)->syncTrabajo($trabajo);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'terminado',
        ]);
    }

    public function test_finished_work_with_pedido_and_without_billing_derives_pending_facturar(): void
    {
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        ['trabajo' => $trabajo, 'pedido' => $pedido] = $this->createTrabajoBillingScenario(
            $empresa,
            $estacion,
            [
                'numero_trabajo' => 5102,
                'descripcion_trabajo' => 'Trabajo con pedido sin facturar',
                'fecha_terminacion' => '2026-05-22',
                'estado' => 'terminado',
            ],
            [
                'importe_pedido' => 300,
                'importe_solicitado' => 300,
            ],
        );

        app(TrabajoStateService::class)->syncPedidosAndTrabajosByPedidoIds([$pedido->id_pedido]);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'pendiente_facturar',
        ]);
    }

    public function test_index_exposes_partial_billing_without_promoting_work_to_facturado(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        ['trabajo' => $trabajo, 'pedido' => $pedido] = $this->createTrabajoBillingScenario(
            $empresa,
            $estacion,
            [
                'numero_trabajo' => 5103,
                'descripcion_trabajo' => 'Trabajo con facturacion parcial',
                'fecha_terminacion' => '2026-05-23',
                'estado' => 'terminado',
            ],
            [
                'importe_pedido' => 300,
                'importe_solicitado' => 300,
            ],
        );

        $this->attachInvoiceToPedido($trabajo, $pedido, 300, 120);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?search=5103')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('trabajos.data.0.id_trabajo', $trabajo->id_trabajo)
                    ->where('trabajos.data.0.estado', 'pendiente_facturar')
                    ->where('trabajos.data.0.estado_facturacion', 'facturado_parcial')
            );
    }

    public function test_finished_work_with_full_billing_derives_facturado_and_ready_for_closure(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);

        ['trabajo' => $trabajo, 'pedido' => $pedido] = $this->createTrabajoBillingScenario(
            $empresa,
            $estacion,
            [
                'numero_trabajo' => 5104,
                'descripcion_trabajo' => 'Trabajo facturado completo',
                'fecha_terminacion' => '2026-05-24',
                'estado' => 'terminado',
            ],
            [
                'importe_pedido' => 450,
                'importe_solicitado' => 450,
            ],
        );

        $this->attachInvoiceToPedido($trabajo, $pedido, 450, 450);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'facturado',
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?search=5104')
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('trabajos.data.0.id_trabajo', $trabajo->id_trabajo)
                    ->where('trabajos.data.0.estado', 'facturado')
                    ->where('trabajos.data.0.cierre_secundario', 'listo_para_cierre')
            );
    }

    public function test_finalized_work_remains_finalizado_after_state_sync(): void
    {
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxRepsol->id_contexto,
        ]);

        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'numero_trabajo' => 5105,
            'descripcion_trabajo' => 'Trabajo ya finalizado',
            'fecha_terminacion' => '2026-05-25',
            'estado' => 'finalizado',
        ]);

        app(TrabajoStateService::class)->syncTrabajo($trabajo);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'finalizado',
        ]);
    }

    public function test_index_supports_server_side_sort_by_station_code(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacionAlta = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => '900',
            'nombre' => 'Estacion alta',
        ]);
        $estacionBaja = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'codigo_estacion' => '100',
            'nombre' => 'Estacion baja',
        ]);

        $trabajoAlta = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacionAlta->id_estacion_servicio,
            'numero_trabajo' => 5101,
            'descripcion_trabajo' => 'Trabajo estacion alta',
            'estado' => 'en_curso',
        ]);
        $trabajoBaja = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacionBaja->id_estacion_servicio,
            'numero_trabajo' => 5102,
            'descripcion_trabajo' => 'Trabajo estacion baja',
            'estado' => 'en_curso',
        ]);

        $this->actingAs($gestorMoeve)
            ->get('/trabajos?sort=codigo_estacion&direction=asc')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Index')
                    ->where('trabajos.data.0.id_trabajo', $trabajoBaja->id_trabajo)
                    ->where('trabajos.data.1.id_trabajo', $trabajoAlta->id_trabajo)
            );
    }

    public function test_json_store_creates_work_for_excel_row_and_logs_audit()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);
        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-EXCEL',
            'nombre' => 'Contrato Excel',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-EXCEL')->value('id_contrato');

        $response = $this->actingAs($gestorMoeve)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'numero_trabajo' => 99001,
            'numero_trabajo_operativo' => 'MOEVE-99001-A',
            'descripcion_trabajo' => 'Creado desde fila Excel',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'fecha_encargo' => '2026-05-03',
            'observaciones' => 'Observacion local de fila nueva',
            'id_responsable_ciete' => $gestorMoeve->id_usuario,
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'success' => true,
                'trabajo' => [
                    'numero_trabajo' => 99001,
                    'numero_trabajo_operativo' => 'MOEVE-99001-A',
                    'numero_trabajo_visible' => 'MOEVE-99001-A',
                    'id_contexto' => $this->ctxMoeve->id_contexto,
                    'observaciones' => 'Observacion local de fila nueva',
                    'id_responsable_ciete' => $gestorMoeve->id_usuario,
                    'responsable_ciete' => [
                        'id_usuario' => $gestorMoeve->id_usuario,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('trabajos', [
            'numero_trabajo' => 99001,
            'numero_trabajo_operativo' => 'MOEVE-99001-A',
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_responsable_ciete' => $gestorMoeve->id_usuario,
            'estado' => 'en_curso',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'trabajos',
            'accion' => 'crear',
            'campo' => 'id_trabajo',
        ]);
    }

    public function test_json_store_creates_work_without_fake_order(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);
        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-WITHOUT-PEDIDO',
            'nombre' => 'Contrato sin pedido',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-WITHOUT-PEDIDO')->value('id_contrato');

        $this->actingAs($gestorMoeve)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'numero_trabajo' => 99002,
            'numero_trabajo_operativo' => 'CIETE-TEXTO-99002',
            'descripcion_trabajo' => 'Trabajo sin pedido real',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'fecha_encargo' => '2026-05-03',
        ])->assertCreated();

        $this->assertDatabaseHas('trabajos', [
            'numero_trabajo' => 99002,
            'numero_trabajo_operativo' => 'CIETE-TEXTO-99002',
        ]);
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_json_store_accepts_tarifario_and_derives_contract_for_excel_row(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);
        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-TARIFA-EXCEL',
            'nombre' => 'Contrato con tarifa única',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-TARIFA-EXCEL')->value('id_contrato');

        DB::table('tarifarios')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_contrato' => $contratoId,
            'nombre' => 'Tarifa única Excel',
            'version' => '2026',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tarifarioId = (int) DB::table('tarifarios')->where('nombre', 'Tarifa única Excel')->value('id_tarifario');

        $response = $this->actingAs($gestorMoeve)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'descripcion_trabajo' => 'Trabajo creado con selector combinado',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_tarifario' => $tarifarioId,
            'fecha_encargo' => '2026-05-03',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('trabajo.id_tarifario', $tarifarioId)
            ->assertJsonPath('trabajo.id_contrato', $contratoId)
            ->assertJsonPath('trabajo.nombre_tarifa', 'Tarifa única Excel')
            ->assertJsonPath('trabajo.nombre_contrato', 'Contrato con tarifa única');

        $this->assertDatabaseHas('trabajos', [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_tarifario' => $tarifarioId,
            'id_contrato' => $contratoId,
        ]);
    }

    public function test_json_store_autogenerates_work_number_and_operational_number_for_moeve(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);
        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-AUTO-MOE',
            'nombre' => 'Contrato auto MOE',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-AUTO-MOE')->value('id_contrato');

        Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'numero_trabajo' => 122,
            'numero_trabajo_operativo' => 'MOE-000122',
        ]);

        $response = $this->actingAs($gestorMoeve)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'descripcion_trabajo' => 'Trabajo autogenerado MOEVE',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'fecha_encargo' => '2026-05-03',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('trabajo.numero_trabajo', 123)
            ->assertJsonPath('trabajo.numero_trabajo_operativo', 'MOE-000123')
            ->assertJsonPath('trabajo.numero_trabajo_visible', 'MOE-000123');

        $this->assertDatabaseHas('trabajos', [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'numero_trabajo' => 123,
            'numero_trabajo_operativo' => 'MOE-000123',
        ]);
    }

    public function test_json_store_autogeneration_skips_operational_collisions(): void
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxMoeve->id_contexto,
        ]);
        DB::table('contratos')->insert([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => 'CTR-AUTO-COLLISION',
            'nombre' => 'Contrato auto colision',
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $contratoId = (int) DB::table('contratos')->where('codigo_contrato', 'CTR-AUTO-COLLISION')->value('id_contrato');

        Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'numero_trabajo' => 123,
            'numero_trabajo_operativo' => 'MOE-000123',
        ]);
        Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'numero_trabajo' => 7,
            'numero_trabajo_operativo' => 'MOE-000124',
        ]);

        $response = $this->actingAs($gestorMoeve)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'descripcion_trabajo' => 'Trabajo autogenerado con salto',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'fecha_encargo' => '2026-05-03',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('trabajo.numero_trabajo', 125)
            ->assertJsonPath('trabajo.numero_trabajo_operativo', 'MOE-000125')
            ->assertJsonPath('trabajo.numero_trabajo_visible', 'MOE-000125');
    }

    public function test_json_store_keeps_manual_number_and_generates_repsol_operational_number_when_missing(): void
    {
        $gestorRepsol = $this->createUserWithContext('ejecucion_repsol', $this->ctxRepsol);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_contexto' => $this->ctxRepsol->id_contexto,
        ]);
        DB::table('tipos_documento')->insert([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'nombre' => 'Documento auto Repsol',
            'codigo' => 'REP-DOC-AUTO',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoDocumentoId = (int) DB::table('tipos_documento')->where('codigo', 'REP-DOC-AUTO')->value('id_tipo_documento');

        DB::table('tipos_trabajo')->insert([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_tipo_documento' => $tipoDocumentoId,
            'nombre' => 'Tipo auto Repsol',
            'codigo' => 'REP-TIP-AUTO',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tipoTrabajoId = (int) DB::table('tipos_trabajo')->where('codigo', 'REP-TIP-AUTO')->value('id_tipo_trabajo');

        $response = $this->actingAs($gestorRepsol)->postJson(route('trabajos.store'), [
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'numero_trabajo' => 88001,
            'descripcion_trabajo' => 'Trabajo Repsol con número manual legacy',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_tipo_documento' => $tipoDocumentoId,
            'id_tipo_trabajo' => $tipoTrabajoId,
            'fecha_encargo' => '2026-05-03',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('trabajo.numero_trabajo', 88001)
            ->assertJsonPath('trabajo.numero_trabajo_operativo', 'REP-088001')
            ->assertJsonPath('trabajo.numero_trabajo_visible', 'REP-088001');

        $this->assertDatabaseHas('trabajos', [
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'numero_trabajo' => 88001,
            'numero_trabajo_operativo' => 'REP-088001',
        ]);
    }

    public function test_patch_field_updates_only_requested_cell_and_logs_audit()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'estado' => 'en_curso',
            'descripcion_trabajo' => 'Descripcion estable',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'estado',
            'valor' => 'terminado',
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'campo' => 'estado',
                'valor' => 'terminado',
            ]);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'terminado',
            'descripcion_trabajo' => 'Descripcion estable',
            'fecha_terminacion' => now()->toDateString(),
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'trabajos',
            'registro_id' => $trabajo->id_trabajo,
            'campo' => 'estado',
            'accion' => 'cambiar_estado',
        ]);
    }

    public function test_patch_field_rejects_station_master_edit_without_station_permission()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'poblacion' => 'Plasencia',
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'estado' => 'en_curso',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'municipio',
            'valor' => 'Caceres',
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'No tienes permiso para editar datos maestros de estación desde esta vista.',
            ]);

        $this->assertDatabaseHas('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'poblacion' => 'Plasencia',
        ]);
    }

    public function test_patch_field_updates_station_fk_from_excel_selector()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $estacionInicial = EstacionServicio::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);
        $estacionNueva = EstacionServicio::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_estacion' => 'MOE-FK-02',
            'nombre' => 'Estacion selector',
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacionInicial->id_estacion_servicio,
            'estado' => 'en_curso',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'id_estacion_servicio',
            'valor' => $estacionNueva->id_estacion_servicio,
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'campo' => 'id_estacion_servicio',
                'valor' => $estacionNueva->id_estacion_servicio,
            ]);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_estacion_servicio' => $estacionNueva->id_estacion_servicio,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'trabajos',
            'registro_id' => $trabajo->id_trabajo,
            'campo' => 'id_estacion_servicio',
            'accion' => 'actualizar',
        ]);
    }

    public function test_patch_field_reassigns_selected_pedido_to_trabajo_with_context_guard()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajoOrigen = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);
        $trabajoDestino = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);
        $pedido = Pedido::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoOrigen->id_trabajo,
            'numero_pedido' => 'PED-SEL-001',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajoDestino), [
            'campo' => 'id_pedido_principal',
            'valor' => $pedido->id_pedido,
            'updated_at' => $trabajoDestino->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'campo' => 'id_pedido_principal',
                'valor' => $pedido->id_pedido,
            ]);

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedido->id_pedido,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_trabajo' => $trabajoDestino->id_trabajo,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'pedidos',
            'registro_id' => $pedido->id_pedido,
            'campo' => 'id_trabajo',
            'accion' => 'actualizar',
        ]);
    }

    public function test_patch_field_rejects_pedido_from_another_context()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
        ]);
        $trabajoRepsol = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
        ]);
        $pedidoRepsol = Pedido::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
            'numero_pedido' => 'PED-REPSOL-001',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajoMoeve), [
            'campo' => 'id_pedido_principal',
            'valor' => $pedidoRepsol->id_pedido,
            'updated_at' => $trabajoMoeve->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseHas('pedidos', [
            'id_pedido' => $pedidoRepsol->id_pedido,
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_trabajo' => $trabajoRepsol->id_trabajo,
        ]);
    }

    public function test_patch_field_updates_repsol_work_type_from_catalog()
    {
        $gestorRepsol = $this->createUserWithContext('ejecucion_repsol', $this->ctxRepsol);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $tipoDocumento = TipoDocumento::create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'codigo' => 'REP-DOC',
            'nombre' => 'Documento REPSOL',
            'activo' => true,
        ]);
        $tipoTrabajo = TipoTrabajo::create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_tipo_documento' => $tipoDocumento->id_tipo_documento,
            'codigo' => 'REP-TIP',
            'nombre' => 'Tipo REPSOL',
            'activo' => true,
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_tipo_documento' => $tipoDocumento->id_tipo_documento,
        ]);

        $response = $this->actingAs($gestorRepsol)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'id_tipo_trabajo',
            'valor' => $tipoTrabajo->id_tipo_trabajo,
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'campo' => 'id_tipo_trabajo',
                'valor' => $tipoTrabajo->id_tipo_trabajo,
            ]);

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_tipo_documento' => $tipoDocumento->id_tipo_documento,
            'id_tipo_trabajo' => $tipoTrabajo->id_tipo_trabajo,
        ]);
    }

    public function test_patch_field_returns_conflict_when_updated_at_is_stale_and_modified_recently_by_other()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $otroUsuario = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'observaciones' => 'Valor inicial',
        ]);
        $oldTs = $trabajo->updated_at?->format('Y-m-d H:i:s');

        // addSecond garantiza timestamp distinto al de la factory aunque corran en el mismo segundo
        $serverNow = now()->addSecond();
        DB::table('trabajos')->where('id_trabajo', $trabajo->id_trabajo)->update([
            'observaciones' => 'Valor servidor',
            'updated_at' => $serverNow->format('Y-m-d H:i:s'),
        ]);

        // Audit log reciente por otro usuario → debe bloquear con 409
        AuditLog::create([
            'id_usuario'  => $otroUsuario->id_usuario,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'accion'      => 'actualizar',
            'modulo'      => 'trabajos',
            'tabla'       => 'trabajos',
            'registro_id' => $trabajo->id_trabajo,
            'campo'       => 'observaciones',
            'valor_anterior' => 'Valor inicial',
            'valor_nuevo'    => 'Valor servidor',
            'created_at'  => $serverNow,
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'observaciones',
            'valor' => 'Valor pestaña antigua',
            'updated_at' => $oldTs,
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'conflict' => true,
                'modificado_recientemente' => true,
                'campo' => 'observaciones',
                'valor_actual' => 'Valor servidor',
                'valor_intentado' => 'Valor pestaña antigua',
            ]);
    }

    public function test_patch_field_saves_directly_when_timestamp_stale_but_old_modification()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $otroUsuario = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'observaciones' => 'Valor inicial',
        ]);
        $oldTs = $trabajo->updated_at?->format('Y-m-d H:i:s');

        DB::table('trabajos')->where('id_trabajo', $trabajo->id_trabajo)->update([
            'observaciones' => 'Valor servidor',
            'updated_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        ]);

        // Audit log antiguo (> 1 hora) por otro usuario → debe dejar pasar
        AuditLog::create([
            'id_usuario'  => $otroUsuario->id_usuario,
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'accion'      => 'actualizar',
            'modulo'      => 'trabajos',
            'tabla'       => 'trabajos',
            'registro_id' => $trabajo->id_trabajo,
            'campo'       => 'observaciones',
            'valor_anterior' => 'Valor inicial',
            'valor_nuevo'    => 'Valor servidor',
            'created_at'  => now()->subHours(2),
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'observaciones',
            'valor' => 'Valor guardado sin conflicto',
            'updated_at' => $oldTs,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'observaciones' => 'Valor guardado sin conflicto',
        ]);
    }

    public function test_legacy_all_session_is_normalized_to_real_context_for_web_forms()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        DB::table('usuario_contextos')->insert([
            'id_usuario' => $gestorMoeve->id_usuario,
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'es_contexto_principal' => false,
            'activo' => true,
            'created_at' => now(),
        ]);

        $session = [User::ACTIVE_CONTEXT_SESSION_KEY => User::ACTIVE_CONTEXT_ALL];

        $this->actingAs($gestorMoeve)
            ->withSession($session)
            ->get('/trabajos/crear')
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Trabajos/Form')
                    ->where('auth.user.active_context.is_all', false)
                    ->where('auth.user.active_context.value', $this->ctxMoeve->id_contexto)
            );
    }

    public function test_normal_user_cannot_update_finalized_job_even_when_closed_flag_is_false()
    {
        $gestorRepsol = $this->createUserWithContext('ejecucion_repsol', $this->ctxRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'estado' => 'finalizado',
        ]);

        $response = $this->actingAs($gestorRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Intento de hackeo',
        ]);

        // Verificamos el abort(403) puesto en el TrabajoController
        $response->assertStatus(403);
    }

    public function test_trabajo_permission_only_treats_finalizado_as_protected(): void
    {
        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoTerminado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'estado' => 'terminado',
        ]);
        $trabajoFinalizado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'estado' => 'finalizado',
        ]);

        $this->assertFalse(TrabajoPermission::isClosed($trabajoTerminado));
        $this->assertTrue(TrabajoPermission::isClosed($trabajoFinalizado));
    }

    public function test_director_can_update_finalized_job()
    {
        $directorRepsol = $this->createUserWithContext('director', $this->ctxRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'estado' => 'finalizado',
        ]);

        $response = $this->actingAs($directorRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Modificación autorizada por dirección',
            'estado' => 'finalizado',
        ]);

        // Redirección exitosa tras actualizar
        $response->assertRedirect(route('trabajos.index'));
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajoCerrado->id_trabajo,
            'descripcion_trabajo' => 'Modificación autorizada por dirección',
        ]);
    }

    public function test_director_destroy_cancels_work_without_physical_delete_and_logs_audit()
    {
        $directorMoeve = $this->createUserWithContext('director', $this->ctxMoeve);
        $empresaMoeve = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresaMoeve->id_empresa,
            'estado' => 'en_curso',
        ]);

        $this->actingAs($directorMoeve)
            ->deleteJson(route('api.trabajos.destroy', $trabajo))
            ->assertOk()
            ->assertJsonPath('trabajo.estado', 'cancelado');

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'cancelado',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'trabajos',
            'registro_id' => $trabajo->id_trabajo,
            'accion' => 'cambiar_estado',
            'campo' => 'estado',
        ]);
    }

    private function createTrabajoBillingScenario(Empresa $empresa, EstacionServicio $estacion, array $trabajoOverrides = [], array $pedidoOverrides = []): array
    {
        $contractCode = 'CTR-STATE-' . uniqid();
        $contratoId = (int) DB::table('contratos')->insertGetId([
            'id_contexto' => $estacion->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'codigo_contrato' => $contractCode,
            'nombre' => 'Contrato estados ' . uniqid(),
            'tipo' => 'marco',
            'estado' => 'vigente',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tarifarioId = (int) DB::table('tarifarios')->insertGetId([
            'id_contexto' => $estacion->id_contexto,
            'id_contrato' => $contratoId,
            'nombre' => 'Tarifario estados ' . uniqid(),
            'version' => '2026',
            'factor_multiplicador' => 1,
            'moneda' => 'EUR',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $trabajo = Trabajo::factory()->create(array_merge([
            'id_contexto' => $estacion->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'id_contrato' => $contratoId,
            'id_tarifario' => $tarifarioId,
            'estado' => 'terminado',
            'fecha_terminacion' => '2026-05-20',
        ], $trabajoOverrides));

        $pedido = Pedido::create(array_merge([
            'id_contexto' => $estacion->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_tarifario' => $tarifarioId,
            'numero_pedido' => 'PED-STATE-' . $trabajo->id_trabajo,
            'fecha_solicitud' => '2026-05-20',
            'importe_pedido' => 100,
            'importe_solicitado' => 100,
            'importe_facturado' => 0,
            'unidades_pedido' => 1,
            'unidades_solicitadas' => 1,
            'estado' => 'recibido',
            'pedido_completo' => true,
            'tiene_mas_de_1_item' => false,
            'facturado_completo' => false,
        ], $pedidoOverrides));

        return [
            'trabajo' => $trabajo,
            'pedido' => $pedido,
            'id_contrato' => $contratoId,
            'id_tarifario' => $tarifarioId,
        ];
    }

    private function attachInvoiceToPedido(Trabajo $trabajo, Pedido $pedido, float $lineTotal, float $billedAmount): void
    {
        $pedidoItem = PedidoItem::create([
            'id_contexto' => $pedido->id_contexto,
            'id_pedido' => $pedido->id_pedido,
            'codigo_servicio' => 'SERV-STATE-' . $pedido->id_pedido,
            'descripcion_servicio' => 'Linea de facturacion de estado',
            'precio_unitario' => $lineTotal,
            'cantidad' => 1,
            'total_linea' => $lineTotal,
        ]);

        $factura = Factura::factory()->emitida()->create([
            'id_contexto' => $pedido->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $trabajo->id_empresa_cliente,
            'id_contrato' => $trabajo->id_contrato,
            'numero_factura' => 'FAC-STATE-' . $pedido->id_pedido . '-' . random_int(100, 999),
            'numero_factura_ccp' => 'CCP-STATE-' . $pedido->id_pedido . '-' . random_int(100, 999),
            'orden_factura' => 1,
            'base_imponible' => $billedAmount,
            'importe' => $billedAmount,
            'total' => $billedAmount,
        ]);

        FacturaItem::create([
            'id_factura' => $factura->id_factura,
            'id_pedido_item' => $pedidoItem->id_pedido_item,
            'unidades_facturadas' => 1,
            'importe_facturado' => $billedAmount,
        ]);

        app(TrabajoStateService::class)->syncPedidosAndTrabajosByPedidoIds([$pedido->id_pedido]);
    }
}
