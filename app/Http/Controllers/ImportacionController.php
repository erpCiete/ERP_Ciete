<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreImportacionRequest;
use App\Models\Importacion;
use App\Models\ImportacionFila;
use App\Models\Trabajo;
use App\Models\EstacionServicio;
use App\Services\ExcelParserService;
use App\Support\ContextGuard;
use Throwable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportacionController extends Controller
{
    /**
     * Muestra el historial de importaciones.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'contexto' => trim((string) $request->input('contexto', '')),
            'archivo' => trim((string) $request->input('archivo', '')),
            'estado' => trim((string) $request->input('estado', '')),
            'detalle' => $request->integer('detalle') ?: null,
            'hoja' => trim((string) $request->input('hoja', '')),
            'severidad' => trim((string) $request->input('severidad', '')),
            'clasificacion' => trim((string) $request->input('clasificacion', '')),
            'tipo' => trim((string) $request->input('tipo', '')),
            'resultado' => trim((string) $request->input('resultado', '')),
        ];

        $importacionesBaseQuery = Importacion::query()
            ->with(['contexto:id_contexto,codigo,nombre', 'usuario:id_usuario,email,nombre']);

        $this->applyImportacionFilters($importacionesBaseQuery, $filters);

        $importaciones = (clone $importacionesBaseQuery)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $selectedImportId = $filters['detalle'] ?: ($importaciones->items()[0]->id_importacion ?? null);
        $selectedImport = $selectedImportId
            ? Importacion::query()->with('contexto:id_contexto,codigo,nombre')->find($selectedImportId)
            : null;

        $detailQuery = ImportacionFila::query()
            ->with('importacion.contexto:id_contexto,codigo,nombre')
            ->whereIn('id_importacion', (clone $importacionesBaseQuery)->select('id_importacion'));

        if ($selectedImportId !== null) {
            $detailQuery->where('id_importacion', $selectedImportId);
        }

        $this->applyDetalleFilters($detailQuery, $filters);

        $detalleFilas = (clone $detailQuery)
            ->orderByDesc('id_importacion_fila')
            ->paginate(50, ['*'], 'detalle_page')
            ->withQueryString();

        $preview = null;
        $previewImportacionId = $request->session()->get('preview_importacion_id');

        if ($previewImportacionId !== null) {
            $importacionEnPreview = Importacion::with('filas')->find($previewImportacionId);

            if ($importacionEnPreview && ContextGuard::canOperateContext($request->user(), (int) $importacionEnPreview->id_contexto)) {
                $preview = $this->evaluarFilasImportacion($importacionEnPreview);
            }
        }

        return Inertia::render('Importaciones/Index', [
            'preview' => $preview,
            'importaciones' => $importaciones,
            'resumen' => $this->buildImportSummary(clone $importacionesBaseQuery),
            'detalleImportacion' => $selectedImport,
            'detalleFilas' => $detalleFilas,
            'agrupaciones' => [
                'avisosPorArchivo' => $this->groupIssuesByFile(clone $detailQuery, 'warning'),
                'avisosPorHoja' => $this->groupIssuesBySheet(clone $detailQuery, 'warning'),
                'avisosPorTipo' => $this->groupIssuesByType(clone $detailQuery),
                'ignoradasPorArchivo' => $this->groupResultsByFile(clone $detailQuery, 'ignored'),
                'ignoradasPorHoja' => $this->groupResultsBySheet(clone $detailQuery, 'ignored'),
                'columnasPendientes' => $this->extractUnknownColumns(clone $detailQuery),
            ],
            'filtros' => $filters,
        ]);
    }

    private function applyImportacionFilters($query, array $filters): void
    {
        if ($filters['contexto'] !== '') {
            $contexto = mb_strtoupper($filters['contexto'], 'UTF-8');
            $query->whereHas('contexto', function ($contextQuery) use ($contexto): void {
                $contextQuery
                    ->where('codigo', $contexto)
                    ->orWhere('nombre', 'like', '%' . $contexto . '%');
            });
        }

        if ($filters['archivo'] !== '') {
            $query->where('archivo_original', 'like', '%' . $filters['archivo'] . '%');
        }

        if ($filters['estado'] !== '') {
            $query->where('estado', $filters['estado']);
        }
    }

    private function applyDetalleFilters($query, array $filters): void
    {
        if ($filters['hoja'] !== '') {
            $query->where('hoja_origen', 'like', '%' . $filters['hoja'] . '%');
        }

        if ($filters['severidad'] !== '') {
            $query->where('severidad', $filters['severidad']);
        }

        if ($filters['clasificacion'] !== '') {
            $query->where('clasificacion', $filters['clasificacion']);
        }

        if ($filters['tipo'] !== '') {
            $query->where('tipo_fila', $filters['tipo']);
        }

        if ($filters['resultado'] !== '') {
            $query->where('resultado', $filters['resultado']);
        }
    }

    private function buildImportSummary($query): array
    {
        return [
            'importaciones' => (clone $query)->count(),
            'filas_leidas' => (int) ((clone $query)->sum('total_filas') ?? 0),
            'filas_importadas' => (int) ((clone $query)->sum('filas_importadas') ?? 0),
            'filas_ignoradas' => (int) ((clone $query)->sum('filas_ignoradas') ?? 0),
            'filas_con_aviso' => (int) ((clone $query)->sum('filas_con_aviso') ?? 0),
            'filas_con_error' => (int) ((clone $query)->sum('filas_con_error') ?? 0),
        ];
    }

    private function groupIssuesByFile($query, string $severity): array
    {
        return (clone $query)
            ->select('archivo_origen', DB::raw('count(*) as total'))
            ->where('severidad', $severity)
            ->groupBy('archivo_origen')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn($row) => [
                'archivo' => $row->archivo_origen,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function groupIssuesBySheet($query, string $severity): array
    {
        return (clone $query)
            ->select('archivo_origen', 'hoja_origen', DB::raw('count(*) as total'))
            ->where('severidad', $severity)
            ->groupBy('archivo_origen', 'hoja_origen')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'archivo' => $row->archivo_origen,
                'hoja' => $row->hoja_origen,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function groupIssuesByType($query): array
    {
        return (clone $query)
            ->select('codigo', 'clasificacion', DB::raw('count(*) as total'))
            ->groupBy('codigo', 'clasificacion')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'codigo' => $row->codigo,
                'clasificacion' => $row->clasificacion,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function groupResultsByFile($query, string $result): array
    {
        return (clone $query)
            ->select('archivo_origen', DB::raw('count(*) as total'))
            ->where('resultado', $result)
            ->groupBy('archivo_origen')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn($row) => [
                'archivo' => $row->archivo_origen,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function groupResultsBySheet($query, string $result): array
    {
        return (clone $query)
            ->select('archivo_origen', 'hoja_origen', DB::raw('count(*) as total'))
            ->where('resultado', $result)
            ->groupBy('archivo_origen', 'hoja_origen')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(fn($row) => [
                'archivo' => $row->archivo_origen,
                'hoja' => $row->hoja_origen,
                'total' => (int) $row->total,
            ])
            ->all();
    }

    private function extractUnknownColumns($query): array
    {
        $rows = (clone $query)
            ->where('codigo', 'unknown_columns')
            ->orderByDesc('id_importacion_fila')
            ->limit(40)
            ->get(['archivo_origen', 'hoja_origen', 'clasificacion', 'decision_sugerida', 'datos_json']);

        return $rows
            ->flatMap(function (ImportacionFila $row): array {
                $columns = $row->datos_json['columns'] ?? [];

                return array_map(fn($column) => [
                    'archivo' => $row->archivo_origen,
                    'hoja' => $row->hoja_origen,
                    'columna' => (string) $column,
                    'clasificacion' => $row->clasificacion,
                    'decision' => $row->decision_sugerida,
                ], $columns);
            })
            ->unique(fn(array $row) => $row['archivo'] . '|' . $row['hoja'] . '|' . $row['columna'])
            ->values()
            ->all();
    }

    /**
     * Recibe el archivo, lo parsea y lo persiste en staging (importacion_filas)
     * para que el usuario pueda revisar antes de confirmar.
     */
    public function store(Request $request): RedirectResponse
    {
        // La validación usa mimetypes en lugar de mimes para evitar falsos negativos
        // con CSV en Windows, donde finfo los detecta como text/plain.
        $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,' .
                    'application/vnd.ms-excel,' .
                    'text/csv,text/plain,text/comma-separated-values,' .
                    'application/csv,application/octet-stream',
                'max:10240',
            ],
            'tipo' => ['required', 'string', \Illuminate\Validation\Rule::in(['estaciones', 'trabajos', 'tarifario', 'facturas'])],
        ], [
            'archivo.required' => 'Debe adjuntar un archivo para importar.',
            'archivo.mimetypes' => 'El archivo debe ser un Excel válido (.xlsx, .xls) o un CSV.',
            'archivo.max' => 'El archivo no puede pesar más de 10 MB.',
            'tipo.required' => 'Debe especificar el tipo de importación.',
            'tipo.in' => 'El tipo de importación no es válido.',
        ]);
        $parser = $this->resolveParser();

        if (! $parser) {
            return back()->withErrors([
                'archivo' => 'El módulo de importaciones no está operativo en este entorno.',
            ]);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('archivo');
            $path = $file->store('importaciones/temp', 'local');
            $fullPath = Storage::disk('local')->path($path);

            $parsedData = $parser->parseFile($fullPath);

            if (empty($parsedData)) {
                Storage::disk('local')->delete($path);
                return back()->withErrors(['archivo' => 'El archivo Excel está vacío o no tiene el formato correcto.']);
            }

            $importacion = Importacion::create([
                'id_contexto'         => ContextGuard::activeContextIdForCreate($request->user()),
                'id_usuario'          => $request->user()->id_usuario ?? $request->user()->id,
                'tipo'                => $request->input('tipo', 'trabajos'),
                'archivo_original'    => $file->getClientOriginalName(),
                'total_filas'         => count($parsedData),
                'filas_importadas'    => 0,
                'filas_con_error'     => 0,
                'filas_duplicadas'    => 0,
                'estado'              => 'subido',
                'version_importacion' => 1,
                'started_at'          => now(),
            ]);

            // 4. Preparar filas en Staging (importacion_filas)
            $filasToInsert = [];
            $now = now();
            foreach ($parsedData as $index => $row) {
                $filasToInsert[] = [
                    'id_importacion' => $importacion->id_importacion ?? $importacion->id,
                    'numero_fila'    => $index + 2, // +2 asumiendo cabecera en fila 1
                    'datos_json'     => json_encode($row),
                    'estado'         => 'pendiente',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }

            // 5. Insertar en "Chunks" (Trozos) para evitar saturar la base de datos si suben miles de filas
            foreach (array_chunk($filasToInsert, 500) as $chunk) {
                ImportacionFila::insert($chunk);
            }

            DB::commit();

            // 6. Como ya tenemos las filas en la BD, ya no necesitamos el archivo Excel. Lo borramos.
            Storage::disk('local')->delete($path);

            return redirect()->route('importaciones.index')
                ->with('success', 'Archivo analizado correctamente.')
                ->with('preview_importacion_id', $importacion->id_importacion ?? $importacion->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación Store: ' . $e->getMessage());
            return back()->withErrors(['archivo' => 'Ocurrió un error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    private function resolveParser(): ?ExcelParserService
    {
        try {
            return app(ExcelParserService::class);
        } catch (Throwable $exception) {
            Log::warning('Importaciones no disponibles: parser Excel no resoluble.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Evalúa cada fila en staging de una importación para mostrarla en la
     * vista previa embebida de Importaciones/Index antes de confirmarla.
     */
    private function evaluarFilasImportacion(Importacion $importacion): \Illuminate\Support\Collection
    {
        return $importacion->filas->map(function ($fila) use ($importacion) {
            $datos = is_string($fila->datos_json) ? json_decode($fila->datos_json, true) : $fila->datos_json;
            $errores = [];

            // Regla 1: Estación
            $estacion = EstacionServicio::where('codigo_estacion', $datos['codigo_estacion'])->first();
            if (!$estacion) {
                $errores[] = "La estación '{$datos['codigo_estacion']}' no existe.";
            }

            // Regla 2: Duplicidad
            $existeTrabajo = Trabajo::where('numero_trabajo', $datos['numero_trabajo'])
                ->where('id_contexto', $importacion->id_contexto) // Blindado por contexto
                ->exists();

            if ($existeTrabajo) {
                $errores[] = "El Nº Trabajo '{$datos['numero_trabajo']}' ya existe.";
            }

            return [
                'id_importacion'      => $importacion->id_importacion ?? $importacion->id,
                'id_importacion_fila' => $fila->id_importacion_fila ?? $fila->id,
                'numero_fila'         => $fila->numero_fila,
                'datos'               => $datos,
                'valido'              => empty($errores),
                'errores'             => $errores,
            ];
        });
    }

    /**
     * Confirma la importación: Transforma las filas válidas en Obras (Trabajos).
     */
    public function confirm(Request $request, $id): RedirectResponse
    {
        $importacion = Importacion::with('filas')->findOrFail($id);

        if (! ContextGuard::canOperateContext($request->user(), (int) $importacion->id_contexto)) {
            abort(403);
        }

        if ($importacion->estado === 'completado') {
            return redirect()->route('importaciones.index')->with('error', 'Esta importación ya fue procesada.');
        }

        DB::beginTransaction();
        try {
            $importadas = 0;
            $errores    = 0;
            $duplicadas = 0;

            foreach ($importacion->filas as $fila) {
                $datos = is_string($fila->datos_json) ? json_decode($fila->datos_json, true) : $fila->datos_json;

                $estacion = EstacionServicio::where('codigo_estacion', $datos['codigo_estacion'])->first();
                $existeTrabajo = Trabajo::where('numero_trabajo', $datos['numero_trabajo'])
                    ->where('id_contexto', $importacion->id_contexto)
                    ->exists();

                if ($existeTrabajo) {
                    $fila->update([
                        'estado'        => 'error',
                        'mensaje_error' => 'Trabajo duplicado en BD.',
                    ]);
                    $duplicadas++;
                    $errores++;
                } elseif (!$estacion) {
                    $fila->update([
                        'estado'        => 'error',
                        'mensaje_error' => "Estación {$datos['codigo_estacion']} no encontrada.",
                    ]);
                    $errores++;
                } else {
                    // Crear Trabajo
                    $trabajo = Trabajo::create([
                        'id_contexto'          => $importacion->id_contexto,
                        'id_empresa_cliente'   => $estacion->id_empresa_cliente,
                        'id_estacion_servicio' => $estacion->id_estacion_servicio,
                        'numero_trabajo'       => $datos['numero_trabajo'],
                        'descripcion_trabajo'  => $datos['descripcion_trabajo'],
                        'fecha_encargo'        => $datos['fecha_encargo'],
                        'observaciones'        => $datos['observaciones'],
                        'estado'               => 'en_curso',
                        'id_tipo_trabajo'      => 1, // Fallback genérico
                        'id_tipo_documento'    => $importacion->id_contexto === 2 ? 1 : null,
                    ]);

                    // Actualizar trazabilidad en la fila
                    $fila->update([
                        'estado'                => 'importado',
                        'mensaje_error'         => null,
                        'id_registro_destino'   => $trabajo->id_trabajo ?? $trabajo->id,
                        'tipo_registro_destino' => Trabajo::class,
                    ]);
                    $importadas++;
                }
            }

            // Cerrar la importación con sus métricas actualizadas
            $importacion->update([
                'estado'           => 'completado',
                'filas_importadas' => $importadas,
                'filas_con_error'  => $errores,
                'filas_duplicadas' => $duplicadas,
                'finished_at'      => now(),
            ]);

            DB::commit();

            return redirect()->route('trabajos.index')
                ->with('success', "Importación completada: {$importadas} trabajos nuevos creados.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirmando importación: ' . $e->getMessage());
            return back()->with('error', 'Error crítico al procesar los datos: ' . $e->getMessage());
        }
    }
}
