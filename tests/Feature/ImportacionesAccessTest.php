<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ImportacionesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_importaciones_index_even_if_excel_parser_is_not_available(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('importaciones.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('Importaciones/Index'));
    }

    public function test_director_cannot_access_importaciones_without_explicit_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $director = User::query()->where('email', 'cesar@ciete.es')->firstOrFail();

        $this->actingAs($director)
            ->get(route('importaciones.index'))
            ->assertForbidden();
    }

    public function test_non_admin_user_with_explicit_import_permission_can_access_importaciones(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = $this->createUserWithImportPermissions(['importaciones.ver']);

        $this->actingAs($user)
            ->get(route('importaciones.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('Importaciones/Index'));
    }

    public function test_importaciones_index_exposes_grouped_warning_summary(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->where('email', 'admin@ciete.es')->firstOrFail();
        $contextoId = $admin->id_contexto;

        $importId = DB::table('importaciones')->insertGetId([
            'id_contexto' => $contextoId,
            'id_usuario' => $admin->id_usuario,
            'tipo' => 'trabajos',
            'archivo_original' => '01 Control de Trabajos Moeve.xlsx',
            'total_filas' => 10,
            'filas_importadas' => 8,
            'filas_ignoradas' => 1,
            'filas_con_error' => 0,
            'filas_con_aviso' => 2,
            'filas_duplicadas' => 0,
            'estado' => 'completado',
            'version_importacion' => 13,
            'started_at' => now(),
            'finished_at' => now(),
            'resumen_json' => json_encode(['issues_by_code' => ['work_amount_without_order' => 1]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('importacion_filas')->insert([
            'id_importacion' => $importId,
            'archivo_origen' => '01 Control de Trabajos Moeve.xlsx',
            'hoja_origen' => 'Trabajos',
            'numero_fila' => 5,
            'datos_json' => json_encode(['columns' => ['TÉCNICO GESTIÓN']]),
            'estado' => 'pendiente',
            'resultado' => 'ignored',
            'tipo_fila' => 'work_amount_without_order',
            'severidad' => 'warning',
            'codigo' => 'work_amount_without_order',
            'clasificacion' => 'functional_decision',
            'mensaje_error' => 'Trabajo MOEVE con importe pero sin numero de pedido; no se crea pedido_item.',
            'decision_sugerida' => 'Confirmar criterio con CIETE.',
            'id_registro_destino' => null,
            'tipo_registro_destino' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('importaciones.index', ['detalle' => $importId]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Importaciones/Index')
                ->has('resumen')
                ->has('agrupaciones.avisosPorArchivo')
                ->has('detalleFilas.data', 1)
                ->where('detalleFilas.data.0.codigo', 'work_amount_without_order')
                ->where('detalleFilas.data.0.clasificacion', 'functional_decision'));
    }

    /**
     * @param  array<int, string>  $permissionSlugs
     */
    private function createUserWithImportPermissions(array $permissionSlugs): User
    {
        $role = Role::query()->create([
            'nombre' => 'Importaciones ' . now()->timestamp,
            'slug' => 'importaciones-' . now()->timestamp,
            'activo' => true,
        ]);

        foreach ($permissionSlugs as $slug) {
            $permission = Permission::query()->firstOrCreate(
                ['slug' => $slug],
                ['nombre' => $slug, 'activo' => true],
            );

            $role->permissions()->syncWithoutDetaching([$permission->id_permiso]);
        }

        $user = User::factory()->create(['id_contexto' => 1]);
        $user->roles()->sync([$role->id_rol]);
        $user->contextos()->sync([
            1 => ['es_contexto_principal' => true, 'activo' => true],
        ]);

        return $user;
    }
}
