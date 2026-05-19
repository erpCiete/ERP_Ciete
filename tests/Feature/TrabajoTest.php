<?php

namespace Tests\Feature;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Pedido;
use App\Models\TipoDocumento;
use App\Models\TipoTrabajo;
use App\Models\Trabajo;
use App\Models\User;
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

    public function test_patch_field_updates_station_cell_from_excel_view()
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
            ->assertOk()
            ->assertJson([
                'success' => true,
                'campo' => 'municipio',
                'valor' => 'Caceres',
            ]);

        $this->assertDatabaseHas('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'poblacion' => 'Caceres',
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'estaciones_servicio',
            'registro_id' => $estacion->id_estacion_servicio,
            'campo' => 'municipio',
            'accion' => 'actualizar',
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

    public function test_patch_field_returns_conflict_when_updated_at_is_stale()
    {
        $gestorMoeve = $this->createUserWithContext('ejecucion_moeve', $this->ctxMoeve);
        $empresa = Empresa::factory()->create(['id_contexto' => $this->ctxMoeve->id_contexto]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $this->ctxMoeve->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'observaciones' => 'Valor inicial',
        ]);

        DB::table('trabajos')->where('id_trabajo', $trabajo->id_trabajo)->update([
            'observaciones' => 'Valor servidor',
            'updated_at' => '2026-05-03 10:05:00',
        ]);

        $response = $this->actingAs($gestorMoeve)->patchJson(route('trabajos.patch-field', $trabajo), [
            'campo' => 'observaciones',
            'valor' => 'Valor pestaña antigua',
            'updated_at' => $trabajo->updated_at?->format('Y-m-d H:i:s'),
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'conflict' => true,
                'campo' => 'observaciones',
                'valor_actual' => 'Valor servidor',
                'valor_intentado' => 'Valor pestaña antigua',
                'updated_at_actual' => '2026-05-03 10:05:00',
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
}
