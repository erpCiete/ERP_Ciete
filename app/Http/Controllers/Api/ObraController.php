<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ObraController extends Controller
{
    /**
     * Listado de obras.
     * Filtrar SIEMPRE por el contexto del cliente (Repsol/Cepsa).
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Extraer el cliente_id del contexto del usuario autenticado o del request.
        return $this->stubResponse('Listado de obras obtenido (Stub)', []);
    }

    /**
     * Crear una nueva obra.
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para creación.
        // TODO: Validar payload y asegurar que la obra se asigna al cliente correcto.
        return $this->stubResponse('Obra creada exitosamente (Stub)', null, 201);
    }

    /**
     * Detalle de una obra específica.
     */
    public function show(string $id): JsonResponse
    {
        // TODO: Validar que el ID pertenece al cliente del usuario en sesión.
        return $this->stubResponse('Detalle de la obra (Stub)', ['id' => $id]);
    }

    /**
     * Actualizar una obra existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para actualización.
        // TODO: Validar payload y asegurar que la obra se asigna al cliente correcto.
        return $this->stubResponse('Obra actualizada exitosamente (Stub)', ['id' => $id]);
    }

    /**
     * Actualizar solo el estado de una obra.
     */
    public function updateEstado(Request $request, string $id): JsonResponse
    {
        // TODO: Validar transición de estado permitida y permisos RBAC.
        return $this->stubResponse('Estado de la obra actualizado (Stub)', [
            'id' => $id,
            'estado' => $request->input('estado', 'sin_cambios'),
        ]);
    }

    /**
     * Eliminar una obra.
     */
    public function destroy(string $id): JsonResponse
    {
        // TODO: Validar permisos y reglas de negocio (ej. no borrar obras cerradas).
        return $this->stubResponse('Obra eliminada exitosamente (Stub)', ['id' => $id]);
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
