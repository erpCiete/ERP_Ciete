<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExcelParserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportacionController extends Controller
{
    use ApiResponse;

    protected ExcelParserService $parserService;

    public function __construct(ExcelParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Sube el archivo, lo guarda en temporal y devuelve la ruta.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'], // Máx 10MB
        ]);

        try {
            $file = $request->file('archivo');
            $filename = uniqid('import_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('importaciones/temp', $filename);

            return $this->successResponse(
                ['archivo_path' => $path],
                'Archivo subido correctamente. Listo para previsualización.',
                201
            );

        } catch (\Exception $e) {
            Log::error('Error guardando archivo de importación: ' . $e->getMessage());
            return $this->errorResponse('Error al guardar el archivo temporalmente.', 500);
        }
    }

    /**
     * Procesa el archivo subido y devuelve un preview (mapeo A-E).
     */
    public function preview(string $id): JsonResponse
    {
        // En este contexto, $id es la ruta o el nombre del archivo temporal que guardamos en store()
        $path = 'importaciones/temp/' . basename($id);

        if (!Storage::exists($path)) {
            return $this->errorResponse('El archivo no se encuentra o ha expirado.', 404);
        }

        try {
            $fullPath = storage_path('app/' . $path);

            // Llamamos a tu método exacto: parseFile()
            $datosParseados = $this->parserService->parseFile($fullPath);

            return $this->successResponse(
                [
                    'archivo_path' => $path,
                    'total_filas' => count($datosParseados),
                    // Devolvemos solo las primeras 5 filas para el preview del frontend
                    'preview' => array_slice($datosParseados, 0, 5), 
                ],
                'Archivo parseado correctamente para revisión.',
                200
            );

        } catch (\Exception $e) {
            Log::error('Error general procesando importación (preview): ' . $e->getMessage());
            return $this->errorResponse($e->getMessage() ?: 'Error interno al procesar el archivo Excel.', 422);
        }
    }

    /**
     * Confirma la importación después de la validación visual en frontend.
     */
    public function confirm(string $id, Request $request): JsonResponse
    {
        // En este contexto, $id es la ruta o el nombre del archivo temporal
        $path = 'importaciones/temp/' . basename($id);

        if (!Storage::exists($path)) {
            return $this->errorResponse('El archivo de importación expiró o no se encontró. Vuelve a subirlo.', 404);
        }

        try {
            $fullPath = storage_path('app/' . $path);

            // 1. Parseamos de nuevo (o recuperamos si lo hubieras guardado en Cache/DB temporal)
            $datosFinales = $this->parserService->parseFile($fullPath);

            // 2. Aquí iría tu lógica de inserción masiva a la Base de Datos.
            // Ej: Trabajo::insert($datosFinales);
            
            // 3. Limpieza del archivo temporal para no ocupar espacio basura
            Storage::delete($path);

            return $this->successResponse(
                ['registros_importados' => count($datosFinales)],
                'La importación se ha completado y guardado con éxito en el sistema.'
            );

        } catch (\Exception $e) {
            Log::error('Error confirmando importación final: ' . $e->getMessage());
            return $this->errorResponse('Ocurrió un error al guardar los datos finales.', 500);
        }
    }
}