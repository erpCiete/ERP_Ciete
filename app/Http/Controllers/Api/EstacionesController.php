<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EstacionesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listado de estaciones disponible',
            'data' => [],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
