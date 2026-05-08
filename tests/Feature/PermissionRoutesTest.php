<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\EstacionServicio;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Trabajo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PermissionRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\ContextosClienteSeeder::class);
    }

    public function test_trabajos_ver_does_not_allow_mutations(): void
    {
        $user = $this->createUserWithPermissions(['trabajos.ver']);
        $trabajo = $this->createTrabajoForUser($user);

        $this->actingAs($user)
            ->getJson("/api/v1/trabajos/{$trabajo->id_trabajo}")
            ->assertOk();

        $this->actingAs($user)
            ->postJson('/api/v1/trabajos', [])
            ->assertForbidden();

        $this->actingAs($user)
            ->patchJson("/api/v1/trabajos/{$trabajo->id_trabajo}", [
                'descripcion_trabajo' => 'No debe editar',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->deleteJson("/api/v1/trabajos/{$trabajo->id_trabajo}")
            ->assertForbidden();
    }

    public function test_specific_trabajos_edit_permission_allows_update_without_create(): void
    {
        $user = $this->createUserWithPermissions(['trabajos.ver', 'trabajos.editar']);
        $trabajo = $this->createTrabajoForUser($user);

        $this->actingAs($user)
            ->postJson('/api/v1/trabajos', [])
            ->assertForbidden();

        $this->actingAs($user)
            ->patchJson("/api/v1/trabajos/{$trabajo->id_trabajo}", [
                'descripcion_trabajo' => 'Editado por permiso especifico',
            ])
            ->assertRedirect(route('trabajos.index'));

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'descripcion_trabajo' => 'Editado por permiso especifico',
        ]);
    }

    public function test_view_permissions_do_not_allow_pedidos_facturas_or_estaciones_creation(): void
    {
        $user = $this->createUserWithPermissions([
            'pedidos.ver',
            'facturas.ver',
            'estaciones.ver',
            'clientes.ver',
        ]);

        $this->actingAs($user)->postJson('/api/v1/pedidos', [])->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/facturas', [])->assertForbidden();
        $this->actingAs($user)->get('/api/v1/facturas/export')->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/estaciones', [])->assertForbidden();
        $this->actingAs($user)->postJson('/api/v1/clientes', [])->assertForbidden();
    }

    public function test_delete_trabajo_cancels_instead_of_hard_delete(): void
    {
        $user = $this->createUserWithPermissions(['trabajos.ver', 'trabajos.eliminar']);
        $trabajo = $this->createTrabajoForUser($user);

        $this->actingAs($user)
            ->deleteJson("/api/v1/trabajos/{$trabajo->id_trabajo}")
            ->assertOk()
            ->assertJsonPath('trabajo.estado', 'cancelado');

        $this->assertDatabaseHas('trabajos', [
            'id_trabajo' => $trabajo->id_trabajo,
            'estado' => 'cancelado',
        ]);
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     */
    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $role = Role::query()->create([
            'nombre' => 'Rol permisos ' . Str::random(8),
            'slug' => 'rol-permisos-' . Str::random(12),
            'activo' => true,
        ]);

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => $slug,
                    'activo' => true,
                ],
            );

            $role->permissions()->syncWithoutDetaching([$permission->id_permiso]);
        }

        $user = User::factory()->create(['id_contexto' => 1]);
        $user->roles()->sync([$role->id_rol]);

        return $user;
    }

    private function createTrabajoForUser(User $user): Trabajo
    {
        $empresa = Empresa::factory()->create(['id_contexto' => $user->id_contexto]);
        $estacion = EstacionServicio::factory()->create([
            'id_contexto' => $user->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
        ]);

        return Trabajo::factory()->create([
            'id_contexto' => $user->id_contexto,
            'id_empresa_cliente' => $empresa->id_empresa,
            'id_estacion_servicio' => $estacion->id_estacion_servicio,
            'estado' => 'en_curso',
        ]);
    }
}
