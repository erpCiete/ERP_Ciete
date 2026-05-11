<?php

namespace Tests\Feature\Api;

use App\Models\ContextoCliente;
use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Factura;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientesEstacionesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
    }

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
        $user = $this->createUserWithPermissions(['clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar']);
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
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'empresas',
            'registro_id' => $clienteNuevo->id_empresa,
            'accion' => 'crear',
            'modulo' => 'clientes',
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/clientes/{$clienteNuevo->id_empresa}", [
                'nombre_comercial' => 'Galp Centro Actualizado',
                'activo' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.nombre_comercial', 'Galp Centro Actualizado')
            ->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'empresas',
            'registro_id' => $clienteNuevo->id_empresa,
            'accion' => 'cambiar_estado',
            'modulo' => 'clientes',
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/v1/clientes/{$clienteNuevo->id_empresa}")
            ->assertOk();

        $this->assertDatabaseHas('empresas', [
            'id_empresa' => $clienteNuevo->id_empresa,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'empresas',
            'registro_id' => $clienteNuevo->id_empresa,
            'accion' => 'desactivar',
        ]);
    }

    public function test_user_with_clientes_permission_rejects_invalid_tax_id_format(): void
    {
        $user = $this->createUserWithPermissions(['clientes.crear']);

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
        $user = $this->createUserWithPermissions(['estaciones.ver', 'estaciones.crear', 'estaciones.editar', 'estaciones.eliminar']);
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
            ->assertJsonPath('data.codigo_estacion', 'EST-900')
            ->assertJsonPath('data.municipio', 'Sevilla')
            ->assertJsonPath('data.provincia', 'Sevilla')
            ->assertJsonPath('data.empresa.id', $cliente->id_empresa);

        $estacion = EstacionServicio::query()->where('nombre', 'Estacion Nueva')->firstOrFail();
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'estaciones_servicio',
            'registro_id' => $estacion->id_estacion_servicio,
            'accion' => 'crear',
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}", [
                'nombre' => 'Estacion Actualizada',
                'activo' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Estacion Actualizada')
            ->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'estaciones_servicio',
            'registro_id' => $estacion->id_estacion_servicio,
            'accion' => 'cambiar_estado',
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}")
            ->assertOk();

        $this->assertDatabaseHas('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'estaciones_servicio',
            'registro_id' => $estacion->id_estacion_servicio,
            'accion' => 'desactivar',
        ]);
    }

    public function test_deactivating_estacion_preserves_related_trabajos(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.eliminar']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Historico',
            'nombre_comercial' => 'Moeve Historico',
            'cif' => $this->makeValidNif(52345678),
        ]);
        $estacion = $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion con trabajos',
            'codigo_estacion' => 'EST-HIST-01',
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $user->id_contexto,
            'id_empresa_cliente' => $cliente->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/v1/estaciones/{$estacion->id_estacion_servicio}")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('estaciones_servicio', [
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'estaciones_servicio',
            'registro_id' => $estacion->id_estacion_servicio,
            'accion' => 'desactivar',
        ]);
    }

    public function test_deactivating_cliente_preserves_historical_relations(): void
    {
        $user = $this->createUserWithPermissions(['clientes.eliminar']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente con historico',
            'nombre_comercial' => 'Operador Historico',
            'cif' => $this->makeValidNif(62345678),
        ]);
        $trabajo = Trabajo::factory()->create([
            'id_contexto' => $user->id_contexto,
            'id_empresa_cliente' => $cliente->id_empresa,
        ]);
        $factura = Factura::factory()->create([
            'id_contexto' => $user->id_contexto,
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $cliente->id_empresa,
            'id_empresa_facturadora' => $cliente->id_empresa,
            'orden_factura' => 1,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/v1/clientes/{$cliente->id_empresa}")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('empresas', [
            'id_empresa' => $cliente->id_empresa,
            'activo' => false,
        ]);
        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'id_empresa_cliente' => $cliente->id_empresa,
        ]);
        $this->assertDatabaseHas('facturas', [
            'id_factura' => $factura->id_factura,
            'id_empresa_cliente' => $cliente->id_empresa,
            'id_empresa_facturadora' => $cliente->id_empresa,
        ]);
        $this->assertDatabaseHas('audit_log', [
            'tabla' => 'empresas',
            'registro_id' => $cliente->id_empresa,
            'accion' => 'desactivar',
        ]);
    }

    public function test_user_with_estaciones_manage_permission_accepts_flexible_station_codes_and_only_rejects_invalid_postal_code(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.crear']);
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

    public function test_estaciones_index_supports_search_by_code_municipio_and_provincia(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.ver']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Busquedas',
            'nombre_comercial' => 'Moeve Busquedas',
            'cif' => $this->makeValidNif(72345678),
        ]);

        $codigo = $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion Codigo',
            'codigo_estacion' => 'BUS-001',
            'poblacion' => 'Sevilla',
            'provincia' => 'Sevilla',
        ]);
        $municipio = $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion Municipio',
            'codigo_estacion' => 'BUS-002',
            'poblacion' => 'Cordoba',
            'provincia' => 'Cordoba',
        ]);
        $provincia = $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion Provincia',
            'codigo_estacion' => 'BUS-003',
            'poblacion' => 'Jerez',
            'provincia' => 'Cadiz',
        ]);

        $this->actingAs($user)
            ->getJson('/api/v1/estaciones?codigo=BUS-001')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $codigo->id_estacion_servicio,
                'codigo_estacion' => 'BUS-001',
            ])
            ->assertJsonMissing(['id' => $municipio->id_estacion_servicio]);

        $this->actingAs($user)
            ->getJson('/api/v1/estaciones?municipio=Cordoba')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $municipio->id_estacion_servicio,
                'municipio' => 'Cordoba',
            ])
            ->assertJsonMissing(['id' => $codigo->id_estacion_servicio]);

        $this->actingAs($user)
            ->getJson('/api/v1/estaciones?provincia=Cadiz')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $provincia->id_estacion_servicio,
                'provincia' => 'Cadiz',
            ])
            ->assertJsonMissing(['id' => $municipio->id_estacion_servicio]);
    }

    public function test_station_code_must_be_unique_within_the_same_client_context(): void
    {
        $user = $this->createUserWithPermissions(['estaciones.crear']);
        $cliente = $this->createCliente($user, [
            'nombre' => 'Cliente Codigo Unico',
            'nombre_comercial' => 'Repsol Codigo Unico',
            'cif' => $this->makeValidNif(82345678),
        ]);

        $this->createEstacion($user, $cliente, [
            'nombre' => 'Estacion Base',
            'codigo_estacion' => 'DUP-001',
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/estaciones', [
                'id_empresa_cliente' => $cliente->id_empresa,
                'nombre' => 'Estacion Duplicada',
                'codigo_estacion' => 'DUP-001',
                'codigo_postal' => '41001',
                'poblacion' => 'Sevilla',
                'provincia' => 'Sevilla',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['codigo_estacion']);
    }

    public function test_station_code_can_repeat_in_different_contexts(): void
    {
        $userContext1 = $this->createUserWithPermissions(['estaciones.crear'], 1);
        $userContext2 = $this->createUserWithPermissions(['estaciones.crear'], 2);

        $clienteContext1 = $this->createCliente($userContext1, [
            'nombre' => 'Cliente Contexto 1',
            'nombre_comercial' => 'Moeve Contexto 1',
            'cif' => $this->makeValidNif(92345678),
        ]);
        $clienteContext2 = $this->createCliente($userContext2, [
            'nombre' => 'Cliente Contexto 2',
            'nombre_comercial' => 'Repsol Contexto 2',
            'cif' => $this->makeValidNif(92345679),
        ]);

        $payload = [
            'nombre' => 'Estacion Compartida',
            'codigo_estacion' => 'CTX-REPEAT',
            'codigo_postal' => '41001',
            'poblacion' => 'Sevilla',
            'provincia' => 'Sevilla',
            'activo' => true,
        ];

        $this->actingAs($userContext1)
            ->postJson('/api/v1/estaciones', array_merge($payload, [
                'id_empresa_cliente' => $clienteContext1->id_empresa,
            ]))
            ->assertCreated();

        $this->actingAs($userContext2)
            ->postJson('/api/v1/estaciones', array_merge($payload, [
                'id_empresa_cliente' => $clienteContext2->id_empresa,
            ]))
            ->assertCreated();
    }

    private function createUserWithPermissions(array $permissionSlugs, int $contextId = 1): User
    {
        $user = User::factory()->create(['id_contexto' => $contextId]);

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
