<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContableAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_contable_can_access_billing_and_general_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($contable)->get('/')->assertOk();
        $this->actingAs($contable)->get('/profile')->assertOk();
        $this->actingAs($contable)->get('/pedidos')->assertOk();
        $this->actingAs($contable)->get('/facturas')->assertOk();
        $this->actingAs($contable)->get('/mensajes')->assertOk();
        $this->actingAs($contable)->get('/soporte')->assertOk();
        $this->actingAs($contable)->get('/estado')->assertOk();
        $this->actingAs($contable)->get('/ayuda')->assertOk();
    }

    public function test_contable_cannot_access_execution_admin_or_direction_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        $this->actingAs($contable)->get('/trabajos')->assertForbidden();
        $this->actingAs($contable)->get('/admin')->assertForbidden();
        $this->actingAs($contable)->get('/admin/usuarios')->assertForbidden();
        $this->actingAs($contable)->get('/cierre')->assertForbidden();
        $this->actingAs($contable)->get('/maestros')->assertForbidden();
        $this->actingAs($contable)->get('/clientes')->assertForbidden();
        $this->actingAs($contable)->get('/estaciones')->assertForbidden();
        $this->actingAs($contable)->get('/importaciones')->assertForbidden();
        $this->actingAs($contable)->get('/dashboard')->assertForbidden();
    }
}
