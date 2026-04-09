<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_empresa,
            'nombre' => $this->nombre,
            'nombre_comercial' => $this->nombre_comercial,
            'operador' => $this->resolveOperator(),
            'razon_social' => $this->razon_social,
            'cif' => $this->cif,
            'tipo_empresa' => $this->tipo_empresa,
            'web' => $this->web,
            'observaciones' => $this->observaciones,
            'activo' => (bool) $this->activo,
        ];
    }

    private function resolveOperator(): ?string
    {
        $value = strtolower(trim((string) $this->nombre_comercial));

        foreach (['repsol', 'cepsa', 'bp', 'galp'] as $operator) {
            if ($value !== '' && str_contains($value, $operator)) {
                return $operator;
            }
        }

        return $value !== '' ? $value : null;
    }
}
