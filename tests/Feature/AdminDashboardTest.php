<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $role = Role::query()->create([
            'nombre' => 'admin',
            'slug' => 'admin',
            'descripcion' => 'Acceso total',
            'activo' => true,
        ]);

        DB::table('usuario_roles')->insert([
            'id_usuario' => $admin->id_usuario,
            'id_rol' => $role->id_rol,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }
}
