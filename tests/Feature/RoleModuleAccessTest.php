<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsuariosInicialesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_modules_are_available_to_admin_control_cierre_and_gestor(): void
    {
        $this->seed(UsuariosInicialesSeeder::class);

        $users = [
            User::query()->where('email', 'admin@ciete.es')->firstOrFail(),
            User::query()->where('email', 'cesar@ciete.es')->firstOrFail(),
            User::query()->where('email', 'usuario@ciete.es')->firstOrFail(),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->get('/proyectos')
                ->assertOk();

            $this->actingAs($user)
                ->get('/clientes')
                ->assertOk();

            $this->actingAs($user)
                ->get('/estaciones')
                ->assertOk();
        }
    }

    public function test_cierre_panel_is_only_available_to_control_cierre(): void
    {
        $this->seed(UsuariosInicialesSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $usuario = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($cesar)
            ->get('/cierre')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/cierre')
            ->assertForbidden();

        $this->actingAs($usuario)
            ->get('/cierre')
            ->assertForbidden();
    }

    public function test_admin_panel_is_only_available_to_admin(): void
    {
        $this->seed(UsuariosInicialesSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $cesar = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $usuario = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($cesar)
            ->get('/admin')
            ->assertForbidden();

        $this->actingAs($usuario)
            ->get('/admin')
            ->assertForbidden();
    }
}
