<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre_estacion' => $this->nombre, // <--- Esto es lo que el test busca
            'identificador_interno' => $this->codigo, // <--- Esto es lo que el test busca
            'direccion_completa' => $this->direccion,
            'cliente_id' => $this->cliente_id,
        ];
    }
}