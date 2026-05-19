<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_unknown_web_route_redirects_to_login(): void
    {
        $this->get('/ruta-que-no-existe')
            ->assertRedirect('/login');
    }

    public function test_authenticated_unknown_web_route_returns_corporate_404_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/ruta-que-no-existe')
            ->assertStatus(404)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Error')
                ->where('status', 404)
                ->where('homeUrl', route('index')));
    }

    public function test_forbidden_web_route_returns_corporate_403_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/clientes')
            ->assertStatus(403)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Error')
                ->where('status', 403)
                ->where('homeUrl', route('index'))
                ->where('accessDenied.backHomeLabel', 'Volver al inicio'));
    }

    public function test_authenticated_error_preview_route_renders_requested_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/_preview/error/419')
            ->assertStatus(419)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Error')
                ->where('status', 419));
    }
}
