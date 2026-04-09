<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $clientes = Empresa::where('id_contexto', $user->id_contexto)
            ->whereIn('tipo_empresa', ['cliente', 'cliente_proveedor'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Listado de clientes obtenido',
            'data' => $clientes,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string',
            'nombre_comercial' => 'nullable|string',
            'razon_social' => 'nullable|string',
            'cif' => 'nullable|string',
            'tipo_empresa' => 'required|string',
            'web' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $data['id_contexto'] = $request->user()->id_contexto;

        $cliente = Empresa::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'data' => $cliente,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $cliente = Empresa::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detalle de cliente obtenido',
            'data' => $cliente,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $cliente = Empresa::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'sometimes|required|string',
            'nombre_comercial' => 'nullable|string',
            'razon_social' => 'nullable|string',
            'cif' => 'nullable|string',
            'tipo_empresa' => 'sometimes|required|string',
            'web' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $cliente->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => $cliente,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        Empresa::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
            'data' => null,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
