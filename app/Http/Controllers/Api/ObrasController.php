<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ObrasController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Listado de obras disponible',
            'data' => [],
            'meta' => [
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
}
