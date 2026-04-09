<?php

namespace Tests\Feature\Api;

use App\Models\ContextoCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_index_devuelve_solo_clientes_del_contexto_autenticado(): void
    {
        $contextoA = ContextoCliente::create([
            'nombre' => 'CEPSA',
            'codigo' => 'CEPSA',
            'descripcion' => 'Contexto CEPSA',
            'activo' => true,
        ]);

        $contextoB = ContextoCliente::create([
            'nombre' => 'REPSOL',
            'codigo' => 'REPSOL',
            'descripcion' => 'Contexto REPSOL',
            'activo' => true,
        ]);

        $user = User::factory()->create([
            'id_contexto' => $contextoA->id_contexto,
        ]);

        DB::table('empresas')->insert([
            [
                'id_contexto' => $contextoA->id_contexto,
                'nombre' => 'Cliente CEPSA',
                'nombre_comercial' => null,
                'razon_social' => 'Cliente CEPSA SL',
                'cif' => 'A11111111',
                'tipo_empresa' => 'cliente',
                'web' => null,
                'observaciones' => null,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_contexto' => $contextoB->id_contexto,
                'nombre' => 'Cliente REPSOL',
                'nombre_comercial' => null,
                'razon_social' => 'Cliente REPSOL SL',
                'cif' => 'B22222222',
                'tipo_empresa' => 'cliente',
                'web' => null,
                'observaciones' => null,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($user, 'web')
            ->getJson('/api/v1/clientes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nombre' => 'Cliente CEPSA'])
            ->assertJsonMissing(['nombre' => 'Cliente REPSOL']);
    }

    public function test_cliente_store_crea_un_cliente_en_el_contexto_del_usuario(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'OTRO',
            'codigo' => 'OTRO',
            'descripcion' => 'Contexto OTRO',
            'activo' => true,
        ]);

        $user = User::factory()->create([
            'id_contexto' => $contexto->id_contexto,
        ]);

        $payload = [
            'nombre' => 'Nuevo Cliente',
            'nombre_comercial' => 'Nuevo Cliente Comercial',
            'razon_social' => 'Nuevo Cliente SL',
            'cif' => 'C33333333',
            'tipo_empresa' => 'cliente',
            'web' => 'https://cliente.test',
            'observaciones' => 'Alta desde test',
            'activo' => true,
        ];

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/clientes', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre', 'Nuevo Cliente');

        $this->assertDatabaseHas('empresas', [
            'nombre' => 'Nuevo Cliente',
            'id_contexto' => $contexto->id_contexto,
            'tipo_empresa' => 'cliente',
        ]);
    }

    public function test_cliente_store_devuelve_422_si_falta_nombre(): void
    {
        $contexto = ContextoCliente::create([
            'nombre' => 'OTRO2',
            'codigo' => 'OTRO2',
            'descripcion' => 'Contexto OTRO2',
            'activo' => true,
        ]);

        $user = User::factory()->create([
            'id_contexto' => $contexto->id_contexto,
        ]);

        $payload = [
            'nombre_comercial' => 'Sin nombre',
            'razon_social' => 'Cliente sin nombre SL',
            'cif' => 'D44444444',
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ];

        $response = $this->actingAs($user, 'web')
            ->postJson('/api/v1/clientes', $payload);

        $response->assertStatus(422);
    }
}