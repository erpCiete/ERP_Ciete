<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrabajoResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_trabajo'           => $this->id_trabajo,
            'numero_trabajo'       => $this->numero_trabajo,
            'id_empresa_cliente'   => $this->id_empresa_cliente,
            'id_estacion_servicio' => $this->id_estacion_servicio,
            'estado'               => $this->estado,
            'fecha_encargo'        => $this->fecha_encargo,
            'fecha_terminado'      => $this->fecha_terminado,
            'id_contexto'          => $this->id_contexto,

            $this->mergeWhen($this->id_contexto === 1, [
                'id_contrato' => $this->id_contrato,
                'categoria'   => $this->categoria,
                // Serializar relación solo si está cargada
                'contrato'    => new ContratoResource($this->whenLoaded('contrato')),
            ]),

            $this->mergeWhen($this->id_contexto === 2, [
                'id_tipo_documento' => $this->id_tipo_documento,
                'id_tipo_trabajo'   => $this->id_tipo_trabajo,
                'numero_aviso'      => $this->numero_aviso,
                'tipo_documento'    => new TipoDocumentoResource($this->whenLoaded('tipoDocumento')),
                'tipo_trabajo'      => new TipoTrabajoResource($this->whenLoaded('tipoTrabajo')),
            ]),

            'empresa'  => new ClienteResource($this->whenLoaded('empresa')),
            'estacion' => new EstacionResource($this->whenLoaded('estacion')),

            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            'can' => [
                'update'  => $request->user()->can('trabajos.editar', $this->resource),
                'delete'  => $request->user()->can('trabajos.eliminar', $this->resource),
                'restore' => $this->estado === 'cerrado' && $request->user()->hasRole('admin'),
            ],
        ];
    }
}