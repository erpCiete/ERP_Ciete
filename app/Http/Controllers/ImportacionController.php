<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreImportacionRequest;
use App\Models\Importacion;
use App\Models\ImportacionFila;
use App\Models\Trabajo;
use App\Models\EstacionServicio;
use App\Services\ExcelParserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 

class ImportacionController extends Controller
{
    private ExcelParserService $parser;

    public function __construct(ExcelParserService $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Muestra el historial de importaciones.
     */
    public function index(Request $request): Response
    {
        $importaciones = Importacion::query()
            ->latest()
            ->paginate(15);

        return Inertia::render('Importaciones/Index', [
            'importaciones' => $importaciones,
        ]);
    }

    /**
     * Muestra el formulario para subir un Excel.
     */
    public function create(): Response
    {
        return Inertia::render('Importaciones/Form');
    }

    /**
     * Recibe el archivo, lo parsea y guarda temporalmente en BD (Staging).
     */
    public function store(StoreImportacionRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $file = $request->file('archivo');
            // 1. Guardar con Storage de forma explícita
            $path = $file->store('importaciones/temp', 'local');
            $fullPath = Storage::disk('local')->path($path);

            // 2. Extraer arrays con ExcelParserService
            $parsedData = $this->parser->parseFile($fullPath);

            if (empty($parsedData)) {
                // Si el archivo está vacío, borramos el temporal para no ensuciar el disco
                Storage::disk('local')->delete($path);
                return back()->withErrors(['archivo' => 'El archivo Excel está vacío o no tiene el formato correcto.']);
            }

            // 3. Crear cabecera de la importación usando el 'tipo' validado en el Request
            $importacion = Importacion::create([
                'id_contexto'         => $request->user()->id_contexto,
                'id_usuario'          => $request->user()->id_usuario ?? $request->user()->id,
                'tipo'                => $request->input('tipo', 'trabajos'), // Dinámico y validado
                'archivo_original'    => $file->getClientOriginalName(),
                'total_filas'         => count($parsedData),
                'filas_importadas'    => 0,
                'filas_con_error'     => 0,
                'filas_duplicadas'    => 0,
                'estado'              => 'pendiente',
                'version_importacion' => '1.0',
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

            return redirect()->route('importaciones.preview', $importacion->id_importacion ?? $importacion->id)
                ->with('success', 'Archivo analizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importación Store: ' . $e->getMessage());
            return back()->withErrors(['archivo' => 'Ocurrió un error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Vista de pre-confirmación: Evalúa cada fila para ver si es válida.
     */
    public function preview(Request $request, $id): Response
    {
        $importacion = Importacion::with('filas')->findOrFail($id);

        if ($importacion->id_contexto !== $request->user()->id_contexto) {
            abort(403);
        }

        $filasEvaluadas = $importacion->filas->map(function ($fila) {
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
                'id_importacion_fila' => $fila->id_importacion_fila ?? $fila->id,
                'numero_fila'         => $fila->numero_fila,
                'datos'               => $datos,
                'valido'              => empty($errores),
                'errores'             => $errores,
            ];
        });

        return Inertia::render('Importaciones/Preview', [
            'importacion' => $importacion,
            'filas'       => $filasEvaluadas,
        ]);
    }

    /**
     * Confirma la importación: Transforma las filas válidas en Obras (Trabajos).
     */
    public function confirm(Request $request, $id): RedirectResponse
    {
        $importacion = Importacion::with('filas')->findOrFail($id);

        if ($importacion->id_contexto !== $request->user()->id_contexto) {
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
                        'estado'               => 'borrador', // Mejor iniciar en borrador por seguridad
                        'cerrado'              => false,
                        'id_tipo_trabajo'      => 1, // Fallback genérico
                        'id_tipo_documento'    => $importacion->id_contexto === 2 ? 1 : null,
                    ]);

                    // Actualizar trazabilidad en la fila
                    $fila->update([
                        'estado'                => 'procesado',
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