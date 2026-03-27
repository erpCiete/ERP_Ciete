<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'nombre_usuario' => $user->nombre_usuario,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Login correcto',
            ]);

        $this->assertAuthenticated();
    }

    public function test_guest_cannot_access_me(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Usuario autenticado',
            ]);
    }

    public function test_authenticated_user_can_access_stub_modules(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/obras')->assertOk();
        $this->actingAs($user)->getJson('/api/v1/pedidos')->assertOk();
        $this->actingAs($user)->getJson('/api/v1/estaciones')->assertOk();
    }
}
