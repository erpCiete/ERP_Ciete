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
            'estado' => 'en_curso',
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
            'estado' => 'en_curso',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['id_contrato']);
    }

    public function test_store_trabajo_request_accepts_valid_repsol_payload(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 91001,
            'descripcion_trabajo' => 'Trabajo válido Repsol',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'en_curso',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.numero_trabajo', 91001);
    }

    public function test_store_trabajo_request_accepts_valid_repsol_payload_without_work_number(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'descripcion_trabajo' => 'Trabajo válido Repsol autogenerado',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'en_curso',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_store_trabajo_request_rejects_textual_internal_work_number_when_other_required_fields_are_valid(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-REP-INT-001',
            'descripcion_trabajo' => 'Trabajo con número interno textual',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'en_curso',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['numero_trabajo']);
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

    public function test_store_trabajo_request_rejects_legacy_statuses_for_new_work(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-REP-LEG-001',
            'descripcion_trabajo' => 'Trabajo legacy no permitido',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'borrador',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['estado']);
    }

    public function test_store_trabajo_request_rejects_closed_status_for_new_work(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 'TR-REP-LEG-002',
            'descripcion_trabajo' => 'Trabajo cerrado no permitido',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'cerrado',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['estado']);
    }

    public function test_store_trabajo_request_rejects_derived_status_for_new_work(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);
        $estacion = $this->createStationForContext(2);

        $response = $this->actingAs($user)->postJson('/test/store-trabajo', [
            'numero_trabajo' => 91002,
            'descripcion_trabajo' => 'Trabajo con estado derivado no permitido',
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'fecha_encargo' => now()->toDateString(),
            'estado' => 'facturado',
            'id_tipo_documento' => 4,
            'id_tipo_trabajo' => 8,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['estado']);
    }

    public function test_update_trabajo_request_rejects_derived_status_in_full_form_flow(): void
    {
        $user = User::factory()->create(['id_contexto' => 2]);

        $response = $this->actingAs($user)->patchJson('/test/update-trabajo', [
            'estado' => 'finalizado',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['estado']);
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
