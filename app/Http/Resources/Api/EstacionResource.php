<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstacionResource extends JsonResource
{
    public function toArray(Request $request): array {
    return [
        'id' => $this->id,
        'nombre' => $this->nombre,
        'codigo_estacion' => $this->codigo,
        'creado_el' => $this->created_at->format('d-m-Y'),
    ];
}
}