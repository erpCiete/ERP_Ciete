<?php

namespace Tests\Feature;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Trabajo;
use App\Models\User;
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
        $gestorRepsol = $this->createUserWithContext('gestor_repsol', $this->ctxRepsol);

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
        $gestorMoeve = $this->createUserWithContext('gestor_moeve', $this->ctxMoeve);

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
            'estado' => 'borrador',
            // Omitimos intencionadamente el 'id_contrato'
        ]);

        $response->assertSessionHasErrors(['id_contrato']);
    }

    public function test_normal_user_cannot_update_closed_job()
    {
        $gestorRepsol = $this->createUserWithContext('gestor_repsol', $this->ctxRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'cerrado' => true,
            'estado' => 'cerrado',
        ]);

        $response = $this->actingAs($gestorRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Intento de hackeo',
        ]);

        // Verificamos el abort(403) puesto en el TrabajoController
        $response->assertStatus(403);
    }

    public function test_admin_can_update_closed_job()
    {
        $adminRepsol = $this->createUserWithContext('admin', $this->ctxRepsol);

        $empresaRepsol = Empresa::factory()->create(['id_contexto' => $this->ctxRepsol->id_contexto]);
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => $this->ctxRepsol->id_contexto,
            'id_empresa_cliente' => $empresaRepsol->id_empresa,
            'cerrado' => true,
            'estado' => 'cerrado',
        ]);

        $response = $this->actingAs($adminRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Modificación autorizada por admin',
            'estado' => 'cerrado',
        ]);

        // Redirección exitosa tras actualizar
        $response->assertRedirect(route('trabajos.index'));
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajoCerrado->id_trabajo,
            'descripcion_trabajo' => 'Modificación autorizada por admin',
        ]);
    }
}
