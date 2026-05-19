<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExcelModeAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_contable_keeps_same_access_rules_in_modern_and_excel_modes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $contable = User::query()->where('email', 'contable@ciete.es')->firstOrFail();

        foreach (['ciete_moderno', 'ciete_excel'] as $mode) {
            $contable->forceFill(['interface_mode' => $mode])->save();

            $this->actingAs($contable)->get('/pedidos')->assertOk();
            $this->actingAs($contable)->get('/facturas')->assertOk();
            $this->actingAs($contable)->get('/trabajos')->assertForbidden();
            $this->actingAs($contable)->get('/cierre')->assertForbidden();
            $this->actingAs($contable)->get('/admin')->assertForbidden();
        }
    }

    public function test_execution_keeps_same_access_rules_in_modern_and_excel_modes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $ejecucion = User::query()->where('email', 'usuario@ciete.es')->firstOrFail();

        foreach (['ciete_moderno', 'ciete_excel'] as $mode) {
            $ejecucion->forceFill(['interface_mode' => $mode])->save();

            $this->actingAs($ejecucion)->get('/trabajos')->assertOk();
            $this->actingAs($ejecucion)->get('/pedidos')->assertOk();
            $this->actingAs($ejecucion)->get('/estaciones')->assertOk();
            $this->actingAs($ejecucion)->get('/facturas')->assertForbidden();
            $this->actingAs($ejecucion)->get('/cierre')->assertForbidden();
            $this->actingAs($ejecucion)->get('/admin')->assertForbidden();
        }
    }

    public function test_admin_keeps_read_only_operational_access_in_modern_and_excel_modes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        foreach (['ciete_moderno', 'ciete_excel'] as $mode) {
            $admin->forceFill(['interface_mode' => $mode])->save();

            $this->actingAs($admin)->get('/trabajos')->assertOk();
            $this->actingAs($admin)->get('/pedidos')
                ->assertInertia(fn(Assert $page) => $page->where('canCreate', false));
            $this->actingAs($admin)->get('/facturas')
                ->assertInertia(fn(Assert $page) => $page->where('canCreate', false));
            $this->actingAs($admin)->get('/trabajos/crear')->assertForbidden();
            $this->actingAs($admin)->get('/pedidos/crear')->assertForbidden();
            $this->actingAs($admin)->get('/facturas/crear')->assertForbidden();
        }
    }

    public function test_excel_mode_never_exposes_todos_as_context_option(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();
        $director->forceFill(['interface_mode' => 'ciete_excel'])->save();

        $this->actingAs($director)
            ->get('/pedidos')
            ->assertInertia(
                fn(Assert $page) => $page
                    ->where('auth.user.available_contexts', fn($contexts) => collect($contexts)->every(
                        fn($context) => ($context['workspace_key'] ?? null) !== 'todos' && (string) ($context['value'] ?? '') !== 'all'
                    ))
            );
    }
}
