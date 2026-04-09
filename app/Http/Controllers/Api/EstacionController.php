<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstacionServicio;
use App\Http\Requests\Api\EstacionStoreRequest;
use App\Http\Requests\Api\EstacionUpdateRequest;
use App\Http\Resources\Api\EstacionResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstacionController extends Controller
{
    use ApiResponse;

    /**
     * Listado de estaciones con capacidad de búsqueda dinámica.
     */
    public function index(Request $request): JsonResponse
    {
        $query = EstacionServicio::query()->with('empresaCliente');

        // Búsqueda dinámica implementada (Tarea B6)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('poblacion', 'like', "%{$search}%")
                  ->orWhere('codigo_estacion_interno', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $estaciones = $query->latest('id_estacion_servicio')->paginate($perPage);

        return $this->successResponse(
            EstacionResource::collection($estaciones),
            'Listado de estaciones obtenido correctamente'
        );
    }

    public function store(EstacionStoreRequest $request): JsonResponse
    {
        $estacion = EstacionServicio::create($request->validated());
        
        // Cargamos la relación para devolver el JSON completo al front
        $estacion->load('empresaCliente');

        return $this->successResponse(
            new EstacionResource($estacion),
            'Estación de servicio creada con éxito',
            201
        );
    }

    public function show(EstacionServicio $estacione): JsonResponse
    {
        // Nota: Laravel inyecta $estacione (singular de la ruta definida en apiResource)
        $estacione->load('empresaCliente');

        return $this->successResponse(
            new EstacionResource($estacione),
            'Detalle de la estación obtenido'
        );
    }

    public function update(EstacionUpdateRequest $request, EstacionServicio $estacione): JsonResponse
    {
        $estacione->update($request->validated());
        $estacione->load('empresaCliente');

        return $this->successResponse(
            new EstacionResource($estacione),
            'Estación actualizada exitosamente'
        );
    }

    public function destroy(EstacionServicio $estacione): JsonResponse
    {
        $estacione->delete();

        return $this->successResponse(
            null,
            'Estación eliminada correctamente'
        );
    }
}