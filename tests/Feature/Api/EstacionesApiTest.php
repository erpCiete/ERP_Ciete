<?php

namespace Tests\Feature\Api;

use App\Models\Estacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstacionesApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_obtener_listado_de_estaciones()
    {
        $this->withoutMiddleware();

        Estacion::factory()->create([
            'nombre' => 'Estación Central',
            'codigo' => 'EST-001'
        ]);

        $response = $this->getJson('/api/v1/estaciones');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         '*' => ['id', 'nombre_estacion', 'identificador_interno']
                     ]
                 ]);
    }

    /** @test */
    public function falla_la_validacion_al_crear_sin_datos()
    {
        $this->withoutMiddleware();

        $response = $this->postJson('/api/v1/estaciones', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nombre', 'codigo', 'cliente_id']);
    }
}