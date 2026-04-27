<?php

namespace Tests\Feature\Api;

use App\Http\Requests\Api\StoreTrabajoRequest;
use App\Http\Requests\Api\UpdateTrabajoRequest;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\User;
use Database\Seeders\ContextosClienteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrabajoRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ContextosClienteSeeder::class);

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
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-REP-001',
            'descripcion_trabajo' => 'Trabajo prueba Repsol',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'borrador',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['id_tipo_documento', 'id_tipo_trabajo']);
    }

    public function test_store_trabajo_request_requires_cod_cepsa_for_cepsa_context(): void
    {
        $user = User::factory()->create(['id_contexto' => 1]);
        $estacion = $this->createStationForContext(1);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-MOV-001',
            'descripcion_trabajo' => 'Trabajo prueba Moeve',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'borrador',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['id_contrato']);
    }

    public function test_store_trabajo_request_accepts_valid_repsol_payload(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-REP-OK-001',
            'descripcion_trabajo' => 'Trabajo válido Repsol',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'borrador',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.numero_trabajo', 'TR-REP-OK-001');
    }

    public function test_update_trabajo_request_allows_partial_update_without_context_codes(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);

        $response = $this->actingAs($user)->patchJson('/test/update-trabajo', [
            'descripcion_trabajo' => 'Titulo actualizado',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.descripcion_trabajo', 'Titulo actualizado');
    }

    private function createStationForContext(int $contextId): EstacionServicio
    {
        $empresa = Empresa::factory()->create(['id_contexto' => $contextId]);

        return EstacionServicio::factory()->create([
            'id_contexto' => $contextId,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);
    }
}
