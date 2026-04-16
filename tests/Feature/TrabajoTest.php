<?php

namespace Tests\Feature;

use App\Models\Trabajo;
use App\Models\User;
use App\Models\Role;
use App\Models\ContextoCliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrabajoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup básico de roles y contextos simulados para la prueba
        $this->artisan('db:seed', ['--class' => 'RolesSeeder']); // Asumiendo que existe
    }

    private function createUserWithContext($roleName, $contextId)
    {
        $user = User::factory()->create(['id_contexto' => $contextId]);
        $user->assignRole($roleName); // Usando Spatie Permission o similar
        return $user;
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/trabajos');
        $response->assertRedirect('/login');
    }

    public function test_context_isolation_repsol_cannot_see_moeve_jobs()
    {
        // 1 = Moeve, 2 = Repsol
        $gestorRepsol = $this->createUserWithContext('gestor', 2);
        
        $trabajoMoeve = Trabajo::factory()->create([
            'id_contexto' => 1,
            'numero_trabajo' => 'MOEVE-001'
        ]);

        $this->actingAs($gestorRepsol);

        // Prueba de Listado (Index)
        $response = $this->get('/trabajos');
        $response->assertStatus(200);
        $response->assertDontSee('MOEVE-001'); 

        // Prueba de Edición directa (Debe dar 404 porque el ContextScope lo oculta a nivel SQL)
        $response = $this->get("/trabajos/{$trabajoMoeve->id_trabajo}/editar");
        $response->assertStatus(404);
    }

    public function test_validation_fails_if_moeve_job_misses_contrato()
    {
        $gestorMoeve = $this->createUserWithContext('gestor', 1); // Contexto 1 = Moeve

        $response = $this->actingAs($gestorMoeve)->post('/trabajos', [
            'numero_trabajo' => 999,
            'id_empresa_cliente' => 1,
            'estado' => 'borrador',
            // Omitimos intencionadamente el 'id_contrato'
        ]);

        $response->assertSessionHasErrors(['id_contrato']);
    }

    public function test_normal_user_cannot_update_closed_job()
    {
        $gestorRepsol = $this->createUserWithContext('gestor', 2);
        
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => 2,
            'cerrado' => true, // Estado bloqueado
            'estado' => 'cerrado'
        ]);

        $response = $this->actingAs($gestorRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Intento de hackeo',
        ]);

        // Verificamos el abort(403) puesto en el TrabajoController
        $response->assertStatus(403);
    }

    public function test_admin_can_update_closed_job()
    {
        $adminRepsol = $this->createUserWithContext('admin', 2);
        
        $trabajoCerrado = Trabajo::factory()->create([
            'id_contexto' => 2,
            'cerrado' => true,
        ]);

        $response = $this->actingAs($adminRepsol)->put("/trabajos/{$trabajoCerrado->id_trabajo}", [
            'descripcion_trabajo' => 'Modificación autorizada por admin',
            'estado' => 'cerrado' // Simulamos pasar validaciones básicas
        ]);

        // Redirección exitosa tras actualizar
        $response->assertRedirect(route('trabajos.index'));
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajoCerrado->id_trabajo,
            'descripcion_trabajo' => 'Modificación autorizada por admin'
        ]);
    }
}