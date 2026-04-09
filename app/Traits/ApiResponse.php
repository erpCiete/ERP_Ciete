<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

trait ApiResponse
{
    
    protected function successResponse(mixed $data = null, string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        $meta = [
            'timestamp' => Carbon::now()->toIso8601ZuluString(),
        ];

        // Si pasan un JsonResource (ej. EstacionResource::collection)
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->additional([
                'success' => true,
                'message' => $message,
                'meta'    => $meta,
            ])->response()->setStatusCode($code);
        }

        // Respuesta estándar para arrays o datos planos
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ], $code);
    }

    /**
     * Construye una respuesta de error estandarizada con su respectivo código de negocio.
     */
    protected function errorResponse(string $message, string $errorCode, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $message,
            'error_code' => $errorCode,
            'errors'     => $errors,
            'meta'       => [
                'timestamp' => Carbon::now()->toIso8601ZuluString(),
            ]
        ], $code);
    }
}