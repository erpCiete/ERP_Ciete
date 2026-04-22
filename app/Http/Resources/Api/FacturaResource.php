<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    /**
     * Transforma el recurso en un array para la API.
     * Garantiza el tipado estricto para una integración fluida con React.
     */
    public function toArray(Request $request): array
    {
        return [
            // Identificadores
            'id_factura' => $this->id_factura,
            'id_contexto' => $this->id_contexto,
            'id_trabajo' => $this->id_trabajo,
            'id_empresa_cliente' => $this->id_empresa_cliente,

            // Numeración y Serie
            'numero_factura' => $this->numero_factura,
            'numero_factura_ccp' => $this->numero_factura_ccp, // Específico MOEVE
            'serie' => $this->serie,
            'orden_factura' => $this->orden_factura ? (int) $this->orden_factura : null, // Específico REPSOL (1 o 2)

            // Fechas formateadas para inputs tipo date
            'fecha_solicitud' => optional($this->fecha_solicitud)->format('Y-m-d') ?: $this->fecha_solicitud,
            'fecha_emision' => optional($this->fecha_emision)->format('Y-m-d') ?: $this->fecha_emision,
            'fecha_vencimiento' => optional($this->fecha_vencimiento)->format('Y-m-d') ?: $this->fecha_vencimiento,

            // Control Económico (Casting a float para evitar strings numéricos)
            'importe' => isset($this->importe) ? (float) $this->importe : 0.0,
            'base_imponible' => isset($this->base_imponible) ? (float) $this->base_imponible : 0.0,
            'iva' => isset($this->iva) ? (float) $this->iva : 0.0,
            'retencion' => isset($this->retencion) ? (float) $this->retencion : 0.0,
            'total' => isset($this->total) ? (float) $this->total : 0.0,

            // Estados y Datos de Empresa
            'estado' => $this->estado,
            'autofactura' => (bool) $this->autofactura,
            'sociedad' => $this->sociedad,
            'observaciones' => $this->observaciones,

            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relaciones Eager Loaded
            'trabajo' => new TrabajoResource($this->whenLoaded('trabajo')),
            
            // Mapeo de pedidos vinculados a través de la tabla pivote factura_pedidos
            'pedidos' => $this->whenLoaded('pedidos', function () {
                return $this->pedidos->map(function ($pedido) {
                    return [
                        'id_pedido' => $pedido->id_pedido,
                        'numero_pedido' => $pedido->numero_pedido,
                        'importe_aplicado' => isset($pedido->pivot->importe_aplicado) 
                                            ? (float) $pedido->pivot->importe_aplicado 
                                            : null,
                    ];
                });
            }),
        ];
    }
}