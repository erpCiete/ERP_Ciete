<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportacionResource extends JsonResource
{
    /**
     * Transforma el modelo de Importación a un array JSON estructurado.
     */
    public function toArray(Request $request): array
    {
        return [
            'id_importacion'      => $this->id_importacion,
            'id_contexto'         => $this->id_contexto,
            'tipo'                => $this->tipo,
            'archivo_original'    => $this->archivo_original,
            
            // Métricas
            'metricas' => [
                'total_filas'      => (int) $this->total_filas,
                'filas_importadas' => (int) $this->filas_importadas,
                'filas_con_error'  => (int) $this->filas_con_error,
                'filas_duplicadas' => (int) $this->filas_duplicadas,
            ],
            
            // Estado y progreso
            'estado'              => $this->estado,
            'version_importacion' => $this->version_importacion,
            
            // Fechas
            'started_at'          => optional($this->started_at)->format('Y-m-d H:i:s') ?: $this->started_at,
            'finished_at'         => optional($this->finished_at)->format('Y-m-d H:i:s') ?: $this->finished_at,
            'created_at'          => optional($this->created_at)->toIso8601String(),
            
            // Opcional: Si el usuario que lo subió está cargado en las relaciones
            'usuario'             => new UsuarioResource($this->whenLoaded('usuario')),
        ];
    }
}