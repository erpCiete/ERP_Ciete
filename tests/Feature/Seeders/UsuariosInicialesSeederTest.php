<?php

namespace Tests\Feature\Seeders;

use App\Models\User;
use Database\Seeders\UsuariosInicialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuariosInicialesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuarios_iniciales_seeder_assigns_expected_role_permissions(): void
    {
        $this->seed(UsuariosInicialesSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $usuario = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->assertContains('admin', $admin->role_slugs);
        $this->assertContains('empresas_contactos.gestionar', $admin->permission_slugs);
        $this->assertContains('estaciones.gestionar', $admin->permission_slugs);

        $this->assertContains('control_cierre', $cesar->role_slugs);
        $this->assertContains('pedidos.ver', $cesar->permission_slugs);
        $this->assertContains('legalizaciones.ver', $cesar->permission_slugs);
        $this->assertContains('reportes.ver', $cesar->permission_slugs);
        $this->assertContains('empresas_contactos.gestionar', $cesar->permission_slugs);
        $this->assertContains('estaciones.gestionar', $cesar->permission_slugs);

        $this->assertContains('gestor', $usuario->role_slugs);
        $this->assertContains('empresas_contactos.gestionar', $usuario->permission_slugs);
        $this->assertContains('estaciones.gestionar', $usuario->permission_slugs);
    }
}
