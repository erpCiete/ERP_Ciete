<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExecutionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_execution_profiles_can_access_operational_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $users = [
            User::query()->where('email', 'usuario@ciete.es')->firstOrFail(),
            User::query()->where('email', 'moeve@ciete.es')->firstOrFail(),
            User::query()->where('email', 'repsol@ciete.es')->firstOrFail(),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)->get('/')->assertOk();
            $this->actingAs($user)->get('/profile')->assertOk();
            $this->actingAs($user)->get('/trabajos')->assertOk();
            $this->actingAs($user)->get('/pedidos')->assertOk();
            $this->actingAs($user)->get('/clientes')->assertOk();
            $this->actingAs($user)->get('/estaciones')->assertOk();
            $this->actingAs($user)->get('/mensajes')->assertOk();
            $this->actingAs($user)->get('/soporte')->assertOk();
            $this->actingAs($user)->get('/estado')->assertOk();
            $this->actingAs($user)->get('/ayuda')->assertOk();
        }
    }

    public function test_execution_profiles_cannot_access_admin_direction_or_billing_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $users = [
            User::query()->where('email', 'usuario@ciete.es')->firstOrFail(),
            User::query()->where('email', 'moeve@ciete.es')->firstOrFail(),
            User::query()->where('email', 'repsol@ciete.es')->firstOrFail(),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)->get('/admin')->assertForbidden();
            $this->actingAs($user)->get('/admin/usuarios')->assertForbidden();
            $this->actingAs($user)->get('/cierre')->assertForbidden();
            $this->actingAs($user)->get('/maestros')->assertForbidden();
            $this->actingAs($user)->get('/facturas')->assertForbidden();
            $this->actingAs($user)->get('/importaciones')->assertForbidden();
            $this->actingAs($user)->get('/dashboard')->assertForbidden();
        }
    }
}
