<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estacion;
use App\Http\Requests\Api\EstacionStoreRequest;
use App\Http\Requests\Api\EstacionUpdateRequest;
use App\Http\Resources\Api\EstacionResource;
use Illuminate\Http\JsonResponse;

class EstacionController extends Controller
{
    /**
     * Listado de estaciones.
     */
    public function index(): JsonResponse
    {
        // Importante: usamos paginate para que coincida con tu lógica de 'meta'
        $estaciones = Estacion::paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Listado de estaciones obtenido correctamente',
            'data'    => EstacionResource::collection($estaciones),
            'meta'    => [
                'timestamp' => now()->toIso8601String(),
                'pagination' => [
                    'total' => $estaciones->total(),
                    'count' => $estaciones->count(),
                    'per_page' => $estaciones->perPage(),
                    'current_page' => $estaciones->currentPage(),
                    'total_pages' => $estaciones->lastPage()
                ]
            ]
        ], 200);
    }

    public function store(EstacionStoreRequest $request): JsonResponse
    {
        $estacion = Estacion::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Estación creada exitosamente',
            'data'    => new EstacionResource($estacion),
        ], 201);
    }

    public function show(Estacion $estacion): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detalle de la estación obtenido',
            'data'    => new EstacionResource($estacion),
        ]);
    }

    public function update(EstacionUpdateRequest $request, Estacion $estacion): JsonResponse
    {
        $estacion->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Estación actualizada exitosamente',
            'data'    => new EstacionResource($estacion),
        ]);
    }

    public function destroy(Estacion $estacion): JsonResponse
    {
        $estacion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Estación eliminada correctamente',
            'data'    => null,
        ]);
    }
}