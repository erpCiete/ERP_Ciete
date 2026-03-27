<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PedidosController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listado de pedidos disponible',
            'data' => [],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
