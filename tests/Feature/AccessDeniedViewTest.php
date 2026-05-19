<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccessDeniedViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_access_renders_corporate_403_with_return_to_home_metadata(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($contable)
            ->get('/trabajos')
            ->assertStatus(403)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Error')
                ->where('status', 403)
                ->where('homeUrl', route('index'))
                ->where('accessDenied.message', 'No tienes permiso para acceder a este módulo.')
                ->where('accessDenied.backHomeLabel', 'Volver al inicio')
            );
    }
}
