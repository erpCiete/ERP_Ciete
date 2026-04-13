<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\EstacionStoreRequest;
use App\Http\Requests\Api\EstacionUpdateRequest;
use App\Http\Resources\Api\EstacionResource;
use App\Models\EstacionServicio;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstacionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $clienteId = $request->integer('cliente_id');
        $operador = trim((string) $request->input('operador', ''));

        $estaciones = EstacionServicio::query()
            ->with('empresa')
            ->when($clienteId > 0, function ($query) use ($clienteId): void {
                $query->where('id_empresa_cliente', $clienteId);
            })
            ->when($operador !== '', function ($query) use ($operador): void {
                $query->whereHas('empresa', function ($empresaQuery) use ($operador): void {
                    $empresaQuery->where('nombre_comercial', 'like', "%{$operador}%");
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo_estacion', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('poblacion', 'like', "%{$search}%")
                        ->orWhere('provincia', 'like', "%{$search}%")
                        ->orWhereHas('empresa', function ($empresaQuery) use ($search): void {
                            $empresaQuery
                                ->where('nombre', 'like', "%{$search}%")
                                ->orWhere('nombre_comercial', 'like', "%{$search}%")
                                ->orWhere('razon_social', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $estaciones,
            EstacionResource::collection($estaciones->getCollection())->resolve(),
            'Listado de estaciones obtenido',
        );
    }

    public function store(EstacionStoreRequest $request): JsonResponse
    {
        $estacion = EstacionServicio::create($request->validated());

        return $this->successResponse(
            (new EstacionResource($estacion->load('empresa')))->resolve(),
            'Estacion creada correctamente',
            201,
        );
    }

    public function show(EstacionServicio $estacion): JsonResponse
    {
        return $this->successResponse(
            (new EstacionResource($estacion->load('empresa')))->resolve(),
            'Detalle de estacion obtenido',
        );
    }

    public function update(EstacionUpdateRequest $request, EstacionServicio $estacion): JsonResponse
    {
        $estacion->update($request->validated());

        return $this->successResponse(
            (new EstacionResource($estacion->fresh()->load('empresa')))->resolve(),
            'Estacion actualizada correctamente',
        );
    }

    public function destroy(EstacionServicio $estacion): JsonResponse
    {
        $estacion->delete();

        return $this->successResponse(null, 'Estacion eliminada correctamente');
    }
}
