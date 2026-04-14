<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $empresa = $this->whenLoaded('empresa');

        return [
            'id' => $this->id_estacion_servicio,
            'id_empresa_cliente' => $this->id_empresa_cliente,
            'nombre' => $this->nombre,
            'codigo_estacion' => $this->codigo_estacion,
            'direccion' => $this->direccion,
            'codigo_postal' => $this->codigo_postal,
            'poblacion' => $this->poblacion,
            'provincia' => $this->provincia,
            'pais' => $this->pais,
            'estado' => $this->estado,
            'observaciones' => $this->observaciones,
            'activo' => (bool) $this->activo,
            'empresa' => $empresa ? [
                'id' => $empresa->id_empresa,
                'nombre' => $empresa->nombre,
                'nombre_comercial' => $empresa->nombre_comercial,
                'operador' => $this->resolveOperator((string) $empresa->nombre_comercial),
            ] : null,
            'operador' => $empresa ? $this->resolveOperator((string) $empresa->nombre_comercial) : null,
        ];
    }

    private function resolveOperator(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        foreach (['repsol', 'moeve', 'bp', 'galp'] as $operator) {
            if ($normalized !== '' && str_contains($normalized, $operator)) {
                return $operator;
            }
        }

        return $normalized !== '' ? $normalized : null;
    }
}
