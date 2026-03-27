<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Listado de pedidos.
     * Filtrar SIEMPRE por el contexto del cliente (Repsol/Cepsa).
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Extraer el cliente_id del contexto del usuario autenticado o del request.
        return $this->stubResponse('Listado de pedidos obtenido (Stub)', []);
    }

    /**
     * Crear un nuevo pedido.
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para creación.
        // TODO: Validar payload y asegurar que el pedido se asigna al cliente correcto.
        return $this->stubResponse('Pedido creado exitosamente (Stub)', null, 201);
    }

    /**
     * Detalle de un pedido específico.
     */
    public function show(string $id): JsonResponse
    {
        // TODO: Validar que el ID pertenece al cliente del usuario en sesión.
        return $this->stubResponse('Detalle del pedido (Stub)', ['id' => $id]);
    }

    /**
     * Actualizar un pedido existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para actualización.
        // TODO: Validar payload y asegurar que el pedido se asigna al cliente correcto.
        return $this->stubResponse('Pedido actualizado exitosamente (Stub)', ['id' => $id]);
    }

    /**
     * Eliminar un pedido.
     */
    public function destroy(string $id): JsonResponse
    {
        // TODO: Validar permisos y reglas de negocio antes de eliminar.
        return $this->stubResponse('Pedido eliminado exitosamente (Stub)', ['id' => $id]);
    }

    private function stubResponse(string $message, mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ], $status);
    }
}
