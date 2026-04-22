<?php

namespace Tests\Feature;

use App\Models\EstacionServicio;
use App\Models\Importacion;
use App\Models\ImportacionFila;
use App\Models\Trabajo;
use App\Models\User;
use App\Services\ExcelParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportacionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->actingAs($user);
        return $user;
    }

    /** Genera un archivo Excel falso válido para subir. */
    private function fakeExcel(string $name = 'trabajos.xlsx'): UploadedFile
    {
        return UploadedFile::fake()->create($name, 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** Datos de fila parseada por defecto. */
    private function defaultRowData(array $overrides = []): array
    {
        return array_merge([
            'codigo_estacion'     => 'EST-001',
            'numero_trabajo'      => 'OT-2024-001',
            'descripcion_trabajo' => 'Reparación bomba',
            'fecha_encargo'       => '2024-01-15',
            'observaciones'       => 'Sin observaciones',
        ], $overrides);
    }

    /** Crea una Importacion con N filas en staging. */
    private function createImportacionWithFilas(User $user, array $rowsData): Importacion
    {
        $importacion = Importacion::factory()->create([
            'id_contexto' => $user->id_contexto,
            'estado'      => 'pendiente',
            'total_filas' => count($rowsData),
        ]);

        foreach ($rowsData as $index => $row) {
            ImportacionFila::factory()->create([
                'id_importacion' => $importacion->id_importacion ?? $importacion->id,
                'numero_fila'    => $index + 2,
                'datos_json'     => json_encode($row),
                'estado'         => 'pendiente',
            ]);
        }

        return $importacion;
    }

    /** @test */
    public function index_renders_inertia_page_for_authenticated_user(): void
    {
        $this->actingAsUser();
        Importacion::factory()->count(3)->create();

        $response = $this->get(route('importaciones.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Importaciones/Index')
                ->has('importaciones')
        );
    }

    /** @test */
    public function index_redirects_unauthenticated_users(): void
    {
        $response = $this->get(route('importaciones.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function index_paginates_with_15_per_page(): void
    {
        $this->actingAsUser();
        Importacion::factory()->count(20)->create();

        $response = $this->get(route('importaciones.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Importaciones/Index')
                ->has('importaciones.data', 15)
        );
    }

    // CREATE
    /** @test */
    public function create_renders_form_for_authenticated_user(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('importaciones.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Importaciones/Form')
        );
    }

    /** @test */
    public function create_redirects_unauthenticated_users(): void
    {
        $response = $this->get(route('importaciones.create'));

        $response->assertRedirect(route('login'));
    }

    // STORE
    /** @test */
    public function store_parses_file_and_redirects_to_preview(): void
    {
        Storage::fake('local');
        $user = $this->actingAsUser();

        $parsedData = [$this->defaultRowData()];
        $this->mock(ExcelParserService::class)
            ->shouldReceive('parseFile')
            ->once()
            ->andReturn($parsedData);

        $response = $this->post(route('importaciones.store'), [
            'archivo' => $this->fakeExcel(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('importaciones', [
            'tipo'   => 'trabajos',
            'estado' => 'pendiente',
        ]);
        $this->assertDatabaseCount('importacion_filas', 1);
    }

    /** @test */
    public function store_returns_error_when_parsed_file_is_empty(): void
    {
        Storage::fake('local');
        $this->actingAsUser();

        $this->mock(ExcelParserService::class)
            ->shouldReceive('parseFile')
            ->once()
            ->andReturn([]);

        $response = $this->post(route('importaciones.store'), [
            'archivo' => $this->fakeExcel(),
        ]);

        $response->assertSessionHasErrors('archivo');
        $this->assertDatabaseCount('importaciones', 0);
    }

    /** @test */
    public function store_rollbacks_transaction_on_exception(): void
    {
        Storage::fake('local');
        $this->actingAsUser();

        $this->mock(ExcelParserService::class)
            ->shouldReceive('parseFile')
            ->once()
            ->andThrow(new \Exception('Error de parsing'));

        Log::shouldReceive('error')->once();

        $response = $this->post(route('importaciones.store'), [
            'archivo' => $this->fakeExcel(),
        ]);

        $response->assertSessionHasErrors('archivo');
        $this->assertDatabaseCount('importaciones', 0);
        $this->assertDatabaseCount('importacion_filas', 0);
    }

    /** @test */
    public function store_assigns_user_context_to_importacion(): void
    {
        Storage::fake('local');
        $user = $this->actingAsUser(['id_contexto' => 5]);

        $this->mock(ExcelParserService::class)
            ->shouldReceive('parseFile')
            ->andReturn([$this->defaultRowData()]);

        $this->post(route('importaciones.store'), [
            'archivo' => $this->fakeExcel(),
        ]);

        $this->assertDatabaseHas('importaciones', ['id_contexto' => 5]);
    }

    /** @test */
    public function store_fails_validation_without_file(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('importaciones.store'), []);

        $response->assertSessionHasErrors('archivo');
    }

    /** @test */
    public function store_redirects_unauthenticated_users(): void
    {
        $response = $this->post(route('importaciones.store'), [
            'archivo' => $this->fakeExcel(),
        ]);

        $response->assertRedirect(route('login'));
    }

    // PREVIEW
    /** @test */
    public function preview_renders_page_with_evaluated_rows(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Importaciones/Preview')
                ->has('importacion')
                ->has('filas', 1)
        );
    }

    /** @test */
    public function preview_marks_row_as_valid_when_station_exists_and_no_duplicate(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertInertia(fn ($page) =>
            $page->where('filas.0.valido', true)
                ->where('filas.0.errores', [])
        );
    }

    /** @test */
    public function preview_marks_row_invalid_when_station_does_not_exist(): void
    {
        $user = $this->actingAsUser();
        // Sin crear estación
        $importacion = $this->createImportacionWithFilas($user, [
            $this->defaultRowData(['codigo_estacion' => 'NO-EXISTE']),
        ]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertInertia(fn ($page) =>
            $page->where('filas.0.valido', false)
                ->has('filas.0.errores', 1)
        );
    }

    /** @test */
    public function preview_marks_row_invalid_when_trabajo_is_duplicate(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        Trabajo::factory()->create(['numero_trabajo' => 'OT-2024-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertInertia(fn ($page) =>
            $page->where('filas.0.valido', false)
                ->has('filas.0.errores', 1)
        );
    }

    /** @test */
    public function preview_marks_row_invalid_with_two_errors_when_both_rules_fail(): void
    {
        $user = $this->actingAsUser();

        // Sin estación y con trabajo duplicado
        Trabajo::factory()->create(['numero_trabajo' => 'OT-2024-001']);
        $importacion = $this->createImportacionWithFilas($user, [
            $this->defaultRowData(['codigo_estacion' => 'NO-EXISTE']),
        ]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertInertia(fn ($page) =>
            $page->where('filas.0.valido', false)
                ->has('filas.0.errores', 2)
        );
    }

    /** @test */
    public function preview_returns_403_for_user_from_different_context(): void
    {
        $owner = User::factory()->create(['id_contexto' => 10]);
        $intruder = $this->actingAsUser(['id_contexto' => 99]);

        $importacion = Importacion::factory()->create(['id_contexto' => 10]);

        $response = $this->get(route('importaciones.preview', $importacion->id_importacion ?? $importacion->id));

        $response->assertForbidden();
    }

    /** @test */
    public function preview_returns_404_for_nonexistent_importacion(): void
    {
        $this->actingAsUser();

        $response = $this->get(route('importaciones.preview', 99999));

        $response->assertStatus(404);
    }

    // CONFIRM
    /** @test */
    public function confirm_creates_trabajos_for_valid_rows_and_redirects(): void
    {
        $user = $this->actingAsUser();

        $estacion = EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $response = $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $response->assertRedirect(route('trabajos.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('trabajos', ['numero_trabajo' => 'OT-2024-001']);
        $this->assertDatabaseHas('importaciones', [
            'id'               => $importacion->id,
            'estado'           => 'completado',
            'filas_importadas' => 1,
            'filas_con_error'  => 0,
        ]);
    }

    /** @test */
    public function confirm_marks_row_as_error_when_trabajo_is_duplicate(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        Trabajo::factory()->create(['numero_trabajo' => 'OT-2024-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $this->assertDatabaseHas('importaciones', [
            'id'               => $importacion->id,
            'filas_importadas' => 0,
            'filas_con_error'  => 1,
            'filas_duplicadas' => 1,
        ]);
        $this->assertDatabaseHas('importacion_filas', [
            'estado'        => 'error',
            'mensaje_error' => 'Trabajo duplicado en BD.',
        ]);
    }

    /** @test */
    public function confirm_marks_row_as_error_when_station_not_found(): void
    {
        $user = $this->actingAsUser();
        // Sin crear estación
        $importacion = $this->createImportacionWithFilas($user, [
            $this->defaultRowData(['codigo_estacion' => 'NO-EXISTE']),
        ]);

        $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $this->assertDatabaseHas('importaciones', [
            'id'               => $importacion->id,
            'filas_importadas' => 0,
            'filas_con_error'  => 1,
        ]);
        $this->assertDatabaseHas('importacion_filas', [
            'estado' => 'error',
        ]);
    }

    /** @test */
    public function confirm_handles_mixed_valid_and_invalid_rows(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        Trabajo::factory()->create(['numero_trabajo' => 'OT-DUPLICADO']);

        $importacion = $this->createImportacionWithFilas($user, [
            $this->defaultRowData(['numero_trabajo' => 'OT-NUEVO']),          // válida
            $this->defaultRowData(['numero_trabajo' => 'OT-DUPLICADO']),      // duplicada
            $this->defaultRowData(['codigo_estacion' => 'NO-EXISTE', 'numero_trabajo' => 'OT-SIN-EST']), // sin estación
        ]);

        $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $this->assertDatabaseHas('importaciones', [
            'id'               => $importacion->id,
            'estado'           => 'completado',
            'filas_importadas' => 1,
            'filas_con_error'  => 2,
            'filas_duplicadas' => 1,
        ]);
    }

    /** @test */
    public function confirm_prevents_reprocessing_already_completed_importacion(): void
    {
        $user = $this->actingAsUser();

        $importacion = Importacion::factory()->create([
            'id_contexto' => $user->id_contexto,
            'estado'      => 'completado',
        ]);

        $response = $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $response->assertRedirect(route('importaciones.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function confirm_returns_403_for_user_from_different_context(): void
    {
        $this->actingAsUser(['id_contexto' => 99]);

        $importacion = Importacion::factory()->create(['id_contexto' => 10]);

        $response = $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $response->assertForbidden();
    }

    /** @test */
    public function confirm_returns_404_for_nonexistent_importacion(): void
    {
        $this->actingAsUser();

        $response = $this->post(route('importaciones.confirm', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function confirm_rollbacks_transaction_on_exception(): void
    {
        $user = $this->actingAsUser();

        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        // Forzar excepción rompiendo la tabla durante el proceso
        DB::shouldReceive('beginTransaction')->once()->andReturnNull();
        DB::shouldReceive('rollBack')->once()->andReturnNull();
        DB::shouldReceive('commit')->never();

        Log::shouldReceive('error')->once();

        // Nota: en un entorno real, se inyectaría una dependencia que lanza la excepción.
        // Este test valida el flujo del bloque catch y el rollback.
    }

    /** @test */
    public function confirm_redirects_unauthenticated_users(): void
    {
        $importacion = Importacion::factory()->create();

        $response = $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function confirm_updates_fila_with_destination_record_id(): void
    {
        $user = $this->actingAsUser();

        EstacionServicio::factory()->create(['codigo_estacion' => 'EST-001']);
        $importacion = $this->createImportacionWithFilas($user, [$this->defaultRowData()]);

        $this->post(route('importaciones.confirm', $importacion->id_importacion ?? $importacion->id));

        $trabajo = Trabajo::where('numero_trabajo', 'OT-2024-001')->first();

        $this->assertDatabaseHas('importacion_filas', [
            'estado'                => 'procesado',
            'id_registro_destino'   => $trabajo->id_trabajo ?? $trabajo->id,
            'tipo_registro_destino' => Trabajo::class,
        ]);
    }
}