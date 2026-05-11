<?php

namespace Tests\Unit\Traits;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\Scopes\ContextScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_aware_models_register_the_context_scope(): void
    {
        $scopes = Empresa::query()->getModel()->getGlobalScopes();

        $this->assertContains(ContextScope::class, array_map(
            static fn(object $scope): string => $scope::class,
            array_values($scopes),
        ));
    }

    public function test_has_context_injects_authenticated_user_context_when_creating_models(): void
    {
        $contexto = ContextoCliente::factory()->create();
        $user = User::factory()->create([
            'id_contexto' => $contexto->id_contexto,
        ]);

        $this->actingAs($user);

        $empresa = Empresa::create([
            'nombre' => 'Empresa con contexto inyectado',
            'tipo_empresa' => 'cliente',
        ]);

        $this->assertSame($contexto->id_contexto, $empresa->id_contexto);
    }

    public function test_has_context_limits_queries_to_the_authenticated_user_context(): void
    {
        $contextoVisible = ContextoCliente::factory()->create();
        $contextoOculto = ContextoCliente::factory()->create();
        $user = User::factory()->create([
            'id_contexto' => $contextoVisible->id_contexto,
        ]);

        $empresaVisible = Empresa::withoutGlobalScopes()->create([
            'id_contexto' => $contextoVisible->id_contexto,
            'nombre' => 'Empresa visible',
            'tipo_empresa' => 'cliente',
        ]);
        Empresa::withoutGlobalScopes()->create([
            'id_contexto' => $contextoOculto->id_contexto,
            'nombre' => 'Empresa oculta',
            'tipo_empresa' => 'cliente',
        ]);

        $this->actingAs($user);

        $ids = Empresa::query()->pluck('id_empresa')->all();

        $this->assertSame([$empresaVisible->id_empresa], $ids);
    }

    public function test_user_with_multiple_contexts_sees_data_from_all_assigned_contexts(): void
    {
        $contexto1 = ContextoCliente::factory()->create();
        $contexto2 = ContextoCliente::factory()->create();
        $contexto3 = ContextoCliente::factory()->create();

        $user = User::factory()->create([
            'id_contexto' => $contexto1->id_contexto,
        ]);

        // Assign contexts 1 and 2 via pivot
        $user->contextos()->attach([
            $contexto1->id_contexto => ['es_contexto_principal' => true, 'activo' => true],
            $contexto2->id_contexto => ['es_contexto_principal' => false, 'activo' => true],
        ]);

        $empresa1 = Empresa::withoutGlobalScopes()->create([
            'id_contexto' => $contexto1->id_contexto,
            'nombre' => 'Empresa contexto 1',
            'tipo_empresa' => 'cliente',
        ]);
        $empresa2 = Empresa::withoutGlobalScopes()->create([
            'id_contexto' => $contexto2->id_contexto,
            'nombre' => 'Empresa contexto 2',
            'tipo_empresa' => 'cliente',
        ]);
        Empresa::withoutGlobalScopes()->create([
            'id_contexto' => $contexto3->id_contexto,
            'nombre' => 'Empresa contexto 3 oculta',
            'tipo_empresa' => 'cliente',
        ]);

        $this->actingAs($user);
        $user->setActiveContextSelection(User::ACTIVE_CONTEXT_ALL);

        $ids = Empresa::query()->pluck('id_empresa')->sort()->values()->all();

        $expected = collect([$empresa1->id_empresa, $empresa2->id_empresa])->sort()->values()->all();
        $this->assertSame($expected, $ids);
    }
}
