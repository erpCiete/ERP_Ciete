<?php

namespace Tests\Feature\Api;
namespace App\Http\Requests\Api;

use App\Http\Requests\Api\StoreTrabajoRequest;
use App\Http\Requests\Api\UpdateTrabajoRequest;
use App\Models\ContextoCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrabajoRequestTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Route::post('/test/store-trabajo', function (StoreTrabajoRequest $request) {
            return response()->json([
                'ok' => true,
                'data' => $request->validated(),
            ]);
        });

        Route::patch('/test/update-trabajo', function (UpdateTrabajoRequest $request) {
            return response()->json([
                'ok' => true,
                'data' => $request->validated(),
            ]);
        });
    }

    public function test_store_trabajo_request_requires_cod_repsol_for_repsol_context(): void
    {
        $user = $this->makeUserWithContext('REPSOL');

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'titulo' => 'Trabajo prueba',
            'id_estacion_servicio' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_store_trabajo_request_requires_cod_cepsa_for_cepsa_context(): void
    {
        $user = $this->makeUserWithContext('CEPSA');

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'titulo' => 'Trabajo prueba',
            'id_estacion_servicio' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_store_trabajo_request_accepts_valid_repsol_payload(): void
    {
        $user = $this->makeUserWithContext('REPSOL');

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'titulo' => 'Trabajo prueba',
            'id_estacion_servicio' => 1,
            'cod_repsol' => 'REP-001',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.cod_repsol', 'REP-001');
    }

    public function test_update_trabajo_request_allows_partial_update_without_context_codes(): void
    {
        $user = $this->makeUserWithContext('REPSOL');

        $response = $this->actingAs($user)->patchJson('/test/update-trabajo', [
            'titulo' => 'Titulo actualizado',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.titulo', 'Titulo actualizado');
    }

    private function makeUserWithContext(string $codigo): User
    {
        $contexto = new ContextoCliente();
        $contexto->id_contexto = $codigo === 'REPSOL' ? 1 : 2;
        $contexto->codigo = $codigo;
        $contexto->nombre = $codigo;

        $user = new User();
        $user->id_usuario = $codigo === 'REPSOL' ? 101 : 102;
        $user->id_contexto = $contexto->id_contexto;
        $user->nombre = 'Test';
        $user->email = strtolower($codigo) . '@test.com';
        $user->setRelation('contexto', $contexto);

        return $user;
    }
}
