<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DireccionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_direccion_cannot_access_direction_dashboard_but_keeps_management_and_closure_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)->get('/dashboard')->assertForbidden();
        $this->actingAs($director)->get('/soporte')->assertOk();
        $this->actingAs($director)->get('/cierre')->assertOk();
        $this->actingAs($director)->get('/maestros')->assertOk();
        $this->actingAs($director)->get('/admin/usuarios')->assertOk();
        $this->actingAs($director)->get('/admin/auditoria')->assertOk();
        $this->actingAs($director)->get('/admin')->assertForbidden();
    }

    public function test_execution_and_contable_cannot_access_direction_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();
        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        foreach ([$ejecucion, $contable] as $user) {
            $this->actingAs($user)->get('/dashboard')->assertForbidden();
            $this->actingAs($user)->get('/soporte')->assertOk();
            $this->actingAs($user)->get('/cierre')->assertForbidden();
            $this->actingAs($user)->get('/maestros')->assertForbidden();
        }
    }
}
