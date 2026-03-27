<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InformeController extends Controller
{
    /**
     * Listado de informes.
     * Filtrar SIEMPRE por el contexto del cliente (Repsol/Cepsa
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Extraer el cliente_id del contexto del usuario autenticado o del request
        // $clienteId = $request->user()->contexto_cliente_id;

        // TODO: Aplicar paginación y retornar respuesta estándar (BE-06)
        return response()->json([
            'success' => true,
            'message' => 'Listado de informes obtenido (Stub)',
            'data'    => [],
            'meta'    => [
                'timestamp' => now()->toIso8601String()
            ]
        ]);
    }

    /**
     * Crear un nuevo informe.
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para creación.
        // TODO: Validar payload y asegurar que el informe se asigna al cliente correcto.
        return response()->json([
            'success' => true,
            'message' => 'Informe creado exitosamente (Stub)',
            'data'    => null,
        ], 201);
    }

    /**
     * Detalle de un informe específico.
     */
    public function show(string $id): JsonResponse
    {
        // TODO: Validar que el ID pertenece al cliente del usuario en sesión.
        return response()->json([
            'success' => true,
            'message' => 'Detalle del informe (Stub)',
            'data'    => ['id' => $id],
        ]);
    }

    /**
     * Actualizar un informe existente.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // TODO: Validar permisos (RBAC) para actualización.
        // TODO: Validar payload y asegurar que el informe se asigna al cliente correcto.
        return response()->json([
            'success' => true,
            'message' => 'Informe actualizado exitosamente (Stub)',
            'data'    => ['id' => $id],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
