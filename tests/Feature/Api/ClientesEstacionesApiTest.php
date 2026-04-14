<?php

namespace Tests\Feature\Api;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientesEstacionesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_clientes_or_estaciones_endpoints(): void
    {
        $this->getJson('/api/v1/clientes')->assertStatus(401);
        $this->getJson('/api/v1/estaciones')->assertStatus(401);
    }

    public function test_user_without_clientes_permission_cannot_access_clientes_endpoints(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/clientes')
            ->assertForbidden();
    }

    public function test_user_with_clientes_permission_can_manage_clientes_within_context(): void
    {
        $user = $this->createUserWithPermissions(['empresas_contactos.gestionar']);
        $clienteVisible = $this->createCliente($user, [
            'nombre' => 'Cliente Visible',
            'nombre_comercial' => 'Repsol Sur',
            'cif' => $this->makeValidNif(12345678),
        ]);

        $otherContext = ContextoCliente::query()->create([
            'nombre' => 'Contexto externo',
            'codigo' => 'CTX-EXT',
            'descripcion' => 'Contexto de prueba externo',
            'activo' => true,
        ]);

        Empresa::query()->create([
            'id_contexto' => $otherContext->id_contexto,
            'nombre' => 'Cliente Oculto',
            'nombre_comercial' => 'Moeve Norte',
            'cif' => $this->makeValidNif(12345679),
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/clientes')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $clienteVisible->id_empresa])
            ->assertJsonMissing(['nombre' => 'Cliente Oculto']);

        $createPayload = [
            'nombre' => 'Cliente Nuevo',
            'nombre_comercial' => 'Galp Centro',
            'razon_social' => 'Cliente Nuevo SL',
            'cif' => $this->makeValidNif(12345680),
            'web' => 'https://cliente-nuevo.test',
            'observaciones' => 'Alta desde test',
            'activo' => true,
        ];

        $this->actingAs($user)
            ->postJson('/api/v1/clientes', $createPayload)
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'Cliente Nuevo');

        $clienteNuevo = Empresa::query()->where('nombre', 'Cliente Nuevo')->firstOrFail();

        $this->assertSame($user->id_contexto, $clienteNuevo->id_contexto);

        $this->actingAs($user)
            ->putJson("/api/v1/clientes/{$clienteNuevo->id_empresa}", [
                'nombre_comercial' => 'Galp Centro Actualizado',
                'activo' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.nombre_comercial', 'Galp Centro Actualizado')
            ->assertJsonPath('data.activo', false);

        $this->actingAs($user)
            ->deleteJson("/api/v1/clientes/{$clienteNuevo->id_empresa}")
            ->assertOk();

        $this->assertDatabaseMissing('empresas', [
            'id_empresa' => $clienteNuevo->id_empresa,
        ]);
    }

    public function test_user_with_clientes_permission_rejects_invalid_tax_id_format(): void
    {
        $user = $this->createUserWithPermissions(['empresas_contactos.gestionar']);

        $this->actingAs($user)
            ->postJson('/api/v1/clientes', [
                'nombre' => 'Cliente Invalido',
                'nombre_comercial' => 'Repsol Centro',
                'razon_social' => 'Cliente Invalido SL',
                'cif' => '12345678A',
                'activo' => true,
            ])
            ->assertStatus(422)
            ->assertJsonPath('errors.cif.0', 'Introduce un identificador fiscal espanol valido (NIF, NIE o CIF).');
    }

    public function test_user_with_estaciones_view_permission_can_list_but_not_modify_estaciones(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.ver']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Estaciones',
            'nombre_comercial' => 'Repsol Test',
            'cif' => $this->makeValidNif(22345678),
        ]);
        $estacion = $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion Visible',
            'codigo_estacion' => 'EST-001',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/estaciones')
            ->assertOk()
            ->assertJsonFragment(['id' => $estacion->id_estacion_servicio]);

        $this->actingAs($user)
            ->postJson('/api/v1/estaciones', [
                'id_empresa_cliente' => $cliente->id_empresa,
                'nombre' => 'Estacion Bloqueada',
            ])
            ->assertForbidden();
    }

    public function test_user_with_estaciones_manage_permission_can_manage_estaciones(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.gestionar']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Operador',
            'nombre_comercial' => 'Moeve Test',
            'cif' => $this->makeValidNif(32345678),
        ]);

        $createPayload = [
            'id_empresa_cliente' => $cliente->id_empresa,
            'nombre' => 'Estacion Nueva',
            'codigo_estacion' => 'EST-900',
            'direccion' => 'Calle Mayor 1',
            'codigo_postal' => '41001',
            'poblacion' => 'Sevilla',
            'provincia' => 'Sevilla',
            'activo' => true,
        ];

        $this->actingAs($user)
            ->postJson('/api/v1/estaciones', $createPayload)
            ->assertCreated()
            ->assertJsonPath('data.nombre', 'Estacion Nueva')
            ->assertJsonPath('data.empresa.id', $cliente->id_empresa);

        $estacion = EstacionServicio::query()->where('nombre', 'Estacion Nueva')->firstOrFail();

        $this->actingAs($user)
            ->putJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}", [
                'nombre' => 'Estacion Actualizada',
                'activo' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Estacion Actualizada')
            ->assertJsonPath('data.activo', false);

        $this->actingAs($user)
            ->deleteJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}")
            ->assertOk();

        $this->assertDatabaseMissing('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
        ]);
    }

    public function test_user_with_estaciones_manage_permission_accepts_flexible_station_codes_and_only_rejects_invalid_postal_code(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.gestionar']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Codigos',
            'nombre_comercial' => 'Moeve Codigos',
            'cif' => $this->makeValidNif(42345678),
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/estaciones', [
                'id_empresa_cliente' => $cliente->id_empresa,
                'nombre' => 'Estacion Invalida',
                'codigo_estacion' => 'EST-VALID',
                'codigo_postal' => '99999',
            ])
            ->assertStatus(422)
            ->assertJsonMissingPath('errors.codigo_estacion')
            ->assertJsonPath('errors.codigo_postal.0', 'Introduce un codigo postal espanol valido.');
    }

    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create();

        if ($permissionSlugs === []) {
            return $user;
        }

        $role = Role::query()->create([
            'nombre' => 'Rol test ' . Str::random(8),
            'slug' => 'rol-test-' . Str::random(12),
            'descripcion' => 'Rol de prueba para endpoints protegidos',
            'activo' => true,
        ]);

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => Str::headline(str_replace('.', ' ', $slug)),
                    'descripcion' => "Permiso {$slug} para pruebas",
                    'activo' => true,
                ]
            );

            $role->permissions()->syncWithoutDetaching([$permission->id_permiso]);
        }

        $user->roles()->syncWithoutDetaching([$role->id_rol]);

        return $user->fresh();
    }

    private function createCliente(User $user, array $attributes = []): Empresa
    {
        return Empresa::query()->create(array_merge([
            'id_contexto' => $user->id_contexto,
            'nombre' => 'Cliente ' . Str::random(6),
            'nombre_comercial' => 'Operador ' . Str::random(6),
            'cif' => $this->makeValidNif(random_int(1, 99999999)),
            'tipo_empresa' => 'cliente',
            'activo' => true,
        ], $attributes));
    }

    private function createEstacion(User $user, Empresa $cliente, array $attributes = []): EstacionServicio
    {
        return EstacionServicio::query()->create(array_merge([
            'id_contexto' => $user->id_contexto,
            'id_empresa_cliente' => $cliente->id_empresa,
            'nombre' => 'Estacion ' . Str::random(6),
            'codigo_estacion' => 'EST-' . random_int(100, 99999),
            'codigo_postal' => '41001',
            'direccion' => 'Direccion ' . Str::random(6),
            'poblacion' => 'Madrid',
            'provincia' => 'Madrid',
            'activo' => true,
        ], $attributes));
    }

    private function makeValidNif(int $number): string
    {
        $digits = str_pad((string) ($number % 100000000), 8, '0', STR_PAD_LEFT);
        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $digits . $letters[((int) $digits) % 23];
    }
}
