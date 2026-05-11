<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;
use Tests\TestCase;

/**
 * Acceso al modulo Estaciones por rol.
 *
 * Verifica que:
 *   - Admin puede acceder y gestionar estaciones.
 *   - El usuario execution_moeve puede ver la lista.
 *   - El usuario execution_moeve no puede crear estaciones.
 *   - Contabilidad no puede acceder a estaciones.
 *   - Un invitado es redirigido al login.
 */
class EstacionesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Evitar error de Vite manifest
        $this->app->instance(Vite::class, new class extends Vite {
            public function __invoke($entrypoints, $buildDirectory = null): HtmlString
            {
                return new HtmlString('');
            }
        });

        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/estaciones')->assertRedirect('/login');
    }

    public function test_admin_can_list_estaciones(): void
    {
        $admin = User::where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/estaciones')->assertOk();
    }

    public function test_admin_can_access_create_estacion(): void
    {
        $admin = User::where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)->get('/estaciones/crear')->assertOk();
    }

    public function test_execution_moeve_can_list_estaciones(): void
    {
        $user = User::where('email', 'moeve@ciete.es')->firstOrFail();

        // Execution roles can inspect stations but cannot manage them.
        $this->actingAs($user)->get('/estaciones')->assertOk();
    }

    public function test_execution_moeve_cannot_create_estacion(): void
    {
        $user = User::where('email', 'moeve@ciete.es')->firstOrFail();

        // Execution users do not have estaciones.crear/editar/eliminar permission.
        $this->actingAs($user)->get('/estaciones/crear')->assertForbidden();
    }

    public function test_accounting_user_cannot_access_estaciones(): void
    {
        $accounting = User::where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($accounting)->get('/estaciones')->assertForbidden();
    }
}
