<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EstacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Mapeo del ID real de la BBDD
            'id' => $this->id_estacion_servicio, 
            
            // Mapeo EXACTO que exige el test de Puchol
            'nombre_estacion'       => $this->nombre, 
            'identificador_interno' => $this->codigo_estacion_interno, 
            'direccion_completa'    => $this->direccion,
            'cliente_id'            => $this->id_empresa_cliente,
            'poblacion'             => $this->poblacion,
            'provincia'             => $this->provincia,
            'activo'                => $this->activo,

            // Relación con Empresa Cliente (Repsol o Cepsa)
            'cliente_nombre' => $this->whenLoaded('empresaCliente', function () {
                // Ajusta 'nombre_comercial' al campo real que tengas en tu modelo Empresa
                return $this->empresaCliente->nombre_comercial ?? 'Cliente Desconocido'; 
            }),
        ];
    }
}