<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ClienteStoreRequest;
use App\Http\Requests\Api\ClienteUpdateRequest;
use App\Http\Resources\Api\ClienteResource;
use App\Models\Empresa;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $search = trim((string) $request->input('search', ''));
        $contexto = trim((string) $request->input('contexto', ''));

        $clientes = Empresa::query()
            ->clientes()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('nombre', 'like', "%{$search}%")
                        ->orWhere('nombre_comercial', 'like', "%{$search}%")
                        ->orWhere('razon_social', 'like', "%{$search}%")
                        ->orWhere('cif', 'like', "%{$search}%");
                });
            })
            ->when($contexto !== '', function ($query) use ($contexto): void {
                $query->where('nombre_comercial', 'like', "%{$contexto}%");
            })
            ->orderBy('nombre_comercial')
            ->orderBy('nombre')
            ->paginate($perPage)
            ->withQueryString();

        return $this->paginatedResponse(
            $clientes,
            ClienteResource::collection($clientes->getCollection())->resolve(),
            'Listado de clientes obtenido',
        );
    }

    public function store(ClienteStoreRequest $request): JsonResponse
    {
        $cliente = Empresa::create($request->validated());

        return $this->successResponse(
            (new ClienteResource($cliente))->resolve(),
            'Cliente creado correctamente',
            201,
        );
    }

    public function show(Empresa $cliente): JsonResponse
    {
        return $this->successResponse(
            (new ClienteResource($cliente))->resolve(),
            'Detalle de cliente obtenido',
        );
    }

    public function update(ClienteUpdateRequest $request, Empresa $cliente): JsonResponse
    {
        $cliente->update($request->validated());

        return $this->successResponse(
            (new ClienteResource($cliente->fresh()))->resolve(),
            'Cliente actualizado correctamente',
        );
    }

    public function destroy(Empresa $cliente): JsonResponse
    {
        $cliente->delete();

        return $this->successResponse(null, 'Cliente eliminado correctamente');
    }
}
